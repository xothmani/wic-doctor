<?php

namespace App\Http\Controllers;

use App\DataTables\DoctorRequestDataTable;
use App\Models\DoctorRequest;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Speciality;

use Illuminate\Support\Facades\DB;
use App\Mail\DoctorRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Upload;
class DoctorRequestController extends Controller
{



    public function index(DoctorRequestDataTable $dataTable)
    {
        return $dataTable->render('doctor_requests.index');
    }


    public function show($id)
    {
        // Récupérer la demande de médecin par ID
        $doctorRequest = DoctorRequest::findOrFail($id);

        // Vérification du pays pour ajuster les informations retournées
        $response = [
            'name' => $doctorRequest->name ?? 'Non spécifié',
            'lastname' => $doctorRequest->lastname ?? 'Non spécifié',
            'email' => $doctorRequest->email ?? 'Non spécifié',
            'Phone' => $doctorRequest->Phone ?? 'Non spécifié',
            'speciality_id' => $doctorRequest->speciality ? $doctorRequest->speciality->name : 'Non spécifié', // Gérer le cas où la spécialité est null
            'description' => $doctorRequest->description ?? 'Non spécifié',
            'adresse' => $doctorRequest->adresse ?? 'Non spécifié',
            'pays' => $doctorRequest->pays ?? 'Non spécifié',
            'type' => $doctorRequest->type ?? 'Non spécifié',
            'status' => $doctorRequest->status ?? 'Non spécifié',
            'code_parent' => $doctorRequest->code_parent ?? 'Non spécifié',
            'code_doctor' => $doctorRequest->code_doctor ?? 'Non spécifié',


        ];

        // Logique conditionnelle pour les informations spécifiques au pays
        if ($doctorRequest->pays === 'tunisie') {
            $response['gouvernorat'] = $doctorRequest->gouvernorat;
            $response['ville'] = $doctorRequest->ville;
        } elseif ($doctorRequest->pays === 'france') {
            $response['departement'] = $doctorRequest->departement;
            $response['region'] = $doctorRequest->region;
        }

        // Retourner les données en format JSON
        return response()->json($response);
    }

