<?php
namespace App\Repositories;

use App\Models\Pattern;
use InfyOm\Generator\Common\BaseRepository;

class PatternRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'nom',
        'type',
        'specialite_id',
        'price',
        'doctor_id',
        'clinic_id',
        'color'
    ];

    public function model(): string
    {
        return Pattern::class;
    }
}
