<?php
/*
 * File name: OrderRepository.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\Order;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class OrderRepository
 *
 * @method Order findWithoutFail($id, $columns = ['*'])
 * @method Order find($id, $columns = ['*'])
 * @method Order first($columns = ['*'])
 */
class OrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'pharmacy',
        'medicine',
        'medicine_options',
        'user_id',
        'order_status_id',
        'address',
        'payment_id',
        'taxes',
        'order_at',
        'note'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Order::class;
    }
}