    public function create()
    {
        $user = null;
        $specialities = Speciality::all(); // Récupération de toutes les spécialités
        return view('doctor_requests.create', compact('user', 'specialities'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request with conditional validation based on the selected country
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'specialite' => 'required_if:type,Docteur|exists:specialities,id', // Ensure the specialty exists
            'description' => 'nullable|string',
            'adresse' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'type' => 'required|string',
            'sexe' => 'required|in:homme,femme', // Ensure valid sex input
        ]);

        // Prepare the data to be stored in the database
        $doctorRequestData = [
            'name' => $request->nom,
            'lastname' => $request->prenom,
            'email' => $request->email,
            'Phone' => $request->phone_number,
            'speciality_id' => $request->specialite,
            'description' => $request->description,
            'adresse' => $request->adresse,
            'pays' => $request->pays,
            'type' => $request->type,
            'sexe' => $request->sexe,
            'status' => 'accepté',
            'created_at' => now(),
            'updated_at' => now(),
            'code_doctor' => 'WD-' . strtoupper(Str::random(4)) . rand(1000, 9999), // Exemple : WD-A1B2C3
        ];

        // If the country is Tunisia, store 'ville' and 'gouvernorat'
        if ($request->pays == 'tunisie') {
            $doctorRequestData['ville'] = $request->ville;
            $doctorRequestData['gouvernorat'] = $request->region;
        }

        // If the country is France, store 'region' and 'departement'
        if ($request->pays == 'france') {
            $doctorRequestData['region'] = $request->region;
            $doctorRequestData['departement'] = $request->ville;
        }

        // Create the new doctor request using validated data
        $doctorRequest = DoctorRequest::create($doctorRequestData);


        if ($doctorRequest->type !== 'Docteur') {
            return redirect()->back()->with('error', 'Seules les demandes de type "Docteur" sont autorisées.');
        }

        try {
            $doctorPassword = Str::random(8); // Toujours générer un mot de passe pour le docteur
            $patientPassword = null; // Initialiser la variable pour le mot de passe patient

            // Rechercher un utilisateur existant
            $user = User::where('email', $doctorRequest->email)
                ->when($doctorRequest->Phone, function ($query, $phone) {
                    $query->orWhere('phone_number', $phone);
                })
                ->first();


            Log::info('Vérification de l\'utilisateur existant.', [
                'email_recherche' => $doctorRequest->email,
                'phone_recherche' => $doctorRequest->Phone,
                'user_retourne' => $user ? $user->toArray() : 'Aucun utilisateur trouvé',
            ]);

            if (!$user) {
                // Création d'un nouvel utilisateur
                $patientPassword = Str::random(8); // Générer un nouveau mot de passe patient
                $user = User::create([
                    'name' => json_encode(['fr' => $doctorRequest->name]),
                    'lastname' => json_encode(['fr' => $doctorRequest->lastname]),
                    'email' => $doctorRequest->email,
                    'phone_number' => $doctorRequest->Phone,
                    'password' => bcrypt($doctorPassword),
                    'passwordpatient' => Hash::make($patientPassword),
                ]);
                

                // Logguer le dernier utilisateur créé
                Log::info('Nouvel utilisateur créé.', ['user_id' => $user->id]);
            } else {
                // Si l'utilisateur existe déjà
                if ($user->passwordpatient) {
                    $patientPassword = 'Mot de passe déjà défini';
                } else {
                    $patientPassword = Str::random(8);
                    $user->passwordpatient = Hash::make($patientPassword);
                }

                if (!$user->password) {
                    $user->password = bcrypt($doctorPassword);
                }
                $user->save();

                // Logguer l'utilisateur existant
                Log::info('Utilisateur existant utilisé.', ['user_id' => $user->id]);
            }

            // Vérifier si l'utilisateur est déjà associé à un docteur
            $existingDoctor = Doctor::where('user_id', $user->id)->first();
            if ($existingDoctor) {
                return redirect()->back()->with('error', 'Docteur déjà conventionné pour cet utilisateur.');
            }
            $availabilityMode = $request->input('availability_mode'); // Récupérer la valeur du formulaire
            $titre = $request->input('titre'); // Récupérer la valeur du formulaire



            // Créer le docteur
            $doctor = $this->createDoctor($user, $doctorRequest, $availabilityMode, $titre);
            // Vérifier si le patient existe déjà
            $existingPatient = Patient::where('user_id', $user->id)->first();
            if (!$existingPatient) {
                $this->createPatient($user, $doctorRequest);
            }

            // Changer le statut de la demande à "accepté"
            $doctorRequest->status = 'accepté';
            $doctorRequest->save(); // Sauvegarder la mise à jour
            Log::info('Mot de passe du docteur : ' . $doctorPassword);

            // Envoi de l'email avec les mots de passe
            $doctor = Doctor::where('user_id', function ($query) use ($doctorRequest) {
                $query->select('id')->from('users')->where('email', $doctorRequest->email);
            })->first();

            if (!$doctor) {
                return redirect()->back()->with('error', 'Le docteur n\'existe pas.');
            }

            Mail::to($doctorRequest->email)->send(new DoctorRequestMail(
                $doctorPassword,
                $patientPassword,
                $doctor
            ));




            return redirect()->route('doctor_requests.index')->with('success', 'Utilisateur, docteur et patient créés avec succès. Les informations ont été envoyées par e-mail.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création : ' . $e->getMessage(), [
                'doctorRequestId' => $doctorRequest->id,
            ]);
            return redirect()->back()->with('error', 'Une erreur est survenue.');
        }
    }


