<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
  use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ConsultationPersoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index(Patient $patient = null)
{
    $user = Auth::user();
    $doctor = $user->doctor;

    Log::info('Accessing consultation perso index', [
        'user_id' => $user->id,
        'patient_id' => $patient?->id
    ]);

  
    // Affichage liste de patients pour le médecin
    $searchTerm = request()->input('search');

    $query = $doctor
        ? $doctor->patients()->with('user')
        : Patient::query()->whereNull('id'); // Vide si pas médecin

    if ($searchTerm) {
        $query->where(function ($q) use ($searchTerm) {
            $q->where('first_name', 'like', "%{$searchTerm}%")
              ->orWhere('last_name', 'like', "%{$searchTerm}%")
              ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                  $userQuery->where('email', 'like', "%{$searchTerm}%");
              });
        });
    }

    $myPatients = $query->paginate(4)
                        ->appends(request()->query());

    Log::info('Viewing consultation perso patient selection', [
        'user_id' => $user->id,
        'patient_count' => $myPatients->total(),
        'search_term' => $searchTerm,
    ]);

    return view('consultation_perso.index', compact('myPatients'));
}




public function selectPdf(Patient $patient)
{
    $user = Auth::user();
    Log::info('Utilisateur connecté :', ['user_id' => $user->id]);

    $doctor = $user->doctor;

    if (!$doctor) {
        Log::error('Aucun docteur lié à cet utilisateur.', ['user_id' => $user->id]);
        abort(403, 'Aucun docteur associé à cet utilisateur.');
    }

    Log::info('Docteur trouvé :', ['doctor_id' => $doctor->id]);

    $speciality = $doctor->specialities->first(); // Une seule spécialité
    if (!$speciality) {
        Log::error('Aucune spécialité trouvée pour ce médecin.', ['doctor_id' => $doctor->id]);
        abort(404, 'Aucune spécialité trouvée pour ce médecin.');
    }

    Log::info('Spécialité du médecin :', ['speciality_name' => $speciality->name]);

 $dossier = "consultation_perso/" . $speciality->name;
Log::info('Recherche des fichiers PDF dans :', ['dossier' => $dossier]);

if (!Storage::disk('public')->exists($dossier)) {
    Log::warning("Le dossier n'existe pas", ['dossier' => $dossier]);
    $pdfs = collect();
} else {
    $fichiers = Storage::disk('public')->files($dossier);
    Log::info("Fichiers trouvés :", $fichiers);

    $pdfs = collect($fichiers)
        ->filter(fn($file) => str_ends_with($file, '.pdf'))
        ->map(fn($file) => [
            'name' => basename($file),
            'url' => asset('storage/' . $file), // ← bon lien public
        ]);
}

    Log::info('Nombre de PDF trouvés : ' . $pdfs->count());

    return view('consultation_perso.select_pdf', compact('patient', 'pdfs', 'speciality'));
}
public function openPdf(Patient $patient, $pdf)
{
    $user = Auth::user();
    $doctor = $user->doctor;

    $speciality = $doctor->specialities->first();
    $path = "consultation_perso/" . $speciality->name . "/" . $pdf;

    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'Fichier introuvable');
    }

    $pdfUrl = asset("storage/" . $path);

    return view('consultation_perso.open_pdf', [
        'patient' => $patient,
        'pdfUrl' => $pdfUrl,
        'originalName' => $pdf
    ]);
}

public function saveFilledPdf(Request $request)
{
    $request->validate([
        'pdf_data' => 'required',
        'patient_id' => 'required|exists:patients,id',
        'original_name' => 'required|string',
    ]);

    $base64Data = $request->input('pdf_data');
    $decodedPdf = base64_decode(preg_replace('#^data:application/pdf;base64,#', '', $base64Data));

    $filename = 'form_rempli_' . Str::random(8) . '.pdf';
    $destinationPath = "patients/{$request->patient_id}/consultations/{$filename}";

    Storage::disk('public')->put($destinationPath, $decodedPdf);

    return response()->json([
        'success' => true,
        'filename' => $filename,
        'path' => $destinationPath
    ]);
}

}
