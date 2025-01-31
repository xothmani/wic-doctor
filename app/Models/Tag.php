<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'speciality_id',
        'country'
    ];

    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = false;



    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }
}
