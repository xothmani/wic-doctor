<?php
/*
 * File name: AddressController.php
 * Last modified: 2024.05.03 at 12:19:50
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\Criteria\Addresses\AddressesOfUserCriteria;
use App\DataTables\AddressDataTable;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Repositories\AddressRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Flash;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    /** @var  AddressRepository */
    private AddressRepository $addressRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(AddressRepository $addressRepo, CustomFieldRepository $customFieldRepo, UserRepository $userRepo)
    {
        parent::__construct();
        $this->addressRepository = $addressRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the Address.
     *
     * @param AddressDataTable $addressDataTable
     * @return mixed
     */
    public function index(AddressDataTable $addressDataTable): mixed
    {
        return $addressDataTable->render('addresses.index');
    }

    /**
     * Show the form for creating a new Address.
     *
     * @return View
     */
    public function create():View
    {
        $hasCustomField = in_array($this->addressRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('addresses.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created Address in storage.
     *
     * @param CreateAddressRequest $request
     *
     * @return RedirectResponse
     */
   /*  public function store(CreateAddressRequest $request): RedirectResponse
        {	
        $input = $request->all();
            $input['description'] = json_encode(['fr' => $input['description']]);
        $input['address'] = json_encode(['fr' => $input['address']]);
        $input['pays'] = json_encode(['fr' => $input['pays']]);
        $input['ville'] = json_encode(['fr' => $input['ville']]);
            $input['user_id'] = Auth::id();
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
            try {
                $address = $this->addressRepository->create($input);
                $address->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

            } catch (ValidatorException $e) {
                Flash::error($e->getMessage());
            }

            Flash::success(__('lang.saved_successfully', ['operator' => __('lang.address')]));

            return redirect(route('addresses.index', $address->id));
        } 
    */

    /**
     * Display the specified Address.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function show(int $id): RedirectResponse|View
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');

            return redirect(route('addresses.index'));
        }

        return view('addresses.show')->with('address', $address);
    }

    /**
     * Show the form for editing the specified Address.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function edit(int $id): RedirectResponse|View
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.address')]));

            return redirect(route('addresses.index'));
        }
        $customFieldsValues = $address->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        $hasCustomField = in_array($this->addressRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('addresses.edit')->with('address', $address)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Address in storage.
     *
     * @param int $id
     * @param UpdateAddressRequest $request
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateAddressRequest $request): RedirectResponse
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');
            return redirect(route('addresses.index'));
        }
        $input = $request->all();
	 $input['description'] = json_encode(['fr' => $request->input('description')]);
    $input['address'] = json_encode(['fr' => $request->input('address')]);
    $input['pays'] = json_encode(['fr' => $request->input('pays')]);
    $input['ville'] = json_encode(['fr' => $request->input('ville')]);

        $input['user_id'] = $address->user->id;
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->addressRepository->model());
        try {
            $address = $this->addressRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $address->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.address')]));

        return redirect(route('addresses.index'));
    }

    /**
     * Remove the specified Address from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->addressRepository->pushCriteria(new AddressesOfUserCriteria(auth()->id()));
        $address = $this->addressRepository->findWithoutFail($id);

        if (empty($address)) {
            Flash::error('Address not found');

            return redirect(route('addresses.index'));
        }

        $this->addressRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.address')]));

        return redirect(route('addresses.index'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'address' => 'required|string|max:255',
            'pays' => 'required|string',
            'ville' => 'nullable|string', 
            'gouvernorat' => 'nullable|string',
            'Région' => 'nullable|string', 
            'Département' => 'nullable|string', 
        ]);
    
        try {
            $user = auth()->user(); // Récupérer l'utilisateur authentifié
            $doctor = Doctor::where('user_id', $user->id)->first();
    
            if (!$doctor) {
                return response()->json(['error' => 'Le médecin n\'existe pas.'], 404);
            }
    
            // Vérifier si l'utilisateur a déjà une adresse
            $address = Address::where('user_id', $user->id)->first();
    
            // Définir les données de mise à jour
            $data = [
                'address' => json_encode(['fr' => $validatedData['address']], JSON_UNESCAPED_UNICODE), // Ne pas échapper les caractères spéciaux
                'pays' => json_encode(['fr' => $validatedData['pays']], JSON_UNESCAPED_UNICODE), // Ne pas échapper les caractères spéciaux
                'user_id' => $user->id,
                'updated_at' => now(), // Mettre à jour la date
            ];
    
            // Vérifier le pays et remplir les champs correspondants
            if ($validatedData['pays'] === 'france') {
                // Si c'est la France, on met à jour Région et Département et on efface les champs liés à la ville et gouvernorat
                $data['Région'] = $validatedData['ville'] ? json_encode(['fr' => $validatedData['ville']], JSON_UNESCAPED_UNICODE) : null;
                $data['Département'] = $validatedData['gouvernorat'] ? json_encode(['fr' => $validatedData['gouvernorat']], JSON_UNESCAPED_UNICODE) : null;
                $data['ville'] = null; // Effacer la ville
                $data['gouvernorat'] = null; // Effacer le gouvernorat
            } elseif ($validatedData['pays'] === 'tunisie') {
                // Si c'est la Tunisie, on met à jour ville et gouvernorat
                $data['ville'] = $validatedData['ville'] ? json_encode(['fr' => $validatedData['ville']], JSON_UNESCAPED_UNICODE) : null;
                $data['gouvernorat'] = $validatedData['gouvernorat'] ? json_encode(['fr' => $validatedData['gouvernorat']], JSON_UNESCAPED_UNICODE) : null;
                $data['Région'] = null; // Effacer la région
                $data['Département'] = null; // Effacer le département
            }
    
            if ($address) {
                // Si l'adresse existe déjà, on la met à jour
                $address->update($data);
                $this->executeNodeScript($doctor);
                $message = 'Adresse mise à jour avec succès';
            } else {
                // Sinon, on crée une nouvelle adresse
                $address = Address::create($data);
                $this->executeNodeScript($doctor);

                $message = 'Adresse enregistrée avec succès';
            }

    
            return response()->json([
                'message' => $message,
                'address' => $address
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
                'error' => $e->getMessage()
            ], 500);
        }
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
