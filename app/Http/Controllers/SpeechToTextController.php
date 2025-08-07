<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DoctorPatients;
use App\Models\Consultation;
use Illuminate\Support\Facades\Log;

class SpeechToTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index()
    {
        return view('speechToText.index');
    }


public function renderRapportPatient()
{
    $user = auth()->user();
    $doctor = $user->doctor;

    if (!$doctor) {
        return redirect()->back()->with('error', 'Aucun médecin associé.');
    }

    $patients = DoctorPatients::where('doctor_id', $doctor->id)
        ->with('patient')
        ->get()
        ->mapWithKeys(function ($relation) use ($user) {
            $patient = $relation->patient;
            $fullName = $patient->first_name . ' ' . $patient->last_name;

            // Récupération des consultations
            $consultations = Consultation::where('patient_id', $patient->id)
                ->where('user_id', $user->id)
                ->orderBy('dateConsultation', 'desc')
                ->get();

            $historiqueMedical = '';
            foreach ($consultations as $consultation) {
                $historiqueMedical .= "<p><strong>📅 " . $consultation->dateConsultation . "</strong> : " . $consultation->motif . "</p>";
            }

            return [
                $patient->id => [
                    'id' => $patient->id,
                    'name' => $fullName,
                    'birthdate' => $patient->date_naissance->format('d/m/Y'),
                    'age' => $patient->age,
                    'avatar' => strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)),
                    'gender' => $patient->gender,
                    'weight' => $patient->weight,
                    'height' => $patient->height,
                    'groupe_sanguin' => $patient->groupe_sanguin,
                    'allergie' => $patient->allergie,
                    'antecedent' => $patient->antecedent,
                    'historique' => $historiqueMedical ?: '<p class="no-history">Aucun historique médical.</p>',
                ]
            ];
        });

    Log::debug('Patients data for rapportPatient:', $patients->toArray());

    return view('speechToText.rapportPatient', [
        'patientsData' => $patients
    ]);
}

  
}
