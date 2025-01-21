<?php
/*
 * File name: AppointmentAPIController.php
 * Last modified: 2024.05.03 at 22:25:44
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;


use App\Criteria\Appointments\AppointmentsOfPatientCriteria;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Notifications\NewAppointment;
use App\Notifications\StatusChangedAppointment;
use App\Repositories\AddressRepository;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentStatusRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ClinicRepository;
use App\Repositories\DoctorPatientsRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentStatusRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Benwilkins\FCM\FcmMessage;
/**
 * Class AppointmentController
 * @package App\Http\Controllers\API
 */
class AppointmentAPIController extends Controller
{
    /** @var  AppointmentRepository */
    private AppointmentRepository $appointmentRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var AppointmentStatusRepository
     */
    private AppointmentStatusRepository $appointmentStatusRepository;
    /**
     * @var PaymentRepository
     */
    private PaymentRepository $paymentRepository;
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;
    /**
     * @var AddressRepository
     */
    private AddressRepository $addressRepository;
    /**
     * @var TaxRepository
     */
    private TaxRepository $taxRepository;
    /**
     * @var DoctorRepository
     */
    private DoctorRepository $doctorRepository;
    /**
     * @var DoctorPAtientsRepository
     */
    private DoctorPAtientsRepository $doctorPatientsRepository;
    /**
     * @var ClinicRepository
     */
    private ClinicRepository $clinicRepository;
    /**
     * @var CouponRepository
     */
    private CouponRepository $couponRepository;
    /**
     * @var PatientRepository
     */
    private PatientRepository $patientRepository;
    /**
     * @var PaymentStatusRepository
     */
    private PaymentStatusRepository $paymentStatusRepository;

    public function __construct(
        AppointmentRepository $appointmentRepo,
        CustomFieldRepository $customFieldRepo,
        UserRepository $userRepo,
        AppointmentStatusRepository $appointmentStatusRepo,
        NotificationRepository $notificationRepo,
        PaymentRepository $paymentRepo,
        AddressRepository $addressRepository,
        TaxRepository $taxRepository,
        DoctorRepository $doctorRepository,
        DoctorPatientsRepository $doctorPatientsRepository,
        ClinicRepository $clinicRepository,
        CouponRepository $couponRepository,
        PatientRepository $patientRepository,
        PaymentStatusRepository $paymentStatusRepository
    )
    {
        parent::__construct();
        $this->appointmentRepository = $appointmentRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->appointmentStatusRepository = $appointmentStatusRepo;
        $this->notificationRepository = $notificationRepo;
        $this->paymentRepository = $paymentRepo;
        $this->addressRepository = $addressRepository;
        $this->taxRepository = $taxRepository;
        $this->doctorRepository = $doctorRepository;
        $this->doctorPatientsRepository = $doctorPatientsRepository;
        $this->clinicRepository = $clinicRepository;
        $this->couponRepository = $couponRepository;
        $this->patientRepository = $patientRepository;
        $this->paymentStatusRepository = $paymentStatusRepository;
    }

