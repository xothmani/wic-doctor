<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telesecretariat;
use App\Models\DoctorTelesecretariat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DoctorTelesecretariatController extends Controller
{
    public function create()
{

    return view('doctor_telesecretariat.create'); 
}
public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    // Rechercher l'utilisateur par adresse email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return redirect()->route('doctor_telesecretariat.create')
            ->with('error', 'Utilisateur non trouvé.');
    }

    // Vérifier si cet utilisateur est un télésécrétariat
    $telesecretariat = Telesecretariat::where('user_id', $user->id)->first();

    if (!$telesecretariat) {
        return redirect()->route('doctor_telesecretariat.create')
            ->with('error', 'Cet utilisateur n\'est pas lié à un télésécrétariat.');
    }

    // Récupérer l'ID du médecin connecté
    $doctor = Auth::user()->doctor;
    if (!$doctor) {
        return redirect()->route('doctor_telesecretariat.create')
            ->with('error', 'Vous n\'êtes pas enregistré comme médecin.');
    }

    // Vérifier si l'association existe déjà
    $exists = DoctorTelesecretariat::where('doctor_id', $doctor->id)
        ->where('telesecretariat_id', $telesecretariat->id)
        ->exists();

    if ($exists) {
        return redirect()->route('doctor_telesecretariat.create')
            ->with('error', 'Cette association existe déjà.');
    }

    // Créer l'association
    $doctorTelesecretariat = new DoctorTelesecretariat();
    $doctorTelesecretariat->doctor_id = $doctor->id;
    $doctorTelesecretariat->telesecretariat_id = $telesecretariat->id;
    $doctorTelesecretariat->save();

    return redirect()->route('doctor_telesecretariat.create')
        ->with('success', 'Télésécrétariat ajouté avec succès.');
}


}