<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telesecretariat extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'telesecretariat';

    /**
     * Les attributs qui peuvent être remplis par l'utilisateur.
     *
     * @var array
     */
    protected $fillable = [
        'nomCentre',
        'adresse',
        'etat',
        'description',
        'user_id',
        'host',
        'username',
        'password',

    ];

    /**
     * Relation : Un télésecrétariat appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    } 
    
    
    public function doctorTelesecretariats()
    {
        return $this->hasMany(DoctorTelesecretariat::class, 'telesecretariat_id');
    }
}
