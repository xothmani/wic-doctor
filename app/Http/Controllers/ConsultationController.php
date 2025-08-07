<?php

namespace App\Http\Controllers;

use App\Models\Fiche;
use App\Models\Report;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Appointment;
use App\Models\AppointmentStatus;
use App\Models\Doctor;
use App\Models\DoctorPatients;
use Illuminate\Support\Facades\DB;
use App\DataTables\ConsultationDataTable;
use App\Http\Requests\CreateConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Repositories\ConsultationRepository;


use Illuminate\Http\JsonResponse;
use Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    private ConsultationRepository $consultationRepository;

    public function __construct(ConsultationRepository $consultationRepo)
    {
        parent::__construct();
        $this->consultationRepository = $consultationRepo;
    }

    public function create(Request $request)
{
    $patient_id = $request->query('patient_id');
    $selectedPatient = null;
    $customFields = '';
    $historiqueMedical = '';
    $showAlert = false;

    // Trouver le doctor correspondant à l'utilisateur connecté
    $doctor = Doctor::where('user_id', auth()->id())->first();

    if (!$doctor) {
        $showAlert = true;
        return view('consultations.create', compact('showAlert'));
    }

    if ($patient_id) {
        // Vérifier si le patient existe
        $selectedPatient = Patient::find($patient_id);
        if (!$selectedPatient) {
            $showAlert = true;
            return view('consultations.create', compact('showAlert'));
        }

        // Vérifier si l'association existe
        $isAssociated = DB::table('doctor_patients')
            ->where('doctor_id', $doctor->id)
            ->where('patient_id', $patient_id)
            ->exists();

        // Si pas encore associé, l'associer automatiquement
        if (!$isAssociated) {
            DB::table('doctor_patients')->insert([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient_id,
              
            ]);
        }

        // Préparer les données du patient
        $selectedPatient->full_name = $selectedPatient->first_name . ' ' . $selectedPatient->last_name;

        $consultations = Consultation::where('patient_id', $patient_id)
            ->where('user_id', auth()->id())
            ->orderBy('dateConsultation', 'desc')
            ->get();

        foreach ($consultations as $consultation) {
            $historiqueMedical .= "<p><strong>📅 " . $consultation->dateConsultation . "</strong> : " . $consultation->motif . "</p>";
        }
    } else {
        $showAlert = true;
        return view('consultations.create', compact('showAlert'));
    }

    return view('consultations.create', compact('selectedPatient', 'customFields', 'historiqueMedical', 'showAlert'));
}

    
    
    public function store(CreateConsultationRequest $request): RedirectResponse
    {
        $input = $request->all();
        $input['motif'] = strip_tags($request->input('motif'));
        $input['raison'] = strip_tags($request->input('raison'));
        $data['duree'] = $request->input('duree', 0); // Valeur par défaut 0 si non fournie
        
        $patient_id = $request->input('patient_id');
        $patient = Patient::find($patient_id);
    
        if (!$patient) {
            Flash::error('Patient non trouvé');
            return redirect()->back()->withInput();
        }
        
        // Mettre à jour les données du patient si l'option est activée
            $patient->update([
                'weight' => $request->input('weight'),
                'height' => $request->input('height'),
                'groupe_sanguin' => $request->input('groupe_sanguin'),
                'medical_history' => $request->input('medical_history'),
                'allergie' => $request->input('allergie'),
                'antecedent' => $request->input('antecedent')
            ]);
            
            \Log::info('Données patient mises à jour', ['patient_id' => $patient->id]);
    
    
        // Récupérer l'ID de l'utilisateur authentifié (le médecin)
        $user_id = auth()->id();
    
        // Vérifier si le médecin existe dans la table doctor
        $doctor = Doctor::where('user_id', $user_id)->first();
    
        if (!$doctor) {
            Flash::error('Médecin non trouvé.');
            return redirect()->back()->withInput();
        }
    
        // Vérifier si la fiche existe pour ce patient et cet utilisateur
        $fiche = Fiche::where('patient_id', $patient_id)
                    ->where('user_id', $user_id)
                    ->first();
    
        if (!$fiche) {
            // Si aucune fiche n'existe, en créer une
            $fiche = new Fiche([
                'patient_id' => $patient_id,
                'user_id' => $user_id,  // Associer la fiche à l'utilisateur authentifié
            ]);
    
            // Générer le code de la fiche avant de la sauvegarder
            $fiche->save();
    
            // Vérifiez si le code a bien été généré après la sauvegarde
            if (!$fiche->code) {
                Flash::error('Une erreur est survenue lors de la génération du code de la fiche.');
                return redirect()->back()->withInput();
            }
    
            // Enregistrer la relation dans la table doctor_patients
            // Vérifiez si la relation existe déjà
            $doctorPatient = DoctorPatients::where('patient_id', $patient_id)
                                          ->where('doctor_id', $doctor->id)  // Utilisation du doctor_id
                                          ->first();
    
            if (!$doctorPatient) {
                // Si la relation n'existe pas, l'ajouter
                DoctorPatients::create([
                    'patient_id' => $patient_id,
                    'doctor_id' => $doctor->id  // Associer à l'ID du médecin (doctor_id)
                ]);
                \Log::info('Relation doctor_patient ajoutée avec succès.', [
                    'patient_id' => $patient_id,
                    'doctor_id' => $doctor->id
                ]);
            }
        }
    
        // Associer le code de la fiche à la consultation
        $input['fiche_code'] = $fiche->code; 
        \Log::info('Fiche associée avec succès:', ['fiche_code' => $fiche->code]);
    
        try {
            // Créer la consultation et l'associer à la fiche du patient
            $consultation = $this->consultationRepository->create($input);
    
            // Chercher le rendez-vous correspondant au patient avec ce médecin ayant le statut "ready"
            $doctorId = auth()->user()->id;  // Récupère l'ID de l'utilisateur authentifié
            $appointment = Appointment::where('patient_id', $patient_id)
                ->whereHas('doctor', function ($query) use ($doctorId) {
                    $query->where('user_id', $doctorId);  // Compare avec l'user_id du médecin
                })
                ->whereHas('appointmentStatus', function ($query) {
                    $query->where('status', 'Ready');  // Recherche de l'état "Ready"
                })
                ->first();
    
            // Chercher le rendez-vous correspondant au patient via le user_id
            $patient = Patient::find($patient_id);
    
            if ($patient) {
                $user_id = $patient->user_id; // Récupérer le user_id depuis la table patients
    
                $appointment = Appointment::where('user_id', $user_id) // Recherche via user_id
                    ->whereHas('doctor', function ($query) {
                        $query->where('user_id', auth()->id()); // Médecin authentifié
                    })
                    ->whereHas('appointmentStatus', function ($query) {
                        $query->where('status', 'Ready'); // Recherche de l'état "Ready"
                    })
                    ->first();
                
                if ($appointment) {
                    // Mettre à jour le statut du rendez-vous à "Done"
                    $doneStatus = AppointmentStatus::where('status', 'Done')->first();
                    if ($doneStatus) {
                        $appointment->appointment_status_id = $doneStatus->id;
                        $appointment->save();
                        \Log::info('Statut du rendez-vous mis à jour avec succès:', ['appointment_id' => $appointment->id]);
                    } else {
                        \Log::warning('Statut "Done" introuvable. Aucune mise à jour effectuée.');
                    }
                } else {
                    \Log::info('Aucun rendez-vous "Ready" trouvé pour ce patient et ce médecin.');
                }
            } else {
                Flash::error('Patient introuvable.');
                return redirect()->back()->withInput();
            }
    
            // Retourner une réponse de redirection vers la liste des consultations
            Flash::success('Consultation créée avec succès.');
            return redirect()->route('prescriptions.create', ['consultation_id' => $consultation->id]);
    
        } catch (\Exception $e) {
            // En cas d'erreur, journaliser l'erreur et afficher un message
            \Log::error('Erreur lors de la création de la consultation:', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
    
            Flash::error('Une erreur est survenue lors de la création de la consultation.');
            return redirect()->back()->withInput();
        }
    }
    
    
    
    

    public function index(ConsultationDataTable $consultationDataTable): mixed
    {
        return $consultationDataTable->render('consultations.index');
    }

    public function show(int $id): RedirectResponse|View
    {
        $consultation = $this->consultationRepository->findWithoutFail($id);

        if (empty($consultation)) {
            Flash::error('Consultation not found');
            return redirect(route('consultations.index'));
        }

        return view('consultations.show')->with('consultation', $consultation);
    }



    public function showPrescriptions(Consultation $consultation)
{
    // Récupère toutes les prescriptions associées à la consultation
    $prescriptions = $consultation->prescriptions;

    // Affiche les prescriptions dans une vue
    return view('consultations.prescriptions', compact('prescriptions', 'consultation'));
}

   /**
     * Store a newly created report pdf file in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
public function addReport(Request $request) : JsonResponse
{
    // Validate input
    $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'doctor_id' => 'required|exists:doctors,user_id',
        'report' => 'required|file|mimes:pdf|max:2048', // PDF file max 2MB
        'title' => 'required|string|max:255',
        'description' => 'required|string'
    ]);

    $patient_id = $request->input('patient_id');
    $user_id = $request->input('doctor_id');

    // Log the input parameters for debugging
    \Log::info('Adding report with patient_id: ' . $patient_id . ' and doctor_id: ' . $user_id);

    // Find Fiche
    $fiche = Fiche::where('patient_id', $patient_id)
                ->where('user_id', $user_id)
                ->first();

    if (!$fiche) {
        \Log::warning('Fiche not found for patient_id: ' . $patient_id . ' and doctor_id: ' . $user_id);
        return $this->sendError('Fiche Patient not found');
    }

    \Log::info('Fiche found with id: ' . $fiche->code);

    try {
        // Handle PDF upload
        if ($request->hasFile('report')) {
            $file = $request->file('report');
            $filename = $request->input('title') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/reports', $filename); // Save to storage

            \Log::info('File uploaded successfully, file path: ' . $path);

            // Create new report entry
            $report = new Report([
                'fiche_id' => $fiche->code,
                'file_path' => str_replace('public/', 'storage/', $path),
                'title' => $request->input('title'),
                'description' => $request->input('description')
            ]);

            \Log::info('Report data: ', $report->toArray());
            $report->save();

            \Log::info('Report added successfully:', ['fiche_id' => $fiche->code, 'report_id' => $report->id]);
        }

        return $this->sendResponse($report->toArray(), 'Report Added successfully');

    } catch (\Exception $e) {
        \Log::error('Error while adding report:', [
            'message' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ]);

        return $this->sendError('Error while adding report: ' . $e->getMessage());
    }
}
  

}

