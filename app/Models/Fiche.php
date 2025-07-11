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
        $lastCode = self::orderByRaw('CAST(code AS UNSIGNED) DESC')->first()?->code ?? '0';
        $numeric = is_numeric($lastCode) ? (int)$lastCode : 0;

        $fiche->code = (string)($numeric + 1); // Pas de str_pad
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
