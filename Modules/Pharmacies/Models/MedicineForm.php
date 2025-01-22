<?php
/*
 * File name: MedicineCategory.php
 * Last modified: 2023.03.01 at 21:48:23
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pharmacies\Database\Factories\MedicineFormFactory;

class MedicineForm extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'medicine_forms';

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return MedicineFormFactory::new();
    }
}
