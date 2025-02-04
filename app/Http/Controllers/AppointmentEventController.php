<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AvailabilityHour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewAppointment;
use Benwilkins\FCM\FcmMessage;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use App\Notifications\StatusChangedAppointment;
class AppointmentEventController extends Controller
{

    public function index(Request $request)
    {
        // Initialize availabilityDays as an empty collection
        $availabilityDays = collect();
        $vacations = collect();

        $doctorId = auth()->user()->getDoctorId(); // Retrieve doctor ID using user relationship

        if (!$doctorId) {
            //Log::warning("Doctor ID not found for logged-in user.");
            return response()->json(['error' => 'Doctor not found for the logged-in user'], 404);
        }

        // Retrieve distinct availability days for the logged-in doctor
        $availabilityDays = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1) // Filter only available days
            ->distinct()
            ->pluck('day'); // Get distinct day names (e.g., Lundi, Mardi)

        //Log::info("Availability days retrieved", ['availability_days' => $availabilityDays->toArray()]);

        // Retrieve vacation data for the doctor
        $vacations = DB::table('vacance')
            ->where('doctor_id', $doctorId)
            ->select('dateDebut', 'dateFin')
            ->get();

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
                    'appointments.user_id',
                    'appointments.patient_id',
                    DB::raw("DATE_FORMAT(appointments.start_at, '%Y-%m-%dT%H:%i:%s') as start_at"),
                    DB::raw("DATE_FORMAT(appointments.ends_at, '%Y-%m-%dT%H:%i:%s') as ends_at"),
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

