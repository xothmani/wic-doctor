<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AvailabilityHour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorSubstitute;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewAppointment;
use Benwilkins\FCM\FcmMessage;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use App\Notifications\StatusChangedAppointment;
use App\Events\AppointmentChangedEvent;
use App\Events\AppointmentStatusChangedEvent;


use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use App\Repositories\ClinicRepository;
use function PHPUnit\Framework\isNull;

class AppointmentEventController extends Controller
{

    /**
     * @var DoctorRepository
     */
    private DoctorRepository $doctorRepository;
    /**
     * @var ClinicRepository
     */
    private ClinicRepository $clinicRepository;

    /**
     * @var PatientRepository
     */
    private PatientRepository $patientRepository;



    public function __construct(
        DoctorRepository $doctorRepository,
        ClinicRepository $clinicRepository,
        PatientRepository $patientRepository,
    ) {

        parent::__construct();
        $this->doctorRepository = $doctorRepository;
        $this->clinicRepository = $clinicRepository;
        $this->patientRepository = $patientRepository;
    }


    public function index(Request $request)
    {
        $availabilityDays = collect();
        $vacations = collect();
        $doctorId = auth()->user()->getDoctorId();

        if (!$doctorId) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $doctor = Doctor::find($doctorId);
        $currentMode = $doctor->availability_mode ?? 'open';

        if ($currentMode === 'open') {
            // Retrieve distinct availability days for the logged-in doctor
            $availabilityDays = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('is_available', 1)
                ->where('mode', 'open')
                ->distinct()
                ->pluck('day'); // Get distinct day names (e.g., Lundi, Mardi)

            //Log::info("Availability days retrieved", ['availability_days' => $availabilityDays->toArray()]);

            // Retrieve vacation data for the doctor
            $vacations = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->select('start_date', 'end_date')
                ->get();
            $urgencies = DB::table('doctor_urgency')
                ->where('doctor_id', $doctorId)
                ->select('jour', 'heurDebut', 'heurFin', 'reason')
                ->get()
                ->map(function ($item) {
                    return (array) $item; // convert stdClass to array
                });
            //Log::info("Vacations retrieved", ['vacations' => $vacations]);

            if ($request->ajax()) {
                $start = $request->start ?? '2024-01-01 00:00:00';
                $end = $request->end ?? '2030-12-31 23:59:59';

                // Retrieve appointments for the logged-in doctor with related data
                $data = Appointment::with(['user', 'appointmentStatus', 'pattern'])
                    ->where('appointments.start_at', '>=', $start)
                    ->where('appointments.ends_at', '<=', $end)
                    ->where('appointments.doctor_id', $doctorId)
                    ->select(
                        'appointments.id',
                        'appointments.online',
                        'appointments.type',
                        'appointments.user_id',
                        'appointments.patient_id',
                        'appointments.hint',
                        DB::raw("DATE_FORMAT(CONVERT_TZ(appointments.start_at, '+00:00', '+01:00'), '%Y-%m-%dT%H:%i:%s') as start_at"),
                        DB::raw("DATE_FORMAT(CONVERT_TZ(appointments.ends_at, '+00:00', '+01:00'), '%Y-%m-%dT%H:%i:%s') as ends_at"),
                        'user.name as user_name',
                        'user.phone_number as user_phone_number',
                        'appointment_status.status as status',
                        'patient.first_name as patient_first_name',
                        'patient.last_name as patient_last_name',
                        'patient.email as patient_email',
                        'patient.phone_number as patient_phone_number',
                        'patient.mobile_number as patient_mobile_number',
                        'pattern.nom as motif_name'
                    )
                    ->join('users as user', 'appointments.user_id', '=', 'user.id')
                    ->join('appointment_statuses as appointment_status', 'appointments.appointment_status_id', '=', 'appointment_status.id')
                    ->join('patients as patient', 'appointments.patient_id', '=', 'patient.id')
                    ->join('pattern as pattern', 'appointments.motif_id', '=', 'pattern.id')
                    ->get();

                //Log::info("Appointments retrieved for Doctor ID {$doctorId}", ['appointments_count' => $data->count()]);

                return response()->json($data->map(function ($appointment) {
                    $decodedFirstName = json_decode($appointment->patient_first_name, true);
                    $decodedLastName = json_decode($appointment->patient_last_name, true);
                    $decodedMotifName = json_decode($appointment->motif_name, true);

                    return [
                        'id' => $appointment->id,
                        'start_at' => $appointment->start_at,
                        'ends_at' => $appointment->ends_at,
                        'title' => $appointment->user_name,
                        'status' => $appointment->status,
                        'patient_id' => $appointment->patient_id,
                        'patient_email' => $appointment->patient_email,
                        'patient_phone_number' => $appointment->patient_phone_number,
                        'patient_name' => ($decodedFirstName['fr'] ?? $decodedFirstName) . ' ' . ($decodedLastName['fr'] ?? $decodedLastName),
                        'patient_first_name' => ($decodedFirstName['fr'] ?? $decodedFirstName),
                        'patient_last_name' => ($decodedLastName['fr'] ?? $decodedLastName),
                        'motif_name' => $decodedMotifName['fr'] ?? $decodedMotifName,
                        'online' => $appointment->online,
                        'type' => $appointment->type,
                        'note' => $appointment->hint,
                    ];
                }));
            }
            $patterns = DB::table('pattern')
                ->select('id', 'nom')
                ->where('doctor_id', $doctorId)
                ->get()
                ->mapWithKeys(function ($pattern) {
                    $name = json_decode($pattern->nom, true);
                    return [$pattern->id => $name[app()->getLocale()] ?? $name['fr']];
                })
                ->toArray();
            $patternsByType = DB::table('pattern')
                ->select('id', 'nom', 'type')
                ->where('doctor_id', $doctorId)
                ->get()
                ->groupBy('type')
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($pattern) {
                        $name = json_decode($pattern->nom, true);
                        return [$pattern->id => $name[app()->getLocale()] ?? $name['fr']];
                    });
                });

            // Retrieve patients related to the doctor
            $patients = Patient::whereHas('doctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })->select('id', 'first_name', 'last_name', 'phone_number')->get();

            //Log::info("Patients retrieved", ['patients_count' => $patients->count()]);
            Log::info('Urgencies retrieved for doctor:', ['doctor_id' => $doctorId, 'urgencies' => $urgencies->toArray()]);

            // Pass availabilityDays and vacations to the view
            return view('appointment_events.appointmentEventOpenMode', compact('patients', 'availabilityDays', 'patterns', 'vacations', 'patternsByType', 'urgencies'));


        } else {
            // Precise mode - keeping existing functionality
            $availability = AvailabilityHour::where('doctor_id', $doctorId)
                ->where('mode', 'precise')
                ->with('pattern')
                ->get()
                ->groupBy('day');

            $availabilityDays = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('is_available', 1)
                ->where('mode', 'precise')
                ->distinct()
                ->pluck('day');

            $vacations = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->select('start_date', 'end_date')
                ->get();
            $urgencies = DB::table('doctor_urgency')
                ->where('doctor_id', $doctorId)
                ->select('jour', 'heurDebut', 'heurFin', 'reason')
                ->get()
                ->map(function ($item) {
                    return (array) $item; // convert stdClass to array
                });
            $patterns = DB::table('pattern')
                ->select('id', 'nom')
                ->where('doctor_id', $doctorId)
                ->get()
                ->mapWithKeys(function ($pattern) {
                    $name = json_decode($pattern->nom, true);
                    return [$pattern->id => $name[app()->getLocale()] ?? $name['fr']];
                })
                ->toArray();

            $patients = Patient::whereHas('doctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })->select('id', 'first_name', 'last_name', 'phone_number')->get();

            if ($request->ajax()) {
                $start = $request->start ?? '2024-01-01 00:00:00';
                $end = $request->end ?? '2030-12-31 23:59:59';

                // Retrieve appointments for the logged-in doctor with related data
                $data = Appointment::with(['user', 'appointmentStatus', 'pattern'])
                    ->where('appointments.start_at', '>=', $start)
                    ->where('appointments.ends_at', '<=', $end)
                    ->where('appointments.doctor_id', $doctorId)
                    ->select(
                        'appointments.id',
                        'appointments.online',
                        'appointments.user_id',
                        'appointments.patient_id',
                        'appointments.hint',
                        'appointments.cancel_reason',
                        'appointments.type',
                        DB::raw("DATE_FORMAT(CONVERT_TZ(appointments.start_at, '+00:00', '+01:00'), '%Y-%m-%dT%H:%i:%s') as start_at"),
                        DB::raw("DATE_FORMAT(CONVERT_TZ(appointments.ends_at, '+00:00', '+01:00'), '%Y-%m-%dT%H:%i:%s') as ends_at"),
                        'user.name as user_name',
                        'user.phone_number as user_phone_number',
                        'appointment_status.status as status',
                        'patient.first_name as patient_first_name',
                        'patient.last_name as patient_last_name',
                        'patient.email as patient_email',
                        'patient.phone_number as patient_phone_number',
                        'patient.mobile_number as patient_mobile_number',
                        'pattern.nom as motif_name',
                        'pattern.color as motif_color'
                    )
                    ->join('users as user', 'appointments.user_id', '=', 'user.id')
                    ->join('appointment_statuses as appointment_status', 'appointments.appointment_status_id', '=', 'appointment_status.id')
                    ->join('patients as patient', 'appointments.patient_id', '=', 'patient.id')
                    ->join('pattern as pattern', 'appointments.motif_id', '=', 'pattern.id')
                    ->get();

                return response()->json($data->map(function ($appointment) {
                    $decodedFirstName = json_decode($appointment->patient_first_name, true);
                    $decodedLastName = json_decode($appointment->patient_last_name, true);
                    $decodedMotifName = json_decode($appointment->motif_name, true);
                    $color = $appointment->motif_color;
                    return [
                        'id' => $appointment->id,
                        'start_at' => $appointment->start_at,
                        'ends_at' => $appointment->ends_at,
                        'title' => $appointment->user_name,
                        'status' => $appointment->status,
                        'patient_id' => $appointment->patient_id,
                        'patient_email' => $appointment->patient_email,
                        'patient_phone_number' => $appointment->patient_phone_number,
                        'patient_name' => ($decodedFirstName['fr'] ?? $decodedFirstName) . ' ' . ($decodedLastName['fr'] ?? $decodedLastName),
                        'patient_first_name' => ($decodedFirstName['fr'] ?? $decodedFirstName),
                        'patient_last_name' => ($decodedLastName['fr'] ?? $decodedLastName),
                        'motif_name' => $decodedMotifName['fr'] ?? $decodedMotifName,
                        'online' => $appointment->online,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'note' => $appointment->hint,
                        'cancel_reason' => $appointment->cancel_reason,
                        'type' => $appointment->type,
                    ];

                }));
            }

            return view('appointment_events.appointmentEvent', compact(
                'availability',
                'patients',
                'availabilityDays',
                'patterns',
                'vacations',
                'urgencies'
            ));
        }
    }


    public function saveAppointment(Request $request)
    {
        \Log::info('Appointment Data Received:', $request->all());
        try {
            $doctorId = auth()->user()->getDoctorId();

            if (!$doctorId) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }

            Log::info('Appointment Data Received:', $request->all());

            // Validate the incoming request data
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'patern_id' => 'required',
                'appointment_type' => 'required', // Changed from strings to IDs
                'notes' => 'nullable|string|max:1000', // Add validation for notes
            ]);
            Log::info('validate', $validated);
            $patient = Patient::findOrFail($validated['patient_id']);
            $patientUserId = $patient->user_id; // or null if your patients table doesn't store user_id


            // 5) Create start_at from date + time
            $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time'], 'Africa/Tunis');
            $dayName = $startAt->format('l'); // e.g. "Wednesday"
            \Log::info('Day Name:', ['day' => $dayName]);
            $type = $validated['appointment_type'];
            $availability = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('day', $dayName)
                ->where('type', $type)
                ->where('mode', 'open')
                ->where('is_available', 1)
                ->whereTime('start_at', '<=', $startAt->format('H:i'))
                ->whereTime('end_at', '>', $startAt->format('H:i'))
                ->first();

            $sessionDuration = 15;
            $motifId = null;
            if ($availability) {
                $sessionDuration = $availability->session_duration;
            }
            \Log::info('Session Duration:', ['duration' => $sessionDuration]);
            $endsAt = (clone $startAt)->addMinutes($sessionDuration);

            // 8) appointment_at = just the date portion
            $appointmentAt = $startAt->copy()->startOfDay();
            // Create the appointment
            $appointment = Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $validated['patient_id'],
                'user_id' => $patientUserId,
                'motif_id' => $validated['patern_id'] ?? null,
                'online' => $validated['appointment_type'],
                'appointment_status_id' => 1,
                'appointment_at' => $startAt,
                'start_at' => $startAt,
                'ends_at' => $endsAt,
                'hint' => $validated['notes'] ?? null,
            ]);

            Log::info('Appointment Created Successfully:', ['appointment_id' => $appointment->id]);


            return response()->json([
                'appointment_id' => $appointment->id,
                'status' => 'success',
                'refresh' => true,
                'agenda' => $this->refreshAgenda()->getData() // Get fresh agenda data
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Errors:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected Error:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }
    /**
     * Update or remove availability slots.
     */
    private function deleteAvailability($availability, $startAtFormatted, $endAtFormatted)
    {
        $availabilityStart = Carbon::parse($availability->start_at);
        $availabilityEnd = Carbon::parse($availability->end_at);

        if ($availabilityStart->equalTo($startAtFormatted) && $availabilityEnd->equalTo($endAtFormatted)) {
            // If the entire slot is taken, delete the availability
            DB::table('availability_hours')->where('id', $availability->id)->delete();
        } else if ($availabilityStart->equalTo($startAtFormatted)) {
            // If the start matches, update the start time of the availability
            DB::table('availability_hours')
                ->where('id', $availability->id)
                ->update(['start_at' => $endAtFormatted]);
        } else if ($availabilityEnd->equalTo($endAtFormatted)) {
            // If the end matches, update the end time of the availability
            DB::table('availability_hours')
                ->where('id', $availability->id)
                ->update(['end_at' => $startAtFormatted]);
        } else {
            // If the slot is in the middle, split the availability into two
            DB::table('availability_hours')
                ->where('id', $availability->id)
                ->update(['end_at' => $startAtFormatted]);

            DB::table('availability_hours')->insert([
                'doctor_id' => $availability->doctor_id,
                'start_at' => $endAtFormatted,
                'end_at' => $availability->end_at,
                'patern_id' => $availability->patern_id,
            ]);
        }
    }



    //



    /////////////////////
//les modifications
/////////////////////



    public function updateStatus(Request $request)
    {

        Log::info('Checking if user exists by email or phone number.', request()->all());
        try {
            // Find the appointment by ID
            $appointment = Appointment::findOrFail($request->id);

            /** solve problem cast */
            $doctor = $this->doctorRepository->findWithoutFail($appointment->doctor_id);
            $appointment->doctor = $doctor;
            $clinic = $this->clinicRepository->findWithoutFail($appointment->clinic_id);
            $appointment->clinic = $clinic;
            $patient = $this->patientRepository->findWithoutFail($appointment->patient_id);
            $appointment->patient = $patient;
            /**** */

            // Check if the status is "Canceled" (use the correct status ID for "Canceled")
            if ($request->appointment_status_id == 7) { // Replace 7 with the actual status ID for "Canceled"
                $appointment->cancel_reason = $request->cancel_Reason ?? "Aucune raison fournie";
                // Retrieve the start and end times of the appointment
                $startAt = $appointment->start_at; // Assuming you have these columns
                $endAt = $appointment->ends_at; // Assuming you have these columns
                $doctorId = $appointment->doctor_id;
                $motifId = $appointment->motif_id;  // Adjust based on your schema
                Log::info('ends_at', ['end' => $endAt]);
                //Log::info('DoctorCast - Doctor value:', ['doctor' => $availabilityHours]);
                // Insert or update availability in the `availability_hours` table
                DB::table('availability_hours')->insertOrIgnore([
                    'doctor_id' => $doctorId,
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'patern_id' => $motifId,
                ]);
            }

            $patientUserId = $appointment->user_id;

            $userId = User::find($patientUserId);
            // Update the appointment status
            $appointment->appointment_status_id = $request->appointment_status_id;
            $appointment->save();

            /*** Send notification FCM code hamza ***/
            Log::info("Notification envoyé NotificationController Status changed event");
            //event(new AppointmentChangedEvent($appointment));
            //$appointment->doctor = $this->doctor
            if ($userId->device_token != null) {
                event(new AppointmentStatusChangedEvent($appointment, $input['payment_status_id'], $user->device_token));

            }


            /*if ($appointment->user) {
                $appointment->user->notify(new StatusChangedAppointment($appointment));

            }

            //Log::info('Creating message for appointment status update');
            // Log the message creation
            //Log::info('Creating message for appointment status update');


            if ($appointment->appointment_status_id < 2) {
                $message = $this->createMessageForAppointment($appointment, $appointment->doctor_id);
            } else {
                $message = $this->createMessageForAppointment($appointment, $appointment->user_id);
            }
            // Log the message data
            if ($message !== null) {
                Log::info('Message created:' . $message->formatData());
                $serviceAccountPath = env('OAUTH_SERVICE_ACCOUNT');
                Log::info('Service account path retrieved', ['path' => $serviceAccountPath]);

                $credentials = new ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/firebase.messaging'],
                    $serviceAccountPath
                );
                Log::info('ServiceAccountCredentials created');

                // Fetch the access token
                $accessToken = $credentials->fetchAuthToken()['access_token'];
                Log::info('Access token fetched', ['token' => $accessToken]);

                // Send API request
                try {
                    $response = (new Client())->post($this->getApiUri(), [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                        ],
                        'body' => $message->formatData(),
                    ]);
                    Log::info('API request sent successfully', ['response' => $response->getBody()->getContents()]);
                } catch (\Exception $e) {
                    Log::error('Failed to send API request', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }

                // Handle the response if needed
                if ($response->getStatusCode() !== 200) {
                    // Log the error message
                    Log::error('Failed to send API request:', ['message' => $response->getReasonPhrase()]);
                    return $this->sendError('Failed to send API request');
                }



            }*/

            return response()->json([
                'message' => 'Status updated successfully',
                'refresh' => true,
                'agenda' => $this->refreshAgenda()->getData() // Get fresh agenda data
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
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
        $user = User::findOrFail($id);
        if (!$user->device_token) {
            return null;

        }
        $message = new FcmMessage(); // Example, you may have your own message formatting
        $message->content(['title' => 'Rendez-vous Changé', 'body' => 'Votre statut de rendez-vous a été changé'])->to($user->device_token);
        return $message;
    }

    private function getApiUri()
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        return 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
    }

    //////////////////////
// les getters
/////////////////////

    public function getAvailableTimeSlots(Request $request)
    {
        \Log::info('Request received-1', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');
        $checkAllTypes = $request->boolean('checkAllTypes', false);

        // Get the Monday of the current week based on the selected date
        $dayName = strtolower(Carbon::parse($selectedDate)->locale('en')->dayName);
        if ($checkAllTypes) {
            \Log::info('Checking all appointment types.');

            $allSlots = [];
            $takenSlots = [];

            // Check availability for all types: cabinet, teleconsultation, home_visit
            foreach (['cabinet', 'teleconsultation', 'home_visit'] as $type) {
                \Log::info("Checking availability for type: $type");

                // Fetch availability for this type
                $availability = DB::table('availability_hours')
                    ->where('doctor_id', $doctorId)
                    ->where('day', $dayName)
                    ->where('is_available', 1)
                    ->where('mode', 'open')
                    ->where('type', $type)
                    ->first();

                if ($availability) {
                    \Log::info("Availability found for $type", ['availability' => $availability]);

                    // Generate time slots for this type
                    $startTime = Carbon::parse($availability->start_at);
                    $endTime = Carbon::parse($availability->end_at);
                    $sessionDuration = $availability->session_duration;

                    while ($startTime->lessThan($endTime)) {
                        $allSlots[] = $startTime->format('H:i');
                        $startTime->addMinutes($sessionDuration);
                    }
                }

                // Fetch taken slots for this type
                $taken = Appointment::where('doctor_id', $doctorId)
                    ->whereDate('start_at', $selectedDate)
                    ->where('appointment_status_id', '!=', 7) // Exclude failed appointments
                    ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
                    ->toArray();

                $takenSlots = array_merge($takenSlots, $taken);
            }

            // Remove duplicate slots
            $allSlots = array_unique($allSlots);
            $takenSlots = array_unique($takenSlots);

            return response()->json([
                'all_slots' => array_values($allSlots),
                'taken_slots' => array_values($takenSlots),
                'checkAllTypes' => true,
            ]);
        }
        // Fetch all availability for the entire week
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('mode', 'open')
            ->distinct()
            ->pluck('day');

        \Log::info('Availability fetched', ['availability' => $availability]);

        if ($availability->isEmpty()) {
            \Log::warning('No availability found', ['doctor_id' => $doctorId]);
            return response()->json(['error' => 'No availability found'], 404);
        }

        // Return only the available days
        return response()->json([
            'available_days' => $availability
        ]);
    }

    public function getAvailableTimeSlotsPresice(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        // Get the Monday of the current week based on the selected date
        $weekStart = Carbon::parse($selectedDate)->startOfWeek(); // Get Monday of that week

        // Fetch all availability for the entire week
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('mode', 'precise')
            ->get();

        \Log::info('Availability fetched', ['availability' => $availability]);

        if ($availability->isEmpty()) {
            \Log::warning('No availability found', ['doctor_id' => $doctorId]);
            return response()->json(['error' => 'No availability found'], 404);
        }

        $allSlots = [];

        foreach ($availability as $slot) {
            $startTime = Carbon::parse($slot->start_at);
            $endTime = Carbon::parse($slot->end_at);
            $sessionDuration = $slot->session_duration;

            // Correctly map weekday names to the actual date in the current week
            $dayMapping = [
                'monday' => 0,
                'tuesday' => 1,
                'wednesday' => 2,
                'thursday' => 3,
                'friday' => 4,
                'saturday' => 5,
                'sunday' => 6
            ];

            if (!isset($dayMapping[$slot->day])) {
                \Log::warning("Invalid day name found in DB: " . $slot->day);
                continue;
            }

            // Calculate actual date for the slot's day
            $slotDate = $weekStart->copy()->addDays($dayMapping[$slot->day]);

            while ($startTime->lessThan($endTime)) {
                $allSlots[] = [
                    'time' => $startTime->format('H:i'),
                    'color' => $this->getPatternColor($slot->patern_id),
                    'day' => $slotDate->format('Y-m-d'), // Store exact date
                    'session_duration' => $sessionDuration,
                ];
                $startTime->addMinutes($sessionDuration);
            }
        }

        \Log::info('Generated precise slots:', ['allSlots' => $allSlots]);

        return response()->json([
            'all_slots' => $allSlots,
            'taken_slots' => [] // Keep it for compatibility
        ]);
    }




    public function getAvailableTimeSlotsForOpen(Request $request)
    {
        \Log::info('Request received-2', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');
        $selectedType = $request->input('type', 'cabinet'); // Default to type 1 (cabinet) instead of 'cabinet'

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        // Get the day name for the selected date
        $dayName = strtolower(Carbon::parse($selectedDate)->locale('en')->dayName);
        \Log::info('Day name fetched', ['dayName' => $dayName]);
        \Log::info('Selected type', ['type' => $selectedType]);



        // Fetch availability hours for the selected day, doctor, and type
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1)
            ->where('mode', 'open')
            ->where('type', $selectedType) // Now using numeric type
            ->first();

        \Log::info('Availability fetched', ['availability' => $availability]);

        if (!$availability) {
            \Log::warning('No availability found', ['doctor_id' => $doctorId, 'dayName' => $dayName, 'type' => $selectedType]);
            return response()->json([
                'vacation' => false,
                'all_slots' => [],
                'taken_slots' => [],
                'type' => $selectedType
            ]);
        }

        // Calculate all available time slots
        $startTime = Carbon::parse($availability->start_at);
        $endTime = Carbon::parse($availability->end_at);
        $pauseFrom = Carbon::parse($availability->pause_from);
        $pauseTo = Carbon::parse($availability->pause_to);
        $sessionDuration = $availability->session_duration;

        $allSlots = [];
        while ($startTime->lessThan($endTime)) {
            // Skip pause time
            if ($startTime->greaterThanOrEqualTo($pauseFrom) && $startTime->lessThan($pauseTo)) {
                $startTime->addMinutes($sessionDuration);
                continue;
            }

            $allSlots[] = $startTime->format('H:i');
            $startTime->addMinutes($sessionDuration);
        }

        \Log::info('Calculated all slots', ['allSlots' => $allSlots]);

        // Exclude urgent hours
        $urgentHours = DB::table('doctor_urgency')
            ->where('doctor_id', $doctorId)
            ->whereDate('jour', $selectedDate)
            ->get();

        \Log::info('Urgent hours fetched', ['urgentHours' => $urgentHours]);

        foreach ($urgentHours as $urgent) {
            $urgentStart = Carbon::parse($urgent->heurDebut);
            $urgentEnd = Carbon::parse($urgent->heurFin);

            $allSlots = array_filter($allSlots, function ($slot) use ($pauseFrom, $pauseTo, $urgentStart, $urgentEnd) {
                $slotTime = Carbon::parse($slot);

                // Exclude slots during pause time and urgent hours
                return !(
                    ($slotTime->greaterThanOrEqualTo($pauseFrom) && $slotTime->lessThan($pauseTo)) || // Pause time
                    ($slotTime->greaterThanOrEqualTo($urgentStart) && $slotTime->lessThan($urgentEnd)) // Urgent hours
                );
            });
        }

        \Log::info('Filtered slots after urgency exclusion', ['filteredSlots' => $allSlots]);

        // Get taken slots for the selected date
        $takenSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('start_at', $selectedDate)
            ->where('appointment_status_id', '!=', 7) // Exclude failed appointments
            ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
            ->toArray();

        \Log::info('Taken slots fetched', ['takenSlots' => $takenSlots]);

        // Check for vacations
        $vacations = DB::table('vacance')
            ->where('doctor_id', $doctorId)
            ->whereDate('start_date', '<=', $selectedDate)
            ->whereDate('end_date', '>=', $selectedDate)
            ->exists();

        \Log::info('Vacation status', ['vacations' => $vacations]);

        if ($vacations) {
            return response()->json([
                'vacation' => true, // Doctor is on vacation
                'all_slots' => [],
                'taken_slots' => [],
            ]);
        }

        $response = [
            'vacation' => false, // Doctor is not on vacation
            'all_slots' => array_values($allSlots),
            'taken_slots' => $takenSlots,
            'type' => $selectedType
        ];

        \Log::info('Final response', ['response' => $response]);

        return response()->json($response);
    }

    /**
     * Retrieve the color from the `patterns` table using pattern_id
     */
    private function getPatternColor($patternId)
    {
        return DB::table('pattern')
            ->where('id', $patternId)
            ->value('color') ?? '#C6E7FF'; // Default if color not found
    }


    public function getTeleconsultationTimeSlots(Request $request)
    {
        \Log::info('Request received-2', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }
        $selectedDate = $request->input('date'); // Expected format: YYYY-MM-DD


        // Get the day name for the selected date
        $dayName = Carbon::parse($selectedDate)->locale('fr')->dayName; // Example: "Lundi", "Mardi"

        // Fetch teleconsultation availability hours for the selected day and doctor
        $teleAvailability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1)
            ->first();

        if (!$teleAvailability) {
            // Return a response with an empty slots array
            return response()->json([
                'all_slots' => [],
                'taken_slots' => [],
                //'message' => 'Aucune disponibilité pour la téléconsultation à cette date.',
            ]);
        }
        // Calculate all available time slots for teleconsultation
        $startTime = Carbon::parse($teleAvailability->start_at);
        $endTime = Carbon::parse($teleAvailability->end_at);
        $pauseFrom = $teleAvailability->pause_from ? Carbon::parse($teleAvailability->pause_from) : null;
        $pauseTo = $teleAvailability->pause_to ? Carbon::parse($teleAvailability->pause_to) : null;
        $sessionDuration = $teleAvailability->session_duration;

        $teleSlots = [];
        while ($startTime->lessThan($endTime)) {
            // Skip pause time if defined
            if ($pauseFrom && $pauseTo && $startTime->greaterThanOrEqualTo($pauseFrom) && $startTime->lessThan($pauseTo)) {
                $startTime->addMinutes($sessionDuration);
                continue;
            }

            $teleSlots[] = $startTime->format('H:i');
            $startTime->addMinutes($sessionDuration);
        }

        // Get taken teleconsultation slots for the selected date
        $takenTeleSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('start_at', $selectedDate)
            ->where('online', 'Téléconsultation') // Ensure to filter by teleconsultation type
            ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
            ->toArray();

        return response()->json([
            'all_slots' => $teleSlots,
            'taken_slots' => $takenTeleSlots,
        ]);
    }




    //

    public function getPatients(Request $request)
    {
        if ($request->ajax()) {
            $doctorId = auth()->user()->getDoctorId();

            if (!$doctorId) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }

            $search = $request->input('q');

            // Step 1: Get patient IDs linked to this doctor from the pivot table
            $patientIds = DB::table('doctor_patients')
                ->where('doctor_id', $doctorId)
                ->pluck('patient_id');

            // Step 2: Query patients by those IDs
            $query = Patient::whereIn('id', $patientIds);
            $searchLower = mb_strtolower($search);
            // Step 3: Apply search if needed
            if ($search) {
                $query->where(function ($subQuery) use ($search, $searchLower) {
                    $subQuery->whereRaw("
                        (JSON_VALID(first_name) AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(first_name, '$.fr'))) LIKE ?)
                        OR LOWER(first_name) LIKE ?
                    ", ["%{$searchLower}%", "%{$searchLower}%"])
                        ->orWhereRaw("
                        (JSON_VALID(last_name) AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(last_name, '$.fr'))) LIKE ?)
                        OR LOWER(last_name) LIKE ?
                    ", ["%{$searchLower}%", "%{$searchLower}%"])
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhereRaw("DATE_FORMAT(date_naissance, '%Y-%m-%d') LIKE ?", ["%{$search}%"]);
                });
            }

            $patients = $query->select(
                'id',
                DB::raw("
                    CONCAT(
                        CASE 
                            WHEN JSON_VALID(first_name) THEN JSON_UNQUOTE(JSON_EXTRACT(first_name, '$.fr')) 
                            ELSE first_name 
                        END, ' ',
                        CASE 
                            WHEN JSON_VALID(last_name) THEN JSON_UNQUOTE(JSON_EXTRACT(last_name, '$.fr')) 
                            ELSE last_name 
                        END, ' - ',
                        IFNULL(phone_number, 'N° inconnu'), ' - ',
                        IFNULL(DATE_FORMAT(date_naissance, '%d/%m/%Y'), 'N/S')
                    ) as text
                ")
            )
                ->when($search, fn($q) => $q->limit(20))
                ->get();

            // ✅ Log for debugging
            \Log::info('[getPatients] Found patients:', $patients->toArray());

            return response()->json($patients);
        }
    }






    //

    public function getAppointmentsForDate(Request $request)
    {
        \Log::info('Fetching Appointments for Date:', ['date' => $request->input('date')]);
        $date = $request->input('date');

        // Get all appointments for the selected date
        $appointments = Appointment::whereDate('start_at', $date)
            ->select(DB::raw("DATE_FORMAT(start_at, '%H:%i') as time"))
            ->pluck('time'); // Only get the time part
        Log::info('Fetched Appointments for Date:', ['appointments' => $appointments->toArray()]);

        return response()->json($appointments);
    }
    ///////////////////

    /*public function getVisitsCountByDate(Request $request)
    {
        $userId = Auth::id(); // Get the logged-in user's ID
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        // Exclude canceled appointments (assuming 3 is the status ID for canceled)
        $visitsCount = DB::table('appointments')
            ->select(DB::raw("DATE(start_at) as date"), DB::raw("COUNT(*) as count"))
            ->where('doctor_id', $doctor->id)
            ->where('appointment_status_id', '=', 4) // Exclude canceled appointments
            ->groupBy(DB::raw("DATE(start_at)"))
            ->pluck('count', 'date'); // Format: ['2024-11-29' => 5, '2024-11-30' => 3]

        return response()->json($visitsCount);
    }*/

    public function getPatternForTimeSlot(Request $request)
    {
        \Log::info('Fetching Unavailable Time Slots:', [
            'date' => $request->get('date'),
            'time' => $request->get('time'),
            'type' => $request->get('type')
        ]);
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');
        $selectedTime = $request->input('time');
        $selectedType = $request->input('type');

        if (!$doctorId || !$selectedDate || !$selectedTime || !$selectedType) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $dayName = Carbon::parse($selectedDate)->format('l');
        $now = Carbon::now();

        // Get availability for the selected time slot
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('type', $selectedType)
            ->where('day', $dayName)
            ->whereTime('start_at', '<=', $selectedTime)
            ->whereTime('end_at', '>', $selectedTime)
            ->where('mode', 'precise')
            ->first();

        if (!$availability) {
            return response()->json([
                'pattern_name' => trans('lang.no_pattern_selected'),
                'pattern_color' => '#cccccc',
                'available_slots' => []
            ]);
        }

        // Get pattern details
        $pattern = DB::table('pattern')->where('id', $availability->patern_id)->first();
        $displayMotifName = trans('lang.no_pattern_selected');

        if ($pattern) {
            $lang = app()->getLocale();
            $decodedNom = json_decode($pattern->nom, true);
            $displayMotifName = is_array($decodedNom)
                ? ($decodedNom[$lang] ?? $decodedNom['fr'] ?? $pattern->nom)
                : $pattern->nom;
        }

        // Generate all possible time slots
        $startTime = Carbon::parse($availability->start_at);
        $endTime = Carbon::parse($availability->end_at);
        $sessionDuration = $availability->session_duration;
        $availableSlots = [];
        $pastSlots = []; // Track past slots

        while ($startTime->lessThan($endTime)) {
            $slotTime = $startTime->format('H:i');
            $availableSlots[] = $slotTime;

            // Check if slot is in the past
            $slotDateTime = Carbon::parse($selectedDate . ' ' . $slotTime);
            if ($slotDateTime->isPast()) {
                $pastSlots[] = $slotTime;
            }

            $startTime->addMinutes($sessionDuration);
        }

        // Get taken slots (excluding canceled appointments)
        $takenSlots = DB::table('appointments')
            ->where('doctor_id', $doctorId)
            ->whereDate('start_at', $selectedDate)
            ->where('appointment_status_id', '!=', 7) // Exclude canceled appointments
            ->select(DB::raw("DATE_FORMAT(start_at, '%H:%i') as time"))
            ->pluck('time')
            ->toArray();

        // Combine taken slots with past slots
        $takenSlots = array_unique(array_merge($takenSlots, $pastSlots));

        return response()->json([
            'pattern_id' => $pattern->id,
            'pattern_name' => $displayMotifName,
            'pattern_color' => $pattern ? $pattern->color : '#cccccc',
            'available_slots' => $availableSlots,
            'taken_slots' => $takenSlots
        ]);
    }
    public function getPatternForTimeSlotWithoutType(Request $request)
    {
        \Log::info('Fetching Unavailable Time Slots:', [
            'date' => $request->get('date'),
            'time' => $request->get('time')
        ]);

        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');
        $selectedTime = $request->input('time');

        if (!$doctorId || !$selectedDate || !$selectedTime) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $dayName = Carbon::parse($selectedDate)->format('l'); // e.g., "Monday"
        \Log::info("📆 Selected Date: $selectedDate → 🗓 Day: $dayName");
        \Log::info("⏰ Selected Time: $selectedTime");

        // ✅ Fetch all patterns (motifs) for the doctor
        $patterns = DB::table('pattern')
            ->where('doctor_id', $doctorId)
            ->select('id', 'nom')
            ->get();
        $decodedPatterns = $patterns->map(function ($pattern) {
            $name = json_decode($pattern->nom, true);
            return [
                'id' => $pattern->id,
                'nom' => $name['fr'] ?? $name, // Default to French
            ];
        });

        if ($patterns->isEmpty()) {
            return response()->json([
                'patterns' => [],
                'unavailable_slots' => [],
                'message' => "Aucun motif trouvé pour ce médecin.",
            ]);
        }

        // ✅ Get doctor's availability periods for the selected day
        $availabilities = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('day', $dayName)
            ->where('mode', 'precise')
            ->select('start_at', 'end_at')
            ->get();


        // ✅ Define full day range from 07:00 to 23:59
        $fullDayStart = Carbon::parse($selectedDate . ' 07:00');
        $fullDayEnd = Carbon::parse($selectedDate . ' 23:59');

        // ✅ Generate all possible time slots in a full day
        $allTimeSlots = [];
        $sessionDuration = $availabilities->first()->session_duration ?? 15;
        $currentTime = clone $fullDayStart;

        while ($currentTime->lessThanOrEqualTo($fullDayEnd)) {
            $allTimeSlots[] = $currentTime->format('H:i');
            $currentTime->addMinutes($sessionDuration);
        }

        // ✅ Collect all available time slots
        $availableSlots = [];
        foreach ($availabilities as $availability) {
            $start = Carbon::parse($availability->start_at);
            $end = Carbon::parse($availability->end_at);
            $slotTime = clone $start;
            while ($slotTime->lessThan($end)) {
                $availableSlots[] = $slotTime->format('H:i');
                $slotTime->addMinutes($sessionDuration);
            }
        }

        // ✅ Find unavailable slots (full day minus available slots)
        $unavailableSlots = array_diff($allTimeSlots, $availableSlots);

        // ✅ Check if the selected time is inside availability
        if (!in_array($selectedTime, $availableSlots)) {
            return response()->json([
                'patterns' => $decodedPatterns,
                'session_duration' => $sessionDuration,
                'available_slots' => array_values($availableSlots), // Always include this
                'unavailable_slots' => array_values($unavailableSlots),
                'message' => "Le médecin n'est pas disponible à l'heure sélectionnée.",
            ]);
        }

        return response()->json([
            'patterns' => $decodedPatterns,
            'session_duration' => $sessionDuration,
            'available_slots' => array_values($availableSlots),
            'message' => "Le médecin est disponible à cette heure.",
        ]);
    }
    public function getUnavailableTimeSlots(Request $request)
    {
        \Log::info('Fetching Unavailable Time Slots:', ['date' => $request->get('date')]);

        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $dayName = Carbon::parse($selectedDate)->format('l'); // e.g., "Monday"
        \Log::info("📆 Selected Date: $selectedDate → 🗓 Day: $dayName");

        // ✅ Fetch all patterns (motifs) for the doctor
        $patterns = DB::table('pattern')
            ->where('doctor_id', $doctorId)
            ->select('id', 'nom')
            ->get();
        $decodedPatterns = $patterns->map(function ($pattern) {
            $name = json_decode($pattern->nom, true);
            return [
                'id' => $pattern->id,
                'nom' => $name['fr'] ?? $name, // Default to French
            ];
        });

        if ($patterns->isEmpty()) {
            return response()->json([
                'patterns' => [],
                'unavailable_slots' => [],
                'message' => "Aucun motif trouvé pour ce médecin.",
            ]);
        }

        // ✅ Get doctor's availability periods
        $availabilities = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('day', $dayName)
            ->where('mode', 'precise')
            ->select('start_at', 'end_at')
            ->get();

        // ✅ Define full day range from 07:00 to 23:59
        $fullDayStart = Carbon::parse($selectedDate . ' 07:00');
        $fullDayEnd = Carbon::parse($selectedDate . ' 23:59');

        $allTimeSlots = [];
        $sessionDuration = 15; // 15 minutes session

        // ✅ Generate all possible time slots in a full day
        $currentTime = clone $fullDayStart;
        while ($currentTime->lessThanOrEqualTo($fullDayEnd)) {
            $allTimeSlots[] = $currentTime->format('H:i');
            $currentTime->addMinutes($sessionDuration);
        }

        // ✅ Collect all available time slots
        $availableSlots = [];
        foreach ($availabilities as $availability) {
            $start = Carbon::parse($availability->start_at);
            $end = Carbon::parse($availability->end_at);
            $slotTime = clone $start;
            while ($slotTime->lessThan($end)) {
                $availableSlots[] = $slotTime->format('H:i');
                $slotTime->addMinutes($sessionDuration);
            }
        }

        // ✅ Find unavailable slots (full day minus available slots)
        $unavailableSlots = array_diff($allTimeSlots, $availableSlots);

        return response()->json([
            'patterns' => $decodedPatterns,
            'unavailable_slots' => array_values($unavailableSlots),
            'message' => "Voici les créneaux indisponibles du médecin.",
        ]);
    }


    public function store(Request $request)
    {
        \Log::info('Storing an appointment. Request data:', $request->all());

        // 1) Identify the doctor
        $doctorId = auth()->user()->getDoctorId();
        if (!$doctorId) {
            return back()->withErrors(['error' => 'No associated doctor found.']);
        }
        $doctor = Doctor::find($doctorId);
        $doctorTimezone = $doctor->timezone;
        \Log::info('Doctor timezone:', ['timezone' => $doctorTimezone]);
        // 2) Validate incoming data
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_type' => 'required', // Changed from strings to IDs
            'appointment_date' => 'required|date',
            'appointment_time' => 'required', // e.g. "08:00"
            'notes' => 'nullable|string',
            'motif_id' => 'nullable|integer|exists:pattern,id',
        ]);

        // 3) Fetch the patient to get its user_id
        $patient = Patient::findOrFail($validated['patient_id']);
        $patientUserId = $patient->user_id; // or null if your patients table doesn't store user_id


        // 5) Create start_at from date + time
        $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time'], 'Africa/Tunis');
        $dayName = $startAt->format('l'); // e.g. "Wednesday"
        $type = $validated['appointment_type'];

        // Look up the availability row for that day/time/type
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('type', $type)
            ->where('is_available', 1)
            ->whereTime('start_at', '<=', $startAt->format('H:i'))
            ->whereTime('end_at', '>', $startAt->format('H:i'))
            ->first();
        // Default to 30 min if not found
        $sessionDuration = 0;
        $motifId = null;
        if ($availability) {
            $sessionDuration = $availability->session_duration;
            $motifId = $availability->patern_id; // or 'pattern_id'
        }

        //\Log::info("Session duration (in minutes): " . $sessionDuration);
        //\Log::info("Motif ID from availability:", [$motifId]);

        $endsAt = (clone $startAt)->addMinutes($sessionDuration);

        // 8) appointment_at = just the date portion
        $appointmentAt = $startAt->copy()->startOfDay();

        // 9) Create the appointment
        $appointment = Appointment::create([
            'doctor_id' => $doctorId,
            'patient_id' => $validated['patient_id'],
            'user_id' => $patientUserId,
            'motif_id' => $validated['motif_id'] ?? null,
            'online' => $validated['appointment_type'],
            'appointment_status_id' => 1,
            'appointment_at' => $startAt,
            'start_at' => $startAt,
            'ends_at' => $endsAt,
            'hint' => $validated['notes'] ?? null,
        ]);

        // \Log::info("Appointment created:", ['id' => $appointment->id]);

        // 10) Redirect back
        return redirect()->back()->with('success', 'Appointment created successfully');
    }

    public function storeForced(Request $request)
    {
        \Log::info('Storing forced appointment. Request data:', $request->all());

        $doctorId = auth()->user()->getDoctorId();
        if (!$doctorId) {
            return back()->withErrors(['error' => 'Médecin non trouvé.']);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_type' => 'required', // Changed from strings to IDs
            'appointment_date' => 'required|date',
            'appointment_start_time' => 'required',
            'appointment_end_time' => 'required|after:appointment_start_time',
            'notes' => 'nullable|string',
            'motif_id' => 'required|exists:pattern,id',
        ]);

        $startAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time'], 'Africa/Tunis');
        $endsAt = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_end_time'], 'Africa/Tunis');

        // Check for overlapping appointments
        $overlappingAppointment = Appointment::where('doctor_id', $doctorId)
            ->where(function ($query) use ($startAt, $endsAt) {
                $query->where(function ($q) use ($startAt, $endsAt) {
                    $q->where('start_at', '<', $endsAt)
                        ->where('ends_at', '>', $startAt);
                });
            })
            ->where('appointment_status_id', '!=', 7)
            ->first();

        if ($overlappingAppointment) {
            return response()->json([
                'errors' => [
                    'overlap' => 'Un rendez-vous existe déjà sur ce créneau horaire.'
                ]
            ], 422);
        }

        try {
            $patient = Patient::findOrFail($validated['patient_id']);
            $patientUserId = $patient->user_id;
            $appointmentAt = $startAt->copy()->startOfDay();

            Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $validated['patient_id'],
                'user_id' => $patientUserId,
                'motif_id' => $validated['motif_id'],
                'online' => $validated['appointment_type'],
                'appointment_status_id' => 1,
                'appointment_at' => $appointmentAt,
                'start_at' => $startAt,
                'ends_at' => $endsAt,
                'hint' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rendez-vous forcé créé avec succès.',
                'refresh' => true,
                'agenda' => $this->refreshAgenda()->getData() // Get fresh agenda data
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating forced appointment: ' . $e->getMessage());
            return response()->json([
                'errors' => [
                    'general' => 'Une erreur est survenue lors de la création du rendez-vous.'
                ]
            ], 500);
        }
    }

    public function getAvailableDays()
    {
        $doctorId = auth()->user()->getDoctorId();

        // Get all available days with their types from availability_hours
        $availableDays = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('mode', 'open')
            ->select('day', 'type')
            ->distinct()
            ->get()
            ->groupBy('day')
            ->map(function ($dayTypes) {
                return $dayTypes->pluck('type')->toArray();
            });

        // Get vacations
        $vacations = DB::table('vacance')
            ->where('doctor_id', $doctorId)
            ->select('start_date', 'end_date')
            ->get();

        return response()->json([
            'availableDays' => $availableDays,
            'vacations' => $vacations
        ]);
    }

    private function refreshAgenda()
    {
        return $this->index(request());
    }


    public function getSubstitutes($doctorId)
    {
        \Log::info('Fetching substitutes for doctor:', ['doctor_id' => $doctorId]);

        $substitutes = DoctorSubstitute::where('doctor_id', $doctorId)
            ->with('doctor')
            ->get()
            ->map(function ($substitute) {
                return [
                    'id' => $substitute->id,
                    'name' => $substitute->name,
                    'start_date' => Carbon::parse($substitute->start_date)->format('Y-m-d H:i'), // ✅ FIXED
                    'end_date' => Carbon::parse($substitute->end_date)->format('Y-m-d H:i'), // ✅ FIXED
                    'notes' => $substitute->notes
                ];
            });

        return response()->json($substitutes);
    }
    public function getAppointmentStats($doctorId, $selectedDate = null)
    {
        \Log::info('Fetching Appointment Stats:', [
            'doctor_id' => $doctorId,
            'selected_date' => $selectedDate,
        ]);

        // Validate doctor ID
        if (!$doctorId) {
            return response()->json(['error' => 'Missing doctor ID'], 400);
        }

        // Parse selected date
        $selectedDate = $selectedDate ? Carbon::parse($selectedDate) : Carbon::now();

        $dayName = $selectedDate->format('l');

        // Get the doctor's availability mode
        $doctor = DB::table('doctors')->where('id', $doctorId)->first();
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $availabilityMode = $doctor->availability_mode;

        if ($availabilityMode === 'precise') {
            // ✅ Precise mode logic
            $availabilities = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('is_available', 1)
                ->where('day', $dayName)
                ->where('mode', 'precise')
                ->get();

            $totalAvailableSlots = 0;
            $takenSlots = [];

            foreach ($availabilities as $availability) {
                $startTime = Carbon::parse($availability->start_at);
                $endTime = Carbon::parse($availability->end_at);
                $sessionDuration = (int) $availability->session_duration;

                while ($startTime->lessThan($endTime)) {
                    $slotTime = $startTime->format('H:i');

                    $totalAvailableSlots++;

                    $slotTaken = DB::table('appointments')
                        ->where('doctor_id', $doctorId)
                        ->whereDate('start_at', $selectedDate->format('Y-m-d'))
                        ->whereTime('start_at', $slotTime)
                        ->whereNotIn('appointment_status_id', [6, 7])
                        ->exists();

                    if ($slotTaken) {
                        $takenSlots[] = $slotTime;
                    }

                    $startTime->addMinutes($sessionDuration);
                }
            }

            return response()->json([
                'availability_mode' => $availabilityMode,
                'total_appointments' => $totalAvailableSlots ?? 0,
                'appointments_taken' => count($takenSlots ?? []),
                'available_slots' => ($totalAvailableSlots ?? 0) - count($takenSlots ?? [])
            ]);
        } elseif ($availabilityMode === 'open') {
            // ✅ Open mode: Just count appointments on that date (no time/session logic)
            $availabilities = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('is_available', 1)
                ->where('day', $dayName)
                ->where('mode', 'open')
                ->get();
            $totalAvailableSlots = 0;
            $takenSlots = [];

            foreach ($availabilities as $availability) {
                $startTime = Carbon::parse($availability->start_at);
                $endTime = Carbon::parse($availability->end_at);
                $sessionDuration = (int) $availability->session_duration;

                while ($startTime->lessThan($endTime)) {
                    $slotTime = $startTime->format('H:i');

                    $totalAvailableSlots++;

                    $slotTaken = DB::table('appointments')
                        ->where('doctor_id', $doctorId)
                        ->whereDate('start_at', $selectedDate->format('Y-m-d'))
                        ->whereTime('start_at', $slotTime)
                        ->whereNotIn('appointment_status_id', [6, 7])
                        ->exists();

                    if ($slotTaken) {
                        $takenSlots[] = $slotTime;
                    }

                    $startTime->addMinutes($sessionDuration);
                }
            }
            return response()->json([
                'availability_mode' => $availabilityMode,
                'total_appointments' => $totalAvailableSlots ?? 0,
                'appointments_taken' => count($takenSlots ?? []),
                'available_slots' => ($totalAvailableSlots ?? 0) - count($takenSlots ?? [])
            ]);
        } else {
            return response()->json([
                'error' => 'Unsupported availability mode: ' . $availabilityMode
            ]);
        }

    }



}
