<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Prescription;
use App\Models\Medicament;

use App\Models\Consultation;
use App\Models\User;
use App\Models\Patient;
use App\Models\PrescriptionItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


use PDF; // Import the PDF facade
use App\Models\Analyse;
use App\Models\Radio;
use Illuminate\Support\Facades\Log;

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
        $medicaments = Medicament::all();
        $analyses = Analyse::all(); 
        $radios = Radio::all(); 
        
        $customFields = [];      
        return view('prescriptions.create', compact('medicaments', 'analyses','radios', 'customFields', 'consultation_id'));
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
            $rules['medicaments.*.CODE_PCT'] = 'required|exists:medicaments,CODE_PCT';
            $rules['medicaments.*.dosage'] = 'required|string';
            $rules['medicaments.*.nb_de_jours'] = 'required|string';
            $rules['medicaments.*.duration_unit'] = 'required|string|in:jours,semaines,mois';
            $rules['medicaments.*.horaire'] = 'required|string';
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
                $prescription->medicaments()->attach($medicamentData['CODE_PCT'], [
                    'dosage' => $medicamentData['dosage'],
                    'nb_de_jours' => $medicamentData['nb_de_jours'] . ' ' . $medicamentData['duration_unit'],
                    'horaire' => $medicamentData['horaire'],
                    'nb_de_fois' => $medicamentData['nb_de_fois'] . ' fois',
                ]);
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
                // Lier les radioq à la prescription
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
            foreach ($prescription->medicaments as $medicament) {
                $nom_commercial = DB::table('medicaments')
                    ->where('CODE_PCT', $medicament->pivot->medicament_CODE_PCT)
                    ->value('NOM_COMMERCIAL');
                
                $medicamentDataArray[] = [
                    'nom_commercial' => $nom_commercial,
                    'dosage' => $medicament->pivot->dosage,
                    'nb_de_fois' => $medicament->pivot->nb_de_fois,
                    'horaire' => $medicament->pivot->horaire,
                    'nb_de_jours' => $medicament->pivot->nb_de_jours,
                ];
            }
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
    
        // Charger la vue et générer le PDF avec les données
        $pdf = PDF::loadView('prescriptions.pdf', $pdfData);
        
        // Diffuser le PDF dans le navigateur
        return $pdf->stream('prescription_' . $prescription->id . '.pdf');
    }
    

public function showDetails($prescriptionId)
{
    // Récupérer la prescription et ses médicaments associés avec les informations de la consultation
    $prescription = Prescription::with(['medicaments', 'consultation.patient', 'consultation.user'])->findOrFail($prescriptionId);
    $consultation = $prescription->consultation;

    // Préparer les données des médicaments et autres traitements
    $medicamentDataArray = [];
    $analyseDataArray = [];
    $radioDataArray = [];
    $otherTreatments = [];

    // Si la prescription est de type 'Médicament', on récupère les médicaments
    if ($prescription->type === 'Médicament') {
        foreach ($prescription->medicaments as $medicament) {
            $nom_commercial = DB::table('medicaments')
                ->where('CODE_PCT', $medicament->pivot->medicament_CODE_PCT)
                ->value('NOM_COMMERCIAL');
            
            $medicamentDataArray[] = [
                'nom_commercial' => $nom_commercial,
                'dosage' => $medicament->pivot->dosage,
                'nb_de_fois' => $medicament->pivot->nb_de_fois,
                'horaire' => $medicament->pivot->horaire,
                'nb_de_jours' => $medicament->pivot->nb_de_jours,
            ];
        }
    }elseif ($prescription->type === 'Analyse') {
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
    }elseif ($prescription->type === 'Radio') {
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
    }  else {
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


}

    

    
    
    
