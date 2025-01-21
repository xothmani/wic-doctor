<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorUrgency;
use Carbon\Carbon;

class DoctorUrgencyController extends Controller
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

            // Valider les données envoyées
            $validated = $request->validate([
                'jour' => [
                    'required', 
                    'date_format:Y-m-d', 
                    function ($attribute, $value, $fail) {
                        if (Carbon::parse($value)->isBefore(Carbon::now()->startOfDay())) {
                            $fail('La date doit être égale ou supérieure à la date du jour.');
                        }
                    },
                ],
                'heurDebut' => 'required|date_format:H:i',
                'heurFin' => 'required|date_format:H:i|after:heurDebut',
            ]);

            Log::info("Validated data: ", $validated);

            // Préparer les données à enregistrer
            $data = [
                'doctor_id' => $doctorId,
                'jour' => Carbon::parse($validated['jour'])->toDateString(),
                'heurDebut' => $validated['heurDebut'],
                'heurFin' => $validated['heurFin'],
            ];

            // Enregistrer ou mettre à jour les urgences du médecin
            DoctorUrgency::updateOrCreate(
                ['doctor_id' => $doctorId, 'jour' => $data['jour']], 
                $data
            );

            Log::info("Doctor urgency saved for doctor ID: {$doctorId}");

            // Rediriger avec un message de succès
            return redirect()->route('urgency.index')->with('success', 'Urgence sauvegardée avec succès !');

        } catch (\Exception $e) {
            Log::error('Error saving urgency:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

       public function index()
    {
        try {
            $userId = Auth::id();
            Log::info("Retrieving doctor urgencies for user ID: {$userId}");

            $doctor = Doctor::where('user_id', $userId)->first();

            if (!$doctor) {
                Log::error("No doctor found for user ID: {$userId}");
                return redirect()->route('error.page')->with('error', 'Aucun médecin associé à cet utilisateur.');
            }
            $urgencies = DoctorUrgency::where('doctor_id', $doctor->id)
            ->where('jour', '>=', Carbon::today()->toDateString())  // Filtrer par date
            ->orderBy('jour', 'asc')
            ->get();

            return view('urgency.index', compact('urgencies'));
        } catch (\Exception $e) {
            Log::error('Error fetching doctor urgencies:', ['error' => $e->getMessage()]);
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

        // Trouver l'urgence à supprimer
        $urgency = DoctorUrgency::where('doctor_id', $doctor->id)->where('id', $id)->first();

        if (!$urgency) {
            Log::error("Urgency not found for doctor ID: {$doctor->id}, urgency ID: {$id}");
            return redirect()->route('error.page')->with('error', 'Urgence non trouvée.');
        }

        // Supprimer l'urgence
        $urgency->delete();
        Log::info("Urgency deleted for doctor ID: {$doctor->id}, urgency ID: {$id}");

        return redirect()->route('urgency.index')->with('success', 'Urgence supprimée avec succès !');
    } catch (\Exception $e) {
        Log::error('Error deleting urgency:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}

public function update(Request $request, $id)
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

        // Trouver l'urgence à modifier
        $urgency = DoctorUrgency::where('doctor_id', $doctor->id)->where('id', $id)->first();

        if (!$urgency) {
            Log::error("Urgency not found for doctor ID: {$doctor->id}, urgency ID: {$id}");
            return redirect()->route('error.page')->with('error', 'Urgence non trouvée.');
        }

        // Valider les données envoyées
        $validated = $request->validate([
            'jour' => [
                'required', 
                'date_format:Y-m-d', 
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->isBefore(Carbon::now()->startOfDay())) {
                        $fail('La date doit être égale ou supérieure à la date du jour.');
                    }
                },
            ],
            'heurDebut' => 'required|date_format:H:i',
            'heurFin' => 'required|date_format:H:i|after:heurDebut',
        ]);

        // Mettre à jour les données de l'urgence
        $urgency->jour = Carbon::parse($validated['jour'])->toDateString();
        $urgency->heurDebut = $validated['heurDebut'];
        $urgency->heurFin = $validated['heurFin'];

        $urgency->save();

        Log::info("Urgency updated for doctor ID: {$doctor->id}, urgency ID: {$id}");

        return redirect()->route('urgency.index')->with('success', 'Urgence modifiée avec succès !');
    } catch (\Exception $e) {
        Log::error('Error updating urgency:', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => $e->getMessage()]);
    }
}


}
