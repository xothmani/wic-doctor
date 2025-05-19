<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalizedMessage extends Model
{
    protected $table = 'personalized_message'; // nom exact de la table

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'message',
    ];

    public $timestamps = false;
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
