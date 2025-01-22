<?php
/*
 * File name: Pharmacy.php
 * Last modified: 2023.03.10 at 12:38:29
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2023
 */

namespace Modules\Pharmacies\Models;


use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Pharmacies\Casts\PharmacyCast;
use Modules\Pharmacies\Database\Factories\PharmacyFactory;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\Facades\Module;
use App\Models\Address;
use App\Models\Tax;
use App\Models\User;
use App\Traits\HasTranslations;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\OpeningHours\OpeningHours;


/**
 * Class Pharmacy
 *
 * @property PharmacyType pharmacyType
 * @property Collection[] users
 * @property Collection[] taxes
 * @property Collection[] addresses
 * @property Collection[] awards
 * @property Collection[] experiences
 * @property Collection[] availabilityHoursPharmacies
 * @property Collection pharmacySubscriptions
 * @property Collection[] availabilityHours
 * @property Collection[] medicines
 * @property Collection[] galleries
 * @property integer id
 * @property string name
 * @property integer pharmacy_type_id
 * @property string description
 * @property string phone_number
 * @property string mobile_number
 * @property double availability_range
 * @property boolean available
 * @property boolean featured
 * @property boolean accepted
 */
class Pharmacy extends Model implements HasMedia, Castable
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
        'pharmacy_type_id' => 'required|exists:pharmacy_types,id',
        'phone_number' => 'max:50',
        'mobile_number' => 'max:50',
        'availability_range' => 'required|numeric|max:9999999.99|min:0.01'
    ];
    public $translatable = [
        'name',
        'description',
    ];
    public $table = 'pharmacies';
    public $fillable = [
        'name',
        'pharmacy_type_id',
        'description',
        'phone_number',
        'mobile_number',
        'availability_range',
        'available',
        'featured',
        'accepted'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'image' => 'string',
        'name' => 'string',
        'pharmacy_type_id' => 'integer',
        'description' => 'string',
        'phone_number' => 'string',
        'mobile_number' => 'string',
        'availability_range' => 'double',
        'available' => 'boolean',
        'featured' => 'boolean',
        'accepted' => 'boolean'
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
     * @param array $arguments
     * @return string
     */
    public static function castUsing(array $arguments): string
    {
        return PharmacyCast::class;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory
     */
    protected static function newFactory(): Factory
    {
        return PharmacyFactory::new();
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
    public function scopeNear($query, $latitude, $longitude, $areaLatitude, $areaLongitude)
    {
        // Calculate the distant in mile
        $distance = "SQRT(
                    POW(69.1 * (addresses.latitude - $latitude), 2) +
                    POW(69.1 * ($longitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // Calculate the distant in mile
        $area = "SQRT(
                    POW(69.1 * (addresses.latitude - $areaLatitude), 2) +
                    POW(69.1 * ($areaLongitude - addresses.longitude) * COS(addresses.latitude / 57.3), 2))";

        // convert the distance to KM if the distance unit is KM
        if (setting('distance_unit') == 'km') {
            $distance .= " * 1.60934"; // 1 Mile = 1.60934 KM
            $area .= " * 1.60934"; // 1 Mile = 1.60934 KM
        }

        return $query
            ->join('addresses', 'clinics.address_id', '=', 'addresses.id')
            ->whereRaw("$distance < clinics.availability_range")
            ->select(DB::raw($distance . " AS distance"), DB::raw($area . " AS area"), "clinics.*")
            ->orderBy('area');
    }

    public function openingHours(): OpeningHours
    {
        $openingHoursArray = [];
        foreach ($this->availabilityHoursPharmacies as $element) {
            $openingHoursArray[$element['day']] = [
                'data' => $element['data'],
                $element['start_at'] . '-' . $element['end_at']
            ];
        }
        return OpeningHours::createAndMergeOverlappingRanges($openingHoursArray);
    }

    /**
     * Provider ready when he is accepted by admin and marked as available
     * and is open now
     */
    public function getAvailableAttribute(): bool
    {
        return $this->accepted && $this->attributes['available'] && $this->openingHours()->isOpen();
    }

    public function getHasValidSubscriptionAttribute(): ?bool
    {
        if (!Module::isActivated('Subscription')) {
            return null;
        }
        $result = $this->pharmacySubscriptions
            ->where('expires_at', '>', now())
            ->where('starts_at', '<=', now())
            ->where('active', '=', 1)
            ->count();
        return $result > 0;
    }

    /**
     * @return BelongsTo
     **/
    public function pharmacyType(): BelongsTo
    {
        return $this->belongsTo(PharmacyType::class, 'pharmacy_type_id', 'id');
    }

    /**
     * @return HasMany
     *
     * @throws ModuleNotFoundException
     */
    public function pharmacySubscriptions(): HasMany
    {
        if (Module::isActivated('Subscription'))
            return $this->hasMany('Modules\Subscription\Models\PharmacySubscription', 'pharmacy_id');
        else
            throw new ModuleNotFoundException();

    }
    /**
     * @return HasMany
     **/
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class, 'pharmacy_id');
    }

    /**
     * @return BelongsToMany
     **/
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pharmacy_users');
    }

    /**
     * @return BelongsToMany
     **/
    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'pharmacy_addresses');
    }

    /**
     * @return BelongsToMany
     **/
    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'pharmacy_taxes');
    }

    /**
     * @return HasMany
     **/
    public function availabilityHoursPharmacies(): HasMany
    {
        return $this->hasMany(AvailabilityHourPharmacy::class, 'pharmacy_id')->orderBy('start_at');
    }


    /**
     * Add Media to api results
     * @return bool
     */
    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia('image');
    }

}
