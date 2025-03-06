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

use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists as FirebaseEmailExists;
use Kreait\Firebase\Factory;


class DoctorRequestController extends Controller
{
    /**
     * Display a listing of the doctor requests.
     *
     * @param DoctorRequestDataTable $dataTable
     * @return mixed
     */
    public function index(DoctorRequestDataTable $dataTable)
    {
            return $dataTable->render('doctor_requests.index');
    }
    public function index2()
    {
        // Récupérer tous les docteurs depuis la table doctor_requests_b2b
        $doctors = DoctorRequest::all();
    
        // Passer les données à la vue
        return view('parrainers.parrainer', compact('doctors'));
    }
    
/**
 * Generate a random password and create a user from the selected DoctorRequest,
 * then create a Doctor associated with the User.
 *
 * @param int $doctorRequestId
 * @return \Illuminate\Http\Response
 */

 
 public function createUserFromDoctorRequest($doctorRequestId)
 {
     $doctorRequest = DoctorRequest::findOrFail($doctorRequestId);
 
     if ($doctorRequest->type !== 'Docteur') {
         return redirect()->back()->with('error', 'Seules les demandes de type "Docteur" sont autorisées.');
     }
     Log::info('doctor request type doctor ');

     try {
         $doctorPassword = Str::random(8);
         $patientPassword = null;
         Log::info('generating doctor password ');
         Log::info('doctor request email', ['email' =>  $doctorRequest->email]);

         $user = User::where('email', $doctorRequest->email)
             ->first();
        Log::info('getting user ');

        if ($user) {
            Log::info('User already exists', ['email' => $user->email]);
        } else {
            Log::info('User not found, proceeding with Firebase creation');
        }
        

         if (!$user) {
             $patientPassword = Str::random(8);
 
             // 🔹 Create user in Firebase
             $auth = app(abstract: FirebaseAuth::class);
             try {
                Log::info('add auth to firebase ');

                 $firebaseUser = $auth->createUser([
                     'email' => $doctorRequest->email,
                     'password' => $doctorPassword,
                     'displayName' => $doctorRequest->name . ' ' . $doctorRequest->lastname,
                     'phoneNumber' => $doctorRequest->phone,
                 ]);
                 Log::info('authenticated ');

                 // Get Firebase UID
                 $firebaseUid = $firebaseUser->uid;

 
                 Log::info('Utilisateur ajouté à Firebase avec UID : ' . $firebaseUid . $firebaseToken);
             } catch (FirebaseEmailExists $e) {
                 Log::error('Utilisateur déjà existant dans Firebase.');
                 return redirect()->back()->with('error', 'Cet utilisateur existe déjà dans Firebase.');
             } catch (\Exception $e) {
                 Log::error('Erreur Firebase : ' . $e->getMessage());
                 return redirect()->back()->with('error', 'Erreur lors de la création Firebase.');
             }
 
             // 🔹 Create user in SQL database
             $user = User::create([
                 'name' => $doctorRequest->name,
                 'lastname' => $doctorRequest->lastname,
                 'email' => $doctorRequest->email,
                 'phone_number' => $doctorRequest->phone,
                 'password' => bcrypt($doctorPassword),
                 'passwordpatient' => Hash::make($patientPassword),
                 'firebase_uid' => $firebaseUid, // Store Firebase UID
             ]);
         } else {
             if (!$user->password) {
                 $user->password = bcrypt($doctorPassword);
             }
             if (!$user->passwordpatient) {
                 $patientPassword = Str::random(8);
                 $user->passwordpatient = Hash::make($patientPassword);
             }
             $user->save();
         }
 
         // Check if doctor exists
         $existingDoctor = Doctor::where('user_id', $user->id)->first();
         if ($existingDoctor) {
             return redirect()->back()->with('error', 'Docteur déjà conventionné pour cet utilisateur.');
         }
 
         // Create the doctor
         $doctor = $this->createDoctor($user, $doctorRequest);
 
         // Create the patient if they don’t exist
         $existingPatient = Patient::where('user_id', $user->id)->first();
         if (!$existingPatient) {
             $this->createPatient($user, $doctorRequest);
         }
 
         // Send email with credentials
         Mail::to($doctorRequest->email)->send(new DoctorRequestMail(
             $doctorPassword,
             $patientPassword,
             $doctor
         ));
 
         return redirect()->route('doctor_requests.index')->with('success', 'Utilisateur, docteur et patient créés avec succès. Informations envoyées par e-mail.');
     } catch (\Exception $e) {
         Log::error('Erreur lors de la création : ' . $e->getMessage(), [
             'doctorRequestId' => $doctorRequestId,
         ]);
         return redirect()->back()->with('error', 'Une erreur est survenue.');
     }
 }
 

 

private function createDoctor($user, $doctorRequest)
{
    // Générez un ID aléatoire pour le docteur
    $randomId = random_int(1000000000, 9999999999);
    
    // Vérifiez si l'ID existe déjà et générez-en un nouveau si nécessaire
    while (Doctor::where('id_aleatoire', $randomId)->exists()) {
        $randomId = random_int(1000000000, 9999999999);
    }
    
    // Formater le nom du docteur
    $formattedName = ['fr' => $user->lastname . ' ' . $user->name];
    
    // Log avant la création du docteur
    Log::info('Creating doctor for user:', ['user_id' => $user->id, 'randomId' => $randomId]);
    
    // Créer le docteur
    $doctor = Doctor::create([
        'name' => $formattedName,
        'user_id' => $user->id,
        'id_aleatoire' => $randomId,
    ]);
    
    // Vérifiez si le rôle existe déjà avant de l'assigner
    $roleAssigned = DB::table('model_has_roles')
        ->where('model_id', $user->id)
        ->where('role_id', 5) // Assurez-vous que le rôle 5 existe dans la table roles
        ->exists();
    
    if (!$roleAssigned) {
        // Log avant d'assigner le rôle
        Log::info('Assigning role to user:', [
            'user_id' => $user->id,
            'role_id' => 5,
        ]);
        
        // Insert role for doctor
        DB::table('model_has_roles')->insert([
            'role_id' => 5,
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
        ]);
    } else {
        // Log que le rôle existe déjà
        Log::info('Role already assigned to user:', [
            'user_id' => $user->id,
            'role_id' => 5,
        ]);
    }

    // Insert membership
    $currentDate = now();
    $endDate = $currentDate->copy()->addDays(10);

    DB::table('membership')->insert([
        'user_id' => $user->id,
        'pack_id' => 1,
        'start_date' => $currentDate,
        'end_date' => $endDate,
        'payment_amount' => 0.00,
        'payment_date' => $currentDate,
    ]);

     // Insertion dans la table doctor_specialities
     if ($doctorRequest->speciality_id) {
        DB::table('doctor_specialities')->insert([
            'doctor_id' => $doctor->id,
            'speciality_id' => $doctorRequest->speciality_id,
        ]);
        // Log après insertion
        Log::info('Inserted speciality for doctor:', [
            'doctor_id' => $doctor->id,
            'speciality_id' => $doctorRequest->speciality_id,
        ]);
    } else {
        Log::warning('Speciality ID not provided in doctor request:', [
            'doctor_request_id' => $doctorRequest->id,
        ]);
    }
     // Insertion dans la table addresses
     $addressData = [
        'user_id' => $user->id,
        'description' => json_encode(['fr' => $doctorRequest->adresse]), // Format JSON multilingue
        'address' => json_encode(['fr' => $doctorRequest->adresse]),     // Format JSON multilingue    
        'pays' => json_encode(['fr' => $doctorRequest->pays]),           // Format JSON multilingue        'created_at' => now(),
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
    Log::info('Address inserted for doctor:', $addressData);


  // Étape 1 : Vider le fichier JSON
$filePath = public_path('script-detail-med/file.json');
if (file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]));
    Log::info('File cleared:', ['file_path' => $filePath]);
}


