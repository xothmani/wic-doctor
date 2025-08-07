<?php

namespace App\Http\Controllers\API;


use App\Http\Requests\CreatePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Media;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UploadRepository;
use App\Services\MediaUploadService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

/**
 * Class PatientController
 * @package App\Http\Controllers\API
 */

class PatientAPIController extends Controller
{
    /** @var  PatientRepository */
    private PatientRepository $patientRepository;


    /** @var  CustomFieldRepository */
    private CustomFieldRepository $customFieldRepository;

    /** @var  UploadRepository */
    private UploadRepository $uploadRepository;

    private MediaUploadService $uploadService;

    public function __construct(PatientRepository $patientRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo, MediaUploadService $uploadService)
    {
        $this->patientRepository = $patientRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->uploadService = $uploadService;
        parent::__construct();
    }

    /**
     * Display a listing of the Patient.
     * GET|HEAD /patients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try{
            $this->patientRepository->pushCriteria(new RequestCriteria($request));
            $this->patientRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $patients = $this->patientRepository->all();
        foreach ($patients as $patient) {
            $patient->total_appointment = Appointment::where('patient_id', $patient->id)->count();
        }
        return $this->sendResponse($patients->toArray(), 'Patients retrieved successfully');
    }

    /**
     * Display the specified Patient.
     * GET|HEAD /patients/{id}
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $this->patientRepository->pushCriteria(new RequestCriteria($request));
            $this->patientRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        if (!empty($this->patientRepository)) {
            if ($request->has('user_id')){
                $user_id =  $request->only('user_id');
                $patient  = $this->patientRepository->findWhere('user_id',$user_id);
                $patient->total_appointment = Appointment::where('patient_id', $patient->id)->count();
            }

            else
                $patient = $this->patientRepository->findWithoutFail($id);
        }

        if (empty($patient)) {
            return $this->sendError('Patient not found');
        }
        return $this->sendResponse($patient->toArray(), 'Patient retrieved successfully');
    }

    /**
     * Create a new patient instance after a valid registration.
     *
     * @param CreatePatientRequest $request
     * @return JsonResponse|mixed
     */
    function store(Request $request)
    {
        Log::info("request: " . json_encode($request->all()));
        Log::info($request->file('image'));

        $user= User::find($request->user_id);
        
        try {
            $input = $request->all();
            unset($input['image']);
            Log::info("input: " . json_encode($input));
            $input['email'] = $user->email;
            $input['phone_number'] = $input['mobile_number'];
            $input['mobile_number'] = null;
            $input['gender'] = strtolower($input['gender']);
            $patient = $this->patientRepository->create($input);
            if ($request->hasFile('image')) {
                $media = $request->file('image');
                $this->uploadService->upload($media, 'App\Models\Patient', $patient->id);
            }else{
                Log::info("no image");
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($patient->toArray(), __('lang.saved_successfully', ['operator' => __('lang.patient')]));
    }


    /**
     * Update the specified Patient in storage.
     *
     * @param int $id
     * @param UpdatePatientRequest $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $patient = $this->patientRepository->findWithoutFail($id);
        if (empty($patient)) {
            return $this->sendError('Patient not found');
        }


        $input = $request->all();
        try {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->patientRepository->model());
            /*if (isset($input['image']) && $input['image'] && is_array($input['image'])) {
                foreach ($input['image'] as $fileUuid) {
                    $cacheUpload = $this->uploadRepository->getByUuid($fileUuid);
                    $mediaItem = $cacheUpload->getMedia('image')->first();
                    $mediaItem->copy($patient, 'image');
                }
            }*/

            if(isset($input['mobile_number'])){
                $input['phone_number'] = $input['mobile_number'];
            }else{
                $input['phone_number'] = null;
            }
            //$input['phone_number'] = $input['mobile_number'];
            $input['mobile_number'] = null;
            $input['gender'] = strtolower($input['gender']);

            $patient = $this->patientRepository->update($input, $id);

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $patient->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }

        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 200);
        }

        return $this->sendResponse($patient, __('lang.updated_successfully', ['operator' => __('lang.Patient')]));
    }


    /**
     * Remove the authenticated Patient from storage.
     *
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $patient = $this->patientRepository->delete($id);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($patient, __('lang.deleted_successfully', ['operator' => __('lang.patient')]));
    }


public function totalAppointments($patient_id)
{
    try {
        $totalAppointments = Appointment::where('patient_id', $patient_id)
            ->count();  // Count the number of records

        return $this->sendResponse($totalAppointments, 'Total appointments retrieved successfully.');
    } catch (\Exception $e) {
        return $this->sendError('An error occurred: ' . $e->getMessage());
    }
}

}
