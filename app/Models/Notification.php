<?php
/*
 * File name: Notification.php
 * Last modified: 2024.05.03 at 18:03:34
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */


namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
/**
 * Class Notification
 * @package App\Models
 * @version September 4, 2019, 10:30 am UTC
 *
 * @property User user
 * @property string type
 * @property string read
 */


class Notification extends Model
{

    public $table = 'notifications';
    protected $primaryKey = 'id'; // or null
    public $incrementing = false;



    public $fillable = [
        'notifiable_id',
        'notifiable_type',
        'data',
        'type',
        'read_at',
        'read',
        'body',
        'show_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'read_at' => 'datetime',
        'read' => 'boolean',
        'body' => 'string',
        'data' => 'array',
        'show_at' => 'datetime'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'type' => 'required',
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',

    ];

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

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


    // Générer un UUID lors de la création de l'objet
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid(); // Génère un UUID
        });
    }


    /**
     * @return BelongsTo
     **/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id', 'id');
    }

}

