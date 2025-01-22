<?php
/*
 * File name: PharmacyRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\Pharmacy;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class PharmacyRepository
 *
 * @method Pharmacy findWithoutFail($id, $columns = ['*'])
 * @method Pharmacy find($id, $columns = ['*'])
 * @method Pharmacy first($columns = ['*'])
 */
class PharmacyRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'pharmacy_type_id',
        'description',
        'phone_number',
        'mobile_number',
        'availability_range',
        'available',
        'featured'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Pharmacy::class;
    }
}
