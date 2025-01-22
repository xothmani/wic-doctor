<?php
/*
 * File name: PharmaciesPayment.php
 * Last modified: 2023.03.11 at 22:38:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use App\Models\User;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class PharmaciesPayment
 * @package App\Models
 * @version January 7, 2021, 4:54 pm UTC
 *
 * @property Order order
 */
class PharmaciesPayment extends Payment
{
    /**
     * @return HasOne
     **/
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'payment_id', 'id');
    }

}
