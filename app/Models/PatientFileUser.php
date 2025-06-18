<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientFileUser extends Model
{
    protected $table = 'patient_file_users';

    protected $fillable = [
        'patient_file_id',
        'patient_id',
        'user_id',
        'expiration_date',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    public function patientFile()
    {
        return $this->belongsTo(PatientFile::class, 'patient_file_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }
}