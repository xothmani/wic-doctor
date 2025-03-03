<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSubstitute extends Model
{
    protected $fillable = [
        'doctor_id',
        'name',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
