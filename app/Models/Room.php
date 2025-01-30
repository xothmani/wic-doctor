<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_name', 'meet_link', 'owner_id', 'appointment_id', 'patient_id', 'date', 'time', 'status'];

    // Define relationship with the User model
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'patient_id');
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}

