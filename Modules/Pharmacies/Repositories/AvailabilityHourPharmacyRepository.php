<?php
/*
 * File name: AvailabilityHourRepository.php
 * Last modified: 2021.01.16 at 21:43:36
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace Modules\Pharmacies\Repositories;

use InfyOm\Generator\Common\BaseRepository;
use Modules\Pharmacies\Models\AvailabilityHourPharmacy;

/**
 * Class AvailabilityHourRepository
 * @package App\Repositories
 * @version January 16, 2021, 4:08 pm UTC
 *
 * @method AvailabilityHourPharmacy findWithoutFail($id, $columns = ['*'])
 * @method AvailabilityHourPharmacy find($id, $columns = ['*'])
 * @method AvailabilityHourPharmacy first($columns = ['*'])
 */
class AvailabilityHourPharmacyRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'day',
        'start_at',
        'end_at',
        'data',
        'pharmacy_id'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return AvailabilityHourPharmacy::class;
    }
}
