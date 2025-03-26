<?php
/*
 * File name: AvailabilityHourController.php
 * Last modified: 2021.03.20 at 22:06:35
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\Criteria\AvailabilityHours\AvailabilityHoursOfUserCriteria;
use App\Criteria\Doctors\DoctorsOfUserCriteria;
use App\DataTables\AvailabilityHourDataTable;
use App\Http\Requests\CreateAvailabilityHourRequest;
use App\Http\Requests\UpdateAvailabilityHourRequest;
use App\Repositories\AvailabilityHourRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatternRepository;
use Illuminate\Support\Facades\Auth;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class AvailabilityHourController extends Controller
{
    /** @var  AvailabilityHourRepository */
    private AvailabilityHourRepository $availabilityHourRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var DoctorRepository
     */
    private DoctorRepository $doctorRepository;
    /**
     * @var PatternRepository
     */
    private PatternRepository $patternRepository; // Add this property

    public function __construct(AvailabilityHourRepository $availabilityHourRepo, CustomFieldRepository $customFieldRepo, DoctorRepository $doctorRepo,PatternRepository $patternRepo )
    {
        parent::__construct();
        $this->availabilityHourRepository = $availabilityHourRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->doctorRepository = $doctorRepo;
        $this->patternRepository = $patternRepo; 
    }

    /**
     * Display a listing of the AvailabilityHour.
     *
     * @param AvailabilityHourDataTable $availabilityHourDataTable
     * @return mixed
     */
    public function index(AvailabilityHourDataTable $availabilityHourDataTable): mixed
    {
        return $availabilityHourDataTable->render('availability_hours.index');
    }

    /**
     * Show the form for creating a new AvailabilityHour.
     *
     * @return View
     */
    public function create(Request $request): View
    {
        $userId = Auth::id();
    Log::info("Retrieving doctor for user ID: {$userId}");

    // Retrieve the doctor associated with the logged-in user
    $doctor = Doctor::where('user_id', $userId)->first();
    $doctorId = $doctor->id;

        $doctor = $this->doctorRepository->getByCriteria(new DoctorsOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $patterns = $this->patternRepository->findWhere(['doctor_id' => $doctorId])->mapWithKeys(function ($pattern) {
            $name = json_decode($pattern->nom, true);
            return [$pattern->id => $name[app()->getLocale()]]; // Adjust based on your default locale
        });
        $hasCustomField = in_array($this->availabilityHourRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->availabilityHourRepository->model());
            $html = generateCustomField($customFields);
        }
        $selectedDate = $request->get('date', Carbon::now()->format('Y-m-d'));
        return view('availability_hours.create')->with("customFields", isset($html) ? $html : false)->with("doctor", $doctor)->with("patterns", $patterns)->with('selectedDate', $selectedDate);
    }

    /**
     * Store a newly created AvailabilityHour in storage.
     *
     * @param CreateAvailabilityHourRequest $request
     *
     * @return RedirectResponse
     */

