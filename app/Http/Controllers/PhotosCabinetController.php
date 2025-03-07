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
    // Définir le chemin absolu sur le partage monté
    $path = "/mnt/doctor/{$id}/cabinet/en_attente";

    // Vérifier si le dossier existe
    if (!is_dir($path)) {
        abort(404, "Dossier introuvable.");
    }

    // Récupérer toutes les images dans le dossier
    $files = glob($path . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    // Générer les URLs des images en utilisant le chemin absolu
    $imageUrls = array_map(function ($file) {
        return asset(str_replace('/mnt/doctor', 'storage/doctor', $file));
    }, $files);

    // Récupérer les informations du docteur
    $doctor = Doctor::find($id);
    $doctorName = $doctor ? $doctor->name : 'Docteur Inconnu';

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

    // Définir les chemins de fichiers dans /mnt/doctor
    $sourcePath = "/mnt/doctor/{$doctorId}/cabinet/en_attente/{$imageName}";
    $destinationPath = "/mnt/doctor/{$doctorId}/cabinet/accepte/{$imageName}";

    // Vérifier si l'image existe dans le dossier 'en_attente'
    if (file_exists($sourcePath)) {
        // Déplacer l'image vers le dossier 'accepte'
        rename($sourcePath, $destinationPath);

        // Récupérer le docteur à partir de son ID
        $doctor = Doctor::find($doctorId);

        if ($doctor) {
            // Vérifier si la colonne cabinet_photo contient déjà des images
            $existingImages = $doctor->cabinet_photo;

            // Ajouter le nom de la nouvelle image
            if ($existingImages) {
                $doctor->cabinet_photo = $existingImages . '/' . $imageName;
            } else {
                $doctor->cabinet_photo = $imageName;
            }

            // Mettre à jour pourcentage_cabinet si ce n'est pas déjà 10
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

    // Définir les chemins de fichiers dans /mnt/doctor
    $sourcePath = "/mnt/doctor/{$doctorId}/cabinet/en_attente/{$imageName}";
    $destinationPath = "/mnt/doctor/{$doctorId}/cabinet/refuse/{$imageName}";

    // Vérifier si l'image existe dans le dossier 'en_attente'
    if (file_exists($sourcePath)) {
        // Déplacer l'image vers le dossier 'refuse'
        rename($sourcePath, $destinationPath);

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
