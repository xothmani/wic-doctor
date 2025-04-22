<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Dans app/Models/PatientAssignedUser.php
class PatientAssignedUser extends Model
{
    protected $fillable = ['patient_id', 'user_id', 'relationship'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}