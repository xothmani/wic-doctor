<?php
/*
 * File name: SpecialityController.php
 * Last modified: 2024.05.03 at 15:38:34
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\DataTables\SpecialityDataTable;
use App\Http\Requests\CreateSpecialityRequest;
use App\Http\Requests\UpdateSpecialityRequest;
use App\Repositories\SpecialityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Models\Speciality;
use App\Jobs\RunNodeScript;
use Illuminate\Support\Facades\File;

class SpecialityController extends Controller
{
    /** @var  SpecialityRepository */
    private SpecialityRepository $specialityRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;

    public function __construct(SpecialityRepository $specialityRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->specialityRepository = $specialityRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Speciality.
     *
     * @param SpecialityDataTable $specialityDataTable
     * @return mixed
     */
    public function index(SpecialityDataTable $specialityDataTable):mixed
    {
        return $specialityDataTable->render('specialities.index');
    }

    /**
     * Show the form for creating a new Speciality.
     *
     * @return View
     */
    public function create():View
    {
        $parentSpeciality = $this->specialityRepository->pluck('name', 'id');

        $hasCustomField = in_array($this->specialityRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('specialities.create')->with("customFields", isset($html) ? $html : false)->with("parentSpeciality", $parentSpeciality);
    }

    /**
     * Store a newly created Speciality in storage.
     *
     * @param CreateSpecialityRequest $request
     *
     * @return RedirectResponse
     */

     public function store(CreateSpecialityRequest $request): RedirectResponse
     {
         $input = $request->all();
         $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
     
         try {
             // Créer la spécialité
             $speciality = $this->specialityRepository->create($input);
             $speciality->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
     
             if (isset($input['image']) && $input['image']) {
                 $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                 $mediaItem = $cacheUpload->getMedia('image')->first();
     
                 if ($mediaItem) {
                     // Supprimer l'ancienne image associée à la spécialité
                     $speciality->clearMediaCollection('image');
     
                     // Copier la nouvelle image
                     $mediaItem->copy($speciality, 'image');
     
                     // Mettre à jour l'ID et le file_name du média
                     $speciality->image = $mediaItem->id;
                     $speciality->image_name = $mediaItem->file_name;
                     $speciality->save();
                 }
             }
     
             // Met à jour les données
             $speciality->refresh(); 
     
             // Log après mise à jour
             //\Log::info("Speciality created: ID = {$speciality->id}, Name = {$speciality->name}");
     
             // **Mise à jour de file.json**
             $jsonFilePath = public_path('script-spec/file.json'); // Chemin du fichier JSON
     
             if (File::exists($jsonFilePath)) {
                 $jsonData = json_decode(File::get($jsonFilePath), true);
             } else {
                 $jsonData = []; // Si le fichier n'existe pas encore
             }
     
             // Parcourir les données JSON et mettre à jour toutes les entrées
             foreach ($jsonData as &$entry) {
                 $entry['specialite_id'] = $speciality->id;
                 $entry['specialite'] = json_encode(['fr' => $speciality->name], JSON_UNESCAPED_UNICODE);
             }
     
             // Sauvegarder les nouvelles données
             File::put($jsonFilePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
     
             //\Log::info("Mise à jour du fichier JSON réussie avec specialite_id: {$speciality->id}");
     
             // Dispatch du job Laravel pour exécuter ton script Node.js
             dispatch(new RunNodeScript($speciality->id, $speciality->name));
     
             //\Log::info("Job RunNodeScript dispatché avec succès pour la spécialité ID {$speciality->id}");
     
         } catch (ValidatorException $e) {
             //\Log::error("Erreur lors de la création de la spécialité : " . $e->getMessage());
             Flash::error($e->getMessage());
         }
     
         Flash::success(__('lang.saved_successfully', ['operator' => __('lang.speciality')]));
     
         return redirect(route('specialities.index'));
     }
    /**
     * Display the specified Speciality.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function show(int $id):RedirectResponse|View
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);

        if (empty($speciality)) {
            Flash::error('Speciality not found');

            return redirect(route('specialities.index'));
        }

        return view('specialities.show')->with('speciality', $speciality);
    }

    /**
     * Show the form for editing the specified Speciality.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id):RedirectResponse|View
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);
        $parentSpeciality = $this->specialityRepository->pluck('name', 'id')->prepend(__('lang.speciality_parent_id_placeholder'), '');

        if (empty($speciality)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.speciality')]));

            return redirect(route('specialities.index'));
        }
        $customFieldsValues = $speciality->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
        $hasCustomField = in_array($this->specialityRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('specialities.edit')->with('speciality', $speciality)->with("customFields", isset($html) ? $html : false)->with("parentSpeciality", $parentSpeciality);
    }

    /**
     * Update the specified Speciality in storage.
     *
     * @param int $id
     * @param UpdateSpecialityRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdateSpecialityRequest $request): RedirectResponse
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);
    
        if (empty($speciality)) {
            Flash::error('Speciality not found');
            return redirect(route('specialities.index'));
        }
    
        // Exclure 'image' pour éviter qu'il devienne null s'il n'est pas modifié
        $input = $request->except(['image']);
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
    
        try {
            // Mettre à jour la spécialité avec les nouvelles données
            $speciality = $this->specialityRepository->update($input, $id);
    
            // Vérifier si une nouvelle image a été envoyée
            if ($request->has('image') && !empty($request->image)) { 
                $cacheUpload = $this->uploadRepository->getByUuid($request->image);
                $mediaItem = $cacheUpload->getMedia('image')->first();
    
                if ($mediaItem) {
                    // Supprimer l'ancienne image associée à la spécialité
                    $speciality->clearMediaCollection('image');
    
                    // Copier la nouvelle image
                    $mediaItem->copy($speciality, 'image');
    
                    // Mettre à jour l'ID et le file_name du média
                    $speciality->image = $mediaItem->id;
                    $speciality->image_name = $mediaItem->file_name;
                }
            } else {
                // Conserver l'ancienne image si aucune nouvelle n'est envoyée
                $speciality->image = $speciality->getFirstMedia('image') ? $speciality->getFirstMedia('image')->id : null;
                $speciality->image_name = $speciality->getFirstMedia('image') ? $speciality->getFirstMedia('image')->file_name : null;
            }
    
            // Sauvegarder les informations mises à jour
            $speciality->save();
    
            // Mise à jour des champs personnalisés
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $speciality->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
    
            // Met à jour les données
            $speciality->refresh(); 
    
            // Log après mise à jour
           // \Log::info("Speciality updated: ID = {$speciality->id}, Name = {$speciality->name}");
    
            // **Mise à jour de file.json**
            $jsonFilePath = public_path('script-spec/file.json'); // Chemin du fichier JSON
    
            if (File::exists($jsonFilePath)) {
                $jsonData = json_decode(File::get($jsonFilePath), true);
            } else {
                $jsonData = []; // Si le fichier n'existe pas encore
            }
    
           // \Log::info("Contenu du fichier JSON avant mise à jour : " . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
            // Mettre à jour toutes les entrées indépendamment de leurs valeurs
            foreach ($jsonData as &$entry) {
                $entry['specialite_id'] = $speciality->id;
                $entry['specialite'] = json_encode(['fr' => $speciality->name], JSON_UNESCAPED_UNICODE);
            }
    
            //\Log::info("Contenu du fichier JSON après mise à jour : " . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
            // Sauvegarder les nouvelles données
            File::put($jsonFilePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
           // \Log::info("Mise à jour du fichier JSON réussie avec specialite_id: {$speciality->id}");
    
            // Dispatch du job Laravel pour exécuter ton script Node.js
            //\Log::info("Nom de la spécialité passé au job : {$speciality->name}");
            dispatch(new RunNodeScript($speciality->id, $speciality->name));
    
           // \Log::info("Job RunNodeScript dispatché avec succès pour la spécialité ID {$speciality->id}");
    
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
    
        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.speciality')]));
    
        return redirect(route('specialities.index'));
    }
    

    /**
     * Remove the specified Speciality from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id):RedirectResponse
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);

        if (empty($speciality)) {
            Flash::error('Speciality not found');

            return redirect(route('specialities.index'));
        }

        $this->specialityRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.speciality')]));

        return redirect(route('specialities.index'));
    }

    /**
     * Remove Media of Speciality
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $speciality = $this->specialityRepository->findWithoutFail($input['id']);
        try {
            if ($speciality->hasMedia($input['collection'])) {
                $speciality->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
    public function getNameAttribute($value)
{
    $name = json_decode($value, true);
    return $name['fr'] ?? $value; // Retourne la valeur 'fr' ou la valeur brute si non trouvée
}

public function getSpecialitiesByCountry(Request $request)
{
    $pays = $request->input('pays');  // Récupère le pays depuis la requête

    if (!$pays) {
        return response()->json(['error' => 'Le pays est requis'], 400);
    }

    // Vérifiez que vous avez bien des spécialités pour ce pays dans la base de données
    $specialities = Speciality::where('pays', $pays)->get();

    if ($specialities->isEmpty()) {
        return response()->json(['message' => 'Aucune spécialité trouvée pour ce pays'], 404);
    }

    return response()->json($specialities);  // Retourne les spécialités en JSON
}



}
