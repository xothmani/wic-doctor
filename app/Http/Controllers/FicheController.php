<?php
namespace App\Http\Controllers;

use App\Models\Fiche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FicheController extends Controller
{
    public function show($id)
    {
        // Récupérer l'utilisateur connecté
        $userId = auth()->id();
    
        // Charger la fiche et inclure la relation 'patient' et 'consultations'
        $fiche = Fiche::with(['patient', 'consultations' => function($query) {
            // Trier les consultations par date
            $query->orderBy('dateConsultation', 'desc');
        }])
        ->where('patient_id', $id)  // Récupérer la fiche du patient
        ->where('user_id', $userId) // Ajouter le filtre pour l'utilisateur connecté
        ->first();
    
        // Vérifier si la fiche est trouvée
        if (!$fiche) {
            return redirect()->back()->with('alert', trans('lang.no_fiche_found'));
        }
    
        // Récupérer le nom de l'assurance du patient
        $assuranceNom = DB::select(
            'SELECT a.nom 
             FROM assurances a 
             JOIN patients p ON p.assurance = a.id 
             WHERE p.assurance = ?', 
            [$fiche->patient->assurance] // Passer l'ID de l'assurance du patient
        );
    
        // Vérifier si l'assurance est trouvée
        $nomAssurance = !empty($assuranceNom) ? $assuranceNom[0]->nom : null;
    
        // Filtrer les consultations de cette fiche spécifiquement en fonction de fiche_code
        $consultationsFiche = $fiche->consultations->where('fiche_code', $fiche->fiche_code);
    
        // Passer la fiche, les consultations et le nom de l'assurance à la vue
        return view('fiche.show', compact('fiche', 'consultationsFiche', 'nomAssurance'));
    }
    
}
