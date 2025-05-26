<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentPrescription extends Model
{
    use HasFactory;

    protected $table = 'medicament_prescription';
    
    protected $fillable = [
        'prescription_id',
        'medicament_CODE_PCT',
        'dosage',
        'nb_de_jours',
        'horaire',
        'nb_de_fois',
        'medicament_id',
        'nom_medicament',
        'status_medicament'
    ];

    /**
     * Relation avec la prescription
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
/**
 * Scope pour filtrer les médicaments avec nom non null et status "en cours"
 */
public function scopeEnCours($query)
{
    return $query->whereNotNull('nom_medicament')
                ->where('status_medicament', 'en cours');
}
}