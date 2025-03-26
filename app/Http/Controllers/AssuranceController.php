<?php
namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Repositories\AssuranceRepository;
use App\Repositories\CustomFieldRepository;
use App\DataTables\AssuranceDataTable;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Http\RedirectResponse;

class AssuranceController extends Controller
{
    protected $assuranceRepository;
    protected $customFieldRepository;

    public function __construct(AssuranceRepository $assuranceRepository, CustomFieldRepository $customFieldRepository)
    {
        $this->assuranceRepository = $assuranceRepository;
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param AssuranceDataTable $assuranceDataTable
     * @return mixed
     */
    public function index(AssuranceDataTable $assuranceDataTable): mixed
    {
        return $assuranceDataTable->render('assurances.index');
    }

    /**
     * Show the form for creating a new Assurance.
     *
     * @return View
     */
    public function create(): View
    {
        $hasCustomField = in_array($this->assuranceRepository->model(), setting('custom_field_models', []));
        
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->assuranceRepository->model());
            $html = generateCustomField($customFields);
        }

        return view('assurances.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created Assurance in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation des données
        $validatedData = $request->validate(Assurance::$rules);   
    
        // Vérification si le nom de l'assurance existe déjà
        $existingAssurance = Assurance::where('nom', $request->nom)->first();
        if ($existingAssurance) {
            Flash::error(__('lang.assurance_exists', ['name' => $request->nom])); // Affiche un message d'erreur si le nom existe déjà
            return redirect()->back()->withInput(); // Redirige l'utilisateur en arrière avec les anciennes valeurs
        }
    
        try {
            // Création de l'assurance
            Assurance::create($validatedData);
            Flash::success(__('lang.saved_successfully', ['operator' => __('lang.assurance')]));
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }
        
        return redirect()->route('assurances.index');
    }
    

    /**
     * Show the form for editing the specified Assurance.
     *
     * @param int $id
     * @return RedirectResponse|View
     */
    public function edit(int $id): RedirectResponse|View
    {
        $assurance = Assurance::find($id);

        if (empty($assurance)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.assurance')]));
            return redirect(route('assurances.index'));
        }

        $customFieldsValues = $assurance->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->assuranceRepository->model());
        $hasCustomField = in_array($this->assuranceRepository->model(), setting('custom_field_models', []));
        
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('assurances.edit')->with('assurance', $assurance)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Assurance in storage.
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(int $id, Request $request): RedirectResponse
    {
        $assurance = Assurance::find($id);
    
        if (empty($assurance)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.assurance')]));
            return redirect(route('assurances.index'));
        }
    
        // Validation des données
        $validatedData = $request->validate(Assurance::$rules);
    
        // Vérification si le nom de l'assurance existe déjà, en excluant l'assurance actuelle
        $existingAssurance = Assurance::where('nom', $request->nom)
            ->where('id', '!=', $id) // On exclut l'assurance actuelle
            ->first();
    
        if ($existingAssurance) {
            Flash::error(__('lang.assurance_exists', ['name' => $request->nom])); // Affiche un message d'erreur
            return redirect()->back()->withInput(); // Redirige l'utilisateur en arrière
        }
    
        try {
            // Mise à jour de l'assurance
            $assurance->update($validatedData);
            Flash::success(__('lang.updated_successfully', ['operator' => __('lang.assurance')]));
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }
    
        return redirect(route('assurances.index'));
    }
    

    /**
     * Remove the specified Assurance from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $assurance = Assurance::find($id);

        if (empty($assurance)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.assurance')]));
            return redirect(route('assurances.index'));
        }

        try {
            $assurance->delete();
            Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.assurance')]));
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
        }

        return redirect(route('assurances.index'));
    }
}