    public function createUserFromDoctorRequest($doctorRequestId, Request $request)
    {
        $doctorRequest = DoctorRequest::findOrFail($doctorRequestId);
        $availabilityMode = $request->input(key: 'availability_mode'); // Récupérer la valeur du formulaire
        $titre = $request->input('titre'); // Récupérer la valeur du formulaire


        if ($doctorRequest->type !== 'Docteur') {
            return redirect()->back()->with('error', 'Seules les demandes de type "Docteur" sont autorisées.');
        }

        try {
            $doctorPassword = Str::random(8); // Toujours générer un mot de passe pour le docteur
            $patientPassword = null; // Initialiser la variable pour le mot de passe patient

            // Rechercher un utilisateur existant
            $user = User::where('email', $doctorRequest->email)
                ->when($doctorRequest->Phone, function ($query, $phone) {
                    $query->orWhere('phone_number', $phone);
                })
                ->first();


            Log::info('Vérification de l\'utilisateur existant.', [
                'email_recherche' => $doctorRequest->email,
                'phone_recherche' => $doctorRequest->Phone,
                'user_retourne' => $user ? $user->toArray() : 'Aucun utilisateur trouvé',
            ]);

            if (!$user) {
                // Création d'un nouvel utilisateur
                $patientPassword = Str::random(8); // Générer un nouveau mot de passe patient
                $user = User::create([
                    'name' => json_encode(['fr' => $doctorRequest->name]),
                    'lastname' => json_encode(['fr' => $doctorRequest->lastname]),
                    'email' => $doctorRequest->email,
                    'phone_number' => $doctorRequest->Phone,
                    'password' => bcrypt($doctorPassword),
                    'passwordpatient' => Hash::make($patientPassword),
                ]);

                // Logguer le dernier utilisateur créé
                Log::info('Nouvel utilisateur créé.', ['user_id' => $user->id]);
            } else {
                // Si l'utilisateur existe déjà
                if ($user->passwordpatient) {
                    $patientPassword = 'Mot de passe déjà défini';
                } else {
                    $patientPassword = Str::random(8);
                    $user->passwordpatient = Hash::make($patientPassword);
                }

                if (!$user->password) {
                    $user->password = bcrypt($doctorPassword);
                }
                $user->save();

                // Logguer l'utilisateur existant
                Log::info('Utilisateur existant utilisé.', ['user_id' => $user->id]);
            }

            // Vérifier si l'utilisateur est déjà associé à un docteur
            $existingDoctor = Doctor::where('user_id', $user->id)->first();
            if ($existingDoctor) {
                // Logguer une tentative de doublon
                Log::warning('Tentative de conventionnement pour un utilisateur déjà existant.', [
                    'user_id' => $user->id,
                    'doctorRequestId' => $doctorRequestId,
                ]);
                return redirect()->back()->with('error', 'Docteur déjà conventionné pour cet utilisateur.');
            }

            // Créer le docteur
            $doctor = $this->createDoctor($user, $doctorRequest, $availabilityMode, $titre);


            // Vérifier si le patient existe déjà
            $existingPatient = Patient::where('user_id', $user->id)->first();
            if (!$existingPatient) {
                $this->createPatient($user, $doctorRequest);
            }

            // Changer le statut de la demande à "accepté"
            $doctorRequest->status = 'accepté';
            $doctorRequest->save(); // Sauvegarder la mise à jour

            Log::info('Mot de passe du docteur : ' . $doctorPassword);

            // Envoi de l'email avec les mots de passe
            $doctor = Doctor::where('user_id', function ($query) use ($doctorRequest) {
                $query->select('id')->from('users')->where('email', $doctorRequest->email);
            })->first();

            if (!$doctor) {
                return redirect()->back()->with('error', 'Le docteur n\'existe pas.');
            }

            Mail::to($doctorRequest->email)->send(new DoctorRequestMail(
                $doctorPassword,
                $patientPassword,
                $doctor
            ));



            return redirect()->route('doctor_requests.index')->with('success', 'Utilisateur, docteur et patient créés avec succès. Les informations ont été envoyées par e-mail.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création : ' . $e->getMessage(), [
                'doctorRequestId' => $doctorRequestId,
            ]);
            return redirect()->back()->with('error', 'Une erreur est survenue.');
        }
    }



