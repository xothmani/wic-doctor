<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'observation',
        'type',
        'consultation_id',
        'pdf',
        'pdfForMail',

    ];

    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'medicament_prescription')
                    ->withPivot('dosage', 'nb_de_jours', 'horaire', 'nb_de_fois')
                    ->withTimestamps();
    }
    public function analyses()
    {
        return $this->belongsToMany(Analyse::class, 'analyse_prescription')
                    ->withTimestamps();
    }
    public function radios()
    {
        return $this->belongsToMany(Radio::class, 'radio_prescription')
                    ->withTimestamps();
    }


public function items()
{
    return $this->hasMany(PrescriptionItem::class);
}
public function consultation()
{
    return $this->belongsTo(Consultation::class, 'consultation_id');
}

public function prescriptionItems()
{
    return $this->hasMany(PrescriptionItem::class);
}

    
}
