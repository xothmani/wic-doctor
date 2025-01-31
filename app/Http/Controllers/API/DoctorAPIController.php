<?php
/*
 * File name: DoctorAPIController.php
 * Last modified: 2024.04.13 at 08:14:30
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;


use App\Criteria\Doctors\DoctorsOfUserCriteria;
use App\Criteria\Doctors\NearCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Repositories\DoctorRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\DoctorUrgency;
use App\Models\DoctorVacation;
use Carbon\Carbon;
use App\Models\Speciality;
use App\Models\DoctorSpeciality;
use App\Criteria\FilterByGouvernoratCriteria;

/**
 * Class DoctorController
 * @package App\Http\Controllers\API
 */
class DoctorAPIController extends Controller
{
    /** @var  DoctorRepository */
    private DoctorRepository $doctorRepository;
    /** @var UserRepository */
    private UserRepository $userRepository;
    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;

    public function __construct(DoctorRepository $doctorRepo, UserRepository $userRepository, UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->doctorRepository = $doctorRepo;
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * Display a listing of the Doctor.
     * GET|HEAD /doctors
     *
     * @param Request $request
     * @return JsonResponse
     */
public function index(Request $request): JsonResponse
{
    try {
        // Push existing criteria
        $this->doctorRepository->pushCriteria(new RequestCriteria($request));
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $this->doctorRepository->pushCriteria(new NearCriteria($request));

        // Apply gouvernorat filter if provided
        if ($request->has('gouvernorat') && !empty($request->input('gouvernorat'))) {
            $gouvernoratList = (array) $request->input('gouvernorat'); // Ensure it's an array
            $this->doctorRepository->pushCriteria(new FilterByGouvernoratCriteria($gouvernoratList));
        }

        // Load the address relationship
        $doctors = $this->doctorRepository->with('address')->all();

        // Additional filtering and processing
        if (!$request->has('all')) {
            $this->availableDoctors($doctors);
        }
        $this->hasValidSubscription($request, $doctors);
        $this->orderByRating($request, $doctors);
        $this->limitOffset($request, $doctors);
        $this->filterCollection($request, $doctors);

        // Convert collection to array
        $doctors = array_values($doctors->toArray());

        // Return response
        return $this->sendResponse($doctors, 'Doctors retrieved successfully');
    } catch (\Exception $e) {
        // Handle any exceptions that may occur
        return $this->sendError('Error retrieving doctors: ' . $e->getMessage());
    }
}
    /**
     * @param Collection $doctors
     */
   private function availableDoctors(Collection &$doctors)
{
// Assuming you have Eloquent models for the tables `Doctor`, `DoctorSpeciality`, and `Speciality`

    // Iterate over the doctors and load their specialties
    $doctors->each(function ($doctor) {
        // Get the specialties for each doctor
        $doctor->specialities = Speciality::whereIn('id',
            DoctorSpeciality::where('doctor_id', $doctor->id)
                ->pluck('speciality_id')
        )->pluck('name');
    });
    return $doctors;
}


    /**
     * @param Request $request
     * @param Collection $doctors
     */
    private function hasValidSubscription(Request $request, Collection &$doctors)
    {
	    return true;
        if (Module::isActivated('Subscription')) {
            $doctors = $doctors->filter(function ($element) {
                return $element->clinic->hasValidSubscription && $element->clinic->accepted;
            });
        } else {
            $doctors = $doctors->filter(function ($element) {
                return $element->clinic->accepted;
            });
        }
    }

    /**
     * @param Request $request
     * @param Collection $doctors
     */
    private function orderByRating(Request $request, Collection &$doctors)
    {
        if ($request->has('rating')) {
            $doctors = $doctors->sortBy('rate', SORT_REGULAR, true);
        }
    }

    /**
     * Display the specified Doctor.
     * GET|HEAD /doctors/{id}
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
public function show(Request $request, int $id): JsonResponse
{
    try {
        $this->doctorRepository->pushCriteria(new RequestCriteria($request));
        $this->doctorRepository->pushCriteria(new LimitOffsetCriteria($request));
    } catch (RepositoryException $e) {
        return $this->sendError($e->getMessage());
    }

    // Eager load the 'address' relationship with the doctor
    $doctor = $this->doctorRepository->with('address')->findWithoutFail($id);

    if (empty($doctor)) {
        return $this->sendError('Doctor not found');
    }

    // Optionally, handle API token and authentication if provided
    if ($request->has('api_token')) {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();
        if (!empty($user)) {
            auth()->login($user, true);
        }
    }


    // Return the doctor data, now including the address
    return $this->sendResponse($doctor->toArray(), 'Doctor retrieved successfully');
}


    /**
     * Store a newly created Doctor in storage.
     *
     * @param CreateDoctorRequest $request
     *
     * @return JsonResponse
     */
    public function store(CreateDoctorRequest $request): JsonResponse
    {
        try {
            $input = $request->all();
            $doctor = $this->doctorRepository->create($input);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    if ($cacheUpload) {
                        $mediaItem = $cacheUpload->getMedia('image')->first();
                        $mediaItem->copy($doctor, 'image');
                    }
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($doctor->toArray(), __('lang.saved_successfully', ['operator' => __('lang.doctor')]));
    }

    /**
     * Update the specified Doctor in storage.
     *
     * @param int $id
     * @param UpdateDoctorRequest $request
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function update(int $id, UpdateDoctorRequest $request): JsonResponse
    {
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);

        if (empty($doctor)) {
            return $this->sendError('Doctor not found');
        }
        try {
            $input = $request->all();
            $input['specialities'] = isset($input['specialities']) ? $input['specialities'] : [];
            $doctor = $this->doctorRepository->update($input, $id);
            if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    if ($cacheUpload) {
                        $mediaItem = $cacheUpload->getMedia('image')->first();
                        $mediaItem->copy($doctor, 'image');
                    }

                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($doctor->toArray(), __('lang.updated_successfully', ['operator' => __('lang.doctor')]));
    }

    /**
     * Remove the specified Doctor from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
        $doctor = $this->doctorRepository->findWithoutFail($id);

        if (empty($doctor)) {
            return $this->sendError('Doctor not found');
        }

        $doctor = $this->doctorRepository->delete($id);

        return $this->sendResponse($doctor, __('lang.deleted_successfully', ['operator' => __('lang.doctor')]));

    }

    /**
     * Remove Media of Doctor
     * @param Request $request
     * @throws RepositoryException
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        try {
            $this->doctorRepository->pushCriteria(new DoctorsOfUserCriteria(auth()->id()));
            $doctor = $this->doctorRepository->findWithoutFail($input['id']);
            if ($doctor->hasMedia($input['collection'])) {
                $doctor->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

public function getUrgencyHours(int $id, Request $request): JsonResponse
{
    try {
        // Apply criteria as needed for the doctor repository or other criteria
        $this->doctorRepository->pushCriteria(new RequestCriteria($request));
        $this->doctorRepository->pushCriteria(new LimitOffsetCriteria($request));
    } catch (RepositoryException $e) {
        return $this->sendError($e->getMessage());
    }

    // Retrieve the doctor information
    $doctor = $this->doctorRepository->findWithoutFail($id);

    if (empty($doctor)) {
        return $this->sendError('Doctor not found');
    }

    // Retrieve the urgency hours for the doctor
    $urgencies = DoctorUrgency::where('doctor_id', $id)
                            ->where(function ($query) use ($request) {
                                $date = $request->input('date');
                                if ($date) {
                                    // If a date filter is applied, fetch urgency hours for that date
                                    $query->where('jour', '=', $date);
                                }
                            })
                            ->get();

    // Format the urgency hours into an array
    $urgencyHours = $urgencies->map(function ($urgency) {
        return [
            'date' => $urgency->jour,  // The date for the urgency
            'start_time' => $urgency->heurDebut,  // Start time of the urgency
            'end_time' => $urgency->heurFin,  // End time of the urgency
            'isUrgency' => true  // Mark as urgency
        ];
    });

    // Optionally, add vacation dates exclusion (you can adapt this if needed)
    $vacations = DoctorVacation::where('doctor_id', $id)
                                ->where(function ($query) use ($request) {
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

    // Exclude vacation dates from the urgency hours
    $urgencyHours = $urgencyHours->filter(function ($urgency) use ($vacationDates) {
        return !in_array($urgency['date'], $vacationDates);
    });

    // Return the formatted urgency hours with success response
    return $this->sendResponse($urgencyHours, 'Urgency hours retrieved successfully');
}


}

