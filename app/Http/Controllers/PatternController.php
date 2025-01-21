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
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $dataTable->render('patterns.index'); // Render for admin (all patterns)
        } else {
            $doctor = Doctor::where('user_id', $user->id)->first();

            if (!$doctor) {
                Flash::error('Doctor not found. Please log in as a doctor to view patterns.');
                return redirect()->back();
            }

            // Filter the query for the logged-in doctor
            $dataTable->query(function ($query) use ($doctor) {
                return $query->where('doctor_id', $doctor->id);
            });

            return $dataTable->render('patterns.index'); // Render filtered data for doctor
        }
    }

    public function create()
    {
        $doctor = Doctor::where('user_id', auth()->id())->with('specialities')->first();

        // Get the first speciality ID (or null if no specialities exist)
        $doctorSpecialityId = $doctor && $doctor->specialities->isNotEmpty()
            ? $doctor->specialities->first()->id
            : null;

        // Get clinics for the form
        $clinics = $this->clinicRepository->pluck('name', 'id'); // Assuming you have a Clinic model
        $customFields = ''; // Set to an empty string or `null` if you don’t have custom fields
        $isClinicSet = false;
        $isAdomicileSet = false;
        return view('patterns.create', compact('doctorSpecialityId', 'clinics', 'customFields', 'isClinicSet', 'isAdomicileSet'));
    }



    public function store(CreatePatternRequest $request): RedirectResponse
    {
        $input = $request->all();
        Log::info('Request Data:', $input);

        $userId = Auth::id();
        Log::info("Retrieving doctor for user ID: {$userId}");

        // Retrieve the doctor associated with the logged-in user
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::warning("Doctor not found for user ID: {$userId}");
            return response()->json(['error' => 'Doctor not found for the logged-in user'], 404);
        }

        //$doctorId = $doctor->id;

        // Set the `doctor_id` in the input
        $input['doctor_id'] = $doctor->id;

        // Assuming the language is set in the request or app configuration
        $language = app()->getLocale(); // or $request->input('language');

        // Format the `nom` as JSON
        $input['nom'] = json_encode([$language => $input['nom']]);
        $typeMapping = [
            'cabinet' => 1,
            'clinique' => 2,
            'adomicile' => 3,
        ];
        $input['type'] = $typeMapping[$input['type']];
        // Save the pattern
        $this->patternRepository->create($input);

        Flash::success('Motif enregistré avec succées');
        return redirect(route('patterns.index'));
    }
    public function edit($id)
    {
        // Retrieve the pattern by ID
        $pattern = $this->patternRepository->findWithoutFail($id);

        // Check if the pattern exists
        if (empty($pattern)) {
            Flash::error('Pattern not found');
            return redirect(route('patterns.index'));
        }

        // Retrieve the doctor's speciality
        $doctor = auth()->user()->doctor; // Assuming `auth()->user()->doctor` gives the logged-in doctor's model
        $doctorSpecialityId = $doctor ? $doctor->specialite_id : null;

        // Pass the necessary data to the view
        $customFields = []; // Add custom fields if applicable
        $locale = app()->getLocale(); // Get the current locale

        // Decode and localize the `nom` field
        $nomArray = json_decode($pattern->nom, true);
        $pattern->nom = $nomArray[$locale] ?? '';

        // Retrieve clinics for the dropdown
        $clinics = $this->clinicRepository->pluck('name', 'id');

        // Add a flag to determine if `clinic_id` is set
        $selectedClinicId = $pattern->clinic_id; // Retrieve the selected clinic ID from the pattern

        // Decode the `type` field to determine the current selection
        $isClinicSet = $pattern->type == 2; // Assuming 2 is for 'clinique'
        $isAdomicileSet = $pattern->type == 3; // Assuming 3 is for 'adomicile'

        // Return view with all necessary data
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
        $input = $request->all();

        // Find the pattern by ID
        $pattern = $this->patternRepository->findWithoutFail($id);

        if (empty($pattern)) {
            Flash::error('Pattern not found');
            return redirect(route('patterns.index'));
        }

        // Map `type` to a numerical value
        $typeMapping = [
            'cabinet' => 1,
            'clinique' => 2,
            'adomicile' => 3,
        ];

        if (isset($input['type']) && array_key_exists($input['type'], $typeMapping)) {
            $input['type'] = $typeMapping[$input['type']];
        }

        // Set `clinic_id` to null if the type is "cabinet" (1)
        if (isset($input['type']) && $input['type'] === 1 || $input['type'] === 3) {
            $input['clinic_id'] = null;
        }

        // Encode the `nom` input into JSON format with the current locale
        $locale = app()->getLocale();
        $nomArray = json_decode($pattern->nom, true); // Decode existing data
        $nomArray[$locale] = $input['nom']; // Update the current locale value
        $input['nom'] = json_encode($nomArray); // Encode back to JSON

        // Update the pattern in the repository
        $this->patternRepository->update($input, $id);

        Flash::success('Motif modifié avec succées.');
        return redirect(route('patterns.index'));
    }



    public function destroy($id)
    {
        try {
            $this->patternRepository->delete($id);

            Flash::success(trans('Motif supprimé avec succées'));
            return redirect()->route('patterns.index');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') { // SQLSTATE 23000: Integrity constraint violation
                Flash::error(trans('lang.pattern_cannot_be_deleted')); // Custom error message
            } else {
                Flash::error(trans('lang.unexpected_error')); // General error message
            }
            return redirect()->route('patterns.index');
        }
    }

    public function getPatterns(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->input('q');
            $locale = app()->getLocale(); // Get current locale

            // Query patterns table
            $query = DB::table('pattern');
            if ($search) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(nom, '$.\"{$locale}\"')) LIKE ?", ["%{$search}%"]);
            }

            // Select id and localized text
            $patterns = $query->select(
                'id',
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(nom, '$.\"{$locale}\"')) as text")
            )
                ->when($search, fn($q) => $q->limit(10)) // Limit results to 10 when searching
                ->get();

            return response()->json($patterns);
        }
    }

}
