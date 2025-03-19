<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DoctorUrgency
 * @package App\Models
 * @version December 18, 2024
 *
 * @property Doctor doctor
 * @property string id
 * @property string jour
 * @property string heurDebut
 * @property string heurFin
 * @property integer doctor_id
 */
class DoctorUrgency extends Model
{
    use HasFactory;

    /**
     * Validation rules
     *
     * @var array
     */


    public $timestamps = false;
    public $table = 'doctor_urgency';

    protected $fillable = [
        'doctor_id',
        'reason',
        'jour',
        'heurDebut',
        'heurFin'
    ];

    /**
     * Les attributs qui doivent être castés aux types natifs.
     *
     * @var array
     */
    protected $casts = [
        'doctor_id' => 'integer',
        'jour' => 'date:Y-m-d',
        'heurDebut' => 'string', // Les heures sont traitées comme des chaînes
        'heurFin' => 'string',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relation avec le modèle Doctor
     *
     * @return BelongsTo
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}
