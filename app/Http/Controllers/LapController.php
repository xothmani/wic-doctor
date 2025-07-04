<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorPatients;
class LapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();
    $doctor = $user->doctor;

    if (!$doctor) {
        return redirect()->back()->with('error', 'Aucun médecin associé.');
    }

    // On récupère les patients avec leurs informations
    $patients = DoctorPatients::where('doctor_id', $doctor->id)
        ->with('patient') // doit contenir les champs first_name et last_name
        ->get();

    return view('Lap.index', compact('patients'));
}

  
}
