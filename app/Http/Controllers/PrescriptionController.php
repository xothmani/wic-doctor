<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\Medicament;

use App\Models\Consultation;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


use PDF; 
use App\Models\Analyse;
use App\Models\Radio;
use Illuminate\Support\Facades\Log;
use App\Mail\SendPrescriptionPdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\Models\MedicamentFrance;
use App\Models\Doctor;
class PrescriptionController extends Controller
{


    /**
     * Show the form for creating a new Prescription.
     *
     * @return \Illuminate\View\View
     */


     public function create(Request $request)
     {
         $consultation_id = $request->query('consultation_id');
         
         // Récupérer le médecin associé à l'utilisateur connecté
         $doctorId = auth()->user()->getDoctorId();
         $doctor = Doctor::find($doctorId);
     
         if (!$doctor) {
             Log::error('Médecin non trouvé pour cet utilisateur', ['user_id' => auth()->id()]);
             return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
         }
     
         // Récupérer l'adresse du médecin
         $userWithAddress = $doctor->user()->with('address')->first();
         $address = $userWithAddress->address ?? null;
     
         // Extraire le pays depuis l'adresse
         $pays = $address && $address->pays ? json_decode($address->pays, true) : null;
         $pays = isset($pays['fr']) ? strtolower($pays['fr']) : (is_array($pays) ? strtolower(reset($pays) ?: '') : ($pays ? strtolower($pays) : null));
     
         // Vérifier si le pays est la France
         $isFrance = $pays === 'france';
     
         // Sélectionner les médicaments en fonction du pays
         $medicaments = $isFrance ? MedicamentFrance::all() : Medicament::all();
     
         // Log des médicaments récupérés
         Log::info('Médicaments récupérés', [
             'pays' => $pays,
             'isFrance' => $isFrance,
             'medicaments' => $medicaments->toArray()
         ]);
     
         $analyses = Analyse::all(); 
         $radios = Radio::all(); 
         
         $customFields = [];      
         return view('prescriptions.create', compact('medicaments', 'analyses', 'radios', 'customFields', 'consultation_id', 'isFrance'));
        }
     
