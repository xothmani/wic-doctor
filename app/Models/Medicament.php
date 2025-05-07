<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    // Vous devez définir la clé primaire si ce n'est pas l'ID par défaut
    protected $primaryKey = 'CODE_PCT';

    protected $fillable = [
        'CODE_PCT',
        'NOM_COMMERCIAL',
        'atc_code',
        'category',
        'form',
        'strength',
        'format',
        'company',
        'cnam',
        'ap',
        'ttc_price',

    ];

    protected $casts = [
        'CODE_PCT' => 'string',
    ];

    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'medicament_prescription')
                    ->withPivot('dosage', 'nb_de_jours', 'horaire', 'nb_de_fois')
                    ->withTimestamps();
    }
    public function drugClass()
    {
        return $this->belongsTo(DrugClass::class, 'atc_code', 'atc_code');
    }
    

}
