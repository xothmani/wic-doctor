<?php
/*
 * File name: User.php
 * Last modified: 2021.06.28 at 23:44:43
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Relations\HasOne;
/**
 * Class User
 * @package App\Models
 * @version July 10, 2018, 11:44 am UTC
 *
 * @property int id
 * @property string name
 * @property string email
 * @property string phone_number
 * @property string phone_verified_at
 * @property string password
 * @property string api_token
 * @property string device_token
 */
class User extends Authenticatable implements HasMedia
{
    use Notifiable;
    use Billable;
    use InteractsWithMedia {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }
    use HasRoles;
    use HasFactory;

    /**
     * Validation rules
     *
     * @var array
     */
    public static array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|max:255|unique:users',
        'phone_number' => 'required|max:255|unique:users',
        'password' => 'required',
    ];

    public $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name',
        'email',
        'phone_number',
        'phone_verified_at',
        'password',
        'api_token',
        'lastname',
        'device_token',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'phone_number' => 'string',
        'password' => 'string',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'api_token' => 'string',
        'device_token' => 'string',
        'remember_token' => 'string'
    ];
    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        'has_media'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Route notifications for the FCM channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string|null
     */
    public function routeNotificationForFcm(\Illuminate\Notifications\Notification $notification): ?string
    {
        return $this->device_token;
    }

    /**
     * @param Media|null $media
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->nonQueued(); // Force immediate conversion for testing

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->nonQueued();
    }

    /**
     * to generate media url in case of fallback will
     * return the file type icon
     * @param string $conversion
     * @return string url
     */
    public function getFirstMediaUrl(string $collectionName = 'default', string $conversion = ''): string
    {
        $url = $this->getFirstMediaUrlTrait($collectionName);
        if ($url) {
            $array = explode('.', $url);
            $extension = strtolower(end($array));
            if (in_array($extension, config('media-library.extensions_has_thumb'))) {
                return asset($this->getFirstMediaUrlTrait($collectionName, $conversion));
            } else {
                return asset(config('media-library.icons_folder') . '/' . $extension . '.png');
            }
        } else {
            return asset('images/avatar_default.png');
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
            ->select(['value', 'view', 'name'])
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
        return $this->hasMedia('avatar');
    }

    /**
     * @return BelongsToMany
     **/
    public function clinic(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class, 'clinic_users');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }


    /**
     * @return HasMany
     **/
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
    public function associations()
    {
        return $this->hasMany(DoctorAssociate::class, 'doctor_id');
    }
    public function associatedDoctors()
    {
        return $this->hasMany(DoctorAssociate::class, 'user_id');
    }


    public function hasPermissionInContext(string $permissionName, ?int $doctorId = null): bool
    {
        // Log the start of the method
        \Log::info('Checking permission in context', [
            'user_id' => $this->id,
            'permission_name' => $permissionName,
            'doctor_id' => $doctorId,
        ]);

        // Retrieve the permission by name
        $permission = Permission::where('name', $permissionName)->first();

        if (!$permission) {
            \Log::warning('Permission not found', ['permission_name' => $permissionName]);
            return false;
        }

        // If the user is an admin, allow all permissions
        if ($this->hasRole('admin')) {
            \Log::info('Permission granted for admin role', ['user_id' => $this->id]);
            return true;
        }

        // If the user is a doctor
        if ($this->hasRole('doctor')) {
            $selfDoctorId = $this->doctor->id ?? null;
            if ($doctorId === null || $doctorId === $selfDoctorId) {
                \Log::info('Permission granted for doctor role', ['user_id' => $this->id, 'doctor_id' => $selfDoctorId]);
                return true;
            }
            \Log::warning('Doctor attempted to access unrelated doctor context', [
                'user_id' => $this->id,
                'doctor_id' => $selfDoctorId,
                'attempted_doctor_id' => $doctorId,
            ]);
            return false;
        }

        // If the user is a secretary
        if ($this->hasRole('Secretary') || $this->hasRole('Telesecretary')) {
            // Query the role_profile_permission table for secretary permissions
            $query = \DB::table('role_profile_permission')
                ->join('model_has_roles', 'role_profile_permission.role_id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $this->id)
                ->where('role_profile_permission.permission_id', $permission->id);

            // Apply doctor_id filter if provided
            if ($doctorId) {
                $query->where('role_profile_permission.doctor_id', $doctorId);
                \Log::info('Applying doctor filter for secretary role', ['doctor_id' => $doctorId]);
            } else {
                \Log::info('No doctor filter applied for secretary role');
            }
            $query->where('role_profile_permission.user_id', $this->id);
            \Log::info('Applying user filter for secretary role', ['user_id' => $this->id]);
            // Check if the record exists
            $exists = $query->exists();

            // Log the result
            \Log::info('Permission check result for secretary', [
                'user_id' => $this->id,
                'permission_name' => $permissionName,
                'doctor_id' => $doctorId,
                'exists' => $exists,
            ]);

            return $exists;
        }

        // If none of the roles match, deny permission
        \Log::warning('Permission denied for user', [
            'user_id' => $this->id,
            'permission_name' => $permissionName,
            'doctor_id' => $doctorId,
        ]);

        return false;
    }

    public function getDoctorId(): ?int
    {
        // If the user has a doctor role, return their own ID
        if ($this->hasRole('doctor')) {
            return $this->doctor->id ?? null; // Assuming the `doctor` relation returns a `Doctor` model
        }

        // If the user is a secretary, return the associated doctor ID(s)
        if ($this->hasRole('Secretary')) {
            $associatedDoctor = $this->associatedDoctors->first(); // Fetch the first associated doctor
            return $associatedDoctor->doctor_id ?? null;
        }

        // For other roles, return null
        return null;
    }


    public function patient()
    {
        return $this->hasOne(Patient::class);
    }
    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'user_id', 'id');
    }
}
