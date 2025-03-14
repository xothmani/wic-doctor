<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;

class ActiveDoctor extends Component
{
    public $activeDoctor;

    public function __construct()
    {

        $user = Auth::user();
        if ($user) {
            if ($user->hasRole('Telesecretary')) {
                // For tele-secretaries, get the doctor id from the session.
                $doctorId = session('selectedDoctorId');
                \Log::info('DoctorId: ' . $doctorId);
                $this->activeDoctor = $doctorId ? Doctor::find($doctorId) : null;
                \Log::info('ActiveDoctor: ' . $doctorId);
            } else {
                // Otherwise, use the fixed doctor id associated with the user.
                $this->activeDoctor = Doctor::find($user->getDoctorId());
            }
        }
    }

    public function render()
    {
        return view('components.active-doctor');
    }
}
