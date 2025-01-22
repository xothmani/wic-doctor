<?php
/*
 * File name: PharmaciesEarning.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Earning
 *
 * @property Pharmacy pharmacy
 * @property integer pharmacy_id
 * @property integer total_orders
 * @property double total_earning
 * @property double admin_earning
 * @property double pharmacy_earning
 * @property double taxes
 */
class PharmaciesEarning extends Model
{

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'pharmacy_id' => 'required|exists:pharmacies,id'
    ];
    public $table = 'pharmacies_earnings';
    public $fillable = [
        'pharmacy_id',
        'total_orders',
        'total_earning',
        'admin_earning',
        'pharmacy_earning',
        'taxes'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'pharmacy_id' => 'integer',
        'total_orders' => 'integer',
        'total_earning' => 'double',
        'admin_earning' => 'double',
        'pharmacy_earning' => 'double',
        'taxes' => 'double'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',

    ];

    public function getCustomFieldsAttribute(): array
    {
        $hasCustomField = in_array(static::class, setting('custom_field_models', []));
        if (!$hasCustomField) {
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields', 'custom_fields.id', '=', 'custom_field_values.custom_field_id')
            ->where('custom_fields.in_table', '=', true)
            ->get()->toArray();

        return convertToAssoc($array, 'name');
    }

    public function customFieldsValues(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * @return BelongsTo
     **/
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id', 'id');
    }

}
