<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Doctor;
use App\Models\DoctorTag;

class DoctorTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }
    
        // Récupérer le médecin associé à l'utilisateur connecté
        $doctor = Doctor::where('user_id', $user->id)->first();
    
        if (!$doctor) {
            return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
        }
    
        // Récupérer les spécialités du médecin
        $specialities = $doctor->specialities;
    
        if ($specialities->isEmpty()) {
            return response()->json(['error' => 'Aucune spécialité trouvée pour ce médecin'], 400);
        }
    
        // Récupérer les tags associés à chaque spécialité
        $tags = Tag::whereIn('speciality_id', $specialities->pluck('id'))->get();
         // Transformer les noms des tags pour afficher uniquement la valeur en français
        $tags->transform(function ($tag) {
            $name = json_decode($tag->name, true);
            $tag->name = $name['fr'] ?? 'Non spécifié';
            return $tag;
        });
    
        // Récupérer les tags sélectionnés par le médecin (via la table doctor_tag)
        $doctorTags = DoctorTag::where('doctor_id', $doctor->id)->pluck('tag_id')->toArray();
    
        // Retourner la vue avec les tags
        return view('doctor_tag.index', compact('tags', 'doctor', 'doctorTags'));
    }
    
    

/**
 * Store the selected tags for a doctor.
 */
public function store(Request $request)
{
    // Valider les données reçues
    $request->validate([
        'tags' => 'nullable|array', // Permet de ne pas avoir d'étiquette
        'tags.*' => 'exists:tags,id', // Vérifie que chaque tag existe
    ]);

    // Récupérer le médecin connecté
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Utilisateur non connecté'], 401);
    }

    // Récupérer le médecin associé à l'utilisateur connecté
    $doctor = Doctor::where('user_id', $user->id)->first();
    
    if (!$doctor) {
        return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
    }

    // Supprimer tous les tags précédemment associés
    DoctorTag::where('doctor_id', $doctor->id)->delete();

    // Si des tags sont envoyés dans le formulaire, les ajouter
    if ($request->has('tags') && !empty($request->tags)) {
        $tags = Tag::whereIn('id', $request->tags)->get();
        
        foreach ($tags as $tag) {
            DoctorTag::create([
                'doctor_id' => $doctor->id,
                'tag_id' => $tag->id,
            ]);
        }

        // Mettre à jour le champ pourcentage_tags du médecin à 20
        $doctor->update([
            'pourcentage_tags' => 20,
        ]);
    } else {
        // Si aucun tag n'est sélectionné, mettre à jour pourcentage_tags à 0
        $doctor->update([
            'pourcentage_tags' => 0,
        ]);
    }
    
    return redirect()->route('doctor_tag.index');
}

    
    
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
