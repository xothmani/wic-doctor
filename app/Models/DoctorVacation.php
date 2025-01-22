<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DoctorVacation
 * @package App\Models
 * @version December 18, 2024
 *
 * @property Doctor doctor
 * @property string id
 * @property string type
 * @property string raison
 * @property string dateDebut
 * @property string dateFin
 * @property integer doctor_id
 */
class DoctorVacation extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'type' => 'required|in:journée,période',
        'raison' => 'nullable|max:255',
        'dateDebut' => 'required|date_format:Y-m-d',
        'dateFin' => 'nullable|date_format:Y-m-d|required_if:type,période', // Date de fin obligatoire uniquement si 'type' est 'période'
    ];

    public array $translatable = [
        'raison', // raison peut être traduit
    ];

    public $timestamps = false;
    public $table = 'vacance';
    
    protected $fillable = [
        'type',
        'raison',
        'dateDebut',
        'dateFin',
        'doctor_id',
    ];

    /**
     * Les attributs qui doivent être castés aux types natifs.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'raison' => 'string',
        'dateDebut' => 'date:Y-m-d',
        'dateFin' => 'date:Y-m-d',
        'doctor_id' => 'integer',
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
