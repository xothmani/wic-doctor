<?php
namespace App\Http\Controllers;

use App\Models\Fiche;
use App\Models\User;
use App\Models\Patient;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FicheController extends Controller
{
    public function show($id)
{
    $userId = auth()->id();
    
    // Charger la fiche avec les relations nécessaires
    $fiche = Fiche::with(['patient', 'consultations' => function($query) {
        $query->orderBy('dateConsultation', 'desc');
    }])
    ->where('patient_id', $id)
    ->where('user_id', $userId)
    ->first();

    if (!$fiche) {
        return redirect()->back()->with('alert', trans('lang.no_fiche_found'));
    }

    $patient = $fiche->patient;
    
    // Récupérer l'utilisateur assigné si existant
    $assignedUser = null;
    if ($patient->assigned_user_id) {  // Modifié: utiliser assigned_user_id au lieu de user_id
        $assignedUser = User::find($patient->assigned_user_id);
    }

    // Récupération de l'assurance
    $nomAssurance = DB::table('assurances')
        ->where('id', $fiche->patient->assurance)
        ->value('nom');

    return view('fiche.show', compact(
        'fiche',
        'nomAssurance',
        'patient',
        'assignedUser'
    ));
}
}