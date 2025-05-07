<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorAssociate extends Model
{
    use HasFactory;

    protected $table = 'doctor_associate';

    protected $fillable = [
        'doctor_id',
        'user_id',
    ];

    /**
     * Relationship: Get the doctor for this association.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
// App\Models\DoctorAssociate.php

public function telesecretariat()
{
    return $this->belongsTo(Telesecretariat::class, 'user_id', 'id');
}
public function doctorModel()
{
    return $this->belongsTo(Doctor::class, 'doctor_id');
}
    /**
     * Relationship: Get the associated user (secretary/telesecetary).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
