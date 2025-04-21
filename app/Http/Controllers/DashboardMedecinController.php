<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Consultation;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;

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
    
        // 🧠 Récupérer tous les patients de ce médecin
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

    
        return view('dashboardmedecin.index', compact(
            'user',
            'doctor',
            'consultationCount',
            'appointmentsToday',
            'appointmentsThisWeek',
            'totalPatients',
            'agePercentages',
            'ageCounts' 
        ));
    }
    
}
