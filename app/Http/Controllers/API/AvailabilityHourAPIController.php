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

use App\Models\Pattern;
use App\Repositories\AvailabilityHourRepository;
use App\Repositories\DoctorRepository;
use Carbon\Carbon;
use DB;


use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\DoctorVacation;

use App\Models\AvailabilityHour;
use App\Models\AvailabilityHourTranslation;
use App\Models\Doctor;
use App\Models\DoctorUrgency;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;

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
            $dayTranslations = [
                'lundi' => 'monday',
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

    /*********************** Code hamza ****************************** */
    public function show(int $id, Request $request): JsonResponse
    {
        Log::info("Availibility hours controller => show function ");
        try {


            $this->doctorRepository->pushCriteria(new RequestCriteria($request));
            $this->availabilityHourRepository->pushCriteria(new AvailabilityHoursOfUserCriteria($id));
            $this->doctorRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $doctor = $this->doctorRepository->findWithoutFail($id);


        // Convert collection to array
        $doctor->availabilityHours = $doctor->availabilityHours->map(function ($availability) {
            $availability->day = $this->translateDayToEnglish($availability->day); // Corrigé
            return $availability;
        });

        Log::info("Availibility hours", ["Availibility hours" => $doctor->availabilityHours->toArray()]);


        if (empty($doctor)) {
            return $this->sendError('Doctor not found');
        }


        if (empty($doctor->availabilityHours->toArray())) {
            return $this->sendError('Doctor not available');
        }

        // Récupérer les jours de congé du docteur
        $vacations = DoctorVacation::where('doctor_id', $id)
            ->where(function ($query) use ($request) {
                $date = $request->input('date');
                if ($date) {
                    $query->where('start_date', '<=', $date)
                        ->where(function ($q) use ($date) {
                            $q->where('end_date', '>=', $date)
                                ->orWhereNull('end_date');

                        });
                }
            })
            ->get();


        // Conversion des congés en tableau de dates
        $vacationDates = $vacations->flatMap(function ($vacation) {
            $dates = [];
            if ($vacation->type === 'journée') {
                $dates[] = $vacation->dateDebut;
            } elseif ($vacation->type === 'période' && $vacation->dateDebut && $vacation->dateFin) {
                $startDate = Carbon::parse($vacation->dateDebut);
                $endDate = Carbon::parse($vacation->dateFin);


                while ($startDate->lte($endDate)) {
                    $dates[] = $startDate->toDateString();
                    $startDate->addDay();
                }
            }
            return $dates;
        })->toArray();


        // Gestion du calendrier des disponibilités
        $calendar = [];
        $date = $request->input('date');
        //$online = Str::lower($request->input('online', 'false')) === 'true';
        $typeConsultation = $request->input('type', '');
        $pattern_id = (int) $request->input('pattern', 0);
        if (!empty($date)) {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            Log::info("Availibility hours controller => show function ", ['date' => $date]);

            if ($doctor->availability_mode == 'precise') {
                Log::info("Checking type consultation (show availibility_hours) **", [
                    'typeConsultation' => $typeConsultation,
                    'pattern_id' => $pattern_id
                ]);
                $calendar = $doctor->weekCalendarRange($date, $typeConsultation, 'precise', $pattern_id);
            } else {
                Log::info("Checking type consultation (show availibility_hours)", [
                    'typeConsultation' => $typeConsultation
                ]);
                $calendar = $doctor->weekCalendarRange($date, $typeConsultation);
            }

            // Exclure les jours de congé du calendrier et traduire les jours
            //return response()->json($calendar);
            $calendar = array_filter($calendar, function ($day) use ($vacationDates) {
                // Convertir les jours en anglais
                if (is_string($day)) {
                    $day = $this->translateDayToEnglish($day);
                }


                return !in_array($day, $vacationDates);
            });
        }

        return $this->sendResponse($calendar, 'Availability Hours retrieved successfully');

    }




    // Retourner les disponibilité de chaque docteur selon date pour l'utiliser dans le filtre
    public function getAvailibilityHoursHamza(int $id, $date): array
    {
        $availabilityHours = DB::table('availability_hours')->where('doctor_id', $id)->get();
        Log::info("Availibility hours doctor", ["Availibility hours" => $availabilityHours]);
        // Convert collection to array
        $availabilityHours = $availabilityHours->map(function ($availability) {
            $availability->day = $this->translateDayToEnglish($availability->day); // Corrigé
            return $availability;
        });

        Log::info("Appel de getAvailibilityHoursHamza ligne 3 ");



        // Récupérer les jours de congé du docteur
        $vacations = DoctorVacation::where('doctor_id', $id)
            ->where(function ($query) use ($date) {
                if ($date) {
                    $query->where('start_date', '<=', $date)
                        ->where(function ($q) use ($date) {
                            $q->where('end_date', '>=', $date)
                                ->orWhereNull('end_date');
                        });
                }
            })
            ->get();
        Log::info("Appel de getAvailibilityHoursHamza ligne 4 ", ["vacations" => $vacations]);
        // Conversion des congés en tableau de dates
        $vacationDates = $vacations->flatMap(function ($vacation) {
            $dates = [];
            if ($vacation->type === 'journée') {
                $dates[] = $vacation->start_date;
            } elseif ($vacation->type === 'période' && $vacation->start_date && $vacation->end_date) {
                $startDate = Carbon::parse($vacation->start_date);
                $endDate = Carbon::parse($vacation->end_date);

                while ($startDate->lte($endDate)) {
                    $dates[] = $startDate->toDateString();
                    $startDate->addDay();
                }
            }
            return $dates;
        })->toArray();

        Log::info("Appel de getAvailibilityHoursHamza ligne 5 ");

        // Gestion du calendrier des disponibilités
        $calendar = [];

        if (!empty($date)) {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            $doctor = Doctor::find($id);
            $patterns = Pattern::where('doctor_id', $id)->get();
            Log::info('Appel Patterns calendar', ["patterns" => $patterns]);
            $calendar = [];

            // Ajouter les disponibilités "open"
            $calendar = array_merge($calendar, $doctor->weekCalendarRange($date, 'teleconsultation', 'open'));
            $calendar = array_merge($calendar, $doctor->weekCalendarRange($date, 'cabinet', 'open'));

            // Ajouter les disponibilités "precise" pour chaque pattern
            foreach ($patterns as $pattern) {
                $calendar = array_merge($calendar, $doctor->weekCalendarRange($date, 'teleconsultation', 'precise', $pattern->id));
                $calendar = array_merge($calendar, $doctor->weekCalendarRange($date, 'cabinet', 'precise', $pattern->id));
            }


            Log::info("Appel de getAvailibilityHoursHamza calendar", ["calendar" => $calendar]);
            // Exclure les jours de congé du calendrier et traduire les jours
            $calendar = array_filter($calendar, function ($day) use ($vacationDates) {
                // Convertir les jours en anglais
                if (is_string($day)) {
                    $day = $this->translateDayToEnglish($day);
                }

                return !in_array($day, $vacationDates);
            });
        }
        Log::info("Appel de getAvailibilityHoursHamza ligne 6 ");
        return $calendar;
    }




    public function translateDayToEnglish($day)
    {

        $dayLower = strtolower($day);
        $translations = [
            'lundi' => 'monday',
            'mardi' => 'tuesday',
            'mercredi' => 'wednesday',
            'jeudi' => 'thursday',
            'vendredi' => 'friday',
            'samedi' => 'saturday',
            'dimanche' => 'sunday',
        ];
        return $translations[$dayLower] ?? $dayLower;
    }


    /*********************** Fin Code hamza ****************************** */


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
        $patternNames = $availabilityHours->map(function ($availabilityHour) {
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
