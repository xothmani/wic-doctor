<?php
/*
 * File name: MedicineOptionGroupRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\MedicineOptionGroup;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class MedicineOptionGroupRepository
 *
 * @method MedicineOptionGroup findWithoutFail($id, $columns = ['*'])
 * @method MedicineOptionGroup find($id, $columns = ['*'])
 * @method MedicineOptionGroup first($columns = ['*'])
 */
class MedicineOptionGroupRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'allow_multiple'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return MedicineOptionGroup::class;
    }
}
