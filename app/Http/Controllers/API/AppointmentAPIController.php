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
<<<<<<< HEAD
use App\Models\Doctor;
=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
use App\Events\AppointmentStatusChangedEvent;
use App\Repositories\RoomRepository;
use App\Enums\RoomStatus;



=======
>>>>>>> merging_dev_agendabranch
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

<<<<<<< HEAD
    private  RoomRepository $roomRepository;

=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
        PaymentStatusRepository $paymentStatusRepository,
        RoomRepository $roomRepository
=======
        PaymentStatusRepository $paymentStatusRepository
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
        $this->roomRepository = $roomRepository;
=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
	        ->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
=======
	    ->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
                'appointments.cancel_reason',
=======
>>>>>>> merging_dev_agendabranch
                'appointments.created_at',
                'appointments.updated_at',
                'appointments.motif_id',
		'addresses.address as doctor_address',
		'pattern.id as pattern_id', 
        	'pattern.nom as pattern_name',
                // Doctor fields
                'doctors.name as doctor_name',
                'doctors.discount_price as doctor_discount_price',
<<<<<<< HEAD
                'doctors.price as doctor_price',
                'doctors.enable_appointment as doctor_enable_appointment',
                'doctors.enable_at_clinic as doctor_enable_at_clinic',
                'doctors.enable_at_customer_address as doctor_enable_at_customer_address',
                'doctors.enable_online_consultation as doctor_enable_online_consultation',
=======
>>>>>>> merging_dev_agendabranch
                // Patient fields
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.age as patient_age',
                'patients.gender as patient_gender',
<<<<<<< HEAD
		        'appointment_statuses.status as appointment_status_name',
		        'appointment_statuses.order as appointment_status_order'
            )->where('appointments.user_id', '=', $userId)
    	    ->orderBy('appointments.appointment_at', 'desc') // Sort by closest appointment time
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
        $doctorEnableAppointment = $decodedDoctorEnableAppointment ?? 0 ;
        $doctorEnableAtClinic = $decodedDoctorEnableAtClinic ?? 0;
        $doctorEnableAtAddress = $decodedDoctorEnableAtAddress ?? 0;
        $doctorEnableOnlineConsultation = $decodedDoctorEnableOnlineConsultation ?? 0;
=======
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
>>>>>>> merging_dev_agendabranch

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
<<<<<<< HEAD
                'cancel_reason' => $appointment->cancel_reason,
=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
                    ],
                    'price' => $doctorPrice,
                    'enabled_appointment' => $doctorEnableAppointment,
                    'at_clinic' => $doctorEnableAtClinic,
                    'at_customer_address' => $doctorEnableAtAddress,
                    'enable_online_consultation' => $doctorEnableOnlineConsultation
=======
                    ]
>>>>>>> merging_dev_agendabranch
                ],
                // Pattern object
                'pattern' => [
                    'id' => $appointment->pattern_id,
                    'name' => $appointment->pattern_name,
                ]
            ];
        });

<<<<<<< HEAD

        Log::info("Formatted Appointments Type:", ['type' => gettype($formattedAppointments)]);


        $formattedAppointments = $formattedAppointments->map(function ($app) {
            Log::info("Appointment foreach = ", ["json data" => json_encode($app)]);
            
            // Recherche du docteur
            $doc = Doctor::find($app['doctor_id']);
            $user = User::find($doc['user_id']);
            $doc->user=$user;
            Log::info("Appointment Doctor", ["Doctor" => $doc]);
            if ($doc) {
                Log::info("Appointment foreach avant = ", ["Doctor" => json_encode($app)]);
                $app['doctor'] = $doc; // Ajouter le rate
                Log::info("Appointment foreach après = ", ["Doctor" => json_encode($app)]);
            }
            
            return $app; // Retourner l'élément modifié
        });


