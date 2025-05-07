<?php

namespace App\Http\Controllers;

use App\DataTables\PatientDataTable;
use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Repositories\PatientRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Assurance;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Fiche;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\AddPatientMail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;


class PatientController extends Controller
{
    /** @var  PatientRepository */
    private PatientRepository $patientRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;

    public function __construct(
        PatientRepository $patientRepo,
        CustomFieldRepository $customFieldRepo,
        UserRepository $userRepo
        ,
        UploadRepository $uploadRepo
    ) {
        parent::__construct();
        $this->patientRepository = $patientRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->uploadRepository = $uploadRepo;

    }

    /**
     * Display a listing of the Patient.
     *
     * @param PatientDataTable $patientDataTable
     * @return mixed
     */
    public function index(PatientDataTable $patientDataTable): mixed
    {
        $doctorId = auth()->user()->getDoctorId();
        Log::info('Active doctor id used in PatientController::index', ['doctorId' => $doctorId]);
        return $patientDataTable->render('patients.index');
    }

    /**
     * Show the form for creating a new Patient.
     *
     * @return View
     */

    public function create(): View
    {
        $user = $this->userRepository->pluck('name', 'id');
        

        $hasCustomField = in_array($this->patientRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->patientRepository->model());
            $html = generateCustomField($customFields);
        }