    /**
     * Display a listing of the Appointment.
     * GET|HEAD /appointments
     *
     * @param Request $request
     * @return JsonResponse
     */
	

public function index(): JsonResponse
{
    try {
        // Get the logged-in user's ID
        $userId = auth()->id();

        // Retrieve appointments and related data
        $appointments = DB::table('appointments')
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
	    ->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
            ->leftJoin('addresses', 'addresses.user_id', '=', 'doctors.user_id') // Join addresses using the doctor's user_id
            ->leftJoin('pattern', 'appointments.motif_id', '=', 'pattern.id') // Join patterns using motif_id
            ->select(
                'appointments.id',
                'appointments.clinic_id',
                'appointments.doctor_id',
                'appointments.patient_id',
                'appointments.user_id',
                'appointments.quantity',
                'appointments.appointment_status_id',
                'appointments.address',
                'appointments.payment_id',
                'appointments.coupon',
                'appointments.taxes',
                'appointments.appointment_at',
                'appointments.start_at',
                'appointments.ends_at',
                'appointments.hint',
                'appointments.online',
                'appointments.cancel',
                'appointments.created_at',
                'appointments.updated_at',
                'appointments.motif_id',
		'addresses.address as doctor_address',
		'pattern.id as pattern_id', 
        	'pattern.nom as pattern_name',
                // Doctor fields
                'doctors.name as doctor_name',
                'doctors.discount_price as doctor_discount_price',
                // Patient fields
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.age as patient_age',
                'patients.gender as patient_gender',
		'appointment_statuses.status as appointment_status_name',
		'appointment_statuses.order as appointment_status_order'
            )
            ->where('appointments.user_id', '=', $userId)
    	    ->orderBy('appointments.appointment_at', 'desc') // Sort by closest appointment time
            ->get();

        // Format the appointments to include nested objects
        $formattedAppointments = $appointments->map(function ($appointment) {
		$decodedFirstName = json_decode($appointment->patient_first_name, true);
    $decodedLastName = json_decode($appointment->patient_last_name, true);
    $decodedDoctorName = json_decode($appointment->doctor_name, true);
    $decodedPatternName = json_decode($appointment->pattern_name, true);
    $decodedDoctorAddress = json_decode($appointment->doctor_address, true); // Parse the address JSON

    // Use 'fr' key if available, or fallback to raw values
    $patientFirstName = $decodedFirstName['fr'] ?? $appointment->patient_first_name;
    $patientLastName = $decodedLastName['fr'] ?? $appointment->patient_last_name;
    $doctorName = $decodedDoctorName['fr'] ?? $appointment->doctor_name;
    $patternName = $decodedPatternName['fr'] ?? $appointment->pattern_name;
    $doctorAddress = $decodedDoctorAddress['fr'] ?? $appointment->doctor_address;

            return [
                'id' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'user_id' => $appointment->user_id,
                'quantity' => $appointment->quantity,
                'appointment_status_id' => $appointment->appointment_status_id,
                'address' => $appointment->address,
                'payment_id' => $appointment->payment_id,
                'coupon' => $appointment->coupon,
                'taxes' => $appointment->taxes,
                'appointment_at' => $appointment->appointment_at,
                'start_at' => $appointment->start_at,
                'ends_at' => $appointment->ends_at,
                'hint' => $appointment->hint,
                'online' => $appointment->online,
                'cancel' => $appointment->cancel,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
                'motif_id' => $appointment->motif_id,
		'appointment_status' => [
                    'id' => $appointment->appointment_status_id,
                    'name' => $appointment->appointment_status_name,
		   'order' => $appointment->appointment_status_order,
                ],
                // Patient object
                'patient' => [
                    'id' => $appointment->patient_id,
		    'first_name' => $patientFirstName,
            		'last_name' => $patientLastName,
                    'age' => $appointment->patient_age,
                    'gender' => $appointment->patient_gender,
                ],
                // Doctor object
                'doctor' => [
                    'id' => $appointment->doctor_id,
                    'name' => $doctorName,
                    'discount_price' => $appointment->doctor_discount_price,
                    // Address object for the doctor
                    'address' => [
                        'address' => $doctorAddress,
                    ]
                ],
                // Pattern object
                'pattern' => [
                    'id' => $appointment->pattern_id,
                    'name' => $appointment->pattern_name,
                ]
            ];
        });

        // Log the formatted data
        Log::info('Appointments retrieved successfully for user ID ' . $userId, ['appointments' => $formattedAppointments->toArray()]);

        // Return the response with the formatted appointments
        return response()->json([
            'status' => 200,
            'message' => 'Appointments retrieved successfully',
            'data' => $formattedAppointments
        ], 200);
    } catch (Exception $e) {
        // Log the error message
        Log::error('Error retrieving appointments:', ['message' => $e->getMessage()]);

        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve appointments: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Display the specified Appointment.
     * GET|HEAD /appointments/{id}
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $this->appointmentRepository->pushCriteria(new RequestCriteria($request));
            $this->appointmentRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $appointment = $this->appointmentRepository->findWithoutFail($id);
        $appointment->doctor->user=$this->userRepository->findWithoutFail($appointment->doctor->user_id);
        if (empty($appointment)) {
            return $this->sendError('Appointment not found');
        }
        return $this->sendResponse($appointment->toArray(), 'Appointment retrieved successfully');


    }

    /**
     * Store a newly created Appointment in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
public function store(Request $request): JsonResponse
{
    try {
        // Log the incoming request data
        Log::info('Store Appointment Request:', $request->all());
        $motifObject = $request->input('motif_id');
        Log::info('Motif Object:', ['motif_id' => $motifObject]);

        // Extract the necessary data from the nested objects
        $data = [
            'doctor_id' => $request->input('doctor.id'), // Extract doctor ID
            'patient_id' => $request->input('patient.id'), // Extract patient ID if needed
            'user_id' => $request->input('user_id'),
            'clinic_id' => $request->input('clinic.id'), // Extract clinic ID
            'quantity' => $request->input('quantity', 1),
            'appointment_status_id' => $request->input('appointment_status_id', 1),
            'address' => $request->input('address.address'), // Extract address as a string
            'payment_id' => $request->input('payment_id'),
            'coupon' => $request->input('coupon'),
            'taxes' => json_encode($request->input('taxes', [])), // Ensure taxes is an array and encode as JSON
            'appointment_at' => $request->input('appointment_at'),
            'start_at' => Carbon::parse($request->input('start_at'))->setTimezone(config('app.timezone')),
	    'ends_at' => Carbon::parse($request->input('ends_at'))->setTimezone(config('app.timezone')),
            'hint' => $request->input('hint'),
            'online' => 'mobile',
            'cancel' => $request->input('cancel', false),
            'motif_id' => $request->input('motif_id.id')
        ];

        // Log each item in the data array to verify its contents
        foreach ($data as $key => $value) {
            Log::info("Field {$key}: ", [$value]);
        }

        // Validate that critical fields are not null
        if (is_null($data['doctor_id']) || is_null($data['user_id']) || is_null($data['clinic_id'])) {
            Log::error("Missing required data: doctor_id, user_id, or clinic_id is null.");
            return response()->json([
                'status' => 400,
                'error_type' => 'Validation Error',
                'message' => 'Doctor ID, User ID, and Clinic ID are required fields and cannot be null.'
            ], 400);
        }

        // Insert the data into the appointments table
        $appointmentId = DB::table('appointments')->insertGetId($data);

        // Check if the doctor-patient relationship exists
        $existingRecord = DB::table('doctor_patients')
            ->where('doctor_id', $data['doctor_id'])
            ->where('patient_id', $data['patient_id'])
            ->first();

        // If the doctor-patient relationship doesn't exist, insert it
        if (!$existingRecord) {
            DB::table('doctor_patients')->insert([
                'doctor_id' => $data['doctor_id'],
                'patient_id' => $data['patient_id']
            ]);
            Log::info('Inserted new doctor-patient relationship:', [
                'doctor_id' => $data['doctor_id'],
                'patient_id' => $data['patient_id']
            ]);
        }

        // Return success response with appointment ID
        return response()->json([
            'status' => 200,
            'message' => 'Appointment created successfully',
            'appointment_id' => $appointmentId
        ], 200);

    } catch (\Exception $e) {
        // Log the exception message if an error occurs
        Log::error('Error Creating Appointment:', ['message' => $e->getMessage()]);

        // Return an error response
        return response()->json([
            'status' => 500,
            'error_type' => 'General Error',
            'message' => 'An error occurred while creating the appointment: ' . $e->getMessage()
        ], 500);
    }
}


    /**
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        // Log the incoming request data
        Log::info('Update Appointment Request:', $request->all());
    
        // Find the appointment, return error if not found
        $oldAppointment = $this->appointmentRepository->findWithoutFail($id);
        if (empty($oldAppointment)) {
            // Log the error message
            Log::error('Appointment not found for ID ' . $id);
            return $this->sendError('Appointment not found');
        }
    
        // Validate input if necessary (optional)
        $input = $request->all();
    
        try {
            // If cancel request is present, modify the status accordingly
            if (isset($input['cancel']) && $input['cancel'] == '1') {
                $input['payment_status_id'] = 3; // Assuming 3 means canceled
		$input['appointment_status_id'] = 7; // Assuming 7 means canceled status
		$input['cancel']=1;
            }
    
            // Log the input data
            Log::info('Update Appointment Input:', $input);
    
            // Update the appointment
            $appointment = $this->appointmentRepository->update($input, $id);
    
            // Log the updated appointment data
            Log::info('Updated Appointment:', $appointment->toArray());
    
            // If the appointment status is below a certain threshold, send API request
            if ($appointment->appointmentStatus->order < 40) {
                // Log the message creation
                Log::info('Creating message for appointment status update');
                // Ensure $message is defined before using it (replace this with actual message creation logic)
                $message = $this->createMessageForAppointment($appointment, $oldAppointment->doctor->id);
    
                // Log the message data
               
    
                // Send API request
                $response = (new Client())->post($this->getApiUri(), [
                    'headers' => [
                        'Authorization' => 'key=' . $this->accessToken, // Replace this with your actual token handling logic
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $message->formatData(),  // Ensure body is properly formatted
                ]);
    
                // Handle the response if needed
                if ($response->getStatusCode() !== 200) {
                    // Log the error message
                    Log::error('Failed to send API request:', ['message' => $response->getReasonPhrase()]);
                    return $this->sendError('Failed to send API request');
                }
            } else {
                $message = $this->createMessageForAppointment($appointment, $oldAppointment->user->id);
                $response = (new Client())->post($this->getApiUri(), [ 
                    'headers' => [
                    'Authorization'=> 'key='. $this->accessToken,
                    ],
                    'json'=> $message->formatData(),
                    ]);
                if ($response->getStatusCode() !== 200) {
                    // Log the error message
                    Log::error('Failed to send API request:', ['message' => $response->getReasonPhrase()]);
                    return $this->sendError('Failed to send API request');
                }
            }
    
        } catch (ValidatorException $e) {
            // Handle validation errors
            return $this->sendError($e->getMessage());
        } catch (Exception $e) {
            // Handle unexpected errors
            return $this->sendError('An error occurred: ' . $e->getMessage());
        }
    
        // Return the updated appointment data
        return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
    }
    
    
    /**
     * Create message for the API request (example function).
     *
     * @param Appointment $appointment
     * @return FcmMessage
     */
    private function createMessageForAppointment(Appointment $appointment, string $id)
    {
        // Logic to create the message object based on the appointment data
         $user = User::find($id);
    if (!$user) {
        throw new Exception("User not found for ID: $id");
    }
        $message = new FcmMessage(); // Example, you may have your own message formatting
        $message->content(['title' => 'NOTIFICATION', 'body' => 'YOUR APPOINTMENT STATUS HAS CHANGED'])->to($user->device_token);
        return $message;
    }
    
    private function getApiUri()
    {
        return 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
    }
    

}
