<?php
/*
 * File name: DoctorController.php
 * Last modified: 2024.05.03 at 15:11:01
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\Criteria\Clinics\ClinicsOfUserCriteria;
use App\Criteria\Doctors\DoctorsOfUserCriteria;
use App\DataTables\DoctorDataTable;
use App\Http\Requests\CreateDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Repositories\SpecialityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ClinicRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
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
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Repositories\RoleRepository;
use App\Models\Address;
use App\Models\Doctor;
use App\Models\Speciality;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /** @var  DoctorRepository */
    private DoctorRepository $doctorRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;
    /**
     * @var SpecialityRepository
     */
    private SpecialityRepository $specialityRepository;
    /**
     * @var ClinicRepository
     */
    private ClinicRepository $clinicRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
        /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    public function __construct(DoctorRepository $doctorRepo, RoleRepository $roleRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo
        , SpecialityRepository $specialityRepo
        , ClinicRepository $clinicRepo
        ,UserRepository $userRepo)

    {
        parent::__construct();
        $this->doctorRepository = $doctorRepo;
        $this->roleRepository = $roleRepo;

        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->specialityRepository = $specialityRepo;
        $this->clinicRepository = $clinicRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the Doctor.
     *
     * @param DoctorDataTable $doctorDataTable
     * @return mixed
     */
    public function index(DoctorDataTable $doctorDataTable): mixed
    {
        return $doctorDataTable->render('doctors.index');
    }

    /**
     * Show the form for creating a new Doctor.
     *
     * @return View
     */
    public function create():View
    {
        $user = $this->userRepository->pluck('name','id');
        $speciality = $this->specialityRepository->pluck('name', 'id');
        $clinic = $this->clinicRepository->getByCriteria(new ClinicsOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $specialitiesSelected = [];
        $hasCustomField = in_array($this->doctorRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->doctorRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('doctors.create')->with("customFields", isset($html) ? $html : false)->with("speciality", $speciality)->with("specialitiesSelected", $specialitiesSelected)->with("clinic", $clinic)->with("user",$user);
    }

    /**
     * Store a newly created Doctor in storage.
     *
     * @param CreateDoctorRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateDoctorRequest $request):RedirectResponse
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->doctorRepository->model());
        try {
            $doctor = $this->doctorRepository->create($input);
            $doctor->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($doctor, 'image');
                }
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.doctor')]));

        return redirect(route('users.profile'));
    }

    /**
     * Display the specified Doctor.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function show(int $id): RedirectResponse|View
    {
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);

        if (empty($doctor)) {
            Flash::error('Doctor not found');

            return redirect(route('doctors.index'));
        }

        return view('doctors.show')->with('doctor', $doctor);
    }

    /**
     * Show the form for editing the specified Doctor.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function edit(int $id): RedirectResponse|View
    {
        $user = $this->userRepository->pluck('name','id');
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);
        if (empty($doctor)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.doctor')]));

            return redirect(route('doctors.index'));
        }
        $speciality = $this->specialityRepository->pluck('name', 'id');
        $clinic = $this->clinicRepository->getByCriteria(new ClinicsOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $specialitiesSelected = $doctor->specialities()->pluck('specialities.id')->toArray();

        $customFieldsValues = $doctor->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->doctorRepository->model());
        $hasCustomField = in_array($this->doctorRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('doctors.edit')->with('doctor', $doctor)->with("customFields", isset($html) ? $html : false)->with("speciality", $speciality)->with("specialitiesSelected", $specialitiesSelected)->with("clinic", $clinic)->with("user",$user);
    }

    /**
     * Update the specified Doctor in storage.
     *
     * @param int $id
     * @param UpdateDoctorRequest $request
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateDoctorRequest $request):RedirectResponse
    {
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);

        if (empty($doctor)) {
            Flash::error('Doctor not found');
            return redirect(route('doctors.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->doctorRepository->model());
        try {
            $input['specialities'] = isset($input['specialities']) ? $input['specialities'] : [];
            $doctor = $this->doctorRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($doctor, 'image');
                }
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $doctor->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.doctor')]));

        return redirect(route('users.profile'));
    }

    /**
     * Remove the specified Doctor from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function destroy(int $id):RedirectResponse
    {
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);

        if (empty($doctor)) {
            Flash::error('E Service not found');

            return redirect(route('doctors.index'));
        }

        $this->doctorRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.doctor')]));

        return redirect(route('doctors.index'));
    }

    /**
     * Remove Media of Doctor
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $doctor = $this->doctorRepository->findWithoutFail($input['id']);
        try {
            if ($doctor->hasMedia($input['collection'])) {
                $doctor->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function profileDoctor()
    {
        // Récupérer l'utilisateur connecté
        $user = $this->userRepository->findWithoutFail(auth()->id());
        if (!$user) {
            abort(404, "Utilisateur non trouvé");
        }
    
        // Récupérer le docteur associé à cet utilisateur
        $doctor = $user->doctor; 
    
        if (!$doctor) {
            abort(404, "Aucun docteur associé à cet utilisateur");
        }
    
        unset($doctor->password); // Évitez d'envoyer le mot de passe
    
        $customFields = false;
        $role = $this->roleRepository->pluck('name', 'name');
        $rolesSelected = $user->getRoleNames()->toArray();
    
        $speciality = $this->specialityRepository->pluck('name', 'id');
        $clinic = $this->clinicRepository->getByCriteria(new ClinicsOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $specialitiesSelected = $doctor->specialities()->pluck('specialities.id')->toArray();
    
        // Récupérer les expériences du docteur
        $experiences = $doctor->experiences; 
 
        // Débogage : vérifiez si certaines expériences n'ont pas de description
        /*     foreach ($experiences as $experience) {
                if (empty($experience->description)) {
                    \Log::info("Experience sans description : " . json_encode($experience));
                }
                else{
                    \Log::info(json_encode($experience));

                }
            } */
        // Récupérer les champs personnalisés
        $customFieldsValues = $doctor->customFieldsValues()->with('customField')->get();
        $hasCustomField = in_array($this->userRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
            $customFields = generateCustomField($customFields, $customFieldsValues);
        }
    
        // Retourner la vue avec les données nécessaires
        return view('settings.users.profileDoctor', compact(
            'doctor', 
            'role', 
            'rolesSelected', 
            'customFields', 
            'customFieldsValues', 
            'speciality', 
            'specialitiesSelected', 
            'clinic',
            'experiences' // Passer les expériences à la vue
        ));
    }


 public function editProfil()
    {
        // Récupérer l'utilisateur authentifié
        $user = auth()->user();
        
        // Récupérer le médecin associé à l'utilisateur
        $doctor = Doctor::where('user_id', $user->id)->first();
    
        // Récupérer l'adresse du médecin associée à l'utilisateur
        $address = Address::where('user_id', $user->id)->first();

        // Récupérer typ_consultation du doctor
        $consultationMethods = explode(',', $doctor->type_consultation ?? '');
     
     
        // Récupérer payment_methods du doctor
        $paymentMethods = explode(',', $doctor->payment_methods ?? '');

        // Récupérer les langues parlées et les transformer en tableau
        $languesParlees = explode(',', $doctor->langues_parlees ?? '');


// Récupérer la spécialité du médecin via la table d'association
$doctorSpeciality = $doctor->doctorSpeciality;

// Récupérer la description de la table d'association
$descriptionSpecialite = $doctorSpeciality->description ?? '';

// Récupérer la spécialité sélectionnée
$specialitySelected = $doctorSpeciality->speciality ?? null;

// Récupérer toutes les spécialités disponibles
$specialities = Speciality::pluck('name', 'id');

        // Récupérer les diplômes associés au médecin
        $diplomes = $doctor->diplomes;




        // Vérifier que l'adresse existe et que le pays est défini dans le tableau
        if ($address && isset($address->pays['fr'])) {
            $pays = $address->pays['fr'];
            
            // Vérifier le pays et récupérer les informations correspondantes
            if ($pays == 'france') {
                // Récupérer la région, le département et la ville pour la France
                $Région = $address->Région['fr'] ?? null;
                $Département = $address->Département['fr'] ?? null;
                return view('edit_doctor_profil.editProfil', compact('user', 'doctor', 'address', 'Région', 'Département', 'consultationMethods', 'paymentMethods', 'specialities', 'specialitySelected', 'descriptionSpecialite' ,'languesParlees', 'diplomes'));
            } elseif ($pays == 'tunisie') {
                // Récupérer la ville et le gouvernorat pour la Tunisie
                $ville = $address->ville['fr'] ?? null;
                $gouvernorat = $address->gouvernorat['fr'] ?? null;
                return view('edit_doctor_profil.editProfil', compact('user', 'doctor', 'address', 'ville', 'gouvernorat', 'consultationMethods', 'paymentMethods', 'specialities', 'specialitySelected','descriptionSpecialite' , 'languesParlees', 'diplomes'));
            }
        }
    
        // Si aucun pays n'est trouvé ou la structure est incorrecte, retourner la vue sans informations spécifiques
        return view('edit_doctor_profil.editProfil', compact('user', 'doctor', 'address', 'consultationMethods', 'paymentMethods', 'specialities', 'specialitySelected','descriptionSpecialite' , 'languesParlees', 'diplomes'));
    }

    public function editInfoPersonnelle(Request $request)
    {
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();
    
        if (!$doctor) {
            return response()->json(['error' => 'Le médecin n\'existe pas.'], 404);
        }
    
        $user->update([
            'name' => $request->input('lastname'),
            'lastname' => $request->input('name'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
        ]);
        // Convertir les méthodes de consultation en chaîne séparée par des virgules
        $consultationMethods = implode(',', $request->input('consultation_methods', []));
        $payment_methods = implode(',', $request->input('payment_methods', []));

            $doctor->update([
                'name' => $request->input('name') . ' ' . $request->input('lastname'),
                'bio' => $request->input('bio'),
                'type_consultation' => $consultationMethods,
                'fixe' => $request->input('cabinet_number'),
                'facebook' => $request->input('facebook'),
                'instagram' => $request->input('instagram'),
                'site_web' => $request->input('website'),
                'description' => $request->input('description'),
                'payment_methods' => $payment_methods,
            ]);
            $this->executeNodeScript($doctor);

        
            return response()->json(['success' => 'Informations mises à jour avec succès.']);
    }
    
    
    
    
    public function editCV(Request $request)
    {
        $user = auth()->user();
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return response()->json(['error' => 'Le médecin n\'existe pas.'], 404);
        }
        
        // Récupérer les langues sélectionnées
        $langues = $request->input('langues', []);
        
        // Convertir le tableau de langues en chaîne séparée par des virgules
        $languesStr = implode(',', $langues);
        
        // Mettre à jour les langues parlées du médecin
        $doctor->update([
            'langues_parlees' => $languesStr,
        ]);
        
        // Gérer les diplômes
        $diplomes = $request->input('diplomes', []);
        
        // Supprimer les anciens diplômes pour ce médecin
        $doctor->diplomes()->delete();
        
        // Ajouter les nouveaux diplômes
        foreach ($diplomes as $diplome) {
            if (!empty($diplome)) {
                $doctor->diplomes()->create([
                    'name' => $diplome,
                    'doctor_id' => $doctor->id,
                ]);
            }
        }

      // Mettre à jour la description de la spécialité
      $description = $request->input('description');
      $specialityId = $request->input('speciality_id'); // Assurez-vous d'envoyer l'ID de la spécialité
  
      if ($specialityId) {
          DB::table('doctor_specialities')
              ->where('doctor_id', $doctor->id)
              ->where('speciality_id', $specialityId)
              ->update(['description' => $description]);
      }

        return response()->json(['success' => 'Informations mises à jour avec succès.']);
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

        
}
