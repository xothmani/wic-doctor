<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorRequest extends Model
{
    use HasFactory;

    // Définir la table associée
    protected $table = 'doctor_requests_b2b';

    // Colonnes autorisées pour l'attribution de masse
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'Phone',
        'speciality_id', 
        'description', 
        'adresse',
        'pays',
        'departement',
        'region',
        'gouvernorat',
        'ville',
        'type',
        'status',
	'sexe',
        'created_at',
        'updated_at',
    ];

public function speciality()
{
    return $this->belongsTo(Speciality::class);
}


}
