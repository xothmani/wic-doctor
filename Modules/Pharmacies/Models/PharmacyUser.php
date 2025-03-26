<?php
/*
 * File name: PharmacyUser.php
 * Last modified: 2023.02.28 at 22:44:49
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Eloquent as Model;
use Modules\Pharmacies\Database\Factories\PharmacyUserFactory;

class PharmacyUser extends Model
{

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return PharmacyUserFactory::new();
    }

    use HasFactory;
    public $table = 'pharmacy_users';
    public $timestamps = false;
}
