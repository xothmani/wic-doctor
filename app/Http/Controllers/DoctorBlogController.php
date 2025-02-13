<?php

namespace App\Http\Controllers;
use App\Models\DoctorBlog;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;  // Import the Log facade
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\DataTables\DoctorBlogDataTable;
use App\Models\Media;
use Illuminate\Support\Str;
class DoctorBlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DoctorBlogDataTable $dataTable)
    {
        return $dataTable->render('doctor_blog.index'); // Vue à personnaliser
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        return view('doctor_blog.create'); // Passer $user à la vue
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Vérifier si l'utilisateur est connecté
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }
    
        // ✅ Vérifier si l'utilisateur est un médecin
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
        }
    
        // ✅ Valider les données du formulaire
        $request->validate([
            'titre_court' => 'required|string|max:255',
            'titre' => 'required|string',
            'contenu' => 'required|string',
            'image' => 'required', // Validation de l'image
        ]);
    
        // ✅ Créer un nouveau blog pour le médecin
        $doctorBlog = new DoctorBlog();
        $doctorBlog->titre_court = $request->titre_court;
        $doctorBlog->titre = $request->titre;
        $doctorBlog->contenu = $request->contenu;
        $doctorBlog->status = 'en cours';  // Statut "en cours"
        $doctorBlog->doctor_id = $doctor->id;  // ID du médecin connecté
    
        // ✅ Sauvegarder le blog dans la base de données
        $doctorBlog->save();
    
        // ✅ Sauvegarder l'image dans la table `media`
        if ($request->hasFile('image')) {
            // Créer un uuid pour l'image
            $uuid = (string) Str::uuid();
    
            // Obtenir les informations de l'image
            $image = $request->file('image');
            $imageName = $image->getClientOriginalName();
            $mimeType = $image->getMimeType();
            $size = $image->getSize();
    
            // Déplacer l'image dans le dossier public (optionnel si vous utilisez un système de stockage)
            $image->storeAs('public/images', $imageName);
    
            // Insertion de l'image dans la table `media`
            DB::table('media')->insert([
                'uuid' => $uuid,
                'model_type' => 'doctor',
                'model_id' => $doctor->id,
                'collection_name' => 'image',
                'name' => $imageName,
                'file_name' => $imageName,
                'mime_type' => $mimeType,
                'disk' => 'public',
                'size' => $size,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Récupérer l'ID du media inséré
            $mediaId = DB::getPdo()->lastInsertId();
    
            // Associer l'ID du media au blog
            $doctorBlog->media_id = $mediaId;
            $doctorBlog->save();
        }
    
        // ✅ Retourner une réponse de succès
        return redirect()->route('doctor_blog.index')->with('success', 'Blog créé avec succès');
    }
    
    public function storeImage(Request $request)
    {
        // Vérifier si l'utilisateur est connecté et s'il est médecin
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$user || !$doctor) {
            return response()->json(['error' => 'Utilisateur ou médecin non trouvé'], 404);
        }
    
        // Valider le fichier image
        $request->validate([
            'image' => 'required|image|max:2048', // Validation de l'image
        ]);
    
        // Enregistrement de l'image dans la table 'media'
        if ($request->hasFile('image')) {
            $image = $request->file('image');
    
            // Créer un nouvel enregistrement dans la table media
            $media = new Media();
            $media->uuid = (string) Str::uuid(); // Générer un UUID pour l'image
            $media->model_type = 'doctor'; // Type du modèle
            $media->model_id = $doctor->id; // ID du médecin
            $media->collection_name = 'image'; // Nom de la collection
            $media->name = $image->getClientOriginalName(); // Nom original de l'image
            $media->file_name = $image->hashName(); // Nom unique de l'image
            $media->mime_type = $image->getMimeType(); // Type MIME
            $media->disk = 'public'; // Disque pour le stockage
            $media->size = $image->getSize(); // Taille de l'image
            $media->save();
    
            // Déplacer l'image dans le dossier public
            $image->storeAs('public/doctor_images', $media->file_name);
    
            // Retourner une réponse avec les informations de l'image
            return response()->json([
                'success' => true,
                'media' => $media
            ]);
        }
    
        // Retourner une erreur si aucune image n'est envoyée
        return response()->json(['error' => 'Aucune image reçue'], 400);
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
