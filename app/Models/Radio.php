<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radio extends Model
{
    use HasFactory;


    // Vous devez définir la clé primaire si ce n'est pas l'ID par défaut
    protected $primaryKey = 'Nom';
    public $incrementing = false; // Désactiver l'incrémentation automatique
    protected $keyType = 'string'; // Indiquer que la clé est une chaîn

    protected $fillable = [
        'Nom',
        'Definition',
        'Domaine',   
    ];
    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'radio_prescription')
                    ->withTimestamps();
    }
}