        // Récupérer la liste des assurances
        $assurances = Assurance::pluck('nom', 'id');
        return view('patients.create')
        ->with("customFields", isset($html) ? $html : false)
        ->with("user", $user)
        ->with("assurances", $assurances)
        ->with("fiche", null); 
    
    }


    /**
     * Store a newly created Patient in storage.
     *
     * @param CreatePatientRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreatePatientRequest $request): RedirectResponse
    {
        $input = $request->all();
    
        // Générez un mot de passe si aucun mot de passe n'est fourni
        if (empty($request->passwordpatient)) {
            $generatedPassword = Str::random(10);
            $input['passwordpatient'] = $generatedPassword;
            Log::info("Generated password: " . $generatedPassword);
        } else {
            $generatedPassword = $request->password;
            Log::info("Provided password: " . $generatedPassword);
        }
    
        try {
            // Si un patient existant est sélectionné
            if ($request->has('existing_patient_id') && $request->existing_patient_id) {
                $patient = Patient::findOrFail($request->existing_patient_id);
                
                // Établir la relation doctor-patient
                if ($this->associatePatientToDoctor($patient)) {
                    Flash::success("Le patient existant a été associé avec succès.");
                    return redirect()->route('patients.create');
                }
                
                return redirect()->back()->withErrors(['error' => 'Impossible d\'associer le patient existant.']);
            }
    
            // Vérifiez si l'email ou le numéro de téléphone existe déjà
            $query = User::query();
    
            if (!empty($request->phone_number)) {
                $query->where('phone_number', $request->phone_number);
            }
    
            if (!empty($request->email)) {
                $query->orWhere('email', $request->email);
            }
    
            $existingUser = $query->first();
    
            // Si un utilisateur est trouvé avec le même numéro de téléphone ou email
            if ($existingUser) {
                Log::info("Existing user found with ID: " . $existingUser->id);
                
                // Vérifiez si la demande est pour créer un sous-profil
                if ($request->has('create_subprofile') && $request->input('create_subprofile') == 1) {
                    // Création d'un sous-profil
                    Log::info("Creating sub-profile for user ID: " . $existingUser->id);
                    
                    // Vérifier que la relation est renseignée
                    if (!$request->has('type_of_relationship') || empty($request->type_of_relationship)) {
                        return redirect()->back()->withErrors(['error' => 'Veuillez spécifier le type de relation.']);
                    }
                    
                    // Créer le patient en tant que sous-profil
                    $patient = $this->patientRepository->create([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'gender' => $request->gender,
                        'phone_number' => $request->phone_number,
                        'email' => $request->email,
                        'user_id' => $existingUser->id,
                        'is_main_profil' => 0,
                        'type_of_relationship' => $request->type_of_relationship,
                        'date_naissance' => $request->date_naissance
                    ]);
                    
                    Log::info("Sub-profile created with ID: " . $patient->id . " for User ID: " . $existingUser->id);
                    
                    // Établir la relation doctor-patient
                    if ($this->associatePatientToDoctor($patient)) {
                        Flash::success("Le sous-profil a été créé et associé avec succès.");
                        return redirect()->route('patients.create');
                    }
                    
                    return redirect()->back()->withErrors(['error' => 'Impossible d\'associer le sous-profil au médecin.']);
                }
                
                // Si ce n'est pas une demande de sous-profil explicite
                $mainPatient = Patient::where('user_id', $existingUser->id)
                                   ->where('is_main_profil', 1)
                                   ->first();
                
                if ($mainPatient) {
                    session()->flash('existingPatient', [
                        'id' => $mainPatient->id,
                        'name' => $mainPatient->first_name . ' ' . $mainPatient->last_name,
                        'phone_number' => $mainPatient->phone_number,
                    ]);
                    
                    session()->flash('formData', [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'gender' => $request->gender,
                        'date_naissance' => $request->date_naissance,
                        'numFiche' => $request->numFiche


                    ]);
                    
                    session()->flash('showSubProfileModal', true);
                    
                    return redirect()->route('patients.create');
                }
    
                // Si l'utilisateur existe mais n'a pas de patient principal associé
                $patient = $this->patientRepository->create(array_merge($input, [
                    'user_id' => $existingUser->id,
                    'is_main_profil' => 1,
                    'type_of_relationship' => 'principal',
                ]));
                
                if ($this->associatePatientToDoctor($patient)) {
                    Flash::success("Le patient a été associé au médecin avec succès.");
                } else {
                    return redirect()->back()->withErrors(['error' => 'Impossible d\'associer le patient au médecin.']);
                }
            } else {
                // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
                $user = User::create([
                    'name' => json_encode(['fr' => $request->first_name]),
                    'lastname' => json_encode(['fr' => $request->last_name]),
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                    'passwordpatient' => Hash::make($generatedPassword),
                ]);
    
                // Créez le patient et associez-le à l'utilisateur
                $patient = $this->patientRepository->create(array_merge($input, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_main_profil' => 1,
                    'type_of_relationship' => 'principal',
                ]));
                
                if ($this->associatePatientToDoctor($patient)) {
                    Flash::success("Le patient a été associé au médecin avec succès.");
                }
            }
    
            // Gestion des pièces jointes et envoi d'emails/SMS
            $this->handleMediaAttachments($input, $patient);
            $this->sendWelcomeNotifications($patient, $generatedPassword);
    
            Flash::success(__('lang.saved_successfully', ['operator' => __('lang.patient')]));
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement du patient : " . $e->getMessage());
            Flash::error("Une erreur inattendue est survenue. Veuillez réessayer.");
        }
    
        return redirect()->route('patients.create');
    }
    
    public function getRelatedPatients($mainPatientId, $relation)
    {
        $mainPatient = Patient::findOrFail($mainPatientId);
    
        $relatedPatients = Patient::where('user_id', $mainPatient->user_id)
            ->where('type_of_relationship', $relation)
            ->where('id', '!=', $mainPatientId)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'first_name' => is_array($patient->first_name) ? $patient->first_name['fr'] ?? '' : $patient->first_name,
                    'last_name' => is_array($patient->last_name) ? $patient->last_name['fr'] ?? '' : $patient->last_name,
                    'date_naissance' => $patient->date_naissance,
                ];
            });
    
        return response()->json($relatedPatients);
    }
    
    /**
     * Associe un patient à un médecin connecté s'il ne l'est pas déjà.
     *
     * @param Patient $patient
     * @return bool True si l'association a été effectuée, False sinon.
     */
    private function associatePatientToDoctor(Patient $patient): bool
    {
        $doctorId = auth()->user()->getDoctorId();
        $doctor = Doctor::find($doctorId);
    
        if ($doctor) {
            if (!$doctor->patients()->where('patient_id', $patient->id)->exists()) {
                $doctor->patients()->attach($patient->id);
                Log::info("Patient ID: " . $patient->id . " associated with Doctor ID: " . $doctor->id);
            } else {
                Log::info("Patient ID: " . $patient->id . " already associated with Doctor ID: " . $doctor->id);
            }
    
            // Créer la fiche si elle n'existe pas
            $userId = auth()->id();
    
            $fiche = Fiche::where('patient_id', $patient->id)
            ->where('user_id', $userId)
            ->first();

if (!$fiche) {
  // Récupère le numFiche saisi (s'il existe)
  $numFiche = request()->input('numFiche') ?? null;

  $fiche = new Fiche([
      'patient_id' => $patient->id,
      'user_id' => $userId,
      'numFiche' => $numFiche,
  ]);
  $fiche->save();

  if (!$fiche->code) {
      Log::error("Erreur lors de la génération du code de la fiche pour le patient ID: " . $patient->id);
      return false;
  }

  Log::info("Fiche créée pour le patient ID: " . $patient->id . " avec le code: " . $fiche->code . " et numFiche: " . ($fiche->numFiche ?? 'null'));
}

return true;
}

Log::warning("No doctor associated with user ID: " . auth()->id());
return false;
}
    
    
    /**
     * Envoie un SMS de bienvenue au patient
     *
     * @param Patient $patient Le patient
     * @param string $password Le mot de passe généré
     * @param string $shortUrl Le lien court
     * @return void
     */
    private function sendWelcomeSms(Patient $patient, string $password, string $shortUrl): void
    {
        $doctorId = auth()->user()->getDoctorId();
        $doctor = Doctor::find($doctorId);
    
        if (!$doctor) {
            Log::error("Médecin non trouvé pour l'envoi du SMS");
            return;
        }
    
        $numFrance = $doctor->num_france;
        $api = $doctor->api_key;
        $to = $patient->phone_number;
        $alphasender = 'Wic doctor';
        
        $message = "Bienvenue " . $patient->first_name . " " . $patient->last_name . " chez Wic-Dr avec Dr." . $doctor->name . ".\n".
            "Utilisateur: " . $to . "\n" .
            "MDP: $password\n" .
            "RDV: $shortUrl\n";
    
        if (Str::startsWith($to, '+33')) {
            // Envoi via le service SMS France
            $smsResult = $this->sendsms($api, $numFrance, $to, $message, $alphasender);
            
            if ($smsResult) {
                Log::info("SMS envoyé avec succès à $to");
            } else {
                Log::error("Échec de l'envoi du SMS à $to");
            }
        } elseif (Str::startsWith($to, '+216')) {
            // Envoi via le service Tunisie
            $response = Http::post('https://wic-doctor.com:3004/send-sms-vats', [
                'gsm' => str_replace('+', '', $to),
                'message' => $message
            ]);
            
            if ($response->successful() && $response->json('success') === true) {
                Log::info("SMS Tunisie envoyé avec succès à $to");
            } else {
                Log::error("Échec de l'envoi du SMS Tunisie à $to : " . $response->body());
            }
        } else {
            Log::warning("Code pays non pris en charge pour le numéro : $to");
        }
    }
    private function sendWelcomeNotifications(Patient $patient, string $password): void
    {
        // 1. Génération du lien court
        $response = app()->call([$this, 'genererLink']);
    
        if ($response instanceof \Illuminate\Http\JsonResponse && $response->status() === 200) {
            // Récupération du lien court
            $shortUrl = $response->getData()->short_link;
    
            // 2. Envoi de l'email si l'adresse existe
            if (!empty($patient->user->email)) {
                try {
                    // Envoi de l'e-mail avec les informations du patient
                    Mail::to($patient->user->email)->send(
                        new AddPatientMail($patient->user, $password, $patient->user->email, $shortUrl)
                    );
    
                    Log::info("Email envoyé à {$patient->user->email} avec le lien court : {$shortUrl}");
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'envoi de l'email : " . $e->getMessage());
                }
            } else {
                Log::info("Aucun email fourni, envoi d'email ignoré.");
            }
    
            // 3. Envoi du SMS avec les informations de connexion
            $this->sendWelcomeSms($patient, $password, $shortUrl);
        } else {
            Log::error("Échec de la génération du lien court.");
        }
    }
    
    
    private function sendsms($api_key, $from, $to, $message, $alphasender = 'wic doctor')
    {
        $url = 'https://dashboard.wic-sms.com/apis/smscontact/';

        // Supprimer le "+" au début si présent
        if (strpos($to, '+') === 0) {
            $to = substr($to, 1); // Supprime le premier caractère '+'
        }

        $fields = [
            'apikey' => $api_key,
            'from' => $from,
            'to' => $to,
            'message' => $message,
            'alphasender' => $alphasender,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::info("HTTP Code: $httpCode");
        Log::info("API Response: $result");

        // Analyse de la réponse
        $response = json_decode($result, true);
        if (isset($response['status']) && $response['status'] === "0") {
            Log::info("SMS envoyé avec succès à $to : $message from:  $from avec api key:  $api_key ");
        } else {
            Log::error("Échec de l'envoi du SMS. Réponse de l'API : " . $result);
        }

        return $result;
    }



    public function genererLink()
    {
        $doctorId = auth()->user()->getDoctorId();

        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return response()->json(['error' => 'Médecin non trouvé pour cet utilisateur'], 404);
        }

        $userWithAddress = $doctor->user()->with('address')->first();
        $address = $userWithAddress->address;

        $pays = $address && $address->pays ? json_decode($address->pays, true) : null;
        $pays = isset($pays['fr']) ? strtolower($pays['fr']) : (is_array($pays) ? strtolower(reset($pays) ?: '') : ($pays ? strtolower($pays) : null));

        $gouvernorat = $address && $address->gouvernorat ? json_decode($address->gouvernorat, true) : null;
        $gouvernorat = isset($gouvernorat['fr']) ? strtolower($gouvernorat['fr']) : (is_array($gouvernorat) ? strtolower(reset($gouvernorat) ?: '') : ($gouvernorat ? strtolower($gouvernorat) : null));

        if ($gouvernorat) {
            $gouvernorat = str_replace(' ', '-', $gouvernorat);
        }

        if (!$pays || !$gouvernorat) {
            return response()->json(['error' => 'Adresse du médecin incomplète'], 400);
        }

        $specialities = $doctor->specialities;

        if ($specialities->isEmpty()) {
            return response()->json(['error' => 'Aucune spécialité trouvée pour ce médecin'], 400);
        }

        $specialityName = $specialities->first()->name;

        if (is_string($specialityName)) {
            $specialityName = strtolower($specialityName);
        } else {
            $specialityName = json_decode($specialityName, true);
            $specialityName = isset($specialityName['fr']) ? strtolower($specialityName['fr']) : (is_array($specialityName) ? strtolower(reset($specialityName) ?: '') : null);
        }

        if ($specialityName) {
            $specialityName = str_replace(' ', '-', $specialityName);
        }

        $randomId = $doctor->id_aleatoire;
        $doctorName = $doctor->name;
        if (is_string($doctorName)) {
            // Attempt to decode the string as JSON.
            $decoded = json_decode($doctorName, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Use the 'fr' locale if available, or the first value if not.
                $doctorName = isset($decoded['fr'])
                    ? strtolower($decoded['fr'])
                    : (is_array($decoded) ? strtolower(reset($decoded) ?: '') : strtolower($doctorName));
            } else {
                $doctorName = strtolower($doctorName);
            }
        } else {
            // If it's not a string, try decoding anyway.
            $decoded = json_decode($doctorName, true);
            $doctorName = isset($decoded['fr'])
                ? strtolower($decoded['fr'])
                : (is_array($decoded) ? strtolower(reset($decoded) ?: '') : '');
        }

        if ($doctorName) {
            $doctorName = str_replace(' ', '-', $doctorName);
        }

        $link = "https://wic-doctor.com/medecin/{$pays}/{$gouvernorat}/{$specialityName}/dr-{$doctorName}-{$randomId}.html";

        $randomId = rand(100000, 999999);
        $aliasBase = 'dr-' . $randomId;
        $alias = substr($aliasBase . '-' . uniqid(), 0, 10);

        $client = new Client();
        $apiUrl = 'https://wic-link.com/api/v1/link';
        $token = '3|XCU8CPfKmf6oKzKWBv2zz9XCiWvyjIfNRLDB4yyxe5c42bcd';

        try {
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'long_url' => $link,
                    'type' => 'direct',
                    'active' => true,
                    'alias' => $alias,
                ]
            ]);


            $responseContent = $response->getBody()->getContents();
            \Log::info('Full API response: ' . $responseContent);
            $responseData = json_decode($responseContent, true);

            \Log::info('Response Data: ' . print_r($responseData, true));

            if (isset($responseData['status']) && $responseData['status'] == 'success') {
                if (isset($responseData['link']['short_url']) && !empty($responseData['link']['short_url'])) {
                    $shortUrl = $responseData['link']['short_url'];
                    \Log::info('Short URL: ' . $shortUrl);
                    return response()->json(['short_link' => $shortUrl], 200);
                } else {
                    \Log::error('Missing short_url in response data.');
                    return response()->json(['error' => 'Le champ short_url est manquant dans la réponse de l\'API'], 400);
                }
            } else {
                \Log::error('API response status not success: ' . print_r($responseData, true));
                return response()->json(['error' => 'Erreur lors du raccourcissement du lien'], 400);
            }

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                \Log::error('API HTTP error response: ' . $e->getResponse()->getBody()->getContents());
            }
            \Log::error('Request failed: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la requête à l\'API'], 500);
        } catch (\Exception $e) {
            \Log::error('Unexpected error: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de l\'appel à l\'API'], 500);
        }
    }



    

    private function handleMediaAttachments($input, $patient)
    {
        // Gestion des fichiers joints comme l'image ou la carte d'identité
        if (isset($input['image']) && is_array($input['image'])) {
            foreach ($input['image'] as $fileUuid) {
                $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($patient, 'image');
            }
        }

        if (isset($input['card_id']) && is_array($input['card_id'])) {
            foreach ($input['card_id'] as $fileUuid) {
                $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                $mediaItem = $cacheUpload->getMedia('card_id')->first();
                $mediaItem->copy($patient, 'card_id');
            }
        }
    }

    /**
     * Display the specified Patient.
     *
     * @param  int $id
     *
     * @return RedirectResponse|View
     */
    public function show(int $id): RedirectResponse|View
    {
        $patient = $this->patientRepository->findWithoutFail($id);

        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }
        return view('patients.show')->with('patient', $patient);
    }

    /**
     * Show the form for editing the specified Patient.
     *
     * @param  int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id): RedirectResponse|View
    {
        $patient = $this->patientRepository->findWithoutFail($id);

        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));

            return redirect(route('patients.index'));
        }

        $user = $this->userRepository->pluck('name', 'id');
        $customFieldsValues = $patient->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->patientRepository->model());
        $hasCustomField = in_array($this->patientRepository->model(), setting('custom_field_models', []));

        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        // Récupérer les assurances
        $assurances = Assurance::pluck('nom', 'id');
        $fiche = $patient->fiche; // Utilise la relation définie dans le modèle Patient


        return view('patients.edit')
            ->with('patient', $patient)
            ->with('customFields', isset($html) ? $html : false)
            ->with('user', $user)
            ->with('assurances', $assurances) // Passer $assurances à la vue
            ->with('fiche', $fiche); 
    }
    /**
     * Update the specified Patient in storage.
     *
     * @param  int              $id
     * @param UpdatePatientRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdatePatientRequest $request): RedirectResponse
    {
        $patient = $this->patientRepository->findWithoutFail($id);
    
        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }
    
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->patientRepository->model());
    
        try {
            // Vérification spécifique si patient associé tente de changer email ou téléphone
            \Log::info('Email dans input : ' . ($input['email'] ?? 'non défini'));
\Log::info('Email du patient : ' . $patient->email);
\Log::info('Téléphone dans input : ' . ($input['phone_number'] ?? 'non défini'));
\Log::info('Téléphone du patient : ' . $patient->phone_number);

            if (!$patient->is_main_profil) {
                if (
                    !$patient->is_main_profil &&
                    (
                        (array_key_exists('email', $input) && trim($input['email']) !== trim((string)$patient->email)) ||
                        (array_key_exists('phone_number', $input) && trim($input['phone_number']) !== trim((string)$patient->phone_number))
                    )
                )
                
                 {
                    Flash::error("Ce numéro/email est lié au profil principal. Veuillez modifier les informations du profil principal.");
                    return redirect()->back()->withInput();
                }
            }
    
            // Mise à jour du patient
            $patient = $this->patientRepository->update($input, $id);
    
            // Mise à jour des images
            if (isset($input['image']) && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($patient, 'image');
                }
            }
    
            if (isset($input['card_id']) && is_array($input['card_id'])) {
                foreach ($input['card_id'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('card_id')->first();
                    $mediaItem->copy($patient, 'card_id');
                }
            }
    
            // Mise à jour des custom fields
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $patient->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
    
            // Traitement spécifique si patient principal
            if ($patient->is_main_profil && $patient->user) {
                $patient->user->update([
                    'name' => $input['first_name'] ?? $patient->user->name,
                    'lastname' => $input['last_name'] ?? $patient->user->lastname,
                    'email' => $input['email'] ?? $patient->user->email,
                    'phone_number' => $input['phone_number'] ?? $patient->user->phone_number,
                ]);
    
                // Mettre à jour email + numéro chez les patients associés
                $relatedPatients = Patient::where('is_main_profil', 0)
                    ->where('user_id', $patient->user_id)
                    ->get();
    
                foreach ($relatedPatients as $relatedPatient) {
                    $relatedPatient->update([
                        'email' => $patient->email,
                        'phone_number' => $patient->phone_number,
                    ]);
                }
            }
    
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }
    
        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.patient')]));
        return redirect(route('patients.index'));
    }
    
    

    /**
     * Remove the specified Patient from storage.
     *
     * @param  int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {

        // Trouver le patient par ID
        $patient = $this->patientRepository->findWithoutFail($id);

        // Si le patient n'est pas trouvé, retourner une erreur
        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }

        // Chercher l'ID du médecin connecté
        $doctorId = auth()->user()->getDoctorId();

        // Chercher l'association du patient avec ce médecin dans la table doctor_patient
        $doctorPatient = \DB::table('doctor_patients')
            ->where('patient_id', $id)
            ->where('doctor_id', $doctorId) // Vérifier si le patient est associé à ce médecin
            ->first();

        // Si une telle association existe, la supprimer
        if ($doctorPatient) {
            \DB::table('doctor_patients')
                ->where('patient_id', $id)
                ->where('doctor_id', $doctorId)
                ->delete();

            Flash::success("L'association du patient a été supprimée avec succès.");
        } else {
            // Si l'association n'existe pas, retourner une erreur
            Flash::error("Ce patient n'est pas associé à ce médecin.");
        }

        // Retourner à la liste des patients
        return redirect(route('patients.index'));
    }


    /**
     * Remove Media of Patient
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $patient = $this->patientRepository->findWithoutFail($input['id']);
        try {
            if ($patient->hasMedia($input['collection'])) {
                $patient->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }


    /**
     * Open email client with the patient's email.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function openEmailClient(int $id): RedirectResponse
    {
        // Récupérer le patient
        $patient = $this->patientRepository->findWithoutFail($id);

        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }

        // Récupérer l'utilisateur associé au patient
        $user = $this->userRepository->findWithoutFail($patient->user_id);

        if (empty($user) || empty($user->email)) {
            Flash::error(__('lang.no_email', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }

        // Ouvrir le client de messagerie avec l'adresse e-mail de l'utilisateur
        $email = urlencode($user->email);
        return redirect("mailto:{$email}");
    }


    /**
     * Open WhatsApp client with the patient's phone number.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function openWhatsAppClient(int $id): RedirectResponse
    {
        // Récupérer le patient
        $patient = $this->patientRepository->findWithoutFail($id);

        if (empty($patient)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }

        // Récupérer l'utilisateur associé au patient
        $user = $this->userRepository->findWithoutFail($patient->user_id);

        // Vérifiez que le numéro de téléphone est valide
        if (empty($user) || empty($user->phone_number)) {
            Flash::error(__('lang.phone_number', ['operator' => __('lang.patient')]));
            return redirect(route('patients.index'));
        }

        // Ouvrir le client WhatsApp avec le numéro de téléphone
        $phone = urlencode($user->phone_number);
        return redirect("https://web.whatsapp.com/send?phone={$phone}");
    }


  
    

}