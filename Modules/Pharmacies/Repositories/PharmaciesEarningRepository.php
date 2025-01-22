<?php
/*
 * File name: PharmaciesEarningRepository.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\PharmaciesEarning;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class PharmaciesEarningRepository
 *
 * @method PharmaciesEarning findWithoutFail($id, $columns = ['*'])
 * @method PharmaciesEarning find($id, $columns = ['*'])
 * @method PharmaciesEarning first($columns = ['*'])
 */
class PharmaciesEarningRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'pharmacy_id',
        'total_orders',
        'total_earning',
        'admin_earning',
        'pharmacy_earning',
        'taxes'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return PharmaciesEarning::class;
    }
}
