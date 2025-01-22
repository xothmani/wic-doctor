<?php
/*
 * File name: PharmacyTypeRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;


use InfyOm\Generator\Common\BaseRepository;
use Modules\Pharmacies\Models\PharmacyType;

/**
 * Class PharmacyTypeRepository
 *
 * @method PharmacyType findWithoutFail($id, $columns = ['*'])
 * @method PharmacyType find($id, $columns = ['*'])
 * @method PharmacyType first($columns = ['*'])
 */
class PharmacyTypeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'commission',
        'disabled'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return PharmacyType::class;
    }
}
