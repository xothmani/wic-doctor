<?php
/*
 * File name: MedicineRepository.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Repositories;

use Modules\Pharmacies\Models\Medicine;
use InfyOm\Generator\Common\BaseRepository;
use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class MedicineRepository
 *
 * @method Medicine findWithoutFail($id, $columns = ['*'])
 * @method Medicine find($id, $columns = ['*'])
 * @method Medicine first($columns = ['*'])
 */
class MedicineRepository extends BaseRepository implements CacheableInterface
{

    use CacheableRepository;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'generic_name',
        'manufacturer',
        'price',
        'discount_price',
        'quantity_unit',
        'strength',
        'description',
        'storage_conditions',
        'featured',
        'available',
        'stock_quantity',
        'expiry_date',
        'pharmacy_id'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Medicine::class;
    }

    /**
     * @return array
     */
    public function groupedByPharmacies(): array
    {
        $medicines = [];
        foreach ($this->all() as $model) {
            if (!empty($model->pharmacy)) {
                $medicines[$model->pharmacy->name][$model->id] = $model->name;
            }
        }
        return $medicines;
    }
}
