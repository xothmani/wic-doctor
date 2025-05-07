<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fiche extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    // Spécifier la table si elle est au singulier
    protected $table = 'fiche';

    
    protected $fillable = [
        'patient_id',
        'code',
        'user_id',
        'numFiche'
]; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fiche) {
            // Génération d'un code unique auto-incrémenté avec format `001`, `002`, etc.
            $lastCode = self::max('code');
            $nextCode = str_pad((int)$lastCode + 1, 3, '0', STR_PAD_LEFT);
            $fiche->code = $nextCode;
        });
    }

    // Relation avec Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }


    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'fiche_code', 'code');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'fiche_id', 'code');
    }

}
