<?php
/*
 * File name: MedicineCategory.php
 * Last modified: 2023.03.01 at 21:48:23
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use Eloquent as Model;

class MedicineStrength extends Model
{
    public $timestamps = false;
    protected $table = 'medicine_strengths';
}
