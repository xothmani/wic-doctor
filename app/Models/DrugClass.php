<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugClass extends Model
{
    use HasFactory;

    protected $table = 'drug_classes';

    protected $fillable = [
        'atc_code',
        'name',
        'dci_code',
        'level_1',
        'level_2',
        'level_3',
    ];
}
