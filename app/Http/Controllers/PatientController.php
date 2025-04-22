<?php

    namespace App\Http\Controllers;
    use Illuminate\Support\Facades\DB;

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

    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\Session;
    use App\Mail\AddPatientMail;
    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;


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
                ->with("assurances", $assurances);
        }


        /**
         * Store a newly created Patient in storage.
         *
         * @param CreatePatientRequest $request
         *
         * @return RedirectResponse
         */
  

              private function handleMainPatientCreation(array $input): Patient
         {
             $generatedPassword = empty($input['passwordpatient']) 
                 ? Str::random(10) 
                 : $input['passwordpatient'];
         
             // Vérifier d'abord si l'utilisateur existe déjà
             $existingUser = User::where('phone_number', $input['phone_number'])
                             ->orWhere('email', $input['email'])
                             ->first();
         
             // Cas 1: Utilisateur existe déjà avec un patient associé
             if ($existingUser && $existingUser->patient) {
                 throw new \Exception("Ce numéro de téléphone ou email est déjà associé à un patient");
             }
         
             // Cas 2: Utilisateur existe mais sans patient
             if ($existingUser) {
                 return $this->patientRepository->create(array_merge($input, [
                     'user_id' => $existingUser->id,
                     'email' => $existingUser->email,
                     'is_main_profile' => true
                 ]));
             }
         
             // Cas 3: Nouvel utilisateur et nouveau patient
             $user = User::create([
                 'name' => json_encode(['fr' => $input['first_name']]),
                 'lastname' => json_encode(['fr' => $input['last_name']]),
                 'phone_number' => $input['phone_number'],
                 'email' => $input['email'],
                 'password' => Hash::make($generatedPassword),
                 'user_type' => 'patient'
             ]);
         
             return $this->patientRepository->create(array_merge($input, [
                 'user_id' => $user->id,
                 'email' => $user->email,
                 'is_main_profile' => true
             ]));
         }      


         public function store(CreatePatientRequest $request): RedirectResponse
{
    $input = $request->all();
    
    try {
        DB::beginTransaction();
    
        // 1. Créer le patient principal
        $patient = $this->handleMainPatientCreation($input);
    
        // 2. Si un utilisateur est assigné, le créer et lier
        if ($request->has('assigned_user') && !empty($request->assigned_user)) {
            $assignedUser = $this->createAssignedUser($request->assigned_user);
            
            // Mettre à jour le patient avec l'ID de l'utilisateur assigné
            $patient->update([
                'assigned_user_id' => $assignedUser->id,
                'relationship_type' => $request->assigned_user['relationship']
            ]);
        }
    
        // 3. Associer le patient au médecin
        $this->associatePatientToDoctor($patient);
    
        // 4. Gestion des médias
        $this->handleMediaAttachments($input, $patient);
    
        // 5. Envoi des notifications (email/SMS)
        $this->sendPatientNotifications($patient, $input);
    
        DB::commit();

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.patient')]));
        return redirect()->route('patients.edit', $patient->id);

    } catch (\Exception $e) {
        DB::rollBack();
        Flash::error("Erreur : " . $e->getMessage());
        return redirect()->back()->withInput();
    }
}