public function store(Request $request): RedirectResponse
{
    \Log::info('Request Data:', $request->all());
    $userId = Auth::id();
    \Log::info("Retrieving doctor for user ID: {$userId}");

    // Retrieve the doctor associated with the logged-in user
    $doctor = Doctor::where('user_id', $userId)->first();
    if (!$doctor) {
        return back()->with('error', 'Doctor not found.');
    }
    $doctorId = $doctor->id;
 $hasPatterns = DB::table('pattern')->where('doctor_id', $doctorId)->exists();

    if (!$hasPatterns) {
        \Log::info("Doctor ID {$doctorId} has no patterns. Prompting to create a motif.");
        Flash::error('Vous devez créer un motif avant de définir vos disponibilités.');
        return redirect(route('patterns.create')); // Redirect to the motif creation page
    }
    // Validate incoming request
    $request->validate([
        'availability_date' => 'required|date',
        'start_at' => 'required|date_format:H:i',
        'end_at' => 'required|date_format:H:i|after:start_at',
        'patern_id' => 'nullable|exists:pattern,id',
    ], [
        'availability_date.required' => 'La date est obligatoire.',
        'start_at.required' => 'L\'heure de début est obligatoire.',
        'end_at.required' => 'L\'heure de fin est obligatoire.',
        'end_at.after' => 'L\'heure de fin doit être après l\'heure de début.',
    ]);

    try {
        // Combine date and time inputs to create datetime values
        $startAt = Carbon::createFromFormat('Y-m-d H:i', "{$request->availability_date} {$request->start_at}");
        $endAt = Carbon::createFromFormat('Y-m-d H:i', "{$request->availability_date} {$request->end_at}");

        // Check for overlapping appointments
        \Log::info("Checking for overlapping availability for doctor ID: {$doctorId}");
        $existingAppointment = DB::table('availability_hours')
        ->where('doctor_id', $doctorId)
        ->where(function ($query) use ($startAt, $endAt) {
            $query->where(function ($query) use ($startAt, $endAt) {
                $query->where('start_at', '<', $endAt) // Starts before the end
                      ->where('end_at', '>', $startAt); // Ends after the start
            });
            })
            ->first(); // Use `first` to get the conflicting record if any

        if ($existingAppointment) {
            Flash::error(__('Une disponibilité existe déjà à cette date et heure.'));
            \Log::info("Conflict found with availability ID: {$existingAppointment->id}, Start: {$existingAppointment->start_at}, End: {$existingAppointment->end_at}");
            return back()->with('error', 'Une disponibilité existe déjà à cette date et heure.');
            
        }

        \Log::info("No conflict found. Proceeding to save availability.");

        // Prepare data for insertion
        $input = $request->except(['availability_date', 'start_at', 'end_at']);
        $input['start_at'] = $startAt;
        $input['end_at'] = $endAt;
        $input['doctor_id'] = $doctorId; // Automatically assign the logged-in doctor's ID

        // Save the availability hour
        $this->availabilityHourRepository->create($input);

       Flash::success(__('lang.saved_successfully', ['operator' => __('lang.availability_hour')]));
        return redirect(route('availabilityHours.create'))->with('success', 'La disponibilité a été enregistrée avec succès!');
    } catch (\Exception $e) {
        \Log::error("Error saving availability: " . $e->getMessage());
        Flash::error("Erreur : " . $e->getMessage());
        return back()->withInput();
    }
}



    /**
     * Display the specified AvailabilityHour.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function show(int $id): RedirectResponse|View
    {
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
        $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);

        if (empty($availabilityHour)) {
            Flash::error('Availability Hour not found');

            return redirect(route('availabilityHours.index'));
        }

        return view('availability_hours.show')->with('availabilityHour', $availabilityHour);
    }

    /**
     * Show the form for editing the specified AvailabilityHour.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     * @throws RepositoryException
     */
    public function edit(int $id):RedirectResponse|View
    {
$userId = Auth::id();
    Log::info("Retrieving doctor for user ID: {$userId}");

    // Retrieve the doctor associated with the logged-in user
    $doctor = Doctor::where('user_id', $userId)->first();
    $doctorId = $doctor->id;
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
        $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);
        $doctor = $this->doctorRepository->getByCriteria(new DoctorsOfUserCriteria(auth()->id()))->pluck('name', 'id');
        $patterns = $this->patternRepository->findWhere(['doctor_id' => $doctorId])->mapWithKeys(function ($pattern) {
            $name = json_decode($pattern->nom, true);
            return [$pattern->id => $name[app()->getLocale()]]; // Adjust based on your default locale
        });

        if (empty($availabilityHour)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.availability_hour')]));

            return redirect(route('availabilityHours.index'));
        }
        $customFieldsValues = $availabilityHour->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->availabilityHourRepository->model());
           // Extract date and time
    $availabilityHour->formatted_date = Carbon::parse($availabilityHour->start_at)->format('Y-m-d'); // Extract date from start_at
    $availabilityHour->formatted_start_time = Carbon::parse($availabilityHour->start_at)->format('H:i'); // Extract time from start_at
    $availabilityHour->formatted_end_time = Carbon::parse($availabilityHour->end_at)->format('H:i'); // Extract time from end_at
        $hasCustomField = in_array($this->availabilityHourRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('availability_hours.edit')->with('availabilityHour', $availabilityHour)->with("customFields", isset($html) ? $html : false)->with("doctor", $doctor)->with("patterns", $patterns);
    }

    /**
     * Update the specified AvailabilityHour in storage.
     *
     * @param int $id
     * @param UpdateAvailabilityHourRequest $request
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateAvailabilityHourRequest $request): RedirectResponse
{
    \Log::info('Request Data:', $request->all()); // Log all request data

    $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
    $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);

    if (empty($availabilityHour)) {
        Flash::error('Availability Hour not found');
        return redirect(route('availabilityHours.index'));
    }

    // Retrieve and combine the date and time inputs
    $availability_date = $request->input('availability_date'); // Date (Y-m-d)
    $start_time = $request->input('start_at'); // Time (H:i)
    $end_time = $request->input('end_at'); // Time (H:i)

    try {
        // Combine and log the datetime values
        \Log::info("Combining date and time: $availability_date $start_time and $availability_date $end_time");

        // Combine date and time into proper datetime format
        $start_at = Carbon::createFromFormat('Y-m-d H:i', "$availability_date $start_time");
        $end_at = Carbon::createFromFormat('Y-m-d H:i', "$availability_date $end_time");

        // Validate combined inputs
        if ($start_at->gte($end_at)) {
            \Log::error("Validation Error: End time must be after start time.");
            return back()->withErrors(['end_at' => 'The end time must be after the start time.'])->withInput();
        }

        $input = $request->except(['availability_date', 'start_at', 'end_at']);
        $input['start_at'] = $start_at;
        $input['end_at'] = $end_at;

        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->availabilityHourRepository->model());

        // Update availability hour
        $availabilityHour = $this->availabilityHourRepository->update($input, $id);

        foreach (getCustomFieldsValues($customFields, $request) as $value) {
            $availabilityHour->customFieldsValues()
                ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
        }

        \Log::info("Availability Hour updated successfully with ID: {$availabilityHour->id}");

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.availability_hour')]));
        return redirect(route('availabilityHours.index'));
    } catch (\Exception $e) {
        \Log::error("Error updating availability hour: " . $e->getMessage());
        Flash::error("Error: " . $e->getMessage());
        return back()->withInput();
    }
}

    /**
     * Remove the specified AvailabilityHour from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
        $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);

        if (empty($availabilityHour)) {
            Flash::error('Availability Hour not found');

            return redirect(route('availabilityHours.index'));
        }

        $this->availabilityHourRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.availability_hour')]));

        return redirect(route('availabilityHours.index'));
    }

    
}
