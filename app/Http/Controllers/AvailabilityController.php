<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\Pattern;
use App\Models\AvailabilityHour;
use Carbon\Carbon;



class AvailabilityController extends Controller
{
    public function index()
    {
        $doctorId = auth()->user()->getDoctorId();
        if (!$doctorId) {
            return redirect()->route('users.profile');
        }

        $doctor = auth()->user()->doctor;
        $currentMode = $doctor->availability_mode;

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

                // Transform data for each day
                $days = [
                    'monday' => ['is_available' => 0],
                    'tuesday' => ['is_available' => 0],
                    'wednesday' => ['is_available' => 0],
                    'thursday' => ['is_available' => 0],
                    'friday' => ['is_available' => 0],
                    'saturday' => ['is_available' => 0],
                    'sunday' => ['is_available' => 0]
                ];

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

            // Get session duration
            $sessionDuration = AvailabilityHour::where('doctor_id', $doctorId)
                ->where('mode', 'open')
                ->where('type', 'cabinet')
                ->value('session_duration') ?? 15;

            // Convert session_duration to hh:mm format
            $hours = intdiv($sessionDuration, 60);
            $minutes = $sessionDuration % 60;
            $sessionDurationFormatted = sprintf('%02d:%02d', $hours, $minutes);

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

            return view('availability.index', compact(
                'availabilities',
                'sessionDurationFormatted',
                'currentMode',
                'doctorPatterns',
                'breakTime',
                'vacations'
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
                'Téléconsultation' => [],
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

            // For debugging
            \Log::info('Doctor Patterns:', ['patterns' => $doctorPatterns->toArray()]);
            return view('availability.index', compact(
                'availabilities',
                'currentMode',
                'days',
                'vacations',
                'doctorPatterns'
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
        $doctorId = auth()->user()->getDoctorId();
        $type = $request->input('type', 'cabinet');

        try {
            $validated = $request->validate([
                'type' => 'required|in:cabinet,Téléconsultation,home_visit',
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

    public function storeOpen(Request $request)
    {
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
                'session_duration' => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
                'availability' => 'required|array',
                'availability.*.day' => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
                'availability.*.from' => 'required_with:availability.*.is_available|date_format:H:i',
                'availability.*.to' => 'required_with:availability.*.is_available|date_format:H:i|after:availability.*.from',
                'availability.*.pause_from' => 'nullable|required_with:availability.*.pause_to|date_format:H:i',
                'availability.*.pause_to' => 'nullable|required_with:availability.*.pause_from|date_format:H:i|after:availability.*.pause_from',
                'availability.*.is_available' => 'nullable|boolean',
            ], [
                'availability.*.pause_from.required_with' => 'L\'heure de début de pause est requise si l\'heure de fin est remplie',
                'availability.*.pause_to.required_with' => 'L\'heure de fin de pause est requise si l\'heure de début est remplie',
                'availability.*.pause_to.after' => 'L\'heure de fin de pause doit être après l\'heure de début'
            ]);

            Log::info('Validated Open Mode Data with breaks:', ['validated' => $validated]);

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

                    Log::info("Created availability with breaks", [
                        'day' => $data['day'],
                        'pause_from' => $pauseFrom,
                        'pause_to' => $pauseTo
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Disponibilité et pauses sauvegardées avec succès !');
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

}
