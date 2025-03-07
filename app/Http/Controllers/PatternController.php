<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\PatternDataTable;
use App\Http\Requests\CreatePatternRequest;
use App\Http\Requests\UpdatePatternRequest;
use App\Repositories\PatternRepository;
use App\Repositories\SpecialityRepository;
use App\Repositories\ClinicRepository;
use App\Models\Clinic;
use Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;


class PatternController extends Controller
{
    private PatternRepository $patternRepository;
    private SpecialityRepository $specialityRepository;
    private ClinicRepository $clinicRepository;

    public function __construct(PatternRepository $patternRepo, SpecialityRepository $specialityRepo, ClinicRepository $clinicRepo)
    {
        $this->patternRepository = $patternRepo;
        $this->specialityRepository = $specialityRepo;
        $this->clinicRepository = $clinicRepo; // Inject ClinicRepository
    }

    public function index(PatternDataTable $dataTable)
    {
        return $dataTable->render('patterns.index');
    }

    public function create()
    {
        $doctorId = auth()->user()->getActiveDoctorId();

        if (!$doctorId) {
            Flash::error(__('Aucun médecin associé trouvé.'));
            return redirect()->route('patterns.index');
        }

        $doctor = Doctor::find($doctorId);
        $doctorSpecialityId = $doctor && $doctor->specialities->isNotEmpty()
            ? $doctor->specialities->first()->id
            : null;

        $clinics = $this->clinicRepository->pluck('name', 'id');
        $customFields = '';
        $isClinicSet = false;
        $isAdomicileSet = false;

        return view('patterns.create', compact('doctorSpecialityId', 'clinics', 'customFields', 'isClinicSet', 'isAdomicileSet'));
    }



    public function store(CreatePatternRequest $request): RedirectResponse
    {
        $doctorId = auth()->user()->getActiveDoctorId();

        if (!$doctorId) {
            Flash::error(__('Aucun médecin associé trouvé.'));
            return redirect()->route('patterns.index');
        }

        $input = $request->all();
        $input['doctor_id'] = $doctorId;

        $language = app()->getLocale();
        $input['nom'] = json_encode([$language => $input['nom']]);
        $typeMapping = [
            'cabinet' => 1,
            'clinique' => 2,
            'adomicile' => 3,
        ];
        $input['type'] = $typeMapping[$input['type']] ?? null;

        $this->patternRepository->create($input);

        Flash::success(__('Motif enregistré avec succès.'));
        return redirect(route('patterns.index'));
    }
    public function edit($id)
    {
        $pattern = $this->patternRepository->findWithoutFail($id);

        if (empty($pattern)) {
            Flash::error(__('Motif introuvable.'));
            return redirect(route('patterns.index'));
        }

        $doctor = auth()->user()->doctor;
        $doctorSpecialityId = $doctor ? $doctor->specialite_id : null;

        $customFields = ''; // Initialize $customFields, or fetch them if applicable
        $locale = app()->getLocale();
        $nomArray = json_decode($pattern->nom, true);
        $pattern->nom = $nomArray[$locale] ?? '';

        $clinics = $this->clinicRepository->pluck('name', 'id');
        $selectedClinicId = $pattern->clinic_id;
        $isClinicSet = $pattern->type == 2;
        $isAdomicileSet = $pattern->type == 3;

        return view('patterns.edit', compact(
            'pattern',
            'clinics',
            'customFields',
            'doctorSpecialityId',
            'isClinicSet',
            'isAdomicileSet',
            'selectedClinicId'
        ));
    }







    public function update($id, UpdatePatternRequest $request): RedirectResponse
    {
        $doctorId = auth()->user()->getActiveDoctorId();

        if (!$doctorId) {
            Flash::error(__('Aucun médecin associé trouvé.'));
            return redirect()->route('patterns.index');
        }

        $pattern = $this->patternRepository->findWithoutFail($id);

        if (empty($pattern) || $pattern->doctor_id !== $doctorId) {
            Flash::error(__('Motif introuvable ou non autorisé.'));
            return redirect(route('patterns.index'));
        }

        $input = $request->all();

        $typeMapping = [
            'cabinet' => 1,
            'clinique' => 2,
            'adomicile' => 3,
        ];

        if (isset($input['type'])) {
            $input['type'] = $typeMapping[$input['type']] ?? $pattern->type;
        }

        if ($input['type'] === 1 || $input['type'] === 3) {
            $input['clinic_id'] = null;
        }

        $locale = app()->getLocale();
        $nomArray = json_decode($pattern->nom, true);
        $nomArray[$locale] = $input['nom'];
        $input['nom'] = json_encode($nomArray);

        $this->patternRepository->update($input, $id);

        Flash::success(__('Motif modifié avec succès.'));
        return redirect(route('patterns.index'));
    }

    public function destroy($id)
    {
        $doctorId = auth()->user()->getActiveDoctorId();

        if (!$doctorId) {
            Flash::error(__('Aucun médecin associé trouvé.'));
            return redirect()->route('patterns.index');
        }

        $pattern = $this->patternRepository->findWithoutFail($id);

        if (empty($pattern) || $pattern->doctor_id !== $doctorId) {
            Flash::error(__('Motif introuvable ou non autorisé.'));
            return redirect(route('patterns.index'));
        }

        try {
            $this->patternRepository->delete($id);

            Flash::success(__('Motif supprimé avec succès.'));
            return redirect()->route('patterns.index');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                Flash::error(__('Ce motif ne peut pas être supprimé.'));
            } else {
                Flash::error(__('Une erreur inattendue est survenue.'));
            }
            return redirect()->route('patterns.index');
        }
    }

    public function getPatterns(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->input('q');
            $locale = app()->getLocale();

            $query = DB::table('pattern');
            if ($search) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(nom, '$.\"{$locale}\"')) LIKE ?", ["%{$search}%"]);
            }

            $patterns = $query->select(
                'id',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(nom, '$.\"{$locale}\"')) as text")
            )
                ->when($search, fn($q) => $q->limit(10))
                ->get();

            return response()->json($patterns);
        }
    }
}