<?php
/*
 * File name: Category.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


/**
 * Class Category
 *

 * @property Medicine[] featuredMedicines
 * @property Medicine[] medicines
 * @property string name
 * @property string value
 * @property string unit_of_measurement
 * @property string description
 */
class Strength extends Model
{

    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|max:127',
        'value' => 'required|max:36',
        'unit_of_measurement' => 'required|max:36',
        'description' => 'nullable',
    ];
    public $translatable = [
        'name',
        'description'
    ];
    public $table = 'strengths';
    public $fillable = [
        'name',
        'value',
        'unit_of_measurement',
        'description',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'value' => 'string',
        'unit_of_measurement' => 'string',
        'description' => 'string',

    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
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

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }


    /**
     * @return BelongsToMany
     **/
    public function medicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'medicine_strengths');
    }

    /**
     * @return BelongsToMany
     **/
    public function featuredMedicines(): BelongsToMany
    {
        return $this->belongsToMany(Medicine::class, 'medicine_strengths')->where('medicines.featured', '=', true);
    }

}
