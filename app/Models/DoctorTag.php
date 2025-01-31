<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorTag extends Model
{
    // Nom de la table dans la base de données
    protected $table = 'doctor_tags';

    // Indiquer que la table n'a pas de champ `id` auto-incrémenté
    public $incrementing = false;

    // Colonnes qui peuvent être remplies via des opérations en masse
    protected $fillable = [
        'doctor_id',
        'tag_id',

    ];
        // Désactiver les timestamps (si la table n'a pas ces colonnes)
        public $timestamps = false;

}
