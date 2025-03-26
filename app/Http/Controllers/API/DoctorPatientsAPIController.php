<?php

namespace App\Http\Controllers\API;


use App\Models\DoctorPatients;
use App\Repositories\DoctorPatientsRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\Doctor;
/**
 * Class DoctorPatientsController
 * @package App\Http\Controllers\API
 */

class DoctorPatientsAPIController extends Controller
{
    /** @var  DoctorPatientsRepository */
    private DoctorPatientsRepository $doctorPatientsRepository;

    public function __construct(DoctorPatientsRepository $doctorPatientsRepo)
    {
        parent::__construct();
        $this->doctorPatientsRepository = $doctorPatientsRepo;
    }

    /**
     * Display a listing of the DoctorPatients.
     * GET|HEAD /doctorPatients
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try{
            $this->doctorPatientsRepository->pushCriteria(new RequestCriteria($request));
            $this->doctorPatientsRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $doctorPatients = $this->doctorPatientsRepository->all();

        return $this->sendResponse($doctorPatients->toArray(), 'Doctor Patients retrieved successfully');
    }

    /**
     * Display the specified DoctorPatients.
     * GET|HEAD /doctorPatients/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var DoctorPatients $doctorPatients */
        if (!empty($this->doctorPatientsRepository)) {
            $doctorPatients = $this->doctorPatientsRepository->findWithoutFail($id);
        }

        if (empty($doctorPatients)) {
            return $this->sendError('Doctor Patients not found');
        }

        return $this->sendResponse($doctorPatients->toArray(), 'Doctor Patients retrieved successfully');
    }

	    /**
     * Display a listing of the DoctorPatients with recent doctors for a specific patient.
     * GET|HEAD /doctorPatients/recent/{patientId}
     *
     * @param Request $request
     * @param int $patientId
     * @return \Illuminate\Http\JsonResponse
     */
     public function getRecentDoctors($patient_id)
{
    try {
        // Query the doctor_patients table to get the patient_id and doctor_id
        $doctorPatients = DoctorPatients::where('patient_id', $patient_id)
            ->orderBy('doctor_id', 'desc')  // Order by doctor_id
            ->limit(4)  // Limit to 4 results
            ->get();

        // Check if data is found
        if ($doctorPatients->isEmpty()) {
            return $this->sendError('No recent doctors found for this patient.');
        }

        // Retrieve the doctor details based on the doctor_id
        $doctorIds = $doctorPatients->pluck('doctor_id');  // Extract doctor_ids from the doctor_patients table
        
        // Fetch the full doctor objects by doctor_ids
        $doctors = Doctor::whereIn('id', $doctorIds)  // Query the Doctor model using the doctor_ids
            ->get();  // Get all the doctor details

        return $this->sendResponse($doctors->toArray(), 'Recent doctors retrieved successfully.');
    } catch (\Exception $e) {
        return $this->sendError('An error occurred: ' . $e->getMessage());
    }
}
}
