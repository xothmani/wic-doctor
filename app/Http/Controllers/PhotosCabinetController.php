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
     
    
    /*  public function accept($id, $imageName)
     {
         // Définir les chemins des dossiers
         $doctorId = $id; // Récupérer l'ID du docteur
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
     
                 // Sauvegarder les modifications dans la base de données
                 $doctor->save();
             }
     
             // Retourner une réponse ou rediriger avec un message de succès
             return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                              ->with('success', 'Image déplacée vers le dossier "Acceptée" et enregistrée.');
         } else {
             // Retourner une erreur si l'image n'existe pas
             return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                              ->with('error', 'L\'image n\'a pas été trouvée.');
         }
     } */
     
     public function accept(Request $request)
     {
         // Get the doctorId and imageName from the request
         $doctorId = $request->input('doctorId');
         $imageName = $request->input('imageName');
     
         // Log the data to ensure it's coming through
         \Log::info('Doctor ID: ' . $doctorId . ', Image Name: ' . $imageName);
     
         // Return a JSON response with the received data
         return response()->json([
             'message' => 'Data received successfully!',
             'doctorId' => $doctorId,
             'imageName' => $imageName
         ]);
     }
     
     

public function reject($id, $imageName)
{
    // Définir les chemins des dossiers
    $doctorId = $id; // Récupérer l'ID du docteur
    $sourcePath = "public/doctors/{$doctorId}/cabinet/en_attente/{$imageName}";
    $destinationPath = "public/doctors/{$doctorId}/cabinet/refuse/{$imageName}";

    // Vérifier si l'image existe dans le dossier 'en_attente'
    if (Storage::exists($sourcePath)) {
        // Déplacer l'image vers le dossier 'refuse'
        Storage::move($sourcePath, $destinationPath);

        // Retourner une réponse ou rediriger avec un message de succès
        return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                         ->with('success', 'Image déplacée vers le dossier "Refusée".');
    } else {
        // Retourner une erreur si l'image n'existe pas
        return redirect()->route('photos_cabinet.show', ['id' => $doctorId])
                         ->with('error', 'L\'image n\'a pas été trouvée.');
    }
}


}