// Étape 2 : Ajouter l'objet docteur au fichier JSON
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

// Créer l'objet docteur avec toutes les données nécessaires
$doctorData = [
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
      'code-parent' => $codeParrain, // Remplacez $codeParrain par la valeur ou variable appropriée
    'code_doctor' => $codeDoctor,  
    // 'user_id' => $doctor->user_id,
];

// Lire le contenu actuel du fichier pour ajouter le nouveau médecin
$filePath = public_path('script-detail-med/file.json'); // Chemin du fichier JSON
$currentData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

// Ajouter l'objet docteur au tableau existant
$currentData[] = $doctorData;

// Réécrire le fichier avec le tableau mis à jour
file_put_contents($filePath, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); // Ajout de l'option JSON_UNESCAPED_UNICODE pour éviter les échappements des caractères accentués
Log::info('Doctor data written to file:', ['doctor_data' => $doctorData]);

// Étape 3 : Exécuter le script Node.js
$command = 'node /home/support-05/Bureau/wic-doctor-prescription/doctor.way-interactive-convergence.com/public/script-detail-med/nodejs.js';
exec($command, $output, $returnVar);
if ($returnVar !== 0) {
    Log::error('Erreur lors de l\'exécution du script Node.js', ['output' => $output, 'return_var' => $returnVar]);
} else {
    Log::info('Script Node.js exécuté avec succès', ['output' => $output]);
}


    // Retourner l'objet docteur créé
    return $doctor;
}

private function createPatient($user, $doctorRequest)
{
    // Log the patient creation data
    Log::info('Creating patient for user:', ['user_id' => $user->id]);
    Log::info('Creating patient for user:', [
        'user_id' => $user->id,
        'first_name' => $user->name,
        'last_name' => $user->lastname,
        'email' => $user->email,
        'date_naissance' => $doctorRequest->date_naissance ?? 'Not provided',
    ]);

    Patient::create([
        'user_id' => $user->id,
        'first_name' => $user->name,
        'last_name' => $user->lastname,
        'email' => $user->email,
        'date_naissance' => $doctorRequest->date_naissance ?? null, 
    ]);
}
 
}