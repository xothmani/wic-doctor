<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\TelesecretariatDataTable;
use App\Models\Telesecretariat;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; 
use Illuminate\Support\Facades\DB;
use App\Mail\TelesecretariatCreated;
use Illuminate\Support\Facades\Mail;


class TelesecretariatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TelesecretariatDataTable $dataTable)
    {
        return $dataTable->render('telesecretariats.index'); // Vue à personnaliser
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = null; // Pas d'utilisateur pour la création
        return view('telesecretariats.create', compact('user')); // Passer $user à la vue
    }
    public function store(Request $request)
    {
        // Validation des champs
        $validated = $request->validate([
            'nom_centre' => 'required|string|max:255', // Nom du centre, requis, chaîne de caractères, maximum 255 caractères
            'nom_responsable' => 'required|string|max:255', // Nom du responsable, requis, chaîne de caractères, maximum 255 caractères
            'prenom_responsable' => 'required|string|max:255', // Prénom du responsable, requis, chaîne de caractères, maximum 255 caractères
            'phone_number' => 'required|numeric|unique:users,phone_number', // Numéro de téléphone, requis, unique dans la table 'users', maximum 15 caractères
            'email' => 'required|email|unique:users,email|max:255', // Email, requis, format email, unique dans la table 'users', maximum 255 caractères
            'adresse' => 'nullable|string|max:255', // Adresse, facultatif, chaîne de caractères, maximum 255 caractères
            'etat' => 'required|in:0,1', // Etat, requis, soit 0 (inactive) ou 1 (active)
            'description' => 'nullable|string|max:500', // Description, facultatif, chaîne de caractères, maximum 500 caractères
        ]);
    
        // Vérifier si l'email ou le numéro de téléphone existe déjà et est associé à un utilisateur
        $existingUser = User::where('email', $request->email)
                            ->orWhere('phone_number', $request->phone_number)
                            ->first();
    
        // Si un utilisateur avec cet email ou numéro de téléphone existe déjà
        if ($existingUser) {
            // Vérifier si un mot de passe est déjà défini pour cet utilisateur
            if ($existingUser->password) {
                // Vérifier si un telesecretariat est déjà associé à cet utilisateur
                $existingTelesecretariat = Telesecretariat::where('user_id', $existingUser->id)->first();
                
                // Si un telesecretariat est déjà associé à l'utilisateur
                if ($existingTelesecretariat) {
                    return redirect()->back()->with('error', 'Cet utilisateur est déjà associé à un télésecretariat.');
                } else {
                    return redirect()->back()->with('error', 'Cet utilisateur est déjà associé à un autre compte que télésecretariat.');
                }
            } else {
                // Si l'utilisateur n'a pas de mot de passe, générer un mot de passe
                $password = Str::random(10);
                Log::info('Mot de passe généré pour l\'utilisateur : ' . $password);
                $existingUser->update([
                    'password' => bcrypt($password), // Hasher le mot de passe
                ]);
    
                // Récupérer l'ID du rôle "telesecretarial"
                $roleId = Role::where('name', 'telesecretarial')->first()->id;
    
                // Insérer le rôle "telesecretarial" pour l'utilisateur
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $existingUser->id,
                ]);
    
                // Créer le telesecretariat associé à cet utilisateur
                $telesecretariat = Telesecretariat::create([
                    'nomCentre' => $request->nom_centre,
                    'adresse' => $request->adresse,
                    'etat' => $request->etat,
                    'description' => $request->description,
                    'user_id' => $existingUser->id, // Associer le telesecretariat à l'utilisateur
                ]);
    
                return redirect()->route('telesecretariats.index')->with('success', 'Télésecretariat associé à l\'utilisateur existant avec un mot de passe généré.');
            }
        }
    
        // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
        $password = Str::random(10); // Générer un mot de passe
        Log::info('Mot de passe généré pour l\'utilisateur : ' . $password);
    
        // Créer un nouvel utilisateur
        $newUser = User::create([
            'name' => $request->prenom_responsable,
            'lastname' => $request->nom_responsable,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => bcrypt($password), // Hasher le mot de passe
        ]);
    
        // Récupérer l'ID du rôle "telesecretarial"
        $roleId = Role::where('name', 'telesecretarial')->first()->id;
    
        // Insérer le rôle "telesecretarial" pour l'utilisateur
        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_type' => 'App\Models\User',
            'model_id' => $newUser->id,
        ]);
    
        // Créer le telesecretariat associé à cet utilisateur
        $telesecretariat = Telesecretariat::create([
            'nomCentre' => $request->nom_centre,
            'adresse' => $request->adresse,
            'etat' => $request->etat,
            'description' => $request->description,
            'user_id' => $newUser->id, // Associer le telesecretariat à l'utilisateur
        ]);
    
        return redirect()->route('telesecretariats.index')->with('success', 'Télésecretariat créé et utilisateur enregistré.');
    }
    

    
    
    
/**
 * Display the specified resource.
 */
public function show(string $id)
{
    // Trouver le telesecretariat par son ID
    $telesecretariat = Telesecretariat::findOrFail($id);
    
    // Récupérer l'utilisateur associé à ce telesecretariat
    $user = $telesecretariat->user;
    
    // Retourner la vue avec les données de telesecretariat et de l'utilisateur
    return response()->json([
        'telesecretariat' => $telesecretariat,
        'user' => $user
    ]);
}

    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
{
        // Trouver le telesecretariat par son ID
        $telesecretariat = Telesecretariat::findOrFail($id);

        // Récupérer l'utilisateur associé à ce telesecretariat
        $user = $telesecretariat->user;

        // Retourner la vue avec les données de telesecretariat et de l'utilisateur
        return view('telesecretariats.edit', compact('telesecretariat', 'user'));
}

  /**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    // Trouver le telesecretariat par son ID
    $telesecretariat = Telesecretariat::findOrFail($id);

    // Trouver l'utilisateur associé
    $user = $telesecretariat->user;

    // Validation des données reçues
    $request->validate([
        'nom_centre' => 'required|string|max:255',
        'adresse' => 'required|string|max:255',
        'etat' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,  // Exclure l'utilisateur actuel de la validation unique
        'phone_number' => 'required|string|unique:users,phone_number,' . $user->id,  // Exclure l'utilisateur actuel de la validation unique
    ]);

    // Mise à jour de l'utilisateur
    $user->update([
        'email' => $request->email,
        'phone_number' => $request->phone_number,
        'name' => $request->prenom_responsable,
        'lastname' => $request->nom_responsable,
    ]);

    // Mise à jour du telesecretariat
    $telesecretariat->update([
        'nomCentre' => $request->nom_centre,
        'adresse' => $request->adresse,
        'etat' => $request->etat,
        'description' => $request->description,
    ]);

    // Retourner à la liste avec un message de succès
    return redirect()->route('telesecretariats.index')->with('success', 'Télésecretariat et utilisateur mis à jour avec succès.');
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
        // Trouver le télésecrétariat par son ID
        $telesecretariat = Telesecretariat::findOrFail($id);
    
        // Supprimer l'utilisateur associé
        $user = $telesecretariat->user; // Relation entre Telesecretariat et User
        if ($user) {
            $user->delete(); // Supprimer l'utilisateur
        }
    
        // Supprimer le télésecrétariat
        $telesecretariat->delete();
    
        // Redirection avec un message de succès
        return redirect()->route('telesecretariats.index')->with('success', 'Télésecrétariat et utilisateur associés supprimés avec succès.');
}
    
}
