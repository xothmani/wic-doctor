<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentFrance extends Model
{
    use HasFactory;

    protected $table = 'fr_medicament'; // Spécification du nom de la table

    protected $fillable = [
        'id',
        'name',
        'refund_rate',
        'list',
        'has_published_doc',
        'drug_in_sport',
        'midwife',
        'exceptional_prescription',
        'dci',
        'cis',
        'galenic_form',
        'securisable',
        'active_principles',
        'has_interactions',
        'best_doc_type',
        'item_type',
        'has_image',
    ];

    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'medicament_prescription')
                    ->withPivot('dosage', 'nb_de_jours', 'horaire', 'nb_de_fois')
                    ->withTimestamps();
    }
}
