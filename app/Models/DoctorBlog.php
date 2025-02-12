<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorBlog extends Model
{
    use HasFactory;

    protected $table = 'doctor_blogs'; // Remplacez par le vrai nom de votre table si différent

    protected $fillable = [
        'titre',
        'titre_court',
        'contenu',
        'status',
        'doctor_id',
        'media_id',
    ];

    public $timestamps = true; // Laravel gère automatiquement created_at et updated_at

    /**
     * Relation avec le modèle Doctor (Un article appartient à un docteur)
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Relation avec le modèle Media (Un article peut avoir un média)
     */
    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
