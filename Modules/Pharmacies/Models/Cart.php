<?php
/*
 * File name: Cart.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use App\Models\User;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Class Cart
 *
 * @property Medicine medicine
 * @property User user
 * @property Collection medicineOptions
 * @property integer medicine_id
 * @property integer user_id
 * @property integer quantity
 */
class Cart extends Model
{

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'medicine_id' => 'required|exists:medicines,id',
        'user_id' => 'required|exists:users,id'
    ];
    public $table = 'carts';
    public $fillable = [
        'medicine_id',
        'user_id',
        'quantity'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'medicine_id' => 'integer',
        'user_id' => 'integer',
        'quantity' => 'integer'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields'
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
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'id');
    }

    /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsToMany
     **/
    public function medicineOptions(): BelongsToMany
    {
        return $this->belongsToMany(MedicineOption::class, 'cart_medicine_options');
    }
}
