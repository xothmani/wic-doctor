<?php
/*
 * File name: CartRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use InfyOm\Generator\Common\BaseRepository;
use Modules\Pharmacies\Models\Cart;

/**
 * Class CartRepository
 *
 * @method Cart findWithoutFail($id, $columns = ['*'])
 * @method Cart find($id, $columns = ['*'])
 * @method Cart first($columns = ['*'])
 */
class CartRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'medicine_id',
        'user_id',
        'quantity'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Cart::class;
    }
}
