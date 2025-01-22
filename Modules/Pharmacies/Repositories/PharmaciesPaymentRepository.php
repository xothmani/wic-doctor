<?php
/*
 * File name: PharmaciesPaymentRepository.php
 * Last modified: 2023.03.12 at 12:43:53
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use App\Repositories\PaymentRepository;
use Modules\Pharmacies\Models\PharmaciesPayment;

/**
 * @method PharmaciesPayment findWithoutFail($id, $columns = ['*'])
 * @method PharmaciesPayment find($id, $columns = ['*'])
 * @method PharmaciesPayment first($columns = ['*'])
 */
class PharmaciesPaymentRepository extends PaymentRepository
{
    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return PharmaciesPayment::class;
    }
}
