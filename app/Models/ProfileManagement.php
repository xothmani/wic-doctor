<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileManagement extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'profile_management';

    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'doctor_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Telesecretary belongs to a user
     */
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Doctor belongs to a doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

}