    private function createDoctor($user, $doctorRequest, $availabilityMode, $titre)
    {
        $randomId = random_int(1000000000, 9999999999);
        while (Doctor::where('id_aleatoire', $randomId)->exists()) {
            $randomId = random_int(1000000000, 9999999999);
        }

        $formattedName = ['fr' => $user->lastname . ' ' . $user->name];

        // Créer le docteur
        $doctor = Doctor::create([
            'name' => $formattedName,
            'user_id' => $user->id,
            'id_aleatoire' => $randomId,
            'sexe' => $doctorRequest->sexe,
            'code_doctor' => $doctorRequest->code_doctor,
            'availability_mode' => $availabilityMode,
            'titre' => $titre,

        ]);

        // Définir l'image par défaut selon le sexe
        $defaultAvatar = $doctorRequest->sexe === 'homme'

            ? '/var/www/doctor.way-interactive-convergence.com/public/images/avatarHomme.png'
            : '/var/www/doctor.way-interactive-convergence.com/public/images/avatarFemme.png';


        if (!file_exists($defaultAvatar)) {
            Log::error("L'image par défaut est introuvable", ['path' => $defaultAvatar]);
            return back()->withErrors(['image' => 'L\'image par défaut est introuvable.']);
        }

        // 1️⃣ Ajouter l'image au modèle Doctor
        $doctorMedia = $doctor->addMedia($defaultAvatar)->preservingOriginal()->toMediaCollection('image');
        Log::info('Image ajoutée au doctor', ['id' => $doctor->id, 'image' => $doctorMedia->getUrl()]);

        // 2️⃣ Ajouter l'image au modèle User
        $userMedia = $user->addMedia($defaultAvatar)->preservingOriginal()->toMediaCollection('avatar');
        Log::info('Image ajoutée à l\'utilisateur', ['id' => $user->id, 'image' => $userMedia->getUrl()]);

        // 3️⃣ Ajouter l'image au modèle Upload
        $upload = Upload::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'file_name' => basename($defaultAvatar),
            'mime_type' => 'image/png',
            'disk' => 'public',
            'size' => filesize($defaultAvatar),
            'model_type' => 'App\Models\Upload',
            'model_id' => $doctor->id,
            'collection_name' => 'image',
        ]);
        $uploadMedia = $upload->addMedia($defaultAvatar)->preservingOriginal()->toMediaCollection('image');
        Log::info('Image ajoutée à Upload', ['id' => $upload->id, 'image' => $uploadMedia->getUrl()]);
        if (!DB::table('model_has_roles')->where('model_id', $user->id)->where('role_id', 5)->exists()) {
            DB::table('model_has_roles')->insert([
                'role_id' => 5,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id,
            ]);
        }

        DB::table('membership')->insert([
            'user_id' => $user->id,
            'pack_id' => 1,
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'payment_amount' => 0.00,
            'payment_date' => now(),
        ]);

        if ($doctorRequest->speciality_id) {
            DB::table('doctor_specialities')->insert([
                'doctor_id' => $doctor->id,
                'speciality_id' => $doctorRequest->speciality_id,
            ]);
        }

        $addressData = [
            'user_id' => $user->id,
            'description' => json_encode(['fr' => $doctorRequest->adresse], JSON_UNESCAPED_UNICODE),
            'address' => json_encode(['fr' => $doctorRequest->adresse], JSON_UNESCAPED_UNICODE),
            'pays' => json_encode(['fr' => $doctorRequest->pays], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($doctorRequest->pays === 'tunisie') {
            $addressData['gouvernorat'] = json_encode(['fr' => $doctorRequest->gouvernorat]);
            $addressData['ville'] = json_encode(['fr' => $doctorRequest->ville]);
            DB::table('addresses')->insert($addressData);
            // Modifier les permissions avant d'exécuter le script Node.js
            shell_exec('sudo chown -R www-data:www-data /var/www/wic-doctor.com/WicDoctor/medecin/');
            shell_exec('sudo chmod -R 775 /var/www/wic-doctor.com/WicDoctor/medecin/');
            shell_exec('sudo chown -R www-data:www-data /var/www/doctor.way-interactive-convergence.com/public/script-detail-med');
            shell_exec('sudo chmod -R 775 /var/www/doctor.way-interactive-convergence.com/public/script-detail-med');
        
            // Exécuter le script Node.js
            $this->executeNodeScript($doctor);
        } elseif ($doctorRequest->pays === 'france') {
            $addressData['Département'] = json_encode(['fr' => $doctorRequest->departement]);
            $addressData['Région'] = json_encode(['fr' => $doctorRequest->region]);
            DB::table('addresses')->insert($addressData);
            // Modifier les permissions avant d'exécuter le script Node.js
            shell_exec('sudo chown -R www-data:www-data /var/www/wic-doctor.com/france/medecin/');
            shell_exec('sudo chmod -R 775 /var/www/wic-doctor.com/france/medecin/');

            // Exécuter le script Node.js
            $this->executeNodeScriptFrance($doctor);
            
        }




        return $doctor;
    }

    private function executeNodeScript($doctor)
    {
        $user = $doctor->user()->with('address')->first(); // Charger l'adresse avec l'utilisateur
        $experience = $doctor->experience; // Récupérer l'expérience associée au docteur

        // Vérifier si l'adresse est présente et récupérer la ville
        $address = $user ? $user->address : null;
        $ville = $address ? $address->ville : null;
        $pays = $address ? $address->pays : null;
        $gouvernorat = $address ? $address->gouvernorat : null;
        $adresse_exacte = $address ? $address->address : null;
        // Récupérer le titre de l'expérience, si existante
        $title = $experience ? $experience->title : null;
        // Récupérer les spécialités du médecin
        $specialities = $doctor->specialities;
        // Récupérer les spécialités et construire le tableau
        $specialitiesData = $specialities->map(function ($speciality) {
            return [
                'id' => $speciality->id,
                'name' => json_encode(['fr' => $speciality->name]), // Exemple pour la langue 'fr'
            ];
        })->toArray();
        $filePath = public_path('script-detail-med/file.json');

        // Données JSON à écrire
        $data = [
            'id_doctor' => $doctor->id,
            'name' => json_encode(['fr' => $doctor->name]),
            'doctor_photo' => $doctor->doctor_photo,
            'enable_online_consultation' => $doctor->enable_online_consultation,
            'description' => $doctor->description,
            'horaires' => $doctor->horaires,
            'cabinet_photo' => $doctor->cabinet_photo,
            'created_at' => $doctor->created_at,
            'title' => $title,
            'phone_number' => $user ? $user->phone_number : null,
            'ville' => $ville,
            'pays' => $pays,
            'gouvernorat' => $gouvernorat,
            'aleatoire' => $doctor->id_aleatoire,
            'adresse_exacte' => $adresse_exacte,
            'specialities' => $specialitiesData,
            'type' => "conventionné",
            'availability_mode' => $doctor->availability_mode,
            'titre' => $doctor->titre,

        ];

        file_put_contents($filePath, json_encode([$data], JSON_UNESCAPED_UNICODE));

        $command = 'node /var/www/doctor.way-interactive-convergence.com/public/script-detail-med/nodejs.js';
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('Erreur lors de l\'exécution du script Node.js', [
                'output' => $output,
                'return_var' => $returnVar,
            ]);
        } else {
            Log::info('Script Node.js exécuté avec succès', ['output' => $output]);
        }
    }


    private function executeNodeScriptFrance($doctor)
    {
        $user = $doctor->user()->with('address')->first(); // Charger l'adresse avec l'utilisateur
        $experience = $doctor->experience; // Récupérer l'expérience associée au docteur

        // Vérifier si l'adresse est présente et récupérer la ville
        $address = $user ? $user->address : null;
        $region = $address ? $address->ville : null;
        $pays = $address ? $address->pays : null;
        $département = $address ? $address->Département : null;
        $adresse_exacte = $address ? $address->Région : null;
        // Récupérer le titre de l'expérience, si existante
        $title = $experience ? $experience->title : null;
        // Récupérer les spécialités du médecin
        $specialities = $doctor->specialities;
        // Récupérer les spécialités et construire le tableau
        $specialitiesData = $specialities->map(function ($speciality) {
            return [
                'id' => $speciality->id,
                'name' => json_encode(['fr' => $speciality->name]), // Exemple pour la langue 'fr'
            ];
        })->toArray();
        $filePath = public_path('script-detail-med-france/file.json');

        // Données JSON à écrire
        $data = [

            'id_doctor' => $doctor->id,
            'name' => json_encode(['fr' => $doctor->name]),
            'doctor_photo' => $doctor->doctor_photo,
            'enable_online_consultation' => $doctor->enable_online_consultation,
            'description' => $doctor->description,
            'horaires' => $doctor->horaires,
            'cabinet_photo' => $doctor->cabinet_photo,
            'created_at' => $doctor->created_at,
            'title' => $title,
            'phone_number' => $user ? $user->phone_number : null,
            'pays' => $pays,
            'region' => $region,
            'département' => $département,
            'adresse_exacte' => $adresse_exacte,
            'aleatoire' => $doctor->id_aleatoire,
            'specialities' => $specialitiesData,
            'type' => "conventionné",

        ];

        file_put_contents($filePath, json_encode([$data], JSON_UNESCAPED_UNICODE));

        $command = 'node /var/www/doctor.way-interactive-convergence.com/public/script-detail-med-france/nodejs.js';
        exec($command . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('Erreur lors de l\'exécution du script Node.js', [
                'output' => $output,
                'return_var' => $returnVar,
            ]);
        } else {
            Log::info('Script Node.js exécuté avec succès', ['output' => $output]);
        }
    }

    private function createPatient($user, $doctorRequest)
    {
        Patient::create([
            'user_id' => $user->id,
            'first_name' => $user->name,
            'last_name' => $user->lastname,
            'email' => $doctorRequest->email,
            'date_naissance' => $doctorRequest->date_naissance ?? null,
            'phone_number' => $doctorRequest->Phone,

        ]);
    }

    public function destroy(string $id)
    {
        // Trouver la demande par son ID
        $doctorRequest = DoctorRequest::findOrFail($id);


        // Supprimer la demande
        $doctorRequest->delete();

        // Redirection avec un message de succès
        return redirect()->route('doctor_requests.index')->with('success', 'Demande supprimés avec succès.');
    }
}