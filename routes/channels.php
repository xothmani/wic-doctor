<?php
/**
 * File name: channels.php
 * Last modified: 2019.08.27 at 15:37:13
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
// Broadcast::channel('chat.doctor.{doctorId}', function ($user, $doctorId) {
//     return (int) $user->id === (int) $doctorId;
// });

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('appointments', function ($user) {
    return true; // Adjust this if you need to restrict access
});


Broadcast::channel('appointments.{doctorId}', function ($user, $doctorId) {
    \Log::info('Checking appointment channel access', [
        'user_id' => $user->id,
        'doctor_id' => $doctorId,
        'user_roles' => $user->roles->pluck('name'),
    ]);

    // Allow the doctor to listen to their own appointments
    if ($user->hasRole('doctor')) {
        $doctor = $user->doctor; // Get the doctor record associated with the user

        if ($doctor && $doctor->id == $doctorId) {
            \Log::info('Authorization granted for doctor', [
                'user_id' => $user->id,
                'doctor_id' => $doctorId,
            ]);
            return true;
        }
    }

    // Allow the secretary to listen to appointments for their associated doctor
    if ($user->hasRole('Secretary')) {
        $associatedDoctor = $user->associatedDoctors()->where('doctor_id', $doctorId)->first();

        \Log::info('Secretary association check', [
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
            'is_associated' => $associatedDoctor !== null,
        ]);

        return $associatedDoctor !== null;
    }

    // Allow telesecretaries to listen to appointments for their selected doctor
    if ($user->hasRole('Telesecretary')) {
        $isSelectedDoctor = session('selectedDoctorId') == $doctorId;

        \Log::info('Telesecretary selection check', [
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
            'selected_doctor_id' => session('selectedDoctorId'),
            'is_selected' => $isSelectedDoctor,
        ]);

        return $isSelectedDoctor;
    }

    \Log::warning('Authorization denied', [
        'user_id' => $user->id,
        'doctor_id' => $doctorId,
    ]);

    return false; // Deny access for all other cases
});