<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telesecretariat;
use App\Models\DoctorTelesecretariat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class DoctorTelesecretariatController extends Controller
{
    public function index()
    {
        // Récupérer l'ID de l'utilisateur connecté
        $userId = Auth::id();
    
        // Chercher cet ID dans la table Telesecretariat
        $telesecretariat = Telesecretariat::where('user_id', $userId)->first();
    
        if ($telesecretariat) {
            // Récupérer les médecins associés via DoctorTelesecretariat, avec jointure sur la table doctor
            $doctorTelesecretariats = DoctorTelesecretariat::with(['doctor', 'telesecretariat'])
                ->join('doctors', 'doctor_telesecretariat.doctor_id', '=', 'doctors.id') // Jointure sur la table doctor
                ->where('telesecretariat_id', $telesecretariat->id)
                ->orderBy('doctors.name', 'asc') // Trier par nom du médecin
                ->get();
    
            // Passer les données à la vue
            return view('doctor_telesecretariat.index', compact('doctorTelesecretariats'));
        } else {
            return view('doctor_telesecretariat.index', ['doctorTelesecretariats' => []]);
        }
    }
    
    
    
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