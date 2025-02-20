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
use Illuminate\Http\JsonResponse;


class DoctorBlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DoctorBlogDataTable $dataTable)
    {
        return $dataTable->with(['status' => 'en cours'])->render('doctor_blog.index');
    }
    
    public function acceptedBlogs(DoctorBlogDataTable $dataTable)
    {
        return $dataTable->with(['status' => 'accepté'])->render('doctor_blog.accepted');
    }
    public function rejectedBlogs(DoctorBlogDataTable $dataTable)
    {
        return $dataTable->with(['status' => 'rejeté'])->render('doctor_blog.rejected');
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
        // Vérifier si l'utilisateur est connecté
        $user = auth()->user();
        if (!$user) {
            Log::error('Utilisateur non connecté');
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }
    
        // Vérifier si l'utilisateur est un médecin
        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) {
            Log::error('Médecin non trouvé pour cet utilisateur', ['user_id' => $user->id]);
            return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
        }
    
        // Valider les données du formulaire
        $request->validate([
            'titre_court' => 'required|string|max:255',
            'titre' => 'required|string',
            'contenu' => 'required|string',
            'media_id' => 'required|integer', // Validation pour l'ID du média
        ]);
    
        Log::info('Validation réussie pour les champs', $request->all());
    
        // Créer un nouveau blog pour le médecin
        $doctorBlog = new DoctorBlog();
        $doctorBlog->titre_court = $request->titre_court;
        $doctorBlog->titre = $request->titre;
        $doctorBlog->contenu = $request->contenu;
        $doctorBlog->status = 'en cours';  // Statut "en cours"
        $doctorBlog->doctor_id = $doctor->id;  // ID du médecin connecté
        $doctorBlog->media_id = $request->media_id;  // ID du média
        $doctorBlog->created_at = now();  // Date et heure actuelle pour created_at
        $doctorBlog->updated_at = null;  // Laisser vide ou null pour la première création
    
        // Sauvegarder le blog dans la base de données
        $doctorBlog->save();
        Log::info('Blog enregistré', ['blog_id' => $doctorBlog->id]);
    
        // Retourner une réponse de succès
        return redirect()->route('doctor_blog.index')->with('success', 'Blog créé avec succès');
    }
    public function storeImage(Request $request): JsonResponse
    {
        $input = $request->all();
    
        // Valider les entrées
        $request->validate([
            'file' => 'required|image', // Valider le fichier image
            'uuid' => 'required|string', // Validation pour UUID (si nécessaire)
            'field' => 'required|string' // Le champ pour la collection de médias
        ]);
    
        try {
            // Récupérer le médecin connecté
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => "Médecin non trouvé."]);
            }
    
            // Ajouter l'image au modèle 'Doctor'
            $media = $doctor->addMedia($input['file'])
                ->withCustomProperties(['uuid' => $input['uuid'], 'user_id' => auth()->id()])
                ->toMediaCollection($input['field']);  // Ici, 'field' est le nom de la collection
    
            // Retourner une réponse de succès avec l'ID du média
            return response()->json([
                'success' => true,
                'media_id' => $media->id,
                'message' => "Image téléchargée avec succès"
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
     
     
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Récupérer le blog
        $blog = DoctorBlog::findOrFail($id);
    
        // Récupérer l'image depuis la table media (si elle existe)
        $media = Media::where('id', $blog->media_id)->first();
    
        // Construire le chemin de l'image
        $imagePath = $media ? asset('storage/' . $blog->media_id . '/' . $media->file_name) : null;
    
        return view('doctor_blog.show', compact('blog', 'imagePath'));
    }
    
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Récupérer le blog du médecin avec l'id
        $doctorBlog = DoctorBlog::findOrFail($id);
    
        // Récupérer l'image associée au blog
        $media = Media::find($doctorBlog->media_id);
    
        // Retourner la vue d'édition avec les données du blog et l'image
        return view('doctor_blog.edit', compact('doctorBlog', 'media'));
    }
    


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    // Valider les données
    $request->validate([
        'titre_court' => 'required|string|max:255',
        'titre' => 'required|string',
        'contenu' => 'required|string',
        'media_id' => 'required|integer', // Validation pour l'ID du média
    ]);

    // Récupérer le blog du médecin
    $doctor_blog = DoctorBlog::findOrFail($id);

    // Mettre à jour le blog
    $doctor_blog->titre_court = $request->titre_court;
    $doctor_blog->titre = $request->titre;
    $doctor_blog->contenu = $request->contenu;
    $doctor_blog->media_id = $request->media_id;
    $doctor_blog->status = 'en cours';  
    $doctor_blog->updated_at = now();
    $doctor_blog->save();

    // Retourner une réponse de succès
    return redirect()->route('doctor_blog.index')->with('success', 'Blog mis à jour avec succès');
}

    /**
     * Remove the specified resource from storage.
     */
  
    
     public function destroy(string $id)
     {
         // Récupérer le blog à partir de son ID
         $blog = DoctorBlog::findOrFail($id);
     
         // Vérifier si le blog a un media_id associé
         if ($blog->media_id) {
             // Récupérer le média à partir de son ID
             $media = Media::findOrFail($blog->media_id);
     
             // Construire le chemin du dossier dans storage/app/public/ correspondant à l'ID du média
             $folderPath = $media->id;
     
             // Vérifier si le dossier existe et supprimer le dossier avec tout son contenu
             if (Storage::exists('public/' . $folderPath)) {
                 Storage::deleteDirectory('public/' . $folderPath); // Supprimer le dossier et son contenu
             }
     
             // Supprimer le média de la base de données
             $media->delete();
         }
     
         // Supprimer le blog
         $blog->delete();
     
         // Retourner à la liste des blogs avec un message de succès
         return redirect()->route('doctor_blog.index')->with('success', 'Blog et son média supprimés avec succès.');
     }
    
     public function deleteImage(Request $request)
{
    $mediaId = $request->input('media_id');
    $media = Media::find($mediaId);

    if ($media) {
        // Supprimer le fichier du stockage
        $filePath = 'public/' . $media->id . '/' . $media->file_name;
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // Supprimer le dossier associé au média
        $folderPath = 'public/' . $media->id;
        if (Storage::exists($folderPath)) {
            Storage::deleteDirectory($folderPath);
        }

        // Supprimer l'enregistrement de la base de données
        $media->delete();

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'Média non trouvé']);
}
public function accepterBlog($id)
{
    // Trouver le blog par son ID
    $blog = DoctorBlog::find($id);

    // Vérifier si le blog existe
    if (!$blog) {
        return redirect()->back()->with('error', 'Blog non trouvé.');
    }


    // Changer le statut à "accepté"
    $blog->status = 'accepté';
    $blog->save();



    // Redirection vers la page 'doctor_blog.accepted' avec message de succès
    return redirect()->route('doctor_blog.accepted')->with('success', 'Le blog a été accepté avec succès.');
}



}
