<?php
/*
 * File name: PharmacyPayout.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * Class PharmacyPayout
 *
 * @property Pharmacy pharmacy
 * @property integer pharmacy_id
 * @property string method
 * @property double amount
 * @property Date paid_date
 * @property string note
 */
class PharmacyPayout extends Model
{

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'pharmacy_id' => 'required|exists:pharmacies,id',
        'method' => 'required',
        'amount' => 'required|numeric|min:0.01|max:99999999,99'
    ];
    public $table = 'pharmacy_payouts';
    public $fillable = [
        'pharmacy_id',
        'method',
        'amount',
        'paid_date',
        'note'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'pharmacy_id' => 'integer',
        'method' => 'string',
        'amount' => 'double',
        'paid_date' => 'datetime',
        'note' => 'string'
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
