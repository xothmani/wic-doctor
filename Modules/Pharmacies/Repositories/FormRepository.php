<?php
/*
 * File name: CategoryRepository.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\Form;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CategoryRepository
 *
 * @method Form findWithoutFail($id, $columns = ['*'])
 * @method Form find($id, $columns = ['*'])
 * @method Form first($columns = ['*'])
 */
class FormRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'color',
        'description',
        'featured',
        'order',
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Form::class;
    }
}