    /**
     * Store a newly created prescription in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Définir la validation conditionnelle
        $rules = [
            'date' => 'required|date',
            'observation' => 'nullable|string',
            'type' => 'required|string',
            'consultation_id' => 'required|exists:consultations,id',
        ];
    
        // Validation conditionnelle en fonction du type de traitement
        if ($request->input('type') === 'Médicament') {
            $rules['medicaments'] = 'required|array';
            $rules['medicaments.*.CODE_PCT'] = 'required';
            $rules['medicaments.*.dosage'] = 'required|string';
            $rules['medicaments.*.nb_de_jours'] = 'required|string';
            $rules['medicaments.*.duration_unit'] = 'required|string|in:jours,semaines,mois';
            $rules['medicaments.*.horaire'] = 'nullable|string';
            $rules['medicaments.*.nb_de_fois'] = 'required|string';
        } elseif ($request->input('type') === 'Analyse') {
            $rules['analyses'] = 'required|array';
            $rules['analyses.*.Code_Analyse'] = 'required|exists:analyses,Code_Analyse';
        } elseif ($request->input('type') === 'Radio') {
            $rules['radios'] = 'required|array';
            $rules['radios.*.Nom'] = 'required|exists:radios,Nom';
        } else {
            $rules['nom_traitement'] = 'required|array';
            $rules['nom_traitement.*'] = 'required|string';
        }
    
        // Validation des données entrantes
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $observation = strip_tags($request->input('observation'));
    
        $consultation = Consultation::with(['patient', 'user'])->findOrFail($request->input('consultation_id'));
    
        if (!$consultation->patient) {
            return redirect()->back()->withErrors(['patient' => 'No associated patient found for this consultation.'])->withInput();
        }
    
        $prescription = Prescription::create([
            'date' => $request->input('date'),
            'observation' => $observation,
            'type' => $request->input('type'),
            'consultation_id' => $consultation->id,
        ]);
    
        // Lier les médicaments à la prescription
        if ($request->input('type') === 'Médicament') {
            foreach ($request->medicaments as $medicamentData) {
                $isFrance = MedicamentFrance::where('name', $medicamentData['CODE_PCT'])->exists();
    
                if ($isFrance) {
                    // Si le médicament est de France, utiliser medicament_id
                    $medicament = MedicamentFrance::where('name', $medicamentData['CODE_PCT'])->first();
                    if (!$medicament) {
                        throw new \Exception("Médicament non trouvé dans la table fr_medicament : " . $medicamentData['CODE_PCT']);
                    }
    
                    // Insérer dans medicament_prescription avec medicament_id
                    DB::table('medicament_prescription')->insert([
                        'prescription_id' => $prescription->id,
                        'medicament_id' => $medicament->id, // Référence à fr_medicament
                        'dosage' => $medicamentData['dosage'],
                        'nb_de_jours' => $medicamentData['nb_de_jours'] . ' ' . $medicamentData['duration_unit'],
                        'horaire' => $medicamentData['horaire'] ?? null,
                        'nb_de_fois' => $medicamentData['nb_de_fois'] . ' ' . $medicamentData['frequency_unit'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Si le médicament n'est pas de France, utiliser medicament_CODE_PCT
                    $medicament = Medicament::where('CODE_PCT', $medicamentData['CODE_PCT'])->first();
                    if (!$medicament) {
                        throw new \Exception("Médicament non trouvé dans la table medicaments : " . $medicamentData['CODE_PCT']);
                    }
    
                    // Insérer dans medicament_prescription avec medicament_CODE_PCT
                    DB::table('medicament_prescription')->insert([
                        'prescription_id' => $prescription->id,
                        'medicament_CODE_PCT' => $medicament->CODE_PCT, // Référence à medicaments
                        'dosage' => $medicamentData['dosage'],
                        'nb_de_jours' => $medicamentData['nb_de_jours'] . ' ' . $medicamentData['duration_unit'],
                        'horaire' => $medicamentData['horaire'] ?? null,
                        'nb_de_fois' => $medicamentData['nb_de_fois'] . ' ' . $medicamentData['frequency_unit'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        // Lier les analyses à la prescription
        elseif ($request->input('type') === 'Analyse') {
            foreach ($request->analyses as $analyseData) {
                DB::table('analyse_prescription')->insert([
                    'prescription_id' => $prescription->id,
                    'Code_Analyse' => $analyseData['Code_Analyse'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // Lier les radios à la prescription
        elseif ($request->input('type') === 'Radio') {
            foreach ($request->radios as $radioData) {
                DB::table('radio_prescription')->insert([
                    'prescription_id' => $prescription->id,
                    'Nom' => $radioData['Nom'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // Lier un autre traitement à la prescription
        elseif ($request->input('nom_traitement')) {
            foreach ($request->input('nom_traitement') as $traitement) {
                DB::table('autres_traitements')->insert([
                    'prescription_id' => $prescription->id,
                    'nom_traitement' => $traitement,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    
        // Générer les deux PDF
        $pdfs = $this->generatePrescriptionPdf($prescription->id);
    
        // Créer le dossier pour la prescription
        $directory = public_path('storage/prescriptions/' . $prescription->id);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    
        // Noms des fichiers PDF
        $fileName = $consultation->patient->first_name . '_' . $consultation->patient->last_name . '-' . $prescription->created_at . '.pdf';
        $fileNameForMail = $consultation->patient->first_name . '_' . $consultation->patient->last_name . '-' . $prescription->created_at . '_for_mail.pdf';
    
        // Chemins des fichiers PDF
        $filePath = $directory . '/' . $fileName;
        $filePathForMail = $directory . '/' . $fileNameForMail;
    
        // Enregistrer les PDF
        file_put_contents($filePath, $pdfs['pdf']->output());
        file_put_contents($filePathForMail, $pdfs['pdfForMail']->output());
    
        // Stocker le chemin du PDF for mail dans la base de données
        $prescription->pdf = 'storage/prescriptions/' . $prescription->id . '/' . $fileName; // Chemin relatif pour le stockage
        $prescription->pdfForMail = 'storage/prescriptions/' . $prescription->id . '/' . $fileNameForMail; // Chemin relatif pour le stockage
    
        $prescription->save(); // Sauvegarder la mise à jour
    
        return redirect()->route('consultation.prescriptions', ['consultation' => $consultation->id])
                         ->with('success', 'Prescription enregistrée avec succès.');
    }
    
    public function generatePrescriptionPdf($prescriptionId)
    {
        // Récupérer la prescription et ses médicaments associés avec les informations de la consultation
        $prescription = Prescription::with(['medicaments', 'consultation.patient', 'consultation.user'])->findOrFail($prescriptionId);
        $consultation = $prescription->consultation;
    
        // Récupérer l'adresse de l'utilisateur (médecin) à partir de la table addresses
        $address = DB::table('addresses')->where('user_id', $consultation->user_id)->value('address');
    
        // Récupérer le numéro de téléphone et l'adresse du médecin
        $doctorAddress = $address;  
        $doctorPhone = $consultation->user->phone_number;
        $matriculeCNAM = DB::table('doctors')->where('user_id', $consultation->user_id)->value('matricule_CNAM');
        $diplome = DB::table('doctors')->where('user_id', $consultation->user_id)->value('diplome');
        $numOrdre = DB::table('doctors')->where('user_id', $consultation->user_id)->value('numOrdre');
        $speciality = DB::table('users as u')
            ->join('doctors as d', 'u.id', '=', 'd.user_id')
            ->join('doctor_specialities as ds', 'd.id', '=', 'ds.doctor_id')
            ->join('specialities as s', 'ds.speciality_id', '=', 's.id')
            ->where('u.id', $consultation->user_id)
            ->pluck('s.name')
            ->first();
    
        // Initialiser les tableaux des données
        $medicamentDataArray = [];
        $analyseDataArray = [];
        $radioDataArray = []; // Initialiser la variable pour éviter l'erreur
        $otherTreatments = [];
    
        // Préparer les données en fonction du type de prescription
        if ($prescription->type === 'Médicament') {
            // Récupérer les médicaments de France (medicament_id)
            $medicamentsFrance = DB::table('medicament_prescription')
                ->join('fr_medicament', 'medicament_prescription.medicament_id', '=', 'fr_medicament.id')
                ->where('medicament_prescription.prescription_id', $prescription->id)
                ->select(
                    'fr_medicament.name as nom_commercial',
                    'medicament_prescription.dosage',
                    'medicament_prescription.nb_de_fois',
                    'medicament_prescription.horaire',
                    'medicament_prescription.nb_de_jours'
                )
                ->get();
    
            // Récupérer les médicaments non français (medicament_CODE_PCT)
            $medicamentsNonFrance = DB::table('medicament_prescription')
                ->join('medicaments', 'medicament_prescription.medicament_CODE_PCT', '=', 'medicaments.CODE_PCT')
                ->where('medicament_prescription.prescription_id', $prescription->id)
                ->select(
                    'medicaments.NOM_COMMERCIAL as nom_commercial',
                    'medicament_prescription.dosage',
                    'medicament_prescription.nb_de_fois',
                    'medicament_prescription.horaire',
                    'medicament_prescription.nb_de_jours'
                )
                ->get();
    
            // Fusionner les deux listes de médicaments
            $medicamentDataArray = $medicamentsFrance->merge($medicamentsNonFrance)->map(function ($item) {
                return (array) $item; // Convertir chaque objet en tableau
            })->toArray();
                    } elseif ($prescription->type === 'Analyse') {
            $analyseDataArray = DB::table('analyses')
                ->join('analyse_prescription', 'analyses.Code_Analyse', '=', 'analyse_prescription.Code_Analyse')
                ->where('analyse_prescription.prescription_id', $prescription->id)
                ->select('analyses.*', 
                         'analyse_prescription.Code_Analyse as pivot_analyse_Code_Analyse')
                ->get()
                ->map(function ($analyse) {
                    return [
                        'Code_Analyse' => $analyse->pivot_analyse_Code_Analyse,
                    ];
                })
                ->toArray();
        } elseif ($prescription->type === 'Radio') {
            $radioDataArray = DB::table('radios')
                ->join('radio_prescription', 'radios.Nom', '=', 'radio_prescription.Nom')
                ->where('radio_prescription.prescription_id', $prescription->id)
                ->select('radios.Nom as pivot_radio_Nom')
                ->get()
                ->map(function ($radio) {
                    return [
                        'Nom' => $radio->pivot_radio_Nom,
                    ];
                })
                ->toArray();
        } else {
            $otherTreatments = DB::table('autres_traitements')
                ->where('prescription_id', $prescription->id)
                ->pluck('nom_traitement')
                ->toArray();
        }
    
        // Préparer les données pour le PDF
        $pdfData = [
            'date' => $prescription->date,
            'observation' => $prescription->observation,
            'user_name' => $consultation->user->name,
            'patient_name' => $consultation->patient->first_name . ' ' . $consultation->patient->last_name,
            'address' => $address,
            'doctor_address' => $doctorAddress,
            'doctor_phone' => $doctorPhone,
            'doctor_speciality' => $speciality,
            'medicaments' => $medicamentDataArray,
            'analyses' => $analyseDataArray,
            'radios' => $radioDataArray,
            'other_treatments' => $otherTreatments,
            'nombre_medicaments' => count($medicamentDataArray),
            'nombre_analyses' => count($analyseDataArray),
            'nombre_radios' => count($radioDataArray),
            'type' => $prescription->type,
            'matricule_cnam' => $matriculeCNAM,
            'diplome' => $diplome,
            'numOrdre' => $numOrdre,
        ];
    
       // Générer les deux PDF avec les deux vues différentes
    $pdf = PDF::loadView('prescriptions.pdf', $pdfData); // Vue 1
    $pdfForMail = PDF::loadView('prescriptions.pdfForMail', $pdfData); // Vue 2

    // Retourner les deux PDF
    return [
        'pdf' => $pdf,
        'pdfForMail' => $pdfForMail,
    ];
}

public function showDetails($prescriptionId)
{
    // Récupérer la prescription et les informations de la consultation
    $prescription = Prescription::with(['consultation.patient', 'consultation.user'])->findOrFail($prescriptionId);
    $consultation = $prescription->consultation;

    // Préparer les données des médicaments et autres traitements
    $medicamentDataArray = [];
    $analyseDataArray = [];
    $radioDataArray = [];
    $otherTreatments = [];

    // Si la prescription est de type 'Médicament', on récupère les médicaments
    if ($prescription->type === 'Médicament') {
        // Récupérer les médicaments de France (medicament_id)
        $medicamentsFrance = DB::table('medicament_prescription')
            ->join('fr_medicament', 'medicament_prescription.medicament_id', '=', 'fr_medicament.id')
            ->where('medicament_prescription.prescription_id', $prescription->id)
            ->select(
                'fr_medicament.name as nom_commercial',
                'medicament_prescription.dosage',
                'medicament_prescription.nb_de_fois',
                'medicament_prescription.horaire',
                'medicament_prescription.nb_de_jours'
            )
            ->get();

        // Récupérer les médicaments non français (medicament_CODE_PCT)
        $medicamentsNonFrance = DB::table('medicament_prescription')
            ->join('medicaments', 'medicament_prescription.medicament_CODE_PCT', '=', 'medicaments.CODE_PCT')
            ->where('medicament_prescription.prescription_id', $prescription->id)
            ->select(
                'medicaments.NOM_COMMERCIAL as nom_commercial',
                'medicament_prescription.dosage',
                'medicament_prescription.nb_de_fois',
                'medicament_prescription.horaire',
                'medicament_prescription.nb_de_jours'
            )
            ->get();

        // Fusionner les deux listes de médicaments
        $medicamentDataArray = $medicamentsFrance->merge($medicamentsNonFrance)->toArray();
    } elseif ($prescription->type === 'Analyse') {
        $analyseDataArray = DB::table('analyses')
            ->join('analyse_prescription', 'analyses.Code_Analyse', '=', 'analyse_prescription.Code_Analyse')
            ->where('analyse_prescription.prescription_id', $prescription->id)
            ->select('analyses.*', 
                     'analyse_prescription.prescription_id as pivot_prescription_id', 
                     'analyse_prescription.Code_Analyse as pivot_analyse_Code_Analyse', 
                     'analyse_prescription.created_at as pivot_created_at', 
                     'analyse_prescription.updated_at as pivot_updated_at')
            ->get()
            ->map(function ($analyse) {
                return [
                    'Code_Analyse' => $analyse->pivot_analyse_Code_Analyse,
                ];
            })
            ->toArray();
    } elseif ($prescription->type === 'Radio') {
        $radioDataArray = DB::table('radios')
            ->join('radio_prescription', 'radios.Nom', '=', 'radio_prescription.Nom')
            ->where('radio_prescription.prescription_id', $prescription->id)
            ->select('radios.*', 
                     'radio_prescription.prescription_id as pivot_prescription_id', 
                     'radio_prescription.Nom as pivot_radio_Nom', 
                     'radio_prescription.created_at as pivot_created_at', 
                     'radio_prescription.updated_at as pivot_updated_at')
            ->get()
            ->map(function ($radio) {
                return [
                    'Nom' => $radio->pivot_radio_Nom,
                ];
            })
            ->toArray();
    } else {
        // Si le type n'est pas "medicament", récupérer les autres traitements
        $otherTreatments = DB::table('autres_traitements')
            ->where('prescription_id', $prescription->id)
            ->pluck('nom_traitement')
            ->toArray();
    }

    // Récupérer les autres informations nécessaires
    $doctorAddress = DB::table('addresses')->where('user_id', $consultation->user_id)->value('address');
    $doctorPhone = $consultation->user->phone_number;
    $specialities = DB::table('doctor_specialities')
        ->join('specialities', 'doctor_specialities.speciality_id', '=', 'specialities.id')
        ->where('doctor_specialities.doctor_id', $consultation->user_id)
        ->pluck('specialities.name')
        ->toArray();

    // Préparer les données pour la vue JSON
    $responseData = [
        'date' => $prescription->date,
        'observation' => $prescription->observation,
        'user_name' => $consultation->user->name,
        'patient_name' => $consultation->patient->first_name . ' ' . $consultation->patient->last_name,
        'doctor_address' => $doctorAddress,
        'doctor_phone' => $doctorPhone,
        'doctor_specialities' => implode(', ', $specialities),
        'medicaments' => $medicamentDataArray,
        'analyses' => $analyseDataArray,
        'radios' => $radioDataArray,
        'other_treatments' => $otherTreatments,
        'type' => $prescription->type,  // Ajout du type pour la logique dans la vue
    ];

    // Retourner la réponse JSON
    return response()->json($responseData);
}


public function sendEmail(Request $request, $prescriptionId)
{
    // Récupérer la prescription
    $prescription = Prescription::with(['consultation.patient'])->findOrFail($prescriptionId);

    // Vérifier si une adresse e-mail a été fournie par l'utilisateur
    $email = $request->email ?: $prescription->consultation->patient->email;

    if (!$email) {
        return Redirect::back()->with('error', 'Aucune adresse e-mail fournie.');
    }

    if (!$prescription->pdfForMail) {
        Log::error("Aucune prescription PDF associée à cette prescription.");
        return Redirect::back()->with('error', "Aucune prescription PDF associée.");
    }

    $pdfPath = public_path($prescription->pdfForMail);

    if (!file_exists($pdfPath)) {
        Log::error("Le fichier PDF est introuvable : " . $pdfPath);
        return Redirect::back()->with('error', "Le fichier PDF n'existe pas.");
    }

    if (!is_readable($pdfPath)) {
        Log::error("Le fichier PDF n'est pas lisible : " . $pdfPath);
        return Redirect::back()->with('error', "Le fichier PDF n'est pas lisible.");
    }

    // Données pour l'e-mail
    $patientName = $prescription->consultation->patient->first_name . ' ' . $prescription->consultation->patient->last_name;
    $prescriptionDate = $prescription->date;

    // Envoyer l'e-mail
    Mail::to($email)
        ->send(new SendPrescriptionPdf($pdfPath, $patientName, $prescriptionDate));

    return Redirect::back()->with('success', 'La prescription a été envoyée par e-mail avec succès.');
}

}

    

    
    
    
