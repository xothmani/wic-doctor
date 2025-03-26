<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorVacation;
use Carbon\Carbon;

class DoctorVacationController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Log pour vérifier les données reçues
            Log::info('Request data:', $request->all());
    
            // Récupérer l'ID de l'utilisateur connecté
            $userId = Auth::id();
            Log::info("Retrieving doctor for user ID: {$userId}");
            
            // Récupérer le médecin associé à l'utilisateur
            $doctor = Doctor::where('user_id', $userId)->first();
            
            // Log si aucun médecin trouvé
            if (!$doctor) {
                Log::error("No doctor found for user ID: {$userId}");
                return redirect()->route('error.page')->with('error', 'Aucun médecin associé à cet utilisateur.');
            }
    
            // Si le médecin est trouvé, récupérer son ID
            $doctorId = $doctor->id;
            Log::info("Doctor ID: {$doctorId}");
            
            $validated = $request->validate([
                'type' => 'required|in:journée,période',
                'raison' => 'nullable|max:255',
                'dateDebut' => ['required', 'date_format:Y-m-d', function ($attribute, $value, $fail) {
                if (Carbon::parse($value)->isBefore(Carbon::now()->startOfDay())) {
                    $fail('La date de début doit être égale ou supérieure à la date du jour.');
                }
            }],                'dateFin' => 'nullable|date_format:Y-m-d|required_if:type,période|after:dateDebut',
            ]);
            
            if ($validated['type'] == 'journée' && !$validated['dateDebut']) {
                return back()->withErrors(['dateDebut' => 'La date de vacances est obligatoire pour la journée.']);
            }
    
            Log::info("Validated data: ", $validated);
    
            // Préparer les données à enregistrer
           $data = [
    'doctor_id' => $doctorId,
    'type' => $validated['type'],
    'raison' => $validated['raison'],
    'dateDebut' => Carbon::parse($validated['dateDebut'])->toDateString(),
    'dateFin' => $validated['type'] == 'période' 
        ? Carbon::parse($validated['dateFin'])->toDateString() 
        : Carbon::parse($validated['dateDebut'])->toDateString(), // Set to dateDebut if not 'période'
];
    
            // Enregistrer ou mettre à jour les vacances du médecin
            DoctorVacation::updateOrCreate(
                ['doctor_id' => $doctorId, 'dateDebut' => $data['dateDebut']], 
                $data
            );
    
            Log::info("Doctor vacation saved for doctor ID: {$doctorId}");
    
            // Rediriger avec un message de succès
            return redirect()->route('vacance.index')->with('success', 'Vacances sauvegardées avec succès !');
        
        }  catch (\Exception $e) {
            Log::error('Error saving holidays:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);        }
}

public function index()
{
    try {
        $userId = Auth::id();
        Log::info("Retrieving doctor vacances for user ID: {$userId}");

        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::error("No doctor found for user ID: {$userId}");
            return redirect()->route('error.page')->with('error', 'Aucun médecin associé à cet utilisateur.');
        }
        $vacances = DoctorVacation::where('doctor_id', $doctor->id)
      //  ->where('dateFin', '>=', Carbon::today()->toDateString())  // Filtrer par date
        ->orderBy('dateDebut', 'asc')
        ->get();

        return view('vacance.index', compact('vacances'));
    } catch (\Exception $e) {
        Log::error('Error fetching doctor vacances:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}


public function destroy($id)
{
    try {
        // Récupérer l'ID de l'utilisateur connecté
        $userId = Auth::id();
        Log::info("Retrieving doctor for user ID: {$userId}");

        // Récupérer le médecin associé à l'utilisateur
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::error("No doctor found for user ID: {$userId}");
            return redirect()->route('error.page')->with('error', 'Aucun médecin associé à cet utilisateur.');
        }

        // Trouver vacance à supprimer
        $vacance = DoctorVacation::where('doctor_id', $doctor->id)->where('id', $id)->first();

        if (!$vacance) {
            Log::error("Vacance not found for doctor ID: {$doctor->id}, vacance ID: {$id}");
            return redirect()->route('error.page')->with('error', 'Vacance non trouvée.');
        }

        // Supprimer vacance
        $vacance->delete();
        Log::info("Vacance deleted for doctor ID: {$doctor->id}, Vacance ID: {$id}");

        return redirect()->route('vacance.index')->with('success', 'Vacance supprimée avec succès !');
    } catch (\Exception $e) {
        Log::error('Error deleting Vacance:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}

public function update(Request $request, $id)
{
    try {
        $userId = Auth::id();
        Log::info("Retrieving doctor for user ID: {$userId}");

        $doctor = Doctor::where('user_id', $userId)->first();
        if (!$doctor) {
            Log::error("No doctor found for user ID: {$userId}");
            return redirect()->route('error.page')->with('error', 'Aucun médecin associé à cet utilisateur.');
        }

        $vacance = DoctorVacation::where('doctor_id', $doctor->id)->where('id', $id)->first();
        if (!$vacance) {
            Log::error("Vacance not found for doctor ID: {$doctor->id}, vacance ID: {$id}");
            return redirect()->route('error.page')->with('error', 'Vacance non trouvée.');
        }

        $validated = $request->validate([
            'type' => 'required|in:journée,période',
            'raison' => 'nullable|max:255',
            'dateDebut' => ['required', 'date_format:Y-m-d', function ($attribute, $value, $fail) {
                if (Carbon::parse($value)->isBefore(Carbon::now()->startOfDay())) {
                    $fail('La date de début doit être égale ou supérieure à la date du jour.');
                }
            }],
            'dateFin' => 'nullable|date_format:Y-m-d|required_if:type,période|after:dateDebut',
        ]);

        if ($validated['type'] == 'journée' && !$validated['dateDebut']) {
            return back()->withErrors(['dateDebut' => 'La date de vacances est obligatoire pour la journée.']);
        }

        Log::info("Validated data: ", $validated);

        // Mettre à jour les données de vacance
        $vacance->type = $validated['type'];
        $vacance->raison = $validated['raison'] ?? 'N/A';
        $vacance->dateDebut = $validated['dateDebut'];

        if ($validated['type'] === 'période') {
            $vacance->dateFin = $validated['dateFin'];
        } else {
            $vacance->dateFin = null;
        }

        $vacance->save();

        Log::info("Vacance updated for doctor ID: {$doctor->id}, vacance ID: {$id}");

        return redirect()->route('vacance.index')->with('success', 'Vacance modifiée avec succès !');
    } catch (\Exception $e) {
        Log::error('Error updating vacance:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}


}
