<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorTelesecretariat extends Model
{
    // Nom de la table dans la base de données
    protected $table = 'doctor_telesecretariat';
    
    // Indiquer que la table n'a pas de champ `id` auto-incrémenté
    public $incrementing = false;

    // Colonnes qui peuvent être remplies via des opérations en masse
    protected $fillable = [
        'doctor_id',
        'telesecretariat_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Relation avec le modèle Doctor
     */
// Dans le modèle DoctorTelesecretariat
public function doctor()
{
    return $this->belongsTo(Doctor::class, 'doctor_id');
}

    protected $primaryKey = ['doctor_id', 'telesecretariat_id'];
    /**
     * Relation avec le modèle Telesecretariat
     */


     public function doctorTelesecretariats()
     {
         return $this->hasMany(DoctorTelesecretariat::class, 'doctor_id');
     }
 
public function telesecretariat()
{
    return $this->belongsTo(Telesecretariat::class, 'telesecretariat_id');
}
    
}
