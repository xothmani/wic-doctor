<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorDiplome extends Model
{
    use HasFactory;

    protected $table = 'doctor_diplomes';

    protected $primaryKey = 'id';  

    public $timestamps = false;

    protected $fillable = [
        'name',        // Nom du diplôme
        'doctor_id',   // ID du docteur lié au diplôme
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
