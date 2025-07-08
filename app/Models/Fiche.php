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
        $lastCode = self::orderByDesc('code')->first()?->code ?? '0000';
        $numeric = preg_replace('/\D/', '', $lastCode);
        $numeric = $numeric !== '' ? (int)$numeric : 0;

        do {
            $numeric++;
            $code = str_pad($numeric, 4, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->exists());

        $fiche->code = $code;
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
