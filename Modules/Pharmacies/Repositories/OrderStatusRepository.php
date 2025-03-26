<?php
/*
 * File name: OrderStatusRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use InfyOm\Generator\Common\BaseRepository;
use Modules\Pharmacies\Models\OrderStatus;

/**
 * Class OrderStatusRepository
 *
 * @method OrderStatus findWithoutFail($id, $columns = ['*'])
 * @method OrderStatus find($id, $columns = ['*'])
 * @method OrderStatus first($columns = ['*'])
 */
class OrderStatusRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status',
        'order'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return OrderStatus::class;
    }
}
