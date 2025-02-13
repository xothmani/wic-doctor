<?php
/*
 * File name: DoctorSpeciality.php
 * Last modified: 2021.03.02 at 14:35:37
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Eloquent as Model;

class DoctorSpeciality extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'doctor_specialities';
    protected $fillable = [
     'description',
     'doctor_id',
     'speciality_id'

    ];

    public function doctor()
{
    return $this->belongsTo(Doctor::class, 'doctor_id');
}

public function speciality()
{
    return $this->belongsTo(Speciality::class, 'speciality_id');
}
}
