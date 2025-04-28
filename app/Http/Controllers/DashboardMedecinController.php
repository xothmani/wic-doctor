<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
class DashboardMedecinController extends Controller
{

    public function index()
    {
        $user = Auth::user(); 
        $doctor = $user->doctor; 

        $user->last_login_at = Carbon::parse($user->last_login_at);
    
        $currentMonth = Carbon::now()->month;
        $consultationCount = Consultation::where('user_id', $user->id)
                                         ->whereMonth('created_at', $currentMonth)
                                         ->count();
    
        $today = Carbon::today();
        $appointmentsToday = Appointment::where('doctor_id', $doctor->id)
                                        ->whereDate('start_at', $today)
                                        ->count();
    
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $endOfWeek = Carbon::now()->endOfWeek();     
    
        $appointmentsThisWeek = Appointment::where('doctor_id', $doctor->id)
                                           ->whereBetween('start_at', [$startOfWeek, $endOfWeek])
                                           ->count();
    
        //  Récupérer tous les patients de ce médecin
        $patients = DB::table('doctor_patients')
                    ->join('patients', 'doctor_patients.patient_id', '=', 'patients.id')
                    ->where('doctor_patients.doctor_id', $doctor->id)
                    ->select('patients.age')
                    ->get();
    
        $totalPatients = $patients->count();
    
        // Initialiser les compteurs
        $ageGroups = [
            '0-11 mois' => 0,
            '1-17' => 0,
            '18-39' => 0,
            '40-59' => 0,
            '60-99' => 0,
            'Non spécifié' => 0,
        ];
        
    
        foreach ($patients as $patient) {
            $ageRaw = $patient->age;
        
            if (is_null($ageRaw) || $ageRaw === '') {
                $ageGroups['Non spécifié']++;
                continue;
            }
        
            // Normaliser la chaîne (retirer les espaces, mettre en minuscule)
            $ageStr = strtolower(trim($ageRaw));
        
            // Tester si c’est un âge en mois (avec "mois")
            if (preg_match('/^([0-9]+)\s*(mois)$/i', $ageStr, $matches)) {
                $mois = (int)$matches[1];
                if ($mois >= 0 && $mois <= 11) {
                    $ageGroups['0-11 mois']++;
                } else {
                    $ageGroups['Non spécifié']++;
                }
            }
            // Tester si c’est un âge en années (avec "ans" ou "an")
            elseif (preg_match('/^([0-9]+)\s*(an(s)?)$/i', $ageStr, $matches)) {
                $years = (int)$matches[1];
        
                if ($years >= 1 && $years <= 17) {
                    $ageGroups['1-17']++;
                } elseif ($years >= 18 && $years <= 39) {
                    $ageGroups['18-39']++;
                } elseif ($years >= 40 && $years <= 59) {
                    $ageGroups['40-59']++;
                } elseif ($years >= 60 && $years <= 99) {
                    $ageGroups['60-99']++;
                } else {
                    $ageGroups['Non spécifié']++;
                }
            }
            // Tester si c’est un âge en nombre simple (directement un nombre)
            elseif (is_numeric($ageStr)) {
                $age = (int)$ageStr;
        
                if ($age >= 1 && $age <= 17) {
                    $ageGroups['1-17']++;
                } elseif ($age >= 18 && $age <= 39) {
                    $ageGroups['18-39']++;
                } elseif ($age >= 40 && $age <= 59) {
                    $ageGroups['40-59']++;
                } elseif ($age >= 60 && $age <= 99) {
                    $ageGroups['60-99']++;
                } else {
                    $ageGroups['Non spécifié']++;
                }
            }
            // Si ce n’est ni mois ni nombre valide
            else {
                $ageGroups['Non spécifié']++;
            }
        }
    
        $ageCounts = $ageGroups; // <-- tableau des nombres
        // Convertir en pourcentages
        $agePercentages = [];
        foreach ($ageGroups as $group => $count) {
            $agePercentages[$group] = $totalPatients > 0 ? round(($count / $totalPatients) * 100) : 0;
        }

        $patientsGender = DB::table('doctor_patients')
    ->join('patients', 'doctor_patients.patient_id', '=', 'patients.id')
    ->where('doctor_patients.doctor_id', $doctor->id)
    ->select('patients.gender')
    ->get();

        $genderCounts = [
            'Homme' => 0,
            'Femme' => 0,
        ];

        foreach ($patientsGender as $patient) {
            $sexe = strtolower(trim($patient->gender));
            if ($sexe === 'homme') {
                $genderCounts['Homme']++;
            } elseif ($sexe === 'femme') {
                $genderCounts['Femme']++;
            }
        }

        $totalGender = $genderCounts['Homme'] + $genderCounts['Femme'];

        $genderPercentages = [
            'Homme' => $totalGender > 0 ? round(($genderCounts['Homme'] / $totalGender) * 100) : 0,
            'Femme' => $totalGender > 0 ? round(($genderCounts['Femme'] / $totalGender) * 100) : 0,
        ];

// Date d'aujourd'hui
$today = Carbon::today();

// Récupérer tous les statuts
$statuses = DB::table('appointment_statuses')->get()->keyBy('id');

// Récupérer les RDV du jour groupés par statut
$statusCountsToday = Appointment::where('doctor_id', $doctor->id)
    ->whereDate('start_at', $today)
    ->select('appointment_status_id', DB::raw('count(*) as total'))
    ->groupBy('appointment_status_id')
    ->get();

// Total des RDV du jour
$totalAppointmentsToday = $statusCountsToday->sum('total');
// Initialisation
$statusDataToday = [
    'Reçu' => ['count' => 0, 'percent' => 0],
    'Prêt' => ['count' => 0, 'percent' => 0],
    'Annulé' => ['count' => 0, 'percent' => 0],
    'Terminé' => ['count' => 0, 'percent' => 0],
];

// Calculs
foreach ($statusCountsToday as $item) {
    $statusName = strtolower($statuses[$item->appointment_status_id]->status);

    switch ($statusName) {
        case 'received':
            $statusDataToday['Reçu']['count'] = $item->total;
            break;
        case 'ready':
            $statusDataToday['Prêt']['count'] = $item->total;
            break;
        case 'failed':
            $statusDataToday['Annulé']['count'] = $item->total;
            break;
        case 'done':
            $statusDataToday['Terminé']['count'] = $item->total;
            break;
    }
}

// Calculer les pourcentages
foreach ($statusDataToday as &$data) {
    $data['percent'] = $totalAppointmentsToday > 0 ? round(($data['count'] / $totalAppointmentsToday) * 100) : 0;
}
$topMotifs = DB::table('appointments as a')
    ->join('pattern as p', 'a.motif_id', '=', 'p.id')
    ->select('p.id as motif_id', 'p.nom as motif_nom', DB::raw('COUNT(a.id) as total_rendezvous'))
    ->where('a.doctor_id', $doctor->id)
    ->groupBy('p.id', 'p.nom')
    ->orderByDesc('total_rendezvous')
    ->limit(5)
    ->get()
    ->map(function ($item) {
        $decoded = json_decode($item->motif_nom, true);
        $item->motif_nom = $decoded['fr'] ?? $item->motif_nom;
        return (array)$item; // Convertir en tableau
    })
    ->toArray(); // Convertir la collection en tableau
// Log dans le fichier laravel.log
//Log::info('Top 5 motifs pour le docteur ' . $doctor->id, ['motifs' => $topMotifs]);


$appointmentsPerMonth = Appointment::where('doctor_id', $doctor->id)
    ->whereYear('start_at', now()->year)
    ->selectRaw('MONTH(start_at) as month, COUNT(*) as total')
    ->groupBy('month')
    ->orderBy('month')
    ->get();

// Convertir en tableau associatif avec les 12 mois
$monthlyAppointments = array_fill(1, 12, 0); // initialise de 1 à 12 à 0
foreach ($appointmentsPerMonth as $item) {
    $monthlyAppointments[(int)$item->month] = $item->total;
}

// Pour l'envoyer à la vue, on encode les données en JSON
$monthlyAppointmentsLabels = json_encode([
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
]);
$monthlyAppointmentsData = json_encode(array_values($monthlyAppointments));

    
        return view('dashboardmedecin.index', compact(
            'user',
            'doctor',
            'consultationCount',
            'appointmentsToday',
            'appointmentsThisWeek',
            'totalPatients',
            'agePercentages',
            'ageCounts',
            'genderPercentages',
            'genderCounts',
            'statusDataToday',
            'topMotifs',
            'monthlyAppointmentsLabels',
            'monthlyAppointmentsData'




        ));
    }
}