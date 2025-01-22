<?php

namespace App\Repositories;

use App\Models\Assurance;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class AssuranceRepository
 * @package App\Repositories
 * @version November 29, 2024
 *
 * @method Assurance findWithoutFail($id, $columns = ['*'])
 * @method Assurance find($id, $columns = ['*'])
 * @method Assurance first($columns = ['*'])
 */
class AssuranceRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',   
        'description',  
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Assurance::class;
    }

    /**
     * Optionally, you can add custom repository methods specific to the Assurance model.
     */
}
