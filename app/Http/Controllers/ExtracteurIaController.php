<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExtracteurIaController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $doctor = $user->doctor;

    if (!$doctor) {
        return redirect()->back()->with('error', 'Aucun médecin associé.');
    }

return view('extracteur_ia.index', [
    'doctorId' => $doctor->id,
]);
}

}
