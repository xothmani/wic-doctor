<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientFileLog extends Model
{
    protected $fillable = [
        'patient_file_id',
        'user_id',
        'action',
    ];
}