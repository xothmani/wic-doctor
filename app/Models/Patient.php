<?php
namespace App\Models;

use App\Casts\PatientCast;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Carbon\Carbon; // Make sure to import Carbon for date calculations

class Patient extends Model implements HasMedia, Castable
{
    use InteractsWithMedia {
        getFirstMediaUrl as protected getFirstMediaUrlTrait;
    }
    use HasTranslations;
    use HasFactory;

    public $table = 'patients';

    public $fillable = [
        'user_id',
        'clinic_id',
        'first_name',
        'last_name',
        'phone_number',
        'mobile_number',
        'gender',
        'weight',
        'height',
        'medical_history',
        'notes',
        'matriculeCNSS',
        'dateExpiration',
        'assurance',
        'groupe_sanguin',
        'allergie',
	    'antecedent',
        'date_naissance', 
        'antecedent',
	    'email',
        'type_carnet',
        'is_main_profil',
        'type_of_relationship'

   ];


    protected $casts = [
        'image' => 'string',
        'card_id' => 'string',
        'user_id' => 'integer',
        'clinic_id' => 'integer',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone_number' => 'string',
        'mobile_number' => 'string',
        'gender' => 'string',
        'weight' => 'string',
        'height' => 'string',
        'medical_history' => 'string',
        'notes' => 'string',
        'date_naissance' => 'date', 
        'age' => 'string'

    ];

    public static array $rules = [
        'first_name' => 'required|max:127',
        'last_name' => 'required|max:127',
        'phone_number' => 'required|max:50',
        'mobile_number' => 'max:50',

        //'gender' => 'required|max:127',
       // 'weight' => 'required|max:127',
       // 'height' => 'required|max:127',
        //'date_naissance' => 'required|date', 
     
    ];

    public array $translatable = [
        'first_name',
        'last_name',
        'description',
    ];

    protected $appends = [
        'custom_fields',
        'total_appointments',
        'age', // Append the age field dynamically
    ];


    
     // Méthode pour calculer l'âge basé sur la date de naissanc
    public function setAgeAttribute()
     {
         if (!empty($this->attributes['date_naissance'])) {

             $birthDate = Carbon::parse($this->attributes['date_naissance']);
             $ageInYears = $birthDate->age;
             $ageInMonths = $birthDate->diffInMonths(Carbon::now());
             $ageInDays = $birthDate->diffInDays(Carbon::now());
     
             if ($ageInYears >= 1) {
                 $this->attributes['age'] = $ageInYears . ' ans';
             } elseif ($ageInMonths >= 1) {
                 $this->attributes['age'] = $ageInMonths . ' mois';
             } else {
                 $this->attributes['age'] = $ageInDays . ' jours';
             }
         } else {
             $this->attributes['age'] = 'N/S';
         }
     }

         // Mutateur pour nettoyer le champ 'notes'
    public function setNotesAttribute($value)
    {
        // Supprime les balises HTML du champ 'notes'
        $this->attributes['notes'] = strip_tags($value);
    }

    // Mutateur pour nettoyer le champ 'allergie'
    public function setAllergieAttribute($value)
    {
        // Supprime les balises HTML du champ 'allergie'
        $this->attributes['allergie'] = strip_tags($value);
    }
    // Mutateur pour nettoyer le champ 'antecedent'
    public function setAntecedentAttribute($value)
    {
        // Supprime les balises HTML du champ 'antecedent'
        $this->attributes['antecedent'] = strip_tags($value);
    }
    // Mutateur pour nettoyer le champ 'medical_history'
    public function setMedicalHistoryAttribute($value)
    {
        // Supprime les balises HTML du champ 'medical_history'
        $this->attributes['medical_history'] = strip_tags($value);
    }
 
     // Appeler cette méthode lors de la création ou de la mise à jour du patient
     protected static function booted()
     {
         static::creating(function ($patient) {
             $patient->setAgeAttribute(); // Calculer l'âge avant la création
         });
 
         static::updating(function ($patient) {
             $patient->setAgeAttribute(); // Calculer l'âge avant la mise à jour
         });
     }

    public static function castUsing(array $arguments): string
    {
        return PatientCast::class;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 200, 200)
            ->sharpen(10);

        $this->addMediaConversion('icon')
            ->fit(Manipulations::FIT_CROP, 100, 100)
            ->sharpen(10);
    }

    public function getFirstMediaUrl($collectionName = 'default', string $conversion = ''): string
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

    public function customFieldsValues(): MorphMany
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function getHasMediaAttribute(): bool
    {
        return $this->hasMedia('image');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient->id')->orderBy('appointment_at');
    }

    public function getTotalAppointmentsAttribute(): float
    {
        return $this->appointments()->count();
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_patients');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function fiche()
    {
        return $this->hasOne(Fiche::class, 'patient_id');
    }

    // Dynamically calculate the patient's age based on the date of birth
    public function getAgeAttribute(): string
    {
        if ($this->date_naissance) {
            $birthDate = Carbon::parse($this->date_naissance);
            $ageInYears = $birthDate->age; // Âge en années
            $ageInMonths = $birthDate->diffInMonths(Carbon::now()); // Âge en mois
            $ageInDays = $birthDate->diffInDays(Carbon::now()); // Âge en jours
    
            if ($ageInYears >= 1) {
                return $ageInYears . ' ans'; // Si l'âge est supérieur ou égal à 1 an
            } elseif ($ageInMonths >= 1) {
                return $ageInMonths . ' mois'; // Si l'âge est inférieur à 1 an mais supérieur ou égal à 1 mois
            } else {
                return $ageInDays . ' jours'; // Si l'âge est inférieur à 1 mois
            }
        }
    
        return 'N/S'; // Retourner 'N/S' si la date de naissance n'est pas définie
    }
    
    public function assurance(): BelongsTo
    {
        return $this->belongsTo(Assurance::class, 'assurance');
    }
}
