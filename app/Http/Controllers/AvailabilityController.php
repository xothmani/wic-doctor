<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\Pattern;
use App\Models\Appointment;
use App\Models\DoctorSubstitute;
use App\Models\AvailabilityHour;
use App\Models\DoctorUrgency;
use App\Models\DoctorVacation;
use Carbon\Carbon;
use Flash;


class AvailabilityController extends Controller
{
    protected $defaultSchedules = [
        'cabinet' => [
            'start' => '09:00',
            'end' => '17:00',
            'break_start' => null,
            'break_end' => null,
        ],
        'teleconsultation' => [
            'start' => '19:00',
            'end' => '22:00',
            'break_start' => null,
            'break_end' => null
        ],
        'home_visit' => [
            'start' => null,
            'end' => null,
            'break_start' => null,
            'break_end' => null
        ]
    ];

    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login'); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
        }

        // Récupérer l'ID du médecin lié à l'utilisateur connecté
        $doctorId = auth()->user()->getDoctorId();


        if (!$doctorId) {
            session()->flash('error', 'Veuillez d\'abord sélectionner un médecin.');
        }
        $currentMode = null;
        if (auth()->user()->hasRole('doctor')) {
            $doctor = auth()->user()->doctor;
            $currentMode = $doctor->availability_mode;
        } elseif (auth()->user()->hasRole('Telesecretary')) {
            $doctorId = session('selectedDoctorId');
            $doctor = Doctor::find($doctorId);
            $currentMode = $doctor ? $doctor->availability_mode : null;
            \Log::info('Current mode (Telesecretary):', ['mode' => $currentMode]);
        } elseif (auth()->user()->hasRole('Secretary')) {
            $doctor = Doctor::find($doctorId);
            $currentMode = $doctor ? $doctor->availability_mode : null;
            \Log::info('Current mode (Secretary):', ['mode' => $currentMode]);
        }
        \Log::info('Current availability mode:', ['mode' => $currentMode]);

        if ($currentMode == 'open') {
            // Get availability for all three types
            $availabilityTypes = ['cabinet', 'teleconsultation', 'home_visit'];
            $availabilities = [];

            // Debug logging
            \Log::info('Fetching open mode availabilities for doctor:', ['doctor_id' => $doctorId]);

            foreach ($availabilityTypes as $type) {
                $typeAvailability = AvailabilityHour::where('doctor_id', $doctorId)
                    ->where('type', $type)
                    ->where('mode', 'open')
                    ->get();

                // Transform data for each day with default values
                $days = [
                    'monday' => ['is_available' => 0],
                    'tuesday' => ['is_available' => 0],
                    'wednesday' => ['is_available' => 0],
                    'thursday' => ['is_available' => 0],
                    'friday' => ['is_available' => 0],
                    'saturday' => ['is_available' => 0],
                    'sunday' => ['is_available' => 0]
                ];

                foreach ($days as $day => &$data) {
                    // If no existing data, use defaults
                    if (!$typeAvailability->where('day', $day)->count()) {
                        $data['start_at'] = $this->defaultSchedules[$type]['start'];
                        $data['end_at'] = $this->defaultSchedules[$type]['end'];
                        $data['pause_from'] = $this->defaultSchedules[$type]['break_start'];
                        $data['pause_to'] = $this->defaultSchedules[$type]['break_end'];
                    }
                }

                foreach ($typeAvailability as $slot) {
                    $days[strtolower($slot->day)] = [
                        'is_available' => $slot->is_available,
                        'start_at' => $slot->start_at ? Carbon::parse($slot->start_at)->format('H:i') : '09:00',
                        'end_at' => $slot->end_at ? Carbon::parse($slot->end_at)->format('H:i') : '17:00',
                        'pause_from' => $slot->pause_from ? Carbon::parse($slot->pause_from)->format('H:i') : null,
                        'pause_to' => $slot->pause_to ? Carbon::parse($slot->pause_to)->format('H:i') : null,
                    ];
                }

                $availabilities[$type] = collect($days);

                \Log::info("Retrieved open mode for type {$type}:", [
                    'count' => $typeAvailability->count(),
                    'data' => $days
                ]);
            }

            // Get session durations for each type
            $sessionDurations = [];
            foreach ($availabilityTypes as $type) {
                $duration = AvailabilityHour::where('doctor_id', $doctorId)
                    ->where('mode', 'open')
                    ->where('type', $type)
                    ->value('session_duration') ?? 15;

                // Convert to hh:mm format
                $hours = intdiv($duration, 60);
                $minutes = $duration % 60;
                $sessionDurations[$type] = sprintf('%02d:%02d', $hours, $minutes);
            }

            // Get doctor's patterns
            $doctorPatterns = Pattern::where('doctor_id', $doctorId)
                ->get()
                ->map(function ($pattern) {
                    $decodedNom = json_decode($pattern->nom, true);
                    if (is_array($decodedNom) && isset($decodedNom['fr'])) {
                        $pattern->nom = $decodedNom['fr'];
                    }
                    return $pattern;
                });

            // Get breaks data
            $breakTime = AvailabilityHour::where('doctor_id', $doctorId)
                ->where('mode', 'open')
                ->select('pause_from', 'pause_to')
                ->first();

            // Get vacations
            $vacations = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->orderBy('start_date', 'desc')
                ->get();

            // Add substitutes to the view data
            $substitutes = DoctorSubstitute::where('doctor_id', $doctorId)
                ->orderBy('start_date', 'desc')
                ->get();
            $dailyClosures = DB::table('doctor_urgency')
                ->where('doctor_id', $doctorId)
                ->where('jour', '>=', now()->startOfDay())
                ->orderBy('jour', 'asc')
                ->get();
            return view('availability.index', compact(
                'availabilities',
                'sessionDurations', // Replace sessionDurationFormatted with sessionDurations
                'currentMode',
                'doctorPatterns',
                'breakTime',
                'vacations',
                'substitutes',
                'dailyClosures',
            ));
        } elseif ($currentMode == 'precise') {

            $days = [
                "monday",
                "tuesday",
                "wednesday",
                "thursday",
                "friday",
                "saturday",
                "sunday"
            ];

            // Get availability for all types
            $availabilities = [
                'cabinet' => [],
                'teleconsultation' => [],
                'home_visit' => []
            ];

            // Debug log to check what's being retrieved
            \Log::info("Fetching precise mode availabilities for doctor: " . $doctorId);

            // Retrieve availabilities for each type with mode filter
            foreach ($availabilities as $type => &$typeAvailability) {
                $slots = AvailabilityHour::where('doctor_id', $doctorId)
                    ->where('type', $type)
                    ->where('mode', 'precise') // Add mode filter
                    ->get();

                // Group by day
                $typeAvailability = $slots->groupBy('day');

                // Debug log
                \Log::info("Retrieved precise mode for type {$type}:", ['count' => $slots->count()]);
            }

            // Get vacations
            $vacations = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->orderBy('start_date', 'desc')
                ->get();

            // Get doctor's patterns and decode JSON names
            $doctorPatterns = Pattern::where('doctor_id', $doctorId)
                ->get()
                ->map(function ($pattern) {
                    $decodedNom = json_decode($pattern->nom, true);
                    if (is_array($decodedNom) && isset($decodedNom['fr'])) {
                        $pattern->nom = $decodedNom['fr'];
                    }
                    return $pattern;
                });
            $substitutes = DoctorSubstitute::where('doctor_id', $doctorId)
                ->orderBy('start_date', 'desc')
                ->get();
            $dailyClosures = DB::table('doctor_urgency')
                ->where('doctor_id', $doctorId)
                ->where('jour', '>=', now()->startOfDay())
                ->orderBy('jour', 'asc')
                ->get();

            $periodClosures = DB::table('vacance')
                ->where('doctor_id', $doctorId)
                ->where('end_date', '>=', now()->startOfDay())
                ->orderBy('start_date', 'asc')
                ->get();
            // For debugging
            \Log::info('Doctor Patterns:', ['patterns' => $doctorPatterns->toArray()]);
            return view('availability.index', compact(
                'availabilities',
                'currentMode',
                'days',
                'vacations',
                'substitutes',
                'doctorPatterns',
                'dailyClosures',
                'periodClosures'
            ));
        }
    }



    public function indexTele()
    {
        $doctorId = auth()->user()->getDoctorId();
        Log::info("Retrieving doctor ID: {$doctorId}");

        if (!$doctorId) {
            return redirect()->route('users.profile'); // Redirect if no doctor found
        }

        // Récupérer les horaires de disponibilité en ligne du médecin
        $availability = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 1) // Ajouter cette condition
            ->get()
            ->map(function ($dayData) {
                return [
                    'day' => $dayData->day,
                    'is_available' => $dayData->is_available,
                    'start_at' => $dayData->start_at ? Carbon::parse($dayData->start_at)->format('H:i') : '09:00',
                    'end_at' => $dayData->end_at ? Carbon::parse($dayData->end_at)->format('H:i') : '17:00',
                ];
            });

        // Récupérer session_duration depuis une ligne d'AvailabilityHour
        $sessionDuration = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 1) // Ajouter cette condition
            ->value('session_duration') ?? 15; // Par défaut : 15 minutes

        // Convertir session_duration en format hh:mm
        $hours = intdiv($sessionDuration, 60);
        $minutes = $sessionDuration % 60;
        $sessionDurationFormatted = sprintf('%02d:%02d', $hours, $minutes);

        // Récupérer les pauses
        $pauseFrom = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 1) // Ajouter cette condition
            ->value('pause_from');
        $pauseTo = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 1) // Ajouter cette condition
            ->value('pause_to');

        $pauseFrom = $pauseFrom ? Carbon::parse($pauseFrom)->format('H:i') : null;
        $pauseTo = $pauseTo ? Carbon::parse($pauseTo)->format('H:i') : null;

        return view('availability.tele', compact('availability', 'sessionDurationFormatted', 'pauseFrom', 'pauseTo'));
    }





    public function store(Request $request)
    {
        \Log::info('Request received:', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $type = $request->input('type', 'cabinet');

        try {
            $validated = $request->validate([
                'type' => 'required|in:cabinet,teleconsultation,home_visit',
                'availability' => 'required|array',
                'availability.*.day' => 'required|string',
                'availability.*.is_available' => 'nullable|boolean',
                'availability.*.slots' => 'nullable|array',
                'availability.*.slots.start' => 'nullable|array',
                'availability.*.slots.end' => 'nullable|array',
                'availability.*.slots.pattern' => 'nullable|array',
                'availability.*.slots.duration' => 'nullable|array',
            ]);
            \Log::info('Validated Data:', ['validated' => $validated]);
            DB::beginTransaction();

            // Delete existing slots for this type and mode
            AvailabilityHour::where('doctor_id', $doctorId)
                ->where('type', $type)
                ->where('mode', 'precise')  // Add mode filter
                ->delete();

            foreach ($validated['availability'] as $dayData) {
                $day = $dayData['day'];
                $isAvailable = $dayData['is_available'] ?? false;

                if ($isAvailable && isset($dayData['slots'])) {
                    foreach ($dayData['slots']['start'] as $index => $startTime) {
                        if (empty($startTime) || empty($dayData['slots']['end'][$index])) {
                            continue;
                        }

                        // Check for overlaps with other consultation types
                        $hasOverlap = $this->checkOverlappingSlots(
                            $doctorId,
                            $dayData['day'],
                            $startTime,
                            $dayData['slots']['end'][$index],
                            $type
                        );

                        if ($hasOverlap) {
                            throw new \Exception(
                                "Conflit d'horaire détecté entre {$startTime} et {$dayData['slots']['end'][$index]}. " .
                                "Ces horaires chevauchent une autre consultation."
                            );
                        }

                        AvailabilityHour::create([
                            'doctor_id' => $doctorId,
                            'day' => $day,
                            'type' => $type,
                            'start_at' => $startTime,
                            'end_at' => $dayData['slots']['end'][$index],
                            'patern_id' => $dayData['slots']['pattern'][$index] ?? null,
                            'session_duration' => $dayData['slots']['duration'][$index] ?? 30,
                            'is_available' => true,
                            'mode' => 'precise'
                        ]);
                        DB::table('availability_hours_tunisie')->insert([
                            'doctor_id' => $doctorId,
                            'day' => $day,
                            'start_at' => $startTime,
                            'end_at' => $dayData['slots']['end'][$index],
                            'patern_id' => $dayData['slots']['pattern'][$index] ?? null,
                            'session_duration' => $dayData['slots']['duration'][$index] ?? 30,
                            'is_available' => true,
                            'onligne' => $type, // Assuming 'onligne' is meant to represent the type (cabinet/home_visit/etc)
                            'pause_from' => null,
                            'pause_to' => null,
                            'data' => null, // If you want to store additional data later
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Convert new session duration from hh:mm to minutes
            if (isset($validated['slots']['duration'])) {
                foreach ($validated['slots']['duration'] as $duration) {
                    $currentDuration = AvailabilityHour::where('doctor_id', $doctorId)
                        ->where('type', $type)
                        ->where('mode', 'precise')
                        ->value('session_duration');

                    if ($currentDuration && $duration != $currentDuration) {
                        $hasAppointments = DB::table('appointments')
                            ->where('doctor_id', $doctorId)
                            ->where('type', $type)
                            ->where('appointment_at', '>', now())
                            ->exists();

                        if ($hasAppointments) {
                            session()->flash('duration_changed', true);
                            session()->flash('old_duration', $currentDuration);
                            session()->flash('new_duration', $duration);
                            session()->flash('has_existing_appointments', true);
                            session()->flash('affected_type', $type);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Disponibilité sauvegardée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving availability:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    private function adjustAppointments($doctorId, $type, $oldDuration, $newDuration)
    {
        $futureAppointments = DB::table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('type', $type)
            ->where('appointment_at', '>', now())
            ->orderBy('appointment_at')
            ->get();

        DB::beginTransaction();
        try {
            // Group appointments by their start time
            $appointmentGroups = $futureAppointments->groupBy(function ($appointment) {
                return Carbon::parse($appointment->appointment_at)->format('Y-m-d H:i');
            });

            foreach ($appointmentGroups as $startTime => $appointments) {
                $currentStartTime = Carbon::parse($startTime);

                foreach ($appointments as $appointment) {
                    // Update only duration while keeping same start time for concurrent appointments
                    DB::table('appointments')
                        ->where('id', $appointment->id)
                        ->update([
                            'duration' => $newDuration
                        ]);
                }

                // Calculate end time of current group
                $groupEndTime = $currentStartTime->copy()->addMinutes($newDuration);

                // Get next group's appointments that need to be shifted
                $nextAppointments = DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->where('type', $type)
                    ->where('appointment_at', '>', $currentStartTime)
                    ->where('appointment_at', '<', $groupEndTime)
                    ->orderBy('appointment_at')
                    ->get();

                // Shift any overlapping appointments
                if ($nextAppointments->count() > 0) {
                    foreach ($nextAppointments as $nextAppointment) {
                        DB::table('appointments')
                            ->where('id', $nextAppointment->id)
                            ->update([
                                'appointment_at' => $groupEndTime->format('Y-m-d H:i:s'),
                                'duration' => $newDuration
                            ]);

                        $groupEndTime->addMinutes($newDuration);
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adjusting appointments:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function storeOpen(Request $request)
    {
        \Log::info('Request store received:', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $type = $request->input('type', 'cabinet');

        try {
            // Create day name mapping
            $dayMapping = [
                'Lundi' => 'monday',
                'Mardi' => 'tuesday',
                'Mercredi' => 'wednesday',
                'Jeudi' => 'thursday',
                'Vendredi' => 'friday',
                'Samedi' => 'saturday',
                'Dimanche' => 'sunday'
            ];

            // Pre-process the availability data to convert French day names to English
            $processedData = collect($request->input('availability'))->map(function ($item) use ($dayMapping) {
                if (isset($item['day']) && isset($dayMapping[$item['day']])) {
                    $item['day'] = strtolower($dayMapping[$item['day']]); // Store in lowercase
                }
                return $item;
            })->toArray();

            // Replace the original availability data with processed data
            $request->merge(['availability' => $processedData]);

            $validated = $request->validate([
                'type' => 'required|in:cabinet,teleconsultation,home_visit',
                'session_duration' => [
                    'required',
                    'regex:/^\d{1,2}:\d{2}$/',
                ],
                'availability' => 'required|array',
                'availability.*.day' => [
                    'required',
                    'string',
                    'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
                ],
                'availability.*.from' => [
                    'required_if:availability.*.is_available,1',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) use ($request) {
                        $index = explode('.', $attribute)[1];
                        if ($value === '00:00' && $request->input("availability.$index.to") === '00:00') {
                            $fail(trans('validation.invalid_time_range'));
                        }
                    }
                ],
                'availability.*.to' => [
                    'required_if:availability.*.is_available,1',
                    'date_format:H:i',
                    'after:availability.*.from',
                ],
                'availability.*.pause_from' => 'nullable|required_with:availability.*.pause_to|date_format:H:i',
                'availability.*.pause_to' => 'nullable|required_with:availability.*.pause_from|date_format:H:i|after:availability.*.pause_from',
                'availability.*.is_available' => 'nullable|boolean',
            ], [
                'availability.*.from.required_with' => trans('validation.required_time'),
                'availability.*.to.required_with' => trans('validation.required_time'),
                'availability.*.to.after' => trans('validation.end_time_after'),
                'availability.*.pause_from.required_with' => trans('validation.break_start_required'),
                'availability.*.pause_to.required_with' => trans('validation.break_end_required'),
                'availability.*.pause_to.after' => trans('validation.break_end_after'),
                'session_duration.regex' => trans('validation.session_duration_format')
            ]);

            Log::info('Validated Open Mode Data with breaks:', ['validated' => $validated]);

            // Convert new session duration from hh:mm to minutes
            [$hours, $minutes] = explode(':', $validated['session_duration']);
            $newSessionDuration = ($hours * 60) + $minutes;

            // Get current session duration for this specific type
            $currentSessionDuration = AvailabilityHour::where('doctor_id', $doctorId)
                ->where('type', $type)
                ->where('mode', 'open')
                ->value('session_duration');

            // Check for existing appointments if duration is being reduced
            if ($currentSessionDuration && $newSessionDuration < $currentSessionDuration) {
                $hasAppointments = DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->where('type', $type)
                    ->where('appointment_at', '>', now())
                    ->exists();

                if ($hasAppointments) {
                    throw new \Exception(trans('validation.cannot_reduce_duration'));
                }
            }

            if ($currentSessionDuration && $newSessionDuration != $currentSessionDuration) {
                $hasAppointments = DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->where('type', $type)
                    ->where('appointment_at', '>', now())
                    ->exists();

                if ($hasAppointments) {
                    session()->flash('duration_changed', true);
                    session()->flash('old_duration', $currentSessionDuration);
                    session()->flash('new_duration', $newSessionDuration);
                    session()->flash('has_existing_appointments', true);
                    session()->flash('affected_type', $type);  // Add affected type to flash
                }
            }

            DB::beginTransaction();

            // Delete existing slots for this type and mode only
            AvailabilityHour::where('doctor_id', $doctorId)
                ->where('type', $type)
                ->where('mode', 'open')  // Add this line to filter by mode
                ->delete();

            // Convert session duration from hh:mm to minutes
            [$hours, $minutes] = explode(':', $validated['session_duration']);
            $sessionDuration = ($hours * 60) + $minutes;

            foreach ($validated['availability'] as $data) {
                if (isset($data['is_available']) && $data['is_available']) {
                    // Ensure pause times are processed correctly
                    $pauseFrom = !empty($data['pause_from']) ? $data['pause_from'] : null;
                    $pauseTo = !empty($data['pause_to']) ? $data['pause_to'] : null;

                    // Validate that pause_to is after pause_from if both are set
                    if ($pauseFrom && $pauseTo && $pauseFrom >= $pauseTo) {
                        throw new \Exception("L'heure de fin de pause doit être après l'heure de début de pause pour {$data['day']}");
                    }

                    // Check for overlaps with other consultation types
                    $hasOverlap = $this->checkOverlappingSlots(
                        $doctorId,
                        $data['day'],
                        $data['from'],
                        $data['to'],
                        $type
                    );

                    if ($hasOverlap) {
                        throw new \Exception(
                            "Conflit d'horaire détecté entre {$data['from']} et {$data['to']}. " .
                            "Ces horaires chevauchent une autre consultation."
                        );
                    }

                    // Create or update with both type and mode conditions
                    AvailabilityHour::create([
                        'doctor_id' => $doctorId,
                        'day' => $data['day'],
                        'type' => $type,
                        'mode' => 'open',
                        'start_at' => $data['from'],
                        'end_at' => $data['to'],
                        'pause_from' => $pauseFrom,
                        'pause_to' => $pauseTo,
                        'session_duration' => $sessionDuration,
                        'is_available' => true
                    ]);
                    DB::table('availability_hours_tunisie')->insert([
                        'doctor_id' => $doctorId,
                        'day' => $data['day'],
                        'start_at' => $data['from'],
                        'end_at' => $data['to'],
                        'session_duration' => $sessionDuration,
                        'is_available' => true,
                        'onligne' => $type, // Assuming 'onligne' is meant to represent the type (cabinet/home_visit/etc)
                        'pause_from' => null,
                        'pause_to' => null,
                        'data' => null,
                    ]);
                    Log::info("Created availability with breaks", [
                        'day' => $data['day'],
                        'pause_from' => $pauseFrom,
                        'pause_to' => $pauseTo
                    ]);
                }
            }

            // After successful update, store the new duration in session for the warning message
            if ($currentSessionDuration && $newSessionDuration != $currentSessionDuration) {
                session()->flash('duration_changed', true);
                session()->flash('old_duration', $currentSessionDuration);
                session()->flash('new_duration', $newSessionDuration);
            }

            DB::commit();
            return redirect()->back()->with('success', __('messages.availability_saved'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving open mode availability:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeTele(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        Log::info("Retrieving doctor ID: {$doctorId}");


        if (!$doctorId) {
            return redirect()->route('users.profile'); // Redirect if no doctor found
        }


        try {
            Log::info('Form Data Before Processing:', $request->all());

            // Valider les données
            $validated = $request->validate([
                'availability' => 'required|array',
                'availability.*.day' => 'required|string',
                'availability.*.from' => 'nullable|date_format:H:i',
                'availability.*.to' => 'nullable|date_format:H:i|after:availability.*.from',
                'availability.*.is_available' => 'nullable|boolean',
                'session_duration' => ['required', 'regex:/^\d{1,2}:\d{2}$/'], // Valide hh:mm
                'pause_from' => 'nullable|date_format:H:i',
                'pause_to' => 'nullable|date_format:H:i|after:pause_from',
            ]);

            // Convertir la durée de session de hh:mm à minutes
            [$hours, $minutes] = explode(':', $validated['session_duration']);
            $sessionDuration = ($hours * 60) + $minutes;

            // Si les pauses sont définies, les convertir également en format horaire
            $pauseFrom = $validated['pause_from'] ? Carbon::createFromFormat('H:i', $validated['pause_from'])->toTimeString() : null;
            $pauseTo = $validated['pause_to'] ? Carbon::createFromFormat('H:i', $validated['pause_to'])->toTimeString() : null;

            foreach ($validated['availability'] as $availability) {
                $day = ucfirst($availability['day']);
                $isAvailable = $availability['is_available'] ?? 0;

                $startTime = $availability['from'] ? Carbon::createFromFormat('H:i', $availability['from'])->toTimeString() : null;
                $endTime = $availability['to'] ? Carbon::createFromFormat('H:i', $availability['to'])->toTimeString() : null;

                // Vérifier les conflits uniquement si `is_available = 1`
                if ($isAvailable == 1) {
                    $conflicts = DB::table('availability_hours')
                        ->where('doctor_id', $doctorId)
                        ->where('day', $day)
                        ->where('onligne', 0)
                        ->where('is_available', 1)
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->where(function ($q) use ($startTime, $endTime) {
                                $q->where('start_at', '<', $endTime)
                                    ->where('end_at', '>', $startTime);
                            });
                        })
                        ->exists();

                    if ($conflicts) {
                        return redirect()->back()->withErrors([
                            'error' => "Conflit détecté pour le jour {$day} entre {$startTime} et {$endTime}. Veuillez ajuster les horaires."
                        ]);
                    }
                }

                $data = [
                    'doctor_id' => $doctorId,
                    'day' => $day,
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'is_available' => $isAvailable,
                    'session_duration' => $sessionDuration,
                    'pause_from' => $pauseFrom,
                    'pause_to' => $pauseTo,
                    'onligne' => 1, // Ajouter la colonne 'online' avec la valeur 0
                ];

                $existing = DB::table('availability_hours')
                    ->where('doctor_id', $doctorId)
                    ->where('day', $day)
                    ->where('onligne', 1) // Vérification de `onligne = 0`
                    ->first();

                if ($existing) {
                    DB::table('availability_hours')->where('id', $existing->id)->update($data);
                } else {
                    DB::table('availability_hours')->insert($data);
                }
            }

            return redirect()->route('availability.tele')->with('success', 'Disponibilité sauvegardée avec succès !');
        } catch (\Exception $e) {
            Log::error('Error saving availability:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    public function storeVacation(Request $request)
    {
        \Log::info('Request received:', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();

        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:255'
            ]);

            DB::table('vacance')->insert([
                'doctor_id' => $doctorId,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
            ]);

            return redirect()->back()->with('success', 'Vacances ajoutées avec succès!');
        } catch (\Exception $e) {
            Log::error('Error saving vacation:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteVacation($id)
    {
        $doctorId = auth()->user()->getDoctorId();

        try {
            DB::table('vacance')
                ->where('id', $id)
                ->where('doctor_id', $doctorId)
                ->delete();

            return redirect()->back()->with('success', 'Vacances supprimées avec succès!');
        } catch (\Exception $e) {
            Log::error('Error deleting vacation:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function updateVacation($id, Request $request)
    {
        \Log::info('Update vacation request received:', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'nullable|string|max:255'
            ]);

            // Check if vacation exists and belongs to doctor
            $vacation = DB::table('vacance')
                ->where('id', $id)
                ->where('doctor_id', $doctorId)
                ->first();

            if (!$vacation) {
                DB::rollBack();
                \Log::warning('Vacation not found:', ['id' => $id]);
                return redirect()->back()
                    ->with('error', trans('messages.vacation_not_found'))
                    ->withInput();
            }

            // Update the vacation
            $updated = DB::table('vacance')
                ->where('id', $id)
                ->where('doctor_id', $doctorId)
                ->update([
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'reason' => $validated['reason']
                ]);

            DB::commit();

            \Log::info('Vacation updated successfully:', [
                'id' => $id,
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('success', trans('messages.vacation_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating vacation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', trans('messages.vacation_update_failed'))
                ->withInput();
        }
    }
    public function storeBreaks(Request $request)
    {

        $doctorId = auth()->user()->getDoctorId();

        try {
            $validated = $request->validate([
                'pause_from' => 'required|date_format:H:i',
                'pause_to' => 'required|date_format:H:i|after:pause_from',
            ]);

            // Update breaks for all availabilities of this doctor in open mode
            AvailabilityHour::where('doctor_id', $doctorId)
                ->where('mode', 'open')
                ->update([
                    'pause_from' => $validated['pause_from'],
                    'pause_to' => $validated['pause_to']
                ]);

            return redirect()->back()->with('success', 'Pauses sauvegardées avec succès!');
        } catch (\Exception $e) {
            Log::error('Error saving breaks:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getAvailableTimeSlotsForOpen(Request $request)
    {
        \Log::info('Request received-2', ['request' => $request->all()]);
        $doctorId = auth()->user()->getDoctorId();
        $selectedDate = $request->input('date');
        $type = $request->input('type', 'cabinet'); // Default to cabinet if not specified

        if (!$doctorId || !$selectedDate) {
            return response()->json(['error' => 'Doctor or date not found'], 404);
        }

        $dayName = Carbon::parse($selectedDate)->locale('en')->dayName;

        // Fetch availability hours for the selected day, doctor, and type
        $availability = DB::table('availability_hours')
            ->where('doctor_id', $doctorId)
            ->where('day', $dayName)
            ->where('is_available', 1)
            ->where('mode', 'open')
            ->where('type', $type) // Add type filter
            ->first();

        // ...rest of your existing code...
    }

    private function checkOverlappingSlots($doctorId, $day, $startTime, $endTime, $currentType = null, $currentId = null)
    {
        // Get doctor's current mode
        $doctor = DB::table('doctors')->where('id', $doctorId)->first();
        if (!$doctor) {
            \Log::error("Doctor not found for ID: $doctorId");
            return false;
        }

        $doctorMode = $doctor->availability_mode;

        // Original overlap checking logic
        $query = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('day', $day)
            ->where('is_available', true)
            ->where('mode', $doctorMode); // 🔥 Only check conflicts within the same mode

        // Exclude current record if updating
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        // Check other consultation types only if needed
        if ($currentType) {
            $query->where('type', '!=', $currentType);
        }

        // Check for time overlap
        $hasConflict = $query->where(function ($q) use ($startTime, $endTime) {
            $q->where('start_at', '<', $endTime)
                ->where('end_at', '>', $startTime);
        })->exists();

        if ($hasConflict) {
            \Log::info("Conflict detected for Doctor ID $doctorId, Mode: $doctorMode, Type: $currentType, Day: $day, Time: $startTime - $endTime");
        } else {
            \Log::info("✅ No conflict for Doctor ID $doctorId, Mode: $doctorMode, Type: $currentType, Day: $day, Time: $startTime - $endTime");
        }

        return $hasConflict;
    }


    public function storeSubstitute(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date_format:Y-m-d\TH:i',
                'end_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date',
                'notes' => 'nullable|string'
            ]);

            DoctorSubstitute::create([
                'doctor_id' => $doctorId,
                'name' => $validated['name'],
                'start_date' => Carbon::parse($validated['start_date']),
                'end_date' => Carbon::parse($validated['end_date']),
                'notes' => $validated['notes']
            ]);

            return redirect()->back()->with('success', trans('lang.substitute_saved_successfully'));
        } catch (\Exception $e) {
            Log::error('Error saving substitute:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function deleteSubstitute($id)
    {
        $doctorId = auth()->user()->getDoctorId();

        try {
            DoctorSubstitute::where('id', $id)
                ->where('doctor_id', $doctorId)
                ->delete();

            return redirect()->back()->with('success', 'Remplaçant supprimé avec succès!');
        } catch (\Exception $e) {
            Log::error('Error deleting substitute:', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getSubstitutesForRange(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return DoctorSubstitute::where('doctor_id', $doctorId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['id', 'name', 'start_date', 'end_date']);
    }

    public function getWeeklyAppointmentsCount(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $total = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->count();

        $completed = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('start_at', [$startDate, $endDate])
            ->where('appointment_status_id', 5) // Assuming 5 is the "completed" status
            ->count();

        return response()->json([
            'total' => $total,
            'completed' => $completed
        ]);
    }

    public function getDailyAppointmentsCount(Request $request)
    {
        $doctorId = auth()->user()->getDoctorId();
        $date = $request->date;

        $total = Appointment::where('doctor_id', $doctorId)
            ->whereDate('start_at', $date)
            ->count();

        $completed = Appointment::where('doctor_id', $doctorId)
            ->whereDate('start_at', $date)
            ->where('appointment_status_id', 5) // Assuming 5 is the "completed" status
            ->count();

        return response()->json([
            'total' => $total,
            'completed' => $completed
        ]);
    }

    public function storeClosures(Request $request)
    {
        $validated = $request->validate([
            'jour' => 'required|date|after_or_equal:today',
            'heurDebut' => 'required|date_format:H:i',
            'heurFin' => 'required|date_format:H:i|after:heurDebut',
            'reason' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $doctorId = auth()->user()->getDoctorId();

            DoctorUrgency::create([
                'doctor_id' => $doctorId,
                'jour' => $validated['jour'],
                'heurDebut' => $validated['heurDebut'],
                'heurFin' => $validated['heurFin'],
                'reason' => $validated['reason']
            ]);

            DB::commit();
            return redirect()->back()->with('success', trans('messages.closure_created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', trans('messages.closure_creation_failed'));
        }
    }

    public function getClosures()
    {

        $doctorId = auth()->user()->getDoctorId();

        $dailyClosures = DoctorUrgency::where('doctor_id', $doctorId)
            ->where('jour', '>=', now()->startOfDay())
            ->orderBy('jour', 'asc')
            ->orderBy('heurDebut', 'asc')
            ->get();

        return view('availability.index', compact('dailyClosures'));
    }
    public function destroyClosures($id, Request $request)
    {
        \Log::info('Attempting to delete closure:', ['closure_id' => $id]);

        try {
            $doctorId = auth()->user()->getDoctorId();

            $deleted = DoctorUrgency::where('doctor_id', $doctorId)
                ->where('id', $id)
                ->delete();

            \Log::info('Deletion result:', ['deleted' => $deleted]);

            if ($deleted) {
                return redirect()->back()->with('success', trans('messages.closure_deleted_successfully'));
            }

            return redirect()->back()->with('error', trans('messages.closure_not_found'));

        } catch (\Exception $e) {
            \Log::error('Deletion failed:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', trans('messages.closure_deletion_failed'));
        }
    }
    public function updateClosures($id, Request $request)
    {
        $validated = $request->validate([
            'jour' => 'required|date',
            'heurDebut' => 'required',
            'heurFin' => 'required|after:heurDebut',
            'reason' => 'required|string'
        ]);

        try {
            $closure = DoctorUrgency::where('doctor_id', auth()->user()->getDoctorId())
                ->where('id', $id)
                ->firstOrFail();

            $closure->update([
                'jour' => $validated['jour'],
                'heurDebut' => $validated['heurDebut'],
                'heurFin' => $validated['heurFin'],
                'reason' => $validated['reason']
            ]);

            return redirect()->back()->with('success', trans('messages.closure_updated_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', trans('messages.closure_update_failed'));
        }
    }
}
