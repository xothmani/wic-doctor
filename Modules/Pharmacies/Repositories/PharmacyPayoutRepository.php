<?php
/*
 * File name: PharmacyPayoutRepository.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use InfyOm\Generator\Common\BaseRepository;
use Modules\Pharmacies\Models\PharmacyPayout;

/**
 * Class PharmacyPayoutRepository
 *
 * @method PharmacyPayout findWithoutFail($id, $columns = ['*'])
 * @method PharmacyPayout find($id, $columns = ['*'])
 * @method PharmacyPayout first($columns = ['*'])
 */
class PharmacyPayoutRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'pharmacy_id',
        'method',
        'amount',
        'paid_date',
        'note'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return PharmacyPayout::class;
    }
}
