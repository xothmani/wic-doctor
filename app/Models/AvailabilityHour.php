<?php
/*
 * File name: AvailabilityHour.php
 * Last modified: 2021.04.12 at 09:20:07
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class AvailabilityHour
 * @package App\Models
 * @version January 16, 2021, 4:08 pm UTC
 *
 * @property Doctor doctor
 * @property string id
 * @property string day
 * @property string start_at
 * @property string end_at
 * @property string data
 * @property integer doctor_id
 * @property integer patern_id
 */
class AvailabilityHour extends Model
{
    use HasFactory;
    use HasTranslations;

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'data' => 'max:255',
        'doctor_id' => 'required|exists:doctors,id',
        'patern_id' => 'required|exists:pattern,id',
    ];
    public array $translatable = [
        'data',
    ];
    public $timestamps = false;
    public $table = 'availability_hours';
    public $fillable = [
        'doctor_id',
        'day',
        'type',
        'start_at',
        'end_at',
        'patern_id',
        'session_duration',
        'is_available',
        'mode'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'day' => 'string',
        'start_at' => 'datetime:d-m-Y H:i:s',
        'end_at' => 'datetime:d-m-Y H:i:s',
        'data' => 'string',
        'doctor_id' => 'integer',
        'patern_id' => 'integer',
        'session_duration' => 'integer'
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

    /**
     * @return array
     */
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

    /**
     * @return MorphMany
     */
    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * @return BelongsTo
     **/
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
    /**
     * @return BelongsTo
     **/
    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class, 'patern_id', 'id');
    }

}