        // Retrieve patients related to the doctor
        $patients = Patient::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })->select('id', 'first_name', 'last_name', 'phone_number')->get();

        //Log::info("Patients retrieved", ['patients_count' => $patients->count()]);

        // Pass availabilityDays and vacations to the view
        return view('appointment_events.appointmentEvent', compact('patients', 'availabilityDays', 'patterns', 'vacations'));
    }





    public function saveAppointment(Request $request)
    {
        try {
            $doctorId = auth()->user()->getDoctorId();

            if (!$doctorId) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }

            Log::info('Appointment Data Received:', $request->all());

            // Validate the incoming request data
            $validatedData = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'appointment_at' => 'required|date',
                'appointment_time' => 'required',
                'patern_id' => 'required',
                'day_of_week' => 'required',
                'appointment_type' => 'required|in:cabinet,Téléconsultation',
            ]);
            Log::info('validate', $validatedData);
            $availability_hours = AvailabilityHour::where('doctor_id', $doctorId)
                ->where('day', $validatedData['day_of_week']) // Replace with your condition
                ->first();
            $sessionDuration = $availability_hours ? $availability_hours->session_duration : null;
            Log::info("Session Duration: {$sessionDuration}");

            // Check if the appointment time contains a range
            if (str_contains($validatedData['appointment_time'], ' - ')) {
                // Split the time range into start and end times
                [$startTime, $endTime] = explode(' - ', $validatedData['appointment_time']);
            } else {
                // If only a start time is provided, calculate the end time based on session duration
                $startTime = $validatedData['appointment_time'];
                $endTime = Carbon::parse($startTime)->addMinutes($sessionDuration)->format('H:i');
            }
            Log::info("Parsed Times: Start Time - {$startTime}, End Time - {$endTime}");
            $motifId = $validatedData['patern_id'];
            $startAt = "{$validatedData['appointment_at']} $startTime:00";
            $endAt = "{$validatedData['appointment_at']} $endTime:00";
            Log::info("Start At: {$startAt}, End At: {$endAt}");
            // Round times to match the availability format (ignoring seconds)
            $startAtFormatted = Carbon::parse($startAt)->format('Y-m-d H:i:00');
            $endAtFormatted = Carbon::parse($endAt)->format('Y-m-d H:i:00');
            Log::info("Formatted Times: Start At - {$startAtFormatted}, End At - {$endAtFormatted}");
            // Match availability_hours for the doctor and the selected time
            $availability = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('start_at', '<=', value: $startAtFormatted)
                ->where('end_at', '>=', $endAtFormatted)
                ->first(['id', 'start_at', 'end_at', 'patern_id']);
            Log::info('Matched Availability:', $availability ? (array) $availability : ['message' => 'No matching availability found']);

            Log::info("test1");

            //$motifId = $availability->patern_id;

            //Log::info("Availability Matched: {$availability->id}, Pattern ID: {$motifId}");

            $user_id = Patient::where('id', $validatedData['patient_id'])->value('user_id');
            Log::info("test2");
            Log::info("Patient's User ID: {$user_id}");
            $appointmentType = $validatedData['appointment_type']; // Either 'cabinet' or 'teleconsultation'
            Log::info("Appointment Type: {$appointmentType}");
            // Create the appointment
            $appointment = Appointment::create([
                'user_id' => $user_id,
                'doctor_id' => $doctorId,
                'appointment_at' => $validatedData['appointment_at'],
                'start_at' => $startAtFormatted,
                'ends_at' => $endAtFormatted,
                'appointment_status_id' => 2,
                'motif_id' => $motifId,
                'online' => $appointmentType,
                'patient_id' => $validatedData['patient_id'],
            ]);

            Log::info('Appointment Created Successfully:', ['appointment_id' => $appointment->id]);


            return response()->json(['appointment_id' => $appointment->id, 'status' => 'success']);

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


    public function storePatientPassage(Request $request)
    {
        // Validate incoming data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:15|unique:users,phone_number',
            'mobile_number' => 'nullable|string|max:15',
            'age' => 'nullable|integer|min:0',
            'gender' => 'nullable|string|in:male,female,other',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'medical_history' => 'nullable|string',
            'notes' => 'nullable|string',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'password' => 'required|string|min:6|max:20', // Added password validation
        ]);

        DB::beginTransaction();

        try {
            // Check if the user already exists
            Log::info('Checking if user exists by email or phone number.', [
                'email' => $validatedData['email'],
                'phone_number' => $validatedData['phone_number']
            ]);

            $user = User::where('email', $validatedData['email'])
                ->orWhere('phone_number', $validatedData['phone_number'])
                ->first();

            if (!$user) {
                Log::info('User not found, creating a new user.');

                // Create user with plain password
                $user = new User([
                    'name' => "{$validatedData['first_name']} {$validatedData['last_name']}",
                    'email' => $validatedData['email'],
                    'phone_number' => $validatedData['phone_number'],
                    'password' => $validatedData['password'], // Storing plain text password
                ]);

                $user->save();
                Log::info('User created successfully.', ['user_id' => $user->id]);
            }

            // Create the patient associated with the user
            Log::info('Creating patient record.', ['user_id' => $user->id]);

            $patient = Patient::create([
                'user_id' => $user->id,
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'phone_number' => $validatedData['phone_number'],
                'mobile_number' => $validatedData['mobile_number'] ?? null,
                'age' => $validatedData['age'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'weight' => $validatedData['weight'] ?? null,
                'height' => $validatedData['height'] ?? null,
                'medical_history' => $validatedData['medical_history'] ?? null,
                'notes' => $validatedData['notes'] ?? null,
            ]);

            Log::info('Patient created successfully.', ['patient_id' => $patient->id]);


            $userId = Auth::id();
            $doctor = Doctor::where('user_id', $userId)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }
            $sessionDuration = $doctor->session_duration;
            $doctorId = $doctor->id;

            // Construct start and end times for the appointment
            $startAt = "{$validatedData['appointment_date']} {$validatedData['appointment_time']}:00";
            $endAt = Carbon::parse($startAt)->addMinutes(30)->format('Y-m-d H:i:s');
            $startAtFormatted = Carbon::parse($startAt)->format('Y-m-d H:i:00');
            $endAtFormatted = Carbon::parse($endAt)->format('Y-m-d H:i:00');

            // Match availability_hours for the doctor and the selected time
            $availability = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('start_at', '<=', $startAtFormatted)
                ->where('end_at', '>=', $endAtFormatted)
                ->first(['id', 'patern_id']);

            if (!$availability) {
                return response()->json(['error' => 'No matching availability found for the selected time'], 404);
            }

            $motifId = $availability->patern_id;

            Log::info("Availability Matched: {$availability->id}, Pattern ID: {$motifId}");
            // Create an appointment using the patient_id from the newly created patient
            Log::info('Creating appointment.', ['patient_id' => $patient->id, 'start_at' => $startAt, 'end_at' => $endAt]);

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'doctor_id' => $doctorId,
                'appointment_at' => $validatedData['appointment_date'],
                'start_at' => $startAt,
                'ends_at' => $endAt,
                'appointment_status_id' => 2,
                'motif_id' => $motifId,
                'patient_id' => $validatedData['patient_id'],
            ]);

            Log::info('Appointment created successfully.', ['appointment_id' => $appointment->id]);

            DB::commit();

            //Log::info('Transaction committed successfully. User, patient, and appointment saved.');
            //$user->notify(new NewAppointment(
            //  $appointment->appointment_at,
            //$validatedData['email'],
            //$validatedData['password']
            //));

            //Log::info('New appointment notification sent to patient.', ['email' => $validatedData['email']]);

            // Commit the transaction
            DB::commit();
            Log::info('Transaction completed successfully.');

            return response()->json([
                'success' => true,
                'message' => 'User, patient, and appointment saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Transaction failed, rolled back.', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /////////////////////
//les modifications
/////////////////////
    /* public function updateStatus(Request $request)
 {
     try {
         //Log::info('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
         // Find the appointment by ID and update its status
         $appointment = Appointment::findOrFail($request->id);
         $appointment->appointment_status_id = $request->appointment_status_id;
         $appointment->save();

         return response()->json(['message' => 'Status updated successfully']);
     } catch (\Exception $e) {
         return response()->json(['error' => 'Failed to update status'], 500);
     }
 }
 */


    public function updateStatus(Request $request)
    {
        try {
            // Find the appointment by ID
            $appointment = Appointment::findOrFail($request->id);

            // Check if the status is "Canceled" (use the correct status ID for "Canceled")
            if ($request->appointment_status_id == 7) { // Replace 7 with the actual status ID for "Canceled"
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

            // Update the appointment status
            $appointment->appointment_status_id = $request->appointment_status_id;
            $appointment->save();

            if ($appointment->user) {
                $appointment->user->notify(new StatusChangedAppointment($appointment));
            }

            Log::info('Creating message for appointment status update');
            // Log the message creation
            Log::info('Creating message for appointment status update');
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


            }
            return response()->json(['message' => 'Status updated successfully']);
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

    /**public function getAvailableTimeSlots(Request $request)
    {
        $userId = Auth::id(); // Get the logged-in user's ID
        $doctor = Doctor::where('user_id', $userId)->first();
        $selectedDate = $request->input('date');

        if (!$doctor || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        // Fetch all availability hours for the selected date
        $availabilityHours = DB::table('availability_hours')
            ->where('doctor_id', $doctor->id)
            ->whereDate('start_at', $selectedDate)
            ->get(['start_at', 'end_at']);

        if ($availabilityHours->isEmpty()) {
            return response()->json(['error' => 'No availability found for this date'], 404);
        }
        Log::info('DoctorCast - Doctor value:', ['doctor' => $doctor]);

        $sessionDuration = $doctor->session_duration;
        $allSlots = [];
        $takenSlots = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_at', $selectedDate)
            ->where('appointment_status_id', '!=', 7)
            ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
            ->toArray();

        // Loop through each availability range and generate time slots
        foreach ($availabilityHours as $availability) {
            $startTime = Carbon::parse($availability->start_at);
            $endTime = Carbon::parse($availability->end_at);

            while ($startTime->lessThan($endTime)) {
                $allSlots[] = $startTime->format('H:i');
                $startTime->addMinutes($sessionDuration);
            }
        }

        // Remove duplicates in case of overlapping slots
        $allSlots = array_unique($allSlots);
        Log::info('Generated slots', [
            'all_slots' => $allSlots
        ]);
        
        return response()->json([
            'all_slots' => $allSlots,
            'taken_slots' => $takenSlots
        ]);
    }**/
    /*public function getAvailableTimeSlots(Request $request)
    {
        $userId = Auth::id(); // Get the logged-in user's ID
        $doctor = Doctor::where('user_id', $userId)->first();
        $selectedDate = $request->input('date');

        if (!$doctor || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        // Fetch all availability hours for the selected date
        $availabilityHours = DB::table('availability_hours')
            ->where('doctor_id', $doctor->id)
            ->whereDate('start_at', $selectedDate)
            ->get(['start_at', 'end_at']);

        if ($availabilityHours->isEmpty()) {
            return response()->json(['error' => 'No availability found for this date'], 404);
        }
        Log::info('DoctorCast - Doctor value:', ['doctor' => $availabilityHours]);

        $sessionDuration = $doctor->session_duration;
        $allSlots = [];
        $takenSlots = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_at', $selectedDate)
            ->where('appointment_status_id', '!=', 7)
            ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
            ->toArray();    


            Log::info('taken:', ['doctor' => $takenSlots]);
        // Loop through each availability range and generate time slots
        foreach ($availabilityHours as $availability) {
            $startTime = Carbon::parse($availability->start_at);
            $endTime = Carbon::parse($availability->end_at);
            $slotStart = $startTime->format('H:i');
            $slotEnd = $endTime->format('H:i');
            $allSlots[] = "$slotStart - $slotEnd";
        }
        Log::info('DoctorCast - Doctor value:', ['doctor' => $allSlots]);

        // Remove duplicates in case of overlapping slots
        $allSlots = array_unique($allSlots);
        Log::info('Generated slots', [
            'all_slots' => $allSlots
        ]);
        
        return response()->json([
            'all_slots' => $allSlots,
            'taken_slots' => $takenSlots
        ]);
    }*/
    /*public function getAvailableTimeSlots(Request $request)
    {
        $userId = Auth::id(); // Get the logged-in user's ID
        $doctor = Doctor::where('user_id', $userId)->first();
        $selectedDate = $request->input('date'); // Expected format: YYYY-MM-DD

        if (!$doctor || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        // Get the day name for the selected date
        $dayName = Carbon::parse($selectedDate)->locale('fr')->dayName; // Example: "Lundi", "Mardi"

        // Fetch availability hours for the selected day and doctor
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctor->id)
            ->where('day', $dayName)
            ->where('is_available', 1) // Ensure availability is enabled
            ->where('onligne', 0)
            ->first();

        if (!$availability) {
            return response()->json(['error' => 'No availability found for this date'], 404);
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

        // Get taken slots for the selected date
        $takenSlots = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_at', $selectedDate)
            ->where('online', 'cabinet')
            ->pluck(DB::raw("DATE_FORMAT(start_at, '%H:%i')"))
            ->toArray();

        return response()->json([
            'all_slots' => $allSlots,
            'taken_slots' => $takenSlots,
        ]);
    }*/
    public function getAvailableTimeSlots(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }
        // Get the day name for the selected date
        $dayName = Carbon::parse($selectedDate)->locale('fr')->dayName; // Example: "Lundi", "Mardi"

        // Fetch availability hours for the selected day and doctor
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1) // Ensure availability is enabled
            ->where('onligne', 0)
            ->first();

        \Log::info('Availability fetched', ['availability' => $availability]);

        if (!$availability) {
            \Log::warning('No availability found', ['doctor_id' => $doctorId, 'dayName' => $dayName]);
            return response()->json(['error' => 'No availability found for this date'], 404);
        }

        if (!$availability) {
            \Log::warning('No availability found', ['doctor_id' => $doctorId, 'dayName' => $dayName]);
            return response()->json([
                'vacation' => false,
                'all_slots' => [],
                'taken_slots' => [],
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
            ->whereDate('dateDebut', '<=', $selectedDate)
            ->whereDate('dateFin', '>=', $selectedDate)
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
        ];

        \Log::info('Final response', ['response' => $response]);

        return response()->json($response);
    }

    public function getTeleconsultationTimeSlots(Request $request)
    {
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
            ->where('is_available', 1) // Ensure availability is enabled
            ->where('onligne', 1) // Fetch only teleconsultation slots
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
            $doctor = Doctor::where('id', $doctorId)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Doctor not found'], 404);
            }

            $search = $request->input('q');

            // Query patients linked to the logged-in doctor
            $query = Patient::whereHas('doctors', function ($subQuery) use ($doctor) {
                $subQuery->where('doctor_id', $doctor->id);
            });

            if ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereRaw("
                        (JSON_VALID(first_name) AND JSON_EXTRACT(first_name, '$.fr') LIKE ?)
                        OR first_name LIKE ?
                    ", ["%{$search}%", "%{$search}%"])
                        ->orWhereRaw("
                        (JSON_VALID(last_name) AND JSON_EXTRACT(last_name, '$.fr') LIKE ?)
                        OR last_name LIKE ?
                    ", ["%{$search}%", "%{$search}%"])
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            // Select id and concatenated text fields, limit only if searching
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
                    phone_number
                ) as text
            ")
            )
                ->when($search, fn($q) => $q->limit(20)) // Limit to 20 results only when searching
                ->get();

            return response()->json($patients);
        }
    }




    //

    public function getAppointmentsForDate(Request $request)
    {
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





}
