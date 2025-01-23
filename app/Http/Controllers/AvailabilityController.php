<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\AvailabilityHour;
use Carbon\Carbon;



class AvailabilityController extends Controller
{
    public function index()
    {
        $doctorId = auth()->user()->getDoctorId();
        Log::info("Retrieving doctor ID: {$doctorId}");

        if (!$doctorId) {
            return redirect()->route('users.profile'); // Redirect if no doctor found
        }


        // Récupérer les horaires de disponibilité du médecin où online = 0
        $availability = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 0) // Ajouter cette condition
            ->get()->map(function ($dayData) {
                return [
                    'day' => $dayData->day,
                    'is_available' => $dayData->is_available,
                    'start_at' => $dayData->start_at ? Carbon::parse($dayData->start_at)->format('H:i') : '09:00',
                    'end_at' => $dayData->end_at ? Carbon::parse($dayData->end_at)->format('H:i') : '17:00',
                ];
            });

        // Récupérer session_duration depuis une ligne d'AvailabilityHour où online = 0
        $sessionDuration = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 0) // Ajouter cette condition
            ->value('session_duration') ?? 15; // Par défaut : 15 minutes

        // Convertir session_duration en format hh:mm
        $hours = intdiv($sessionDuration, 60);
        $minutes = $sessionDuration % 60;
        $sessionDurationFormatted = sprintf('%02d:%02d', $hours, $minutes);

        // Récupérer les pauses où online = 0
        $pauseFrom = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 0) // Ajouter cette condition
            ->value('pause_from');
        $pauseTo = AvailabilityHour::where('doctor_id', $doctorId)
            ->where('onligne', 0) // Ajouter cette condition
            ->value('pause_to');

        $pauseFrom = $pauseFrom ? Carbon::parse($pauseFrom)->format('H:i') : null;
        $pauseTo = $pauseTo ? Carbon::parse($pauseTo)->format('H:i') : null;

        return view('availability.index', compact('availability', 'sessionDurationFormatted', 'pauseFrom', 'pauseTo'));
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
                        ->where('onligne', 1)
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
                    'onligne' => 0, // Ajouter la colonne 'online' avec la valeur 0
                ];

                $existing = DB::table('availability_hours')
                    ->where('doctor_id', $doctorId)
                    ->where('day', $day)
                    ->where('onligne', 0) // Vérification de `onligne = 0`
                    ->first();

                if ($existing) {
                    DB::table('availability_hours')->where('id', $existing->id)->update($data);
                } else {
                    DB::table('availability_hours')->insert($data);
                }
            }

            return redirect()->route('availability.index')->with('success', 'Disponibilité sauvegardée avec succès !');
        } catch (\Exception $e) {
            Log::error('Error saving availability:', ['error' => $e->getMessage()]);
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




    /* 
    protected function processAllDaysAvailability($validated)
        {
            DB::transaction(function () use ($validated) {
                $doctorId = 64; // Replace with dynamic doctor ID if needed
                $perPatientTime = $validated['per_patient_time'];
                $perPatientMinutes = Carbon::createFromFormat('H:i', $perPatientTime)->hour * 60 +
                    Carbon::createFromFormat('H:i', $perPatientTime)->minute;
        
                foreach ($validated['availability'] as $availability) {
                    $dayName = ucfirst($availability['day']);
                    $isAvailable = $availability['is_available'] ?? 0;
        
                    // Delete existing slots for this day
                    DB::table('availability_hours')
                        ->where('doctor_id', $doctorId)
                        ->where('day', $dayName)
                        ->delete();
        
                    if (!$isAvailable) {
                        Log::info("Day {$dayName} marked as unavailable.");
                        continue;
                    }
        
                    $start = Carbon::createFromTimeString($availability['from']);
                    $end = Carbon::createFromTimeString($availability['to']);
        
                    while ($start->lt($end)) {
                        $slotEnd = $start->copy()->addMinutes($perPatientMinutes);
        
                        if ($slotEnd->gt($end)) {
                            $slotEnd = $end;
                        }
        
                        DB::table('availability_hours')->insert([
                            'day' => $dayName,
                            'data' => json_encode(['status' => 'Disponible']),
                            'doctor_id' => $doctorId,
                            'start_at' => $start->toDateTimeString(),
                            'end_at' => $slotEnd->toDateTimeString(),
                            'patern_id' => 8,
                            'is_available' => $isAvailable,
                        ]);
        
                        Log::info('Inserted new availability:', [
                            'day' => $dayName,
                            'start_at' => $start->toDateTimeString(),
                            'end_at' => $slotEnd->toDateTimeString(),
                        ]);
        
                        $start = $slotEnd; // Move to the next slot
                    }
                }
            });
    }
         */


    /* protected function processSpecificDayAvailability($validated)
    {
        DB::transaction(function () use ($validated) {
            // Logic to handle availability for a specific day
            $doctorId = 64; // Replace with actual doctor ID
            $start = Carbon::createFromFormat('Y-m-d H:i', $validated['specific_date'] . ' ' . $validated['specific_from']);
            $end = Carbon::createFromFormat('Y-m-d H:i', $validated['specific_date'] . ' ' . $validated['specific_to']);

            // Update or insert for the specific day
            DB::table('availability_hours')->updateOrInsert(
                [
                    'doctor_id' => $doctorId,
                    'start_at' => $start->toDateTimeString(),
                    'end_at' => $end->toDateTimeString(),
                ],
                [
                    'day' => ucfirst($start->format('l')),
                    'data' => json_encode(['status' => 'Disponible']),
                    'patern_id' => 8,
                ]
            );
        });
    }
     */



    /*     public function storeBreaks(Request $request)
        {
            try {
                Log::info('Form Data for Breaks:', $request->all());
        
                // Validate input
                if ($request->break_type === 'single_day') {
                    // Validation for Single Day
                    $validated = $request->validate([
                        'break_type' => 'required|string|in:single_day,every_day',
                        'break_date' => 'required|date|after_or_equal:today', // Specific date
                        'breaks.from' => 'required|date_format:H:i',
                        'breaks.to' => 'required|date_format:H:i|after:breaks.from',
                    ]);
        
                    $breakDate = $validated['break_date'];
                    $from = $validated['breaks']['from'];
                    $to = $validated['breaks']['to'];
        
                    DB::transaction(function () use ($breakDate, $from, $to) {
                        $availabilityHours = DB::table('availability_hours')
                            ->where('doctor_id', 64) // Replace with dynamic doctor ID if necessary
                            ->whereDate('start_at', $breakDate)
                            ->get();
        
                        foreach ($availabilityHours as $availability) {
                            // Check if a break already exists for this day and time range
                            $exists = DB::table('availability_breaks')
                                ->where('doctor_id', $availability->doctor_id)
                                ->where('day', $availability->day)
                                ->where('start_at', $breakDate . ' ' . $from . ':00')
                                ->where('end_at', $breakDate . ' ' . $to . ':00')
                                ->exists();
        
                            if (!$exists) {
                                DB::table('availability_breaks')->insert([
                                    'availability_id' => $availability->id,
                                    'doctor_id' => $availability->doctor_id,
                                    'day' => $availability->day,
                                    'start_at' => $breakDate . ' ' . $from . ':00',
                                    'end_at' => $breakDate . ' ' . $to . ':00',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    });
        
                    Log::info('Single Day Breaks saved successfully.');
                } elseif ($request->break_type === 'every_day') {
                    // Validation for Every Day
                    $validated = $request->validate([
                        'break_type' => 'required|string|in:single_day,every_day',
                        'breaks.from' => 'required|date_format:H:i',
                        'breaks.to' => 'required|date_format:H:i|after:breaks.from',
                    ]);
        
                    $from = $validated['breaks']['from'];
                    $to = $validated['breaks']['to'];
        
                    DB::transaction(function () use ($from, $to) {
                        $availabilityHours = DB::table('availability_hours')
                            ->where('doctor_id', 64) // Replace with dynamic doctor ID if necessary
                            ->get();
        
                        $processedDays = []; // Track processed days to prevent duplicate breaks
        
                        foreach ($availabilityHours as $availability) {
                            $day = $availability->day;
        
                            if (!in_array($day, $processedDays)) {
                                $availabilityDate = Carbon::createFromFormat('Y-m-d H:i:s', $availability->start_at)->format('Y-m-d');
                                $breakStart = $availabilityDate . ' ' . $from . ':00';
                                $breakEnd = $availabilityDate . ' ' . $to . ':00';
        
                                // Check if a break already exists for this day
                                $exists = DB::table('availability_breaks')
                                    ->where('doctor_id', $availability->doctor_id)
                                    ->where('day', $day)
                                    ->where('start_at', $breakStart)
                                    ->where('end_at', $breakEnd)
                                    ->exists();
        
                                if (!$exists) {
                                    DB::table('availability_breaks')->insert([
                                        'availability_id' => $availability->id,
                                        'doctor_id' => $availability->doctor_id,
                                        'day' => $day,
                                        'start_at' => $breakStart,
                                        'end_at' => $breakEnd,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
        
                                    $processedDays[] = $day; // Mark this day as processed
                                }
                            }
                        }
                    });
        
                    Log::info('Every Day Breaks saved successfully.');
                }
        
                return redirect()->route('availability.index')->with('success', 'Breaks saved successfully!');
            } catch (\Exception $e) {
                Log::error('Error saving breaks:', ['error' => $e->getMessage()]);
                return back()->with('error', 'An error occurred. Please try again.')->withInput();
            }
        } */



    /* 
        public function storeHolidays(Request $request)
        {
            try {
                Log::info('Form Data for Holidays:', $request->all());
        
                // Validate the input
                $validated = $request->validate([
                    'holiday.date' => 'required|date|after_or_equal:today',
                    'holiday.reason' => 'nullable|string|max:255',
                ]);
        
                Log::info('Validated Holiday Data:', $validated);
        
                DB::transaction(function () use ($validated) {
                    $holidayDate = $validated['holiday']['date'];
                    $reason = $validated['holiday']['reason'] ?? null;
                    $doctorId = auth()->user()->id; // Replace with dynamic doctor ID if necessary
        
                    // Step 1: Insert the holiday into the `doctor_holidays` table
                    DB::table('doctor_holidays')->insert([
                        'doctor_id' => 64,
                        'date' => $holidayDate,
                        'reason' => $reason,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
        
                    Log::info('Holiday saved successfully for date:', ['date' => $holidayDate]);
        
                    // Step 2: Retrieve all availability IDs for the holiday date
                    $availabilityIds = DB::table('availability_hours')
                        ->where('doctor_id', 64)
                        ->whereDate('start_at', $holidayDate)
                        ->pluck('id');
        
                    Log::info('Availability IDs to be deleted:', ['ids' => $availabilityIds]);
        
                    // Step 3: Delete breaks first (to satisfy foreign key constraints)
                    if ($availabilityIds->isNotEmpty()) {
                        $deletedBreaksCount = DB::table('availability_breaks')
                            ->whereIn('availability_id', $availabilityIds)
                            ->delete();
        
                        Log::info('Deleted Breaks Count:', ['count' => $deletedBreaksCount]);
                    }
        
                    // Step 4: Delete availabilities for the holiday date
                    $deletedAvailabilitiesCount = DB::table('availability_hours')
                        ->whereIn('id', $availabilityIds)
                        ->delete();
        
                    Log::info('Deleted Availabilities Count:', ['count' => $deletedAvailabilitiesCount]);
                });
        
                return redirect()->route('availability.index')->with('success', 'Holiday added and associated availabilities and breaks deleted successfully!');
            } catch (\Exception $e) {
                Log::error('Error saving holiday and deleting availabilities:', ['error' => $e->getMessage()]);
                return back()->with('error', 'An error occurred. Please try again.')->withInput();
            }
        }
         */








}