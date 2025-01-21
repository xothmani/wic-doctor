<?php

namespace App\Http\Controllers;

use App\DataTables\DoctorRequestDataTable;
use App\Models\DoctorRequest;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;

use Illuminate\Support\Facades\DB;
use App\Mail\DoctorRequestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DoctorRequestController extends Controller
{
    public function index(DoctorRequestDataTable $dataTable)
    {
        return $dataTable->render('doctor_requests.index');
    }

public function createUserFromDoctorRequest($doctorRequestId)
{
    $doctorRequest = DoctorRequest::findOrFail($doctorRequestId);

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
                'name' => $doctorRequest->name,
                'lastname' => $doctorRequest->lastname,
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
        $doctor = $this->createDoctor($user, $doctorRequest);

        // Vérifier si le patient existe déjà
        $existingPatient = Patient::where('user_id', $user->id)->first();
        if (!$existingPatient) {
            $this->createPatient($user, $doctorRequest);
        }

        // Envoi de l'email avec les mots de passe
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

 

    private function createDoctor($user, $doctorRequest)
    {
        $randomId = random_int(1000000000, 9999999999);
        while (Doctor::where('id_aleatoire', $randomId)->exists()) {
            $randomId = random_int(1000000000, 9999999999);
        }

        $formattedName = ['fr' => $user->lastname . ' ' . $user->name];

        $doctor = Doctor::create([
            'name' => $formattedName,
            'user_id' => $user->id,
            'id_aleatoire' => $randomId,
        ]);

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
    } elseif ($doctorRequest->pays === 'france') {
        $addressData['Département'] = json_encode(['fr' => $doctorRequest->departement]);
        $addressData['Région'] = json_encode(['fr' => $doctorRequest->region]);
    }

        DB::table('addresses')->insert($addressData);

        $this->executeNodeScript($doctor);

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
$specialitiesData = $specialities->map(function($speciality) {
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
}
