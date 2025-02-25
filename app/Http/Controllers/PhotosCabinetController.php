<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\PhotosCabinetDataTable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Doctor;

class PhotosCabinetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PhotosCabinetDataTable $photosCabinetDataTable): mixed
    {
        return $photosCabinetDataTable->render('photos_cabinet.index');
    }


    /**
     * Display the specified resource.
     */

    
     public function show(string $id)
     {
         // Définir le chemin du dossier où les images sont stockées
         $path = "doctors/{$id}/cabinet/en_attente";
         
         // Récupérer toutes les images dans le dossier
         $files = Storage::disk('public')->files($path);
         
         // Générer les URLs des images
         $imageUrls = array_map(function ($file) {
             return Storage::url($file);
         }, $files);
         
         // Récupérer les informations du docteur (ici, on suppose que vous avez un modèle 'Doctor')
         $doctor = Doctor::find($id); // Assurez-vous que vous avez une table 'doctors' ou une table appropriée
         $doctorName = $doctor ? $doctor->name : 'Docteur Inconnu'; // Utilisation d'une valeur par défaut si le docteur n'est pas trouvé
         
         // Retourner la vue avec les images et le nom du docteur
         return view('photos_cabinet.show', compact('imageUrls', 'id', 'doctorName'));
     }
  
     public function accept(Request $request)
     {
         // Log the incoming request data
         \Log::info('Incoming request:', $request->all());
         
         // Validate the incoming data
         $validatedData = $request->validate([
             'doctorId' => 'required|integer', // Ensure doctorId is an integer
             'imageName' => 'required|string'  // Ensure imageName is a string
         ]);
     
         // Log the validated data
         \Log::info('Doctor ID: ' . $request->input('doctorId') . ', Image Name: ' . $request->input('imageName'));
         
         $doctorId = $request->input('doctorId');
         $imageName = $request->input('imageName');
         $sourcePath = "public/doctors/{$doctorId}/cabinet/en_attente/{$imageName}";
         $destinationPath = "public/doctors/{$doctorId}/cabinet/accepte/{$imageName}";
         
         // Vérifier si l'image existe dans le dossier 'en_attente'
         if (Storage::exists($sourcePath)) {
             // Déplacer l'image vers le dossier 'accepte'
             Storage::move($sourcePath, $destinationPath);
     
             // Récupérer le docteur à partir de son ID
             $doctor = Doctor::find($doctorId);
     
             if ($doctor) {
                 // Vérifier si la colonne cabinet_photo contient déjà des images
                 $existingImages = $doctor->cabinet_photo;
                 
                 // Ajouter le nom de la nouvelle image, séparée par une virgule si nécessaire
                 if ($existingImages) {
                     // Ajouter le nouveau nom d'image à la liste existante, en l'ajoutant à la fin
                     $doctor->cabinet_photo = $existingImages . '/' . $imageName;
                 } else {
                     // Si la colonne est vide, on l'initialise avec le nom de l'image
                     $doctor->cabinet_photo = $imageName;
                 }
                 // Mettre à jour purcentage_cabinet si ce n'est pas déjà 10
            if ($doctor->pourcentage_cabinet !== 10) {
                $doctor->pourcentage_cabinet = 10;
            }
     
                 // Sauvegarder les modifications dans la base de données
                 $doctor->save();
             }
     
                    // Stocker un message de succès dans la session
                    return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                    ->with('success', 'Image déplacée vers le dossier "accepte" et enregistrée avec succès.');
                } else {
                // Stocker un message d'erreur dans la session
                return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                    ->with('error', 'L\'image n\'a pas été trouvée dans le dossier "en_attente".');
                }
}
public function rejet(Request $request)
{
    // Log the incoming request data
    \Log::info('Incoming request:', $request->all());
    
    // Validate the incoming data
    $validatedData = $request->validate([
        'doctorId' => 'required|integer', // Ensure doctorId is an integer
        'imageName' => 'required|string'  // Ensure imageName is a string
    ]);

    // Log the validated data
    \Log::info('Doctor ID: ' . $request->input('doctorId') . ', Image Name: ' . $request->input('imageName'));
    
    $doctorId = $request->input('doctorId');
    $imageName = $request->input('imageName');
    $sourcePath = "public/doctors/{$doctorId}/cabinet/en_attente/{$imageName}";
    $destinationPath = "public/doctors/{$doctorId}/cabinet/refuse/{$imageName}";
    
    // Vérifier si l'image existe dans le dossier 'en_attente'
    if (Storage::exists($sourcePath)) {
        // Déplacer l'image vers le dossier 'refuse'
        Storage::move($sourcePath, $destinationPath);

        
// Stocker un message de succès dans la session
return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
->with('success', 'Image déplacée vers le dossier "refuse" et enregistrée avec succès.');
} else {
// Stocker un message d'erreur dans la session
return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
->with('error', 'L\'image n\'a pas été trouvée dans le dossier "en_attente".');
}
}
  
    
}
