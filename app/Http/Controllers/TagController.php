<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\TagDataTable;
use App\Models\Speciality; 
use App\Models\Tag; 

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TagDataTable $dataTable, $id = null)
    {
        $tag = $id ? Tag::findOrFail($id) : null;
        $specialities = Speciality::all();
        return $dataTable->render('tags.index', compact('specialities', 'id', 'tag'));
    }
    
    


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
     //
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:255',

            'speciality' => 'required|exists:specialities,id', // Assurez-vous que l'ID de spécialité existe
        ]);
    
        // Création du tag avec les données validées
        $tag = new \App\Models\Tag();
        $tag->name = json_encode(['fr' => $validated['name']], JSON_UNESCAPED_UNICODE); // Stocker en format JSON avec "fr" comme clé
        $tag->country = $validated['country'];
        $tag->speciality_id = $validated['speciality'];
        $tag->save(); // Sauvegarde du tag dans la base de données
    
        // Redirection vers la page de liste des tags avec un message de succès
        return redirect()->route('tags.index')->with('success', 'Étiquette créé avec succès');
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
    public function edit($id)
    {
        $tag = Tag::find($id);
        
        if (!$tag) {
            return response()->json(['message' => 'Tag non trouvé'], 404);
        }
    
        // Décoder le champ `name` pour récupérer la valeur "fr"
        $name = json_decode($tag->name, true);
        $nameFr = $name['fr'] ?? null; // Récupérer la valeur avec la clé "fr" ou `null` si elle n'existe pas
    
        $specialities = Speciality::all(); // Récupérer toutes les spécialités
    
        return response()->json([
            'name' => $nameFr, // Retourner la valeur spécifique à "fr"
            'country' => $tag->country,
            'speciality_id' => $tag->speciality_id,  // Assurez-vous de renvoyer l'ID de la spécialité
            'specialities' => $specialities // Envoyer toutes les spécialités disponibles
        ]);
    }
    
    
    /**
     * Update the specified resource in storage.
     */
 // Dans le contrôleur TagController.php

public function update(Request $request, $id)
{
    // Validation des données
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'speciality_id' => 'required|exists:specialities,id', // Assurez-vous que l'ID de spécialité existe
    ]);
    
    // Trouver le tag à mettre à jour
    $tag = Tag::findOrFail($id);
    
    // Mise à jour des données
    $tag->name = json_encode(['fr' => $validated['name']], JSON_UNESCAPED_UNICODE); // Stocker en format JSON avec "fr" comme clé
    $tag->country = $validated['country'];

    $tag->speciality_id = $validated['speciality_id'];
    $tag->save(); // Sauvegarder les modifications
    
    // Retourner à la liste des tags avec un message de succès
    return redirect()->route('tags.index')->with('success', 'Tag mis à jour avec succès');
}

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Trouver le tag avec l'ID fourni
        $tag = \App\Models\Tag::findOrFail($id);
        
        // Supprimer le tag
        $tag->delete();
    
        // Redirection vers la liste des tags avec un message de succès
        return redirect()->route('tags.index')->with('success', 'Étiquette supprimé avec succès');
    }
    
}