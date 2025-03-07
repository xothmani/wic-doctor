<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Telesecretariat;
use App\Models\DoctorTelesecretariat;
use App\Models\Appointment;
use App\Models\AvailabilityHour;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Add this line to import Log
use App\Models\DoctorSubstitute;


class DoctorTelesecretariatController extends Controller
{

    public function index()
    {
        Log::info('Entered the index method');
        try {
            $userId = Auth::id();

            // Chercher cet ID dans la table Telesecretariat
            $telesecretariat = Telesecretariat::where('user_id', $userId)->first();
            Log::info('Fetched Telesecretariat', ['telesecretariat' => $telesecretariat]);

            if ($telesecretariat) {
                // Default empty collections to prevent errors
                $doctors = collect();
                $availabilityDays = collect();
                $vacations = collect();
                $patterns = collect();
                $patients = collect();

                try {
                    // Récupérer les médecins associés via DoctorTelesecretariat, avec jointure sur la table doctor
                    $doctors = DoctorTelesecretariat::with(['doctor', 'telesecretariat'])
                        ->join('doctors', 'doctor_telesecretariat.doctor_id', '=', 'doctors.id')
                        ->where('telesecretariat_id', $telesecretariat->id)
                        ->orderBy('doctors.name', 'asc')
                        ->get();
                } catch (\Exception $e) {
                    Log::error('Error fetching doctors', ['error' => $e->getMessage()]);
                }

                return view('doctor_telesecretariat.agenda_card', compact('doctors', 'availabilityDays', 'patterns', 'vacations', 'patients'));
            } else {
                return view('doctor_telesecretariat.agenda_card', compact('doctors', 'availabilityDays', 'patterns', 'vacations', 'patients'));
            }

        } catch (\Exception $e) {
            Log::error('Error in index method', ['error' => $e->getMessage()]);
            return view('doctor_telesecretariat.agenda_card', [
                'doctors' => collect(),
                'availabilityDays' => collect(),
                'patterns' => collect(),
                'vacations' => collect(),
                'patients' => collect(),
                'error' => $e->getMessage()
            ]);
        }
    }
    public function getDoctorData(Request $request)
    {
        //Log::info('ooooooooooooooooooo', ['request_data' => $request->all()]);
        try {
            $doctorId = $request->query('doctor_id');

            if (!$doctorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No doctor ID provided',
                ], 400);
            }
            // Fetch patterns for the selected doctor
            $patterns = DB::table('pattern')
                ->select('id', 'nom')
                ->where('doctor_id', $doctorId)
                ->get()
                ->mapWithKeys(function ($pattern) {
                    $name = json_decode($pattern->nom, true);
                    return [$pattern->id => $name[app()->getLocale()] ?? $name['fr']];
                })
                ->toArray();

