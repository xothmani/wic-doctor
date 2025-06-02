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

use App\Models\Doctor;

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
use App\Events\AppointmentStatusChangedEvent;
use App\Repositories\RoomRepository;
use App\Enums\RoomStatus;




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


    private RoomRepository $roomRepository;


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

        PaymentStatusRepository $paymentStatusRepository,
        RoomRepository $roomRepository

    ) {
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

        $this->roomRepository = $roomRepository;

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


                    'appointments.cancel_reason',

                    'appointments.created_at',
                    'appointments.updated_at',
                    'appointments.motif_id',
                    'appointments.type',
                    'addresses.address as doctor_address',
                    'pattern.id as pattern_id',
                    'pattern.nom as pattern_name',
                    // Doctor fields
                    'doctors.name as doctor_name',
                    'doctors.discount_price as doctor_discount_price',

                    'doctors.price as doctor_price',
                    'doctors.enable_appointment as doctor_enable_appointment',
                    'doctors.enable_at_clinic as doctor_enable_at_clinic',
                    'doctors.enable_at_customer_address as doctor_enable_at_customer_address',
                    'doctors.enable_online_consultation as doctor_enable_online_consultation',

                    // Patient fields
                    'patients.first_name as patient_first_name',
                    'patients.last_name as patient_last_name',
                    'patients.age as patient_age',
                    'patients.gender as patient_gender',
                    'appointment_statuses.status as appointment_status_name',
                    'appointment_statuses.order as appointment_status_order'
                )->where('appointments.user_id', '=', $userId)
                ->orderBy('appointments.start_at', 'desc') // Sort by closest appointment time
                ->get();
            Log::info("test get price doctor id", ['appointments' => $appointments[0]]);
            // Format the appointments to include nested objects
            $formattedAppointments = $appointments->map(function ($appointment) {
                $decodedFirstName = json_decode($appointment->patient_first_name, true);
                $decodedLastName = json_decode($appointment->patient_last_name, true);
                $decodedDoctorName = json_decode($appointment->doctor_name, true);
                $decodedDoctorPrice = json_decode($appointment->doctor_price, true);
                $decodedPatternName = json_decode($appointment->pattern_name, true);
                $decodedDoctorAddress = json_decode($appointment->doctor_address, true);

                $decodedDoctorEnableAppointment = json_decode($appointment->doctor_enable_appointment, true);
                $decodedDoctorEnableAtClinic = json_decode($appointment->doctor_enable_at_clinic, true);
                $decodedDoctorEnableAtAddress = json_decode($appointment->doctor_enable_at_customer_address, true);
                $decodedDoctorEnableOnlineConsultation = json_decode($appointment->doctor_enable_online_consultation, true);

                // Use 'fr' key if available, or fallback to raw values
                $patientFirstName = $decodedFirstName['fr'] ?? $appointment->patient_first_name;
                $patientLastName = $decodedLastName['fr'] ?? $appointment->patient_last_name;
                $doctorName = $decodedDoctorName['fr'] ?? $appointment->doctor_name;
                $doctorPrice = $decodedDoctorPrice ?? 0;
                $patternName = $decodedPatternName['fr'] ?? $appointment->pattern_name;
                $doctorAddress = $decodedDoctorAddress['fr'] ?? $appointment->doctor_address;
                $doctorEnableAppointment = $decodedDoctorEnableAppointment ?? 0;
                $doctorEnableAtClinic = $decodedDoctorEnableAtClinic ?? 0;
                $doctorEnableAtAddress = $decodedDoctorEnableAtAddress ?? 0;
                $doctorEnableOnlineConsultation = $decodedDoctorEnableOnlineConsultation ?? 0;

                
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
                    'cancel_reason' => $appointment->cancel_reason,
                    'type' => $appointment->type,
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

                        ],
                        'price' => $doctorPrice,
                        'enabled_appointment' => $doctorEnableAppointment,
                        'at_clinic' => $doctorEnableAtClinic,
                        'at_customer_address' => $doctorEnableAtAddress,
                        'enable_online_consultation' => $doctorEnableOnlineConsultation

                    ],
                    // Pattern object
                    'pattern' => [
                        'id' => $appointment->pattern_id,
                        'name' => $appointment->pattern_name,
                    ]
                ];
            });



            Log::info("Formatted Appointments Type:", ['type' => gettype($formattedAppointments)]);


            $formattedAppointments = $formattedAppointments->map(function ($app) {
                Log::info("Appointment foreach = ", ["json data" => json_encode($app)]);

                // Recherche du docteur
                $doc = Doctor::find($app['doctor_id']);
                $user = User::find($doc['user_id']);
                $doc->user = $user;
                Log::info("Appointment Doctor", ["Doctor" => $doc]);
                if ($doc) {
                    Log::info("Appointment foreach avant = ", ["Doctor" => json_encode($app)]);
                    $app['doctor'] = $doc; // Ajouter le rate
                    Log::info("Appointment foreach après = ", ["Doctor" => json_encode($app)]);
                }

                return $app; // Retourner l'élément modifié
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
        if (empty($appointment->doctor_id)) {
            return $this->sendError(error: 'Doctor empty');
        } else {
            $appointment->doctor = $this->doctorRepository->findWithoutFail($appointment->doctor_id);
        }
        
        //Log::info($appointment);
        $doctor = $this->doctorRepository->findWithoutFail($appointment->doctor_id);
        $patient = $this->patientRepository->findWithoutFail($appointment->patient_id);
        $clinic = $this->clinicRepository->findWithoutFail($appointment->clinic_id);
        $appointment->doctor = $doctor; // solution pour doctor cast
        $appointment->patient = $patient; // solution pour doctor cast
        $appointment->clinic = $clinic; // solution pour doctor cast
        Log::info("Clinic cast error", [$clinic]);
        //Log::info($appointment->doctor_id);

        $user = $this->userRepository->findWithoutFail($doctor->user_id);
        Log::info($user);
        $doctor->user = $user;
        $address = Address::where('user_id', $doctor->user_id)->first();
        Log::info("Addresse", ["Addresse" => $address]);
        $appointment->doctor->address = $address;
        
        Log::info($appointment);
        if (empty($appointment)) {
            return $this->sendError('Appointment not found');
        }
        //return $this->sendResponse($appointment->toArray(),'Appointment retrieved successfully');
        return response()->json([
            'success' => true,
            'message' => 'Appointment retrieved successfully',
            'data' => $appointment
        ])->header('Content-Type', 'application/json');
    }


    public function getAppointmentById($id): JsonResponse
    {
        $appointment = $this->appointmentRepository->findWithoutFail($id);
        $appointment->doctor->user = $this->userRepository->findWithoutFail($appointment->doctor->user_id);
        $appointment->clinic = $this->clinicRepository->findWithoutFail($appointment->clinic_id);
        $appointment->patient = $this->patientRepository->findWithoutFail($appointment->patient_id);
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
            $utc = new \DateTimeZone('UTC');
            $tunis = new \DateTimeZone('Africa/Tunis');

            // Extract the necessary data from the nested objects
            $data = [
                'doctor_id' => $request->input('doctor.id'), // Extract doctor ID
                'patient_id' => $request->input('patient.id'), // Extract patient ID if needed
                'user_id' => $request->input('user_id'),
                //'clinic_id' => $request->input('clinic.id'), // Extract clinic ID
                'quantity' => $request->input('quantity', 1),
                'appointment_status_id' => $request->input('appointment_status_id', 1),
                'address' => $request->input('address.address'), // Extract address as a string
                'payment_id' => $request->input('payment_id'),
                'coupon' => $request->input('coupon'),
                'taxes' => json_encode($request->input('taxes', [])), // Ensure taxes is an array and encode as JSON
                'appointment_at' => Carbon::parse($request->input('appointment_at',$tunis)),
                'start_at' => Carbon::parse($request->input('start_at'), $tunis),
                'ends_at' => Carbon::parse($request->input('ends_at'), $tunis),
                'hint' => $request->input('hint'),
                'online' => $request->input('type', 'aucun'),
                'cancel' => $request->input('cancel', false),
                'motif_id' => $request->input('motif_id'),
                'type' => 'mobile',
            ];


            // Validate that critical fields are not null
            if (is_null($data['doctor_id']) || is_null($data['user_id'])) {
                Log::error("Missing required data: doctor_id, user_id, or clinic_id is null.");
                return response()->json([
                    'status' => 400,
                    'error_type' => 'Validation Error',
                    'message' => 'Doctor ID, User ID, and Clinic ID are required fields and cannot be null.'
                ], 400);
            }

            // Insert the data into the appointments table
            $appointmentId = DB::table('appointments')->insertGetId($data);



            //If the appointment is remote, create a room for it
            /*if ($data['online'] == 'teleconsultation') {
                try {
                    $startAt = Carbon::parse($request->input('start_at'), $utc)->setTimezone($tunis);
                    // Heure donnée (10:11:00)
                    $date = $startAt->toDateString();
                    $time = $startAt->format('H-i-s');


                    $patient_first_name = $request->input('patient.first_name');
                    $patient_last_name = $request->input('patient.last_name');
                    $patient_phone = $request->input('patient.phone_number');
                    $roomName = "{$patient_first_name}_{$patient_last_name}_{$patient_phone}_{$date}_{$time}";
                    $doctorId = is_array($request->input('doctor.id')) ? $request->input('doctor.id')[0] : $request->input('doctor.id');
                    $doctor = Doctor::find($doctorId);

                    Log::info("RoomName", [
                        "RoomName" => $roomName,
                        "doctor" => $doctor,
                        "user_id" => $doctor->user_id
                    ]);

                    $roomData = [
                        'room_name' => $roomName,
                        'meet_link' => 'https://meet.wic-doctor.com/' . $roomName,
                        'owner_id' => $doctor->user_id,
                        'appointment_id' => $appointmentId,
                        'patient_id' => is_array($request->input('patient.id')) ? $request->input('patient.id')[0] : $request->input('patient.id'),
                        'date' => $date,
                        'time' => $time,
                        'status' => RoomStatus::PENDING,
                    ];

                    $this->roomRepository->create($roomData);
                } catch (Exception $e) {
                    return response()->json("Room not created {$e}", 400);
                }

            }*/


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
        $appointment = Appointment::find($id);
        $new_status_id = $request->input('appointment_status_id');
        $appointment->cancel_reason = $request->input('cancel_reason');
        $appointment->appointment_status_id = $new_status_id;
        $user = $this->userRepository->findWithoutFail($appointment->user_id);
        $deviceToken = $user->device_token;
        $appointment->save();
        event(new AppointmentStatusChangedEvent($appointment,$new_status_id,$deviceToken));
        return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
    }


    //Ajouter une méthode pour update seulement date et heure ( ajouté par hamza )
    public function updateDateAndTime(int $id, Request $request): JsonResponse
    {
        Log::info('Update Appointment Request Test Format date:', $request->all());
        $startAt = $request->input('start_at');
        if ($startAt != null && !empty($startAt)) {
            try {
                //$this->appointmentRepository->update(['start_at' => $request->input('start_at')], $id);
                $this->appointmentRepository->update([
                    'start_at' => $request->input('start_at'),
                    'appointment_at' => $request->input('start_at'),
                    'ends_at' => $request->input('ends_at')
                ], $id);
                return response()->json(true);
                //return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
            } catch (ValidatorException $e) {
                return response()->json(false);
            }
        } else {
            return response()->json(false);
        }
    }



    //Ajouter une méthode pour update seulement status( ajouté par hamza )
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        Log::info('Update Appointment Request Status:', $request->all());
        /*$appointment = Appointment::find($id);
        Log::info('Update Appointment :', ["appointment" => $appointment]);
        Log::info("Update Appointment Status : ",["appointment" => $appointment->doctor_id]);
        $doctor = $this->doctorRepository->findWithoutFail($appointment->doctor_id);
        $appointment->doctor=$doctor;
        Log::info('Update Appointment :', ["appointment" => $appointment]);
        $new_status_id = $request->input('appointment_status_id');
        $deviceToken = $request->input('device_token');
        
        $startAt = Carbon::parse($appointment->start_at);
        $now = Carbon::now();
        Log::info("Start At : ",["startAt" => $startAt]);
        Log::info("now : ",["now" => $now]);
        Log::info("Différence", ["diff" => $startAt->diffInDays($now)]);
        if($startAt->diffInDays($now) >= 2){
            try {
                $this->appointmentRepository->update(['status' => $new_status_id], $id);
                event(new AppointmentStatusChangedEvent($appointment,$new_status_id,$deviceToken));
                //Notification::send([$app->user], new StatusChangedAppointment($app));
                // add notification dans la base de donnée
                return response()->json([
                    "success" => true,
                    "data" => "Status changed successfully"
                ]);
                //return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
            } catch (ValidatorException $e) {
                return response()->json([
                    "success" => false,
                    "data" => $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                "success" => true,
                "data" => "Tu as passé la date limite pour cette opération"
            ]);

        }*/ 
    }




    /**
     * Create message for the API request (example function).
     *
     * @param Appointment $appointment
     * @return FcmMessage
     */
    private function createMessageForAppointment(Appointment $appointment, string $id)
    {

        Log::info("Create message notification firebase");
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

        return 'https://fcm.googleapis.com/v1/projects/wic-doctor-b83e0/messages:send';
    }


}