private function createAssignedUser(array $userData): User
{
    // Vérifier d'abord si l'utilisateur existe déjà
    $existingUser = User::where('phone_number', $userData['phone'])
                    ->orWhere('email', $userData['email'] ?? null)
                    ->first();

    if ($existingUser) {
        return $existingUser;
    }

    // Créer un nouvel utilisateur
    return User::create([
        'name' => json_encode(['fr' => $userData['first_name']]),
        'lastname' => json_encode(['fr' => $userData['last_name']]),
        'phone_number' => $userData['phone'],
        'email' => $userData['email'] ?? null,
        'password' => Hash::make(Str::random(10)),
        'user_type' => 'caregiver'
    ]);
}
        /**
         * Envoi des notifications (email et SMS)
         */
        private function sendPatientNotifications(Patient $patient, array $input): void
        {
            $user = $patient->user;
            $generatedPassword = $input['passwordpatient'] ?? null;

            // Génération du lien court
            $shortUrlResponse = $this->genererLink();
            
            if ($shortUrlResponse instanceof \Illuminate\Http\JsonResponse) {
                $responseData = json_decode($shortUrlResponse->getContent(), true);
                
                if (isset($responseData['short_link'])) {
                    $shortUrl = $responseData['short_link'];

                    // Envoi email
                    if (!empty($user->email)) {
                        try {
                            Mail::to($user->email)->send(new AddPatientMail($user, $generatedPassword, $user->email, $shortUrl));
                        } catch (\Exception $e) {
                            Log::error("Email sending failed: " . $e->getMessage());
                        }
                    }

                    // Envoi SMS
                    $doctorId = auth()->user()->getDoctorId();
                    $doctor = Doctor::find($doctorId);
                    
                    if ($doctor) {
                        $message = "Bienvenue " . $patient->first_name . " " . $patient->last_name . " chez Wic-Dr avec Dr." . $doctor->name . ".\n".
                            "Utilisateur: " . $user->phone_number . "\n" .
                            "MDP: $generatedPassword\n" .
                            "RDV: $shortUrl\n";

                        $this->sendsms($doctor->api_key, $doctor->num_france, $user->phone_number, $message, 'Wic doctor');
                    }
                }
            }
        }
        public function assignUser(Patient $patient, Request $request)
        {
            try {
                $userData = $request->input('assigned_user');
                
                $user = User::create([
                    'name' => json_encode(['fr' => $userData['first_name']]),
                    'lastname' => json_encode(['fr' => $userData['last_name']]),
                    'phone_number' => $userData['phone'],
                    'password' => Hash::make(Str::random(10)),
                    'user_type' => 'sub_profile',
                    'assigned_to_patient_id' => $patient->id,
                    'relationship_type' => $userData['relationship']
                ]);
        
                return response()->json(['success' => true]);
        
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        /**
         * Associe un patient à un médecin
         */
        private function associatePatientToDoctor(Patient $patient): bool
        {
            $doctorId = auth()->user()->getDoctorId();
            $doctor = Doctor::find($doctorId);

            if ($doctor && !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
                $doctor->patients()->attach($patient->id);
                return true;
            }

            return false;
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

            // Récupérer les relations du patient
            $relatedPatients = [];
            if ($patient->is_main_profile) {
                // Si c'est un profil principal, récupérer ses sous-profils
                $relatedPatients = \DB::table('patient_relationships')
                    ->join('patients', 'patient_relationships.sub_patient_id', '=', 'patients.id')
                    ->where('patient_relationships.main_patient_id', $patient->id)
                    ->select('patients.*', 'patient_relationships.relationship_type')
                    ->get();
            } else {
                // Si c'est un sous-profil, récupérer son profil principal
                $mainPatient = \DB::table('patient_relationships')
                    ->join('patients', 'patient_relationships.main_patient_id', '=', 'patients.id')
                    ->where('patient_relationships.sub_patient_id', $patient->id)
                    ->select('patients.*', 'patient_relationships.relationship_type')
                    ->first();
                
                if ($mainPatient) {
                    $relatedPatients = collect([$mainPatient]);
                }
            }

            return view('patients.show')
                ->with('patient', $patient)
                ->with('relatedPatients', $relatedPatients);
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
    
            return view('patients.edit')
                ->with('patient', $patient)
                ->with('customFields', isset($html) ? $html : false)
                ->with('user', $user)
                ->with('assurances', $assurances); // Passer $assurances à la vue
        }
        /**
         * Update the specified Patient in storage.
         *
         * @param  int              $id
         * @param UpdatePatientRequest $request
         *
         * @return RedirectResponse
         */
        public function update(int $id, UpdatePatientRequest $request)
        {
            $patient = $this->patientRepository->findWithoutFail($id);
            if (!$patient) {
                Flash::error(__('lang.not_found', ['operator' => __('lang.patient')]));
                return redirect()->route('patients.index');
            }
    
            $input = $request->all();
    
            try {
                DB::beginTransaction();
    
                // 1) Mise à jour du patient
                $patient = $this->patientRepository->update($input, $id);
    
                // 2) Mise à jour de l’utilisateur assigné
                if ($request->filled('assigned_user.id')) {
                    $this->updateAssignedUser($patient, $request->input('assigned_user'));
                }
    
                // 3) Ta gestion media / custom fields…
                $this->handleMediaAttachments($input, $patient);
                // … / champs personnalisés …
    
                DB::commit();
    
                Flash::success(__('lang.updated_successfully', ['operator' => __('lang.patient')]));
                return redirect()->route('patients.edit', $patient->id);
            } catch (\Exception $e) {
                DB::rollBack();
                Flash::error("Erreur : " . $e->getMessage());
                return redirect()->back()->withInput();
            }
        }
    
        /**
         * Met à jour l’utilisateur assigné ET la colonne patient.assigned_user_id si besoin.
         */
        private function updateAssignedUser(Patient $patient, array $userData): void
        {
            // 1) On récupère le user
            $user = User::find($userData['id']);
            if (! $user) {
                return;
            }
        
            // 2) On met à jour ses infos dans la table users
            $user->update([
                'name'         => json_encode([
                    'fr' => $userData['first_name'],
                    'en' => $userData['first_name'],
                ]),
                'lastname'     => json_encode([
                    'fr' => $userData['last_name'],
                    'en' => $userData['last_name'],
                ]),
                'phone_number' => $userData['phone'],
                'email'        => $userData['email'] ?? null,
            ]);
        
            // 3 & 4) Mettre à jour user_id et type de relation du patient si nécessaire
            $needsSave = false;
        
            if ($patient->user_id !== $user->id) {
                $patient->user_id = $user->id;
                $needsSave = true;
            }
        
            if ($patient->relationship_type !== $userData['relationship']) {
                $patient->relationship_type = $userData['relationship'];
                $needsSave = true;
            }
        
            if ($needsSave) {
                $patient->save();
            }
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


        // ... (le reste des méthodes reste inchangé)
    }