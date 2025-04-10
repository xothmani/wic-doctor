<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpDeskController extends Controller
{
    public function index()
    {
        // Affiche la vue du formulaire
        return view('helpdesk.index');
    }

    public function store(Request $request)
    {
        // Validation des données du formulaire
        $request->validate([
            'prenom' => 'required|string|max:255',
            'email' => 'required|email',
            'telephone' => 'required|string|max:15',
            'message' => 'required|string',
            'fichier' => 'nullable|file|mimes:jpg,png,pdf,docx', // Optionnel et limité à certains formats
        ]);

        // Traitement du fichier s'il existe
        if ($request->hasFile('fichier')) {
            $path = $request->file('fichier')->store('helpdesk_files'); // Stocke dans un répertoire 'helpdesk_files'
        }

        // Traitement des données ici (enregistrement dans une table, envoi de mail, etc.)

        return redirect()->back()->with('success', 'Votre demande a été envoyée avec succès!');
    }
}
