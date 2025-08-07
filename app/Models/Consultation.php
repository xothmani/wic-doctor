<?php
/*
 * File name: Consultation.php
 * Author: Ton Nom
 * Copyright (c) 2024
 */

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Consultation
 * @package App\Models
 *
 * @property int id
 * @property string dateConsultation
 * @property string raison
 * @property string|null motif
 * @property int patient_id
 * @property Patient patient
 * @property BigInt user_id
 * @property User user

 */

class Consultation extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'dateConsultation' => 'required|date',
        'raison' => 'required|string|max:1000',
        'motif' => 'required|string|max:1000',
        'patient_id' => 'required|exists:patients,id',
        'user_id' => 'required|exists:users,id', 


    ];

    protected $table = 'consultations'; // Nom de la table

    protected $fillable = [
        'dateConsultation',
        'raison',
        'motif',
        'patient_id',
        'user_id',
        'fiche_code',  
        'duree'

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return BelongsTo
     **/
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

        /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function fiche()
    {
        return $this->belongsTo(Fiche::class, 'patient_id', 'patient_id');
    }
    

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

}
