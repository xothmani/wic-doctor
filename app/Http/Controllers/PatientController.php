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

use Illuminate\Support\Facades\Mail;
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
            // Vérifiez si l'email ou le numéro de téléphone existe déjà
            $existingUser = User::where('phone_number', $request->phone_number)->first();

            // Si l'utilisateur existe
            if ($existingUser) {
                Log::info("Existing user found with ID: " . $existingUser->id);

                // Vérifiez si l'utilisateur est associé à un patient
                if ($existingUser->patient) {
                    $patient = $existingUser->patient;
                    Log::info("Patient associated with user ID: " . $existingUser->id . " | Patient ID: " . $patient->id);

                    // Associez le patient au médecin connecté
                    if ($this->associatePatientToDoctor($patient)) {
                        Flash::success("Le patient existant a été ajouté à votre liste.");
                        return redirect()->route('patients.create');
                    }

                    return redirect()->back()->withErrors(['error' => 'Ce patient est déjà associé à ce médecin.']);
                }

                Log::info("User exists but no patient associated. User ID: " . $existingUser->id);

                // Associez l'utilisateur à un patient existant
                $patient = $this->patientRepository->create(array_merge($input, ['user_id' => $existingUser->id]));
                Log::info("Patient ID: " . $patient->id . " associated with User ID: " . $existingUser->id);

                // Établir la relation doctor-patient
                if ($this->associatePatientToDoctor($patient)) {
                    Flash::success("Le patient a été associé au médecin avec succès.");
                    return redirect()->route('patients.create');
                }

                return redirect()->back()->withErrors(['error' => 'Impossible d\'associer le patient au médecin.']);
            }

            // Si l'utilisateur n'existe pas, créer un nouvel utilisateur
            Log::info("No existing user found. Creating a new user.");

            // Créez un nouvel utilisateur
            $user = User::create([
                'name' => $request->first_name,
                'lastname' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'passwordpatient' => Hash::make($generatedPassword),
            ]);

            Log::info("New user created with ID: " . $user->id);

            // Vérifiez si le patient est déjà associé à un médecin avant de l'associer à un médecin
            $existingPatient = Patient::where('phone_number', $request->phone_number)->first();

            if ($existingPatient) {
                // Le patient est déjà associé à un médecin, afficher l'erreur
                return redirect()->back()->withErrors(['error' => 'Ce patient est déjà associé à ce médecin.']);
            }

            // Créez le patient et associez-le à l'utilisateur
            // Créez le patient et associez-le à l'utilisateur
            $patient = $this->patientRepository->create(array_merge($input, [
                'user_id' => $user->id,
                'email' => $user->email,
            ]));
            Log::info("New patient created with ID: " . $patient->id);

            // Établir la relation doctor-patient
            if ($this->associatePatientToDoctor($patient)) {
                Log::info("Patient ID: " . $patient->id . " successfully associated with doctor.");
                Flash::success("Le patient a été associé au médecin avec succès.");
            }
            // Vérifiez si le short link a été généré avec succès
            $shortUrlResponse = $this->genererLink();

            if ($shortUrlResponse instanceof \Illuminate\Http\JsonResponse) {
                $responseData = json_decode($shortUrlResponse->getContent(), true);

                if (isset($responseData['short_link'])) {
                    $shortUrl = $responseData['short_link'];

                    // Ajoutez le short link dans l'email
                    if (!empty($request->email)) {
                        try {
                            // Envoi de l'email avec le lien court
                            Mail::to($request->email)->send(new AddPatientMail($user, $generatedPassword, $request->email, $shortUrl));
                            Log::info("Email sent to " . $request->email . " with short link: " . $shortUrl);
                        } catch (\Exception $e) {
                            Log::error("Failed to send email: " . $e->getMessage());
                        }
                    } else {
                        Log::info("No email provided, skipping email sending.");
                    }
                } else {
                    Log::error("Le lien court n'a pas pu être généré.");
                }
            } else {
                Log::error("La réponse n'est pas un JsonResponse valide.");
            }


            // Gestion des pièces jointes
            $this->handleMediaAttachments($input, $patient);




            $shortUrlResponse = $this->genererLink();

            // Ensure that the response is a valid JsonResponse before accessing it
            if ($shortUrlResponse instanceof \Illuminate\Http\JsonResponse) {
                $responseData = json_decode($shortUrlResponse->getContent(), true); // Decode the response content into an array

                // Check if the 'short_link' exists in the response data
                if (isset($responseData['short_link'])) {
                    $shortUrl = $responseData['short_link'];

                    // Continue with the rest of your code
                    $api_key = 'INS15422525105';
                    $from = '33743134840'; // Replace with your sender ID or authorized number
                    $to = $request->phone_number;
                    $alphasender = 'Wic doctor';

                    // SMS message with short link
                    $message = "Bienvenue " . $user->name . " " . $user->lastname . " chez Wic-Doctor.\n" .
                        "Nom d'utilisateur : " . $request->phone_number . "\n" .
                        "Mot de passe : $generatedPassword\n" .
                        "Lien RDV : $shortUrl\n";


                    // Send SMS
                    $smsResult = $this->sendsms($api_key, $from, $to, $message, $alphasender);

                    if ($smsResult) {
                        Log::info("SMS envoyé avec succès à $to : $message");
                    } else {
                        Log::error("Échec de l'envoi du SMS à $to.");
                    }
                } else {
                    Log::error("Le lien court n'a pas pu être généré.");
                }
            } else {
                Log::error("La réponse n'est pas un JsonResponse valide.");
            }


            // Enregistrez un flag pour afficher le modal
            session()->flash('showModal', true);
            Flash::success(__('lang.saved_successfully', ['operator' => __('lang.patient')]));

        } catch (ValidatorException $e) {
            Log::error("Validation error: " . $e->getMessage());
            Flash::error($e->getMessage());
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement du patient : " . $e->getMessage());
            Flash::error("Une erreur inattendue est survenue. Veuillez réessayer.");
        }

        return redirect()->route('patients.create');
    }

    /**
     * Associe un patient à un médecin connecté s'il ne l'est pas déjà.
     *
     * @param Patient $patient
     * @return bool True si l'association a été effectuée, False sinon.
     */
    private function associatePatientToDoctor(\App\Models\Patient $patient): bool
    {
        if (auth()->user()->hasRole('doctor')) {
            $doctor = auth()->user()->doctor;

            if ($doctor) {
                if (!$doctor->patients()->where('patient_id', $patient->id)->exists()) {
                    $doctor->patients()->attach($patient->id);
                    Log::info("Patient ID: " . $patient->id . " associated with Doctor ID: " . $doctor->id);
                    return true;
                }

                Log::info("Patient ID: " . $patient->id . " already associated with Doctor ID: " . $doctor->id);
            } else {
                Log::warning("No doctor associated with user ID: " . auth()->id());
            }
        } else {
            Log::warning("Authenticated user is not a doctor. User ID: " . auth()->id());
        }

        return false;
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
            Log::info("SMS envoyé avec succès à $to : $message");
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
            //dd($input);
            $patient = $this->patientRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($patient, 'image');
                }
            }
            if (isset($input['card_id']) && $input['card_id'] && is_array($input['card_id'])) {
                foreach ($input['card_id'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('card_id')->first();
                    $mediaItem->copy($patient, 'card_id');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $patient->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
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
        $doctorId = auth()->user()->associatedDoctors->pluck('doctor_id')->toArray();

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
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();

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
        $doctorName = strtolower(str_replace(' ', '-', $user->name));

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

}
