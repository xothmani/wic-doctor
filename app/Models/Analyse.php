<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analyse extends Model
{
    use HasFactory;

    // Vous devez définir la clé primaire si ce n'est pas l'ID par défaut
    protected $primaryKey = 'Code_Analyse';
    public $incrementing = false; // Désactiver l'incrémentation automatique
    protected $keyType = 'string'; // Indiquer que la clé est une chaîn

    protected $fillable = [
        'Code_Analyse',
        'Nom',
   
    ];
    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'analyse_prescription')
                    ->withTimestamps();
    }

 }
