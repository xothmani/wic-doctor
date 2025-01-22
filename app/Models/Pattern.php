<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Pattern
 * @package App\Models
 *
 * @property string $nom
 * @property int $specialite_id
 * @property float $price
 * @property int $doctor_id
 * @property int $clinic_id
 * @property string $color
 */
class Pattern extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $table = 'pattern';

    protected $fillable = [
        'nom',
        'type',
        'specialite_id',
        'price',
        'doctor_id',
        'clinic_id',
        'color'
    ];
    protected $casts = [
        'nom' => 'string',
        'type' => 'integer',
        'specialite_id' => 'integer',
        'price' => 'float',
        'doctor_id' => 'integer',
        'clinic_id' => 'integer', // This will cast null to integer if nullable in the database
        'color' => 'string',
    ];
    public static array $rules = [
        'nom' => 'required|max:255',
        'type' => 'required',
        'specialite_id' => 'required|integer',
        'price' => 'required|numeric',
        'doctor_id' => 'required|exists:doctors,id',
        'clinic_id' => 'nullable|exists:clinics,id', // Allow nullable clinic_id
        'color' => 'required|string|max:7', // Assuming it's a hex color
    ];

    // Define relationships if needed
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'specialite_id');
    }
}
