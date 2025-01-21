<?php
/*
 * File name: AvailabilityHourAPIController.php
 * Last modified: 2024.04.13 at 10:05:52
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;


use App\Criteria\AvailabilityHours\AvailabilityHoursOfUserCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAvailabilityHourRequest;
use App\Http\Requests\UpdateAvailabilityHourRequest;
use App\Repositories\AvailabilityHourRepository;
use App\Repositories\DoctorRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\DoctorVacation;
use App\Models\DoctorUrgency;
use Illuminate\Support\Str;

/**
 * Class AvailabilityHourController
 * @package App\Http\Controllers\API
 */
class AvailabilityHourAPIController extends Controller
{
    /** @var  AvailabilityHourRepository */
    private AvailabilityHourRepository $availabilityHourRepository;

    /** @var  DoctorRepository */
    private DoctorRepository $doctorRepository;

    public function __construct(AvailabilityHourRepository $availabilityHourRepo, DoctorRepository $doctorRepo)
    {
        $this->availabilityHourRepository = $availabilityHourRepo;
        $this->doctorRepository = $doctorRepo;
        parent::__construct();
    }


    /**
     * Display a listing of the AvailabilityHour.
     * GET|HEAD /availabilityHours
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->availabilityHourRepository->pushCriteria(new RequestCriteria($request));
            $this->availabilityHourRepository->pushCriteria(new LimitOffsetCriteria($request));
            $availabilityHours = $this->availabilityHourRepository->all();
	    $dayTranslations=[
	'lundi'=>'monday',
	'mardi' => 'tuesday',
        'mercredi' => 'wednesday',
        'jeudi' => 'thursday',
        'vendredi' => 'friday',
        'samedi' => 'saturday',
        'dimanche' => 'sunday',
        ];
	$availabilityHours = $availabilityHours->map(function ($availabilityHour) use ($dayTranslations) {
            $availabilityHour->day = $dayTranslations[$availabilityHour->day] ?? $availabilityHour->day;
            return $availabilityHour;
        });
            $this->filterCollection($request, $availabilityHours);
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($availabilityHours->toArray(), 'Availability Hours retrieved successfully');
    }

    /**
     * Display the specified AvailabilityHour.
     * GET|HEAD /availabilityHours/{id}
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
public function show(int $id, Request $request): JsonResponse
{
{   try {
        $this->doctorRepository->pushCriteria(new RequestCriteria($request));
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria($id));
        $this->doctorRepository->pushCriteria(new LimitOffsetCriteria($request));
    } catch (RepositoryException $e) {
        return $this->sendError($e->getMessage());
    }

    $doctor = $this->doctorRepository->findWithoutFail($id);

    if (empty($doctor)) {
        return $this->sendError('Doctor not found');
    }

    // Retrieve vacation days for the doctor
    $vacations = DoctorVacation::where('doctor_id', $id)
                                ->where(function ($query) use ($request) {
                                    // Check if the date filter is applied
                                    $date = $request->input('date');
                                    if ($date) {
                                        $query->where('dateDebut', '<=', $date)
                                              ->where(function ($q) use ($date) {
                                                  $q->where('dateFin', '>=', $date)
                                                    ->orWhereNull('dateFin');
                                              });
                                    }
                                })
                                ->get();

    $vacationDates = $vacations->flatMap(function ($vacation) {
        $dates = [];
        if ($vacation->type === 'journée') {
            // Add the single day vacation
            $dates[] = $vacation->dateDebut;
        } elseif ($vacation->type === 'période' && $vacation->dateDebut && $vacation->dateFin) {
            // Generate date range for the vacation period
            $startDate = Carbon::parse($vacation->dateDebut);
            $endDate = Carbon::parse($vacation->dateFin);


            // Loop through each day of the period and add to the dates array
            while ($startDate->lte($endDate)) {
                $dates[] = $startDate->toDateString();
                $startDate->addDay();
            }
        }
        return $dates;
    })->toArray();

    $calendar = [];
    $date = $request->input('date');
    $onlinestr = $request->input('online', 'false');
    $online = false;
    if (Str::lower($onlinestr)=='true'){
    $online=true;
    }
    if (!empty($date)) {
        $date = Carbon::createFromFormat('Y-m-d', $date);
        $calendar = $doctor->weekCalendarRange($date, $online);

        // Exclude the vacation dates from the calendar
        $calendar = array_filter($calendar, function ($day) use ($vacationDates) {
            return !in_array($day, $vacationDates);
        });
    }

    return $this->sendResponse($calendar, 'Availability Hours retrieved successfully');
}}

    /**
     * Store a newly created AvailabilityHour in storage.
     *
     * @param CreateAvailabilityHourRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateAvailabilityHourRequest $request): JsonResponse
    {
        $input = $request->all();
        try {
            $availabilityHour = $this->availabilityHourRepository->create($input);

        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($availabilityHour, __('lang.saved_successfully', ['operator' => __('lang.availability_hour')]));
    }

    /**
     * Update the specified AvailabilityHour in storage.
     *
     * @param int $id
     * @param UpdateAvailabilityHourRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateAvailabilityHourRequest $request): JsonResponse
    {
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
        $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);

        if (empty($availabilityHour)) {
            return $this->sendError(__('lang.not_found', ['operator' => __('lang.availability_hour')]));
        }
        $input = $request->all();
        try {
            $availabilityHour = $this->availabilityHourRepository->update($input, $id);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($availabilityHour, __('lang.updated_successfully', ['operator' => __('lang.availability_hour')]));
    }

    /**
     * Remove the specified AvailabilityHour from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria(auth()->id()));
        $availabilityHour = $this->availabilityHourRepository->findWithoutFail($id);

        if (empty($availabilityHour)) {
            return $this->sendError(__('lang.not_found', ['operator' => __('lang.availability_hour')]));
        }

        $this->availabilityHourRepository->delete($id);
        return $this->sendResponse($availabilityHour, __('lang.deleted_successfully', ['operator' => __('lang.availability_hour')]));
    }
	public function getPatternNom()
    {
        // Fetch availability hours and eager load the 'pattern' relation
        $availabilityHours = AvailabilityHour::with('pattern')->get();

        // Retrieve the 'nom' field from the related 'pattern' model
        $patternNames = $availabilityHours->map(function($availabilityHour) {
            return $availabilityHour->pattern->nom;  // Access 'nom' from the related 'pattern'
        });

        // Return the 'nom' values in the response
        return response()->json(['data' => $patternNames]);
    }
public function getMatchingAvailabilityAndAppointments(Request $request): JsonResponse
{
    try {
        $date = $request->input('date');
        if (empty($date)) {
            return $this->sendError('Date is required');
        }

        $parsedDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayInFrench = $parsedDate->translatedFormat('l');
        Log::info("Parsed Date: {$parsedDate->toDateString()} | French Day: {$dayInFrench}");

        $availabilityHours = $this->availabilityHourRepository->findWhere(['day' => $dayInFrench]);
        Log::info("Fetched Availability Hours: ", $availabilityHours->toArray());

        $appointments = \DB::table('appointments')
            ->whereDate('start_at', $parsedDate)
            ->get(['start_at']);
        Log::info("Fetched Appointments: ", $appointments->toArray());

        $matchingAvailability = $availabilityHours->filter(function ($availabilityHour) use ($appointments, $parsedDate) {
            $availabilityStart = Carbon::parse($parsedDate->toDateString() . ' ' . $availabilityHour->start_at);
            $availabilityEnd = Carbon::parse($parsedDate->toDateString() . ' ' . $availabilityHour->end_at);

            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_at);
                Log::info("Comparing Appointment: {$appointmentStart} with Availability: {$availabilityStart} - {$availabilityEnd}");
                if ($appointmentStart->between($availabilityStart, $availabilityEnd)) {
                    return true;
                }
            }
            return false;
        });

        $formattedResults = $matchingAvailability->map(function ($availabilityHour) use ($parsedDate) {
            return [
                'availability_id' => $availabilityHour->id,
                'doctor_id' => $availabilityHour->doctor_id,
                'date' => $parsedDate->toDateString(),
                'availability_start' => $availabilityHour->start_at,
                'availability_end' => $availabilityHour->end_at,
            ];
        });

        return $this->sendResponse($formattedResults->values(), 'Matching availability hours retrieved successfully');
    } catch (Exception $e) {
        Log::error("Error in getMatchingAvailabilityAndAppointments: {$e->getMessage()}");
        return $this->sendError($e->getMessage());
    }
}


}
