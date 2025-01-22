<?php
/*
 * File name: MedicineOptionRepository.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\MedicineOption;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class MedicineOptionRepository
 *
 * @method MedicineOption findWithoutFail($id, $columns = ['*'])
 * @method MedicineOption find($id, $columns = ['*'])
 * @method MedicineOption first($columns = ['*'])
 */
class MedicineOptionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
        'price',
        'medicine_id',
        'medicine_option_group_id'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return MedicineOption::class;
    }
}