            // Retrieve patients associated with the doctor
            $patients = Patient::whereHas('doctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })->select('id', 'first_name', 'last_name', 'phone_number')->get();

            if ($request->has('start') && $request->has('end')) {
                $start = $request->query('start');
                $end = $request->query('end');

                // Fetch appointments within the specified range for the given doctor
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
                        'type' => $appointment->type,
                        'note' => $appointment->hint,
                    ];
                }));
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error fetching doctor data', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching doctor data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getDoctorAppointments(Request $request)
    {
        Log::info('ttttttttttttttttt', ['request_data' => $request->all()]);
        // Initialize collections
        $availabilityDays = collect();
        $vacations = collect();
        $patterns = collect();
        $patients = collect();

        Log::info('Received index request', ['request_data' => $request->all()]);

        // Get the doctor_id from the request
        $doctorId = $request->query('doctor_id');

        if (!$doctorId) {
            Log::warning("No doctor ID provided.");
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No doctor selected',
                    'doctors' => Doctor::all(),
                ]);
            }
            $doctors = Doctor::all();
            return view('doctor_telesecretariat.agenda_card', compact('doctors', 'availabilityDays', 'patterns', 'vacations', 'patients'));
        }

        if ($request->ajax() && $request->has('start') && $request->has('end')) {
            try {
                $start = $request->query('start');
                $end = $request->query('end');

                // Fetch appointments within the specified range for the given doctor
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
                        'type' => $appointment->type,
                        'note' => $appointment->hint,
                    ];
                }));
            } catch (\Exception $e) {
                Log::error('Error fetching appointments', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching appointments',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        try {
            // Fetch data for the selected doctor
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

            Log::info("Data retrieved for Doctor ID: {$doctorId}", [
                'availability_days' => $availabilityDays,
                'vacations' => $vacations,
                'patterns' => $patterns,
                'patients_count' => $patients->count(),
            ]);

            $doctors = Doctor::all();

            return view('doctor_telesecretariat.agenda_card', compact('doctors', 'availabilityDays', 'patterns', 'vacations', 'patients'));
        } catch (\Exception $e) {
            Log::error('Error fetching data for doctor ID', ['doctor_id' => $doctorId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function saveAppointment(Request $request)
    {
        try {
            $doctorId = $request->input('doctor_id'); // Use doctor_id from request

            if (!$doctorId) {
                return response()->json(['error' => 'Doctor ID not provided'], 400);
            }

            $doctor = Doctor::find($doctorId);

            if (!$doctor) {
                return response()->json(['error' => 'Invalid Doctor ID'], 404);
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

            return response()->json(['message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }


    //////////////////////
// les getters
/////////////////////

    public function getDoctorAvailabilityData(Request $request)
    {
        Log::info('vaiabilty', ['request_data' => $request->all()]);

        try {
            $doctorId = $request->query('doctor_id');

            if (!$doctorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor ID is required',
                ], 400);
            }

            // Fetch availability days for the doctor
            $availabilityDays = DB::table('availability_hours')
                ->where('doctor_id', $doctorId)
                ->where('is_available', 1)
                ->distinct()
                ->pluck('day'); // Example: ["Lundi", "Mardi"]

            // Fetch vacation data for the doctor
            $vacations = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->select('start_date', 'end_date')
                ->get();

            return response()->json([
                'success' => true,
                'availabilityDays' => $availabilityDays,
                'vacations' => $vacations,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching doctor availability data', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching doctor availability data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAvailableTimeSlots(Request $request)
    {
        Log::info('Entering getAvailableTimeSlots', ['request_data' => $request->all()]);

        $doctorId = $request->input('doctor_id'); // Use doctor_id from request
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }
        // Get the day name for the selected date
        $dayName = Carbon::parse($selectedDate)->locale('en')->dayName; // Example: "Lundi", "Mardi"

        // Fetch availability hours for the selected day and doctor
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1) // Ensure availability is enabled
            ->where('mode', 'precise') // Fetch only precise slots
            ->first();

        \Log::info('Availability fetched', ['availability' => $availability]);


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
        ];

        \Log::info('Final response', ['response' => $response]);

        return response()->json($response);
    }


    public function getTeleconsultationTimeSlots(Request $request)
    {
        $doctorId = $request->input('doctor_id'); // Use doctor_id from request
        $selectedDate = $request->input('date');

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }
        $selectedDate = $request->input('date'); // Expected format: YYYY-MM-DD


        // Get the day name for the selected date
        $dayName = Carbon::parse($selectedDate)->locale('en')->dayName; // Example: "Lundi", "Mardi"

        // Fetch teleconsultation availability hours for the selected day and doctor
        $teleAvailability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1) // Ensure availability is enabled
            ->where('mode', 'precise') // Fetch only teleconsultation slots
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
    public function getPatterns(Request $request)
    {
        $doctorId = $request->query('doctor_id');

        if (!$doctorId) {
            return response()->json([
                'success' => false,
                'message' => 'No doctor ID provided',
            ], 400);
        }

        // Fetch patterns for the given doctor ID
        $patterns = DB::table('pattern')
            ->select('id', 'nom')
            ->where('doctor_id', $doctorId)
            ->get()
            ->map(function ($pattern) {
                $name = json_decode($pattern->nom, true);
                return [
                    'id' => $pattern->id,
                    'nom' => $name[app()->getLocale()] ?? $name['fr'],
                ];
            });

        return response()->json($patterns);
    }



    //

    public function getPatients(Request $request)
    {
        Log::info('Entering getPatients', ['request_data' => $request->all()]);
        if ($request->ajax()) {
            $doctorId = $request->input('doctor_id'); // Use doctor_id from request

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



    public function showAgenda()
    {
        // Fetch doctors from the database (modify query as needed)
        $doctors = Doctor::all();

        // Pass the data to the view
        return view('doctor_telescretariat.agenda_card', compact('doctors'));
    }
    public function create()
    {

        return view('doctor_telesecretariat.create');

    }
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Rechercher l'utilisateur par adresse email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        // Vérifier si cet utilisateur est un télésécrétariat
        $telesecretariat = Telesecretariat::where('user_id', $user->id)->first();

        if (!$telesecretariat) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas lié à un télésécrétariat.'
            ], 400);
        }

        // Récupérer l'ID du médecin connecté
        $doctor = Auth::user()->doctor;
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas enregistré comme médecin.'
            ], 400);
        }

        // Vérifier si l'association existe déjà
        $exists = DoctorTelesecretariat::where('doctor_id', $doctor->id)
            ->where('telesecretariat_id', $telesecretariat->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Cette association existe déjà.'
            ], 400);
        }

        // Créer l'association
        $doctorTelesecretariat = new DoctorTelesecretariat();
        $doctorTelesecretariat->doctor_id = $doctor->id;
        $doctorTelesecretariat->telesecretariat_id = $telesecretariat->id;
        $doctorTelesecretariat->save();

        return response()->json([
            'success' => true,
            'email' => $user->email
        ]);
    }
    public function BackgroundColorForAgenda(Request $request)
    {
        $doctorId = $request->input('doctor_id'); // Use doctor_id from request
        $selectedDate = $request->input('date');
        \Log::info('Fetching background color for doctor:', ['doctor_id' => $doctorId]);
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
    private function getPatternColor($patternId)
    {
        return DB::table('pattern')
            ->where('id', $patternId)
            ->value('color') ?? '#C6E7FF'; // Default if color not found
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
        $dayName = $selectedDate->format('l'); // Get the day name (e.g., Monday)

        // Retrieve all availability slots for the doctor on this day
        $availabilities = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->where('day', $dayName)
            ->where('mode', 'precise')
            ->get();

        $totalAvailableSlots = 0;
        $takenSlots = [];

        foreach ($availabilities as $availability) {
            // Get start, end time, and session duration
            $startTime = Carbon::parse($availability->start_at);
            $endTime = Carbon::parse($availability->end_at);
            $sessionDuration = (int) $availability->session_duration; // Convert to integer

            // Generate all possible time slots
            while ($startTime->lessThan($endTime)) {
                $slotTime = $startTime->format('H:i'); // e.g., 08:00
                $totalAvailableSlots++;

                // Check if this slot is taken
                $slotTaken = DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->whereDate('start_at', $selectedDate->format('Y-m-d'))
                    ->whereTime('start_at', $slotTime)
                    ->whereNotIn('appointment_status_id', [7, 6])
                    ->exists(); // Check if appointment exists for this slot

                if ($slotTaken) {
                    $takenSlots[] = $slotTime;
                }

                // Move to the next slot based on session duration
                $startTime->addMinutes($sessionDuration);
            }
        }

        // Return response
        return response()->json([
            'total_appointments' => $totalAvailableSlots,
            'appointments_taken' => count($takenSlots),
            'available_slots' => $totalAvailableSlots - count($takenSlots),
        ]);
    }
    public function telegetPatternForTimeSlot(Request $request)
    {
        \Log::info('Fetching Unavailable Time Slots:', [
            'date' => $request->get('date'),
            'time' => $request->get('time'),
            'type' => $request->get('type')
        ]);
        $doctorId = $request->input('doctor_id');
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

    public function telegetPatternForTimeSlotWithoutType(Request $request)
    {
        \Log::info('Fetching Unavailable Time Slots:', [
            'date' => $request->get('date'),
            'time' => $request->get('time')
        ]);

        $doctorId = $request->input('doctor_id');
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
}