=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD

        $appointment = $this->appointmentRepository->findWithoutFail($id);
        if(empty($appointment->doctor_id)) {
            return $this->sendError(error: 'Doctor empty');
        }else{
            $appointment->doctor=$this->doctorRepository->findWithoutFail($appointment->doctor_id);
        }
        //Log::info($appointment);
        $doctor=$this->doctorRepository->findWithoutFail($appointment->doctor_id);
        $patient = $this->patientRepository->findWithoutFail($appointment->patient_id);
        Log::info($patient);
        //Log::info($appointment->doctor_id);

        $user=$this->userRepository->findWithoutFail($doctor->user_id);
        Log::info($user);
        $doctor->user=$user;
        $address = Address::where('user_id', $doctor->user_id)->first();
        Log::info("Addresse",["Addresse" => $address]);
        $appointment->doctor->address=$address;
        $appointment->address_id=$address->id;
        $appointment->doctor=$doctor;
        $appointment->patient=$patient;
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


    public function getAppointmentById($id): JsonResponse{
        try {
            Log::info("Get Appointment By Id : ",["id" => $id]);
            $appointment = Appointment::find($id);
            Log::info("Get Appointment By Id : ",["appointment" => $appointment]);
            return response()->json($appointment);
        }catch(RepositoryException $e){
            return response()->json($e->getMessage());
        }
=======
        $appointment = $this->appointmentRepository->findWithoutFail($id);
        $appointment->doctor->user=$this->userRepository->findWithoutFail($appointment->doctor->user_id);
        if (empty($appointment)) {
            return $this->sendError('Appointment not found');
        }
        return $this->sendResponse($appointment->toArray(), 'Appointment retrieved successfully');


>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
        Log::info('Store Appointment Request:', ['clinic' => $request->input('clinic')]);
        $motifObject = $request->input('motif_id');
        // Extract the necessary data from the nested objects



        $data = [
            //'clinic' => json_encode($request->input('clinic')),
            //'doctor' => json_encode($request->input('doctor')),
            'doctor_id' => is_array($request->input('doctor.id')) ? $request->input('doctor.id')[0] : $request->input('doctor.id'),
            //'patient' => json_encode($request->input('patient')),
            'patient_id' => is_array($request->input('patient.id')) ? $request->input('patient.id')[0] : $request->input('patient.id'),
            'user_id' => is_array($request->input('user_id')) ? $request->input('user_id')[0] : $request->input('user_id'),
            'clinic_id' => is_array($request->input('clinic.id')) ? $request->input('clinic.id')[0] : $request->input('clinic.id'),
            'quantity' => $request->input('quantity', 1),
            'appointment_status_id' => $request->input('appointment_status_id', 1),
            'payment_id' => $request->input('payment_id'),
            'taxes' => json_encode($request->input('taxes')), // Encodé en JSON pour éviter les erreurs
            'appointment_at' => $request->input('appointment_at'),
            'start_at' => Carbon::parse($request->input('start_at'))->setTimezone(config('app.timezone')),
            'ends_at' => Carbon::parse($request->input('ends_at'))->setTimezone(config('app.timezone')),
            'hint' => $request->input('hint'),
            'online' => 'mobile',
            'cancel' => $request->input('cancel', false),
            'motif_id' => is_array($request->input('motif_id.id')) ? $request->input('motif_id.id')[0] : $request->input('motif_id.id'),
            'type' => $request->input('type','aucun'),
        ];



        $coupon = $request->input('coupon'); 
        $address = $request->input('address');
        if (!empty($coupon)) { // Vérifie si la valeur n'est pas vide
            $data['coupon'] = json_encode($coupon); // Ajoute au tableau si elle est définie
        }

        if(!empty($address) || $address != null){
            Log::info("AddressIF",["address" => $address]);
            $data['address'] = json_encode($address); // Ajoute au tableau si elle est définie
        }
        

=======
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

>>>>>>> merging_dev_agendabranch
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

<<<<<<< HEAD

        //If the appointment is remote, create a room for it
        if ($data['online'] == true) {
            Log::info("Create new room for online appointment",["online" => $data['online']]);

            try {
                $startAt = Carbon::parse($request->input('start_at'))->setTimezone(config('app.timezone'));
                // Heure donnée (10:11:00)
                $date = $startAt->toDateString();
                $time = $startAt->format('H-i-s');

                
                $patient_first_name = $request->input('patient.first_name');
                $patient_last_name = $request->input('patient.last_name');
                $patient_phone = $request->input('patient.phone_number');
                $roomName  = "{$patient_first_name}_{$patient_last_name}_{$patient_phone}_{$date}_{$time}";

                $roomData = [
                    'room_name' => $roomName,
                    'meet_link' => 'https://meet.wic-doctor.com/'.$roomName,
                    'owner_id' => is_array($request->input('doctor.id')) ? $request->input('doctor.id')[0] : $request->input('doctor.id'),
                    'appointment_id' => $appointmentId,
                    'patient_id' => is_array($request->input('patient.id')) ? $request->input('patient.id')[0] : $request->input('patient.id'),
                    'date' => $date,
                    'time' => $time,
                    'status' => RoomStatus::PENDING,
                ];

                $this->roomRepository->create($roomData);
            }catch(Exception $e){
                return response()->json("Room not created {$e}", 400);
            }

        }

=======
>>>>>>> merging_dev_agendabranch
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

<<<<<<< HEAD
        Log::info("Appointment eli yarja3 lil mobile ba3ed mé yetssajal", ['appointment_id' => $appointmentId]);

=======
>>>>>>> merging_dev_agendabranch
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
<<<<<<< HEAD
        Log::info("Update Appointment Request:", [$request->all()]);
        $appointment = Appointment::find($id);
        $new_status_id = $request->input('appointment_status_id');
        $appointment->cancel_reason = $request->input('cancel_reason');
        $doctor = $this->doctorRepository->findWithoutFail($appointment->doctor_id);
        $appointment->doctor=$doctor;
        $appointment->appointment_status_id=$new_status_id;
        $user = $this->userRepository->findWithoutFail($appointment->user_id);
        $deviceToken = $user->device_token;
        $patient = $this->patientRepository->findWithoutFail($appointment->patient_id);
        $appointment->patient=$patient;
        $appointment->save();
        event(new AppointmentStatusChangedEvent($appointment,$new_status_id,$deviceToken));
        $body= "Votre rendez-vous avec le Dr. {$appointment->doctor->name} a été mis à jour. Consultez les nouvelles informations dans votre espace personnel.";
        $data = $data = ['appointment_id' => $appointment->id];
        /*\App\Models\Notification::create([
            'notifiable_id' => $user->id,  // Utilise l'ID de l'utilisateur
            'notifiable_type' => get_class($user), // Utilise le nom de la classe de l'utilisateur
            'data' => $data, // Utilise json_encode pour formater les données
            'type' => 'App\Notifications\StatusChangedAppointment',
            'read_at' => null,
            'read' => false,
            'body' => $body,
            'created_at' => now(),
            'updated_at' => now()
        ]);*/
        //Notification::send([$user], new StatusChangedAppointment($appointment));
        return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
    }


    //Ajouter une méthode pour update seulement date et heure ( ajouté par hamza )
    public function updateDateAndTime(int $id, Request $request): JsonResponse
    {
        Log::info('Update Appointment Request Test Format date:', $request->all());
        $startAt = $request->input('start_at');
        if($startAt != null && !empty($startAt)){
            try {
                $this->appointmentRepository->update(['start_at' => $request->input('start_at')], $id);
                return response()->json(true);
                //return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
            } catch (ValidatorException $e) {
                return response()->json(false);
            }
        }else{
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

=======
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
>>>>>>> merging_dev_agendabranch
    
    
    /**
     * Create message for the API request (example function).
     *
     * @param Appointment $appointment
     * @return FcmMessage
     */
    private function createMessageForAppointment(Appointment $appointment, string $id)
    {
<<<<<<< HEAD
        Log::info("Create message notification firebase");
        // Logic to create the message object based on the appointment data
         $user = User::find($id);
        if (!$user) {
            throw new Exception("User not found for ID: $id");
        }
=======
        // Logic to create the message object based on the appointment data
         $user = User::find($id);
    if (!$user) {
        throw new Exception("User not found for ID: $id");
    }
>>>>>>> merging_dev_agendabranch
        $message = new FcmMessage(); // Example, you may have your own message formatting
        $message->content(['title' => 'NOTIFICATION', 'body' => 'YOUR APPOINTMENT STATUS HAS CHANGED'])->to($user->device_token);
        return $message;
    }
    
    private function getApiUri()
    {
<<<<<<< HEAD
        return 'https://fcm.googleapis.com/v1/projects/wic-doctor-b83e0/messages:send';
    }
    

}
=======
        return 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
    }
    

}
>>>>>>> merging_dev_agendabranch
