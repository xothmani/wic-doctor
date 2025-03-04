<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DoctorSubstitute extends Model
{
    protected $fillable = [
        'doctor_id',
        'name',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Accessor to format dates for UI display
    public function getFormattedStartDateAttribute()
    {
        return Carbon::parse($this->start_date)->format('d/m/Y H:i');
    }

    public function getFormattedEndDateAttribute()
    {
        return Carbon::parse($this->end_date)->format('d/m/Y H:i');
    }
}
