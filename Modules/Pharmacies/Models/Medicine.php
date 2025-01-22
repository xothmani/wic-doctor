<?php
/*
 * File name: Medicine.php
 * Last modified: 2023.03.10 at 12:38:28
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;

use App\Traits\HasTranslations;
use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Modules\Pharmacies\Casts\MedicineCast;
use Modules\Pharmacies\Database\Factories\MedicineFactory;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Date;

/**
 * Class Medicine
 *
 * @property Collection categories
 * @property Collection forms
 * @property Pharmacy pharmacy
 * @property Collection medicineOptions
 * @property string name
 * @property string generic_name
 * @property string manufacturer
 * @property integer id
 * @property double price
 * @property double discount_price
 * @property string quantity_unit
 * @property string description
 * @property string storage_conditions
 * @property string strength
 * @property integer stock_quantity
 * @property boolean featured
 * @property boolean available
 * @property integer pharmacy_id
 * @property Date expiry_date
 */
class Medicine extends Model implements HasMedia, Castable
{
    use InteractsWithMedia {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }

    use HasTranslations;
    use HasFactory;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|max:127',
        'price' => 'required|numeric|min:0|max:99999999,99',
        'discount_price' => 'nullable|numeric|min:0|max:99999999,99',
        'description' => 'required',
        'expiry_date' => 'required|date',
        'pharmacy_id' => 'required|exists:pharmacies,id'
    ];
    public $translatable = [
        'name',
        'generic_name',
        'description',
        'quantity_unit',
        'storage_conditions',
    ];
    public $table = 'medicines';
    public $fillable = [
        'name',
        'generic_name',
        'manufacturer',
        'price',
        'discount_price',
        'quantity_unit',
        'stock_quantity',
        'description',
        'storage_conditions',
        'strength',
        'featured',
        'available',
        'expiry_date',
        'pharmacy_id'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'generic_name' => 'string',
        'manufacturer' => 'string',
        'image' => 'string',
        'stock_quantity' => 'integer',
        'price' => 'double',
        'discount_price' => 'double',
        'price_unit' => 'string',
        'description' => 'string',
        'storage_conditions' => 'string',
        'strength' => 'string',
        'featured' => 'boolean',
        'available' => 'boolean',
        'pharmacy_id' => 'integer',
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media',
        'available',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];
    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return MedicineFactory::new();
    }

    /**
     * @param array $arguments
     * @return string
     */
    public static function castUsing(array $arguments): string
    {
        return MedicineCast::class;
    }

    /**
     * @param Media|null $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->sharpen(10);

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->sharpen(10);
    }

    /**
     * to generate media url in case of fallback will
     * return the file type icon
     * @param string $collectionName
     * @param string $conversion
     * @return string url
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $conversion = ''): string
    {
        $url = $this->getFirstMediaUrlTrait($collectionName);
        $array = explode('.', $url);
        $extension = strtolower(end($array));
        if (in_array($extension, config('media-library.extensions_has_thumb'))) {
            return asset($this->getFirstMediaUrlTrait($collectionName, $conversion));
        } else {
            return asset(config('media-library.icons_folder') . '/' . $extension . '.png');
        }
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

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    /**
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia('image');
    }

    public function scopeNear($query, $latitude, $longitude)
    {
        // Calculate the distant in mile
        $distance = "SQRT(
                    POW(69.1 * (addresses.latitude - $latitude), 2) +
                    POW(69.1 * ($longitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // convert the distance to KM if the distance unit is KM
        if (setting('distance_unit') == 'km') {
            $distance .= " * 1.60934"; // 1 Mile = 1.60934 KM
        }

        return $query
            ->join('pharmacies', 'pharmacies.id', '=', 'medicines.pharmacy_id')
            ->join('pharmacy_addresses', 'pharmacy_addresses.pharmacy_id', '=', 'medicines.pharmacy_id')
            ->join('addresses', 'pharmacy_addresses.address_id', '=', 'addresses.id')
            ->whereRaw("$distance < pharmacies.availability_range")
            ->select(DB::raw($distance . " AS distance"), "medicines.*")
            ->orderBy('distance');
    }

    /**
     * Medicine available when
     * This Medicine is marked as available
     * and his
     * Pharmacy is ready, so he is accepted by admin and marked as available and is open now
     */
    public function getAvailableAttribute(): bool
    {
        return isset($this->attributes['available']) && $this->attributes['available'] && isset($this->pharmacy) && $this->pharmacy->accepted && $this->stock_quantity > 0;
    }

    /**
     * @return BelongsTo
     **/
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id', 'id');
    }

    /**
     * @return HasMany
     **/
    public function medicineOptions(): HasMany
    {
        return $this->hasMany(MedicineOption::class, 'medicine_id');
    }

    /**
     * @return BelongsToMany
     **/
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'medicine_categories');
    }


    /**
     * @return BelongsToMany
     **/

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'medicine_forms');
    }


    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price > 0;
    }
}
