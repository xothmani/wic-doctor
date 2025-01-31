<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsLatter extends Model
{
    use HasFactory;

    // Nom de la table
    protected $table = 'News_latter';

    // Clé primaire
    protected $primaryKey = 'id';

    // Désactiver les timestamps pour `updated_at` puisque cette colonne n'existe pas
    public $timestamps = true; // Cela gère `created_at` automatiquement, mais pas `updated_at`

    // Colonnes autorisées pour l'insertion
    protected $fillable = [
        'email',
    ];

}
