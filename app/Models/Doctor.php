<?php


namespace App\Models;

use App\Casts\DoctorCast;
use App\Traits\HasTranslations;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\OpeningHours\OpeningHours;
use Illuminate\Support\Facades\Log;
use App\Models\Address;


/**
 * Class Doctor
 * @package App\Models
 * @version January 19, 2021, 1:59 pm UTC
 *
 * @property Collection speciality
 * @property Clinic clinic
 * @property User user
 * @property Collection Option
 * @property Collection DoctorsReview
 * @property Collection[] availabilityHours
 * @property integer id
 * @property double price
 * @property double discount_price
 * @property string description
 * @property string name
 * @property boolean featured
 * @property boolean enable_appointment
 * @property boolean enable_at_clinic
 * @property boolean enable_at_customer_address
 * @property boolean enable_online_consultation
 * @property boolean available
 * @property double commission
 * @property string session_duration
 * @property integer clinic_id
 * @property integer user_id
 */
class Doctor extends Model implements HasMedia, Castable
{    protected $guard = 'doctor'; 

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
    public static array $rules = [
        'name' => 'required|max:127',
        'price' => 'nullable|numeric|min:0|max:99999999,99',
        'discount_price' => 'nullable|numeric|min:0|max:99999999,99',
        'description' => 'required',
        'clinic_id' => 'nullable|exists:clinics,id',
        'user_id' => 'exists:users,id'
    ];
    public array $translatable = [
        'name',
        'description',
    ];
    public $table = 'doctors';


    public $fillable = [
        'name',
        'price',
        'commission',
        'discount_price',
        'description',
        'featured',
        'enable_appointment',
        'enable_at_clinic',
        'enable_at_customer_address',
        'enable_online_consultation',
        'available',
        'session_duration',
        'clinic_id',
        'user_id',
	    'matricule_CNAM',
	    'diplome',
        'numOrdre',
        'tele_price_tnd',
        'tele_price_eur',
        'id_aleatoire',
        'sexe',
        'code_parent',  
        'code_doctor',
	    'fixe',
        'facebook',
        'instagram',
        'site_web',
        'bio',
        'type_consultation',
        'payment_methods',
        'langues_parlees',
        'num_france',
        'api_key', 
        'cabinet_photo',
        'pourcentage_avatar',
        'pourcentage_adresse',
        'pourcentage_cv',
        'pourcentage_cabinet',
        'pourcentage_profil',
        'pourcentage_tags',
        'verif_chart',
        'availability_mode',
        'titre'

    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'image' => 'string',
        'name' => 'string',
        'price' => 'double',
        'discount_price' => 'double',
        'commission' => 'double',
        'description' => 'string',
        'featured' => 'boolean',
        'enable_appointment' => 'boolean',
        'enable_at_clinic' => 'boolean',
        'enable_at_customer_address' => 'boolean',
        'enable_online_consultation' => 'boolean',
        'available' => 'boolean',
        'session_duration' => 'string',
        'clinic_id' => 'integer',
        'user_id' => 'integer',
        'rate' => 'double',
        'total_reviews' => 'integer',
        'code_parent' => 'string',  // Add the parrain attribute cast if needed
        'code_doctor' => 'string',
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
        'total_reviews',
        'is_favorite',
        'rate'
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];





    /**
     * @param array $arguments
     * @return string
     */
    public static function castUsing(array $arguments):string
    {
        return DoctorCast::class;
    }

    /**
     * @param Media|null $media
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
    
    /* public function getFirstMediaUrl(string $collectionName = 'default', string $conversion = ''): string
    {
        // Get media from both Doctor and User models
        $doctorMedia = $this->getMedia($collectionName)->first();
        $userMedia = $this->user->getMedia($collectionName)->first();
        
        // Determine which media to use (prioritize Doctor's media if available)
        $media = $doctorMedia ?? $userMedia;
        
        // If no media found in either model, return default
        if (!$media) {
            return asset(config('media-library.icons_folder') . '/default.png');
        }
        
        // Get the URL of the selected media
        $url = $media->getUrl($conversion);
        $array = explode('.', $url);
        $extension = strtolower(end($array));
        
        if (in_array($extension, config('media-library.extensions_has_thumb'))) {
            return asset($url);
        } else {
            return asset(config('media-library.icons_folder') . '/' . $extension . '.png');
        }
    } */

    public function getCodeParrainAttribute()
    {
        return $this->attributes['code_parent'];
    }
    public function telesecretariats()
    {
        return $this->hasMany(DoctorTelesecretariat::class);
    }
    

    public function doctorTelesecretariats()
    {
        return $this->hasMany(DoctorTelesecretariat::class, 'doctor_id');
    }
    public function setCodeParrainAttribute($value)
    {
        $this->attributes['code_parent'] = $value;
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

 

public function openingHours(): OpeningHours
{
    // Mapping des jours français vers les jours anglais
    $joursMapping = [
        'Lundi' => 'monday',
        'Mardi' => 'tuesday',
        'Mercredi' => 'wednesday',
        'Jeudi' => 'thursday',
        'Vendredi' => 'friday',
        'Samedi' => 'saturday',
        'Dimanche' => 'sunday',
    ];

    $openingHoursArray = [];


    foreach ($this->availabilityHours as $element) {
        $dayInEnglish = '';
        // Vérifier si le jour est valide
        if (!isset($joursMapping[$element['day']])) {
            if(in_array($element['day'], $joursMapping)){
                $dayInEnglish = $element['day'];
            }else{
                throw new \Exception("Jour invalide : {$element['day']}");
            }
        }else{
            $dayInEnglish = $joursMapping[$element['day']];
        }
        /***** End code hamza */

        // Convertir les horaires au format H:i-H:i
        $timeRange = \Carbon\Carbon::parse($element['start_at'])->format('H:i') . '-' .
                     \Carbon\Carbon::parse($element['end_at'])->format('H:i');

        // Ajouter l'horaire à la liste
        $openingHoursArray[$dayInEnglish][] = $timeRange;
    }

    // Créer l'objet OpeningHours
    return OpeningHours::createAndMergeOverlappingRanges($openingHoursArray);
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
            ->join('clinics','doctors.clinic_id','=','clinics.id')
            ->join('addresses', 'clinics.address_id', '=', 'addresses.id')
            ->whereRaw("$distance < clinics.availability_range")
            ->select(DB::raw($distance . " AS distance"), DB::raw($area . " AS area"), "doctors.*")
            ->orderBy('area');
    }

    /**
     * Extract hours, minutes, and seconds from a time string.
     *
     * @param string $timeStr
     * @return float|int
     */
    public function parseTime(string $timeStr): float|int
    {
        $parts = explode(':', $timeStr);
        $hours = 0;
        $minutes = 0;

        // Parse the time string based on the number of parts (hours and minutes)
        switch (count($parts)) {
            case 2: // Hours and minutes, e.g., "1:40"
                [$hours, $minutes] = $parts;
                break;
            case 1: // Only minutes, e.g., "40"
                $minutes = $parts[0];
                break;
        }

        // Convert hours to minutes and add to minutes
        return (int) $hours * 60 + (int) $minutes;
    }


    /**
     * get each range of doctor duration in min with open/close clinic
     */
    public function weekCalendarRange(Carbon $date, string $typeConsultation, $mode = "open", $pattern_id = 0): array
    {
        $doctorDurationMinutes = 60; // Par défaut
        $filteredAvailability = collect($this->availabilityHours)->filter(callback: function ($item) use ($typeConsultation, $mode, $pattern_id) {
            if ($mode === 'open') {
                return $item->type === $typeConsultation && $item->mode === 'open';
            } else {
                return $item->type === $typeConsultation &&
                    $item->mode === $mode &&
                    $item->patern_id == $pattern_id;
            }
        })->first();



        if ($filteredAvailability && $filteredAvailability->session_duration > 0) {
            $doctorDurationMinutes = $this->parseTime($filteredAvailability->session_duration);
            Log::info("Filtered session_duration found: $doctorDurationMinutes minutes");
        } else {
            Log::warning('No matching availability hour found. Using default session duration of 60 minutes.', [
                'typeConsultation' => $typeConsultation,
                'mode' => $mode,
                'pattern_id' => $pattern_id
            ]);
        }

        Log::info("weekCalendarRange", ["type consultation" => $typeConsultation, "Session duration" => $doctorDurationMinutes]);

        // Utilisation de CarbonPeriod et ajustement de la timezone
        $period = CarbonPeriod::since($date->subDay()->ceilDay()->setTimezone('Africa/Tunis'))
            ->minutes($doctorDurationMinutes)
            ->until($date->addDay()->ceilDay()->setTimezone('Africa/Tunis')->subMinutes($doctorDurationMinutes));

        $dates = [];
        // Obtenir l'heure actuelle en Tunisie et enlever les secondes
        $now = Carbon::now('Africa/Tunis')->setTime(Carbon::now('Africa/Tunis')->hour, Carbon::now('Africa/Tunis')->minute, 0);

        foreach ($period as $d) {
            $isOpen = $this->openingHours()->isOpenAt($d);
            $times = $d->locale('en')->toIso8601String();
            $isPast = $d->lessThan($now);
            $dates[] = [$times, $isOpen, $isPast];
        }

        $vacance = $this->vacance($date);

        foreach ($dates as &$timeSlot) {
            if (!$timeSlot[2] && $timeSlot[1]) { 
                $startTime = new Carbon($timeSlot[0]);
                $startTime->setTimezone('Africa/Tunis');  // Forcer la timezone de startTime
                $endTime = (clone $startTime)->addMinutes($doctorDurationMinutes);


                $appointmentsExist = false;
                if($mode == "open"){
                   $appointmentsExist = Appointment::where('doctor_id', $this->id)
                    ->where('start_at', '>=', $startTime)
                    ->where('ends_at', '<=', $endTime)
                    ->whereNotIn('appointment_status_id', [6, 7])
                    ->exists(); 
                }else{
                    $appointmentsExist = Appointment::where('doctor_id', $this->id)
                    ->where('start_at', '>=', $startTime)
                    ->where('ends_at', '<=', $endTime)
                    ->where('motif_id', $pattern_id)
                    ->whereNotIn('appointment_status_id', [6, 7])
                    ->exists();
                }
                


                $iSameType = false;
                $collusion = false;
                $isLast = false;
                $isFirst = false;
                if ($mode == "precise" && $pattern_id != 0) {
                    $iSameType = $this->isSameType($date, $startTime, $endTime, $typeConsultation, $mode, $pattern_id);
                    $collusion = $this->isSessionCollidingWithPause($date, $startTime, $endTime, $typeConsultation, $pattern_id);
                    $isFirst =$this->isFirstSlotOfTheDay($date, $startTime,$typeConsultation, $pattern_id);
                    $isLast =$this->isLastSlotOfTheDay($date, $startTime, $endTime, $typeConsultation, $pattern_id);
                } else {
                    $iSameType = $this->isSameType($date, $startTime, $endTime, $typeConsultation);
                    $collusion = $this->isSessionCollidingWithPause($date, $startTime, $endTime, $typeConsultation);
                    $isFirst = $this->isFirstSlotOfTheDay($date, $startTime,$typeConsultation);
                    $isLast =$this->isLastSlotOfTheDay($date, $startTime, $endTime, $typeConsultation);
                }

                $timeSlot[1] = !$appointmentsExist && $iSameType && $timeSlot[1] && !$vacance && !$this->isUrgent($date, $startTime, $endTime) && !$collusion && !$isLast && !$isFirst;


            }
        }

        unset($timeSlot);

        return $dates;
    }






    public function weekCalendarRangeForFilter(Carbon $date): array
    {
       $doctorDurationMinutes = 60; // Par défaut
       $filteredAvailability = collect($this->availabilityHours)->first();

       if ($filteredAvailability && $filteredAvailability->session_duration > 0) {
            $doctorDurationMinutes = $this->parseTime($filteredAvailability->session_duration);
        }


        $period = CarbonPeriod::since($date->subDay()->ceilDay()->setTimezone('Africa/Tunis'))
            ->minutes($doctorDurationMinutes)
            ->until($date->addDay()->ceilDay()->setTimezone('Africa/Tunis')->subMinutes($doctorDurationMinutes));

        $dates = [];
        // Obtenir l'heure actuelle en Tunisie et enlever les secondes
        $now = Carbon::now('Africa/Tunis')->setTime(Carbon::now('Africa/Tunis')->hour, Carbon::now('Africa/Tunis')->minute, 0);

        foreach ($period as $d) {
            $isOpen = $this->openingHours()->isOpenAt($d);
            $times = $d->locale('en')->toIso8601String();
            $isPast = $d->lessThan($now);
            $dates[] = [$times, $isOpen, $isPast];
        }

        return $dates;
    }

    


public function vacance(Carbon $date): bool
{
    // Query the 'vacance' table to find any vacation periods for the doctor
    $vacances = DB::table('vacance')
        ->where('doctor_id', $this->id)
        ->where('start_date', '<=', $date->toDateString())  // Start date is less than or equal to the given date
        ->where('end_date', '>=', $date->toDateString())   // End date is greater than or equal to the given date
        ->exists(); // Check if any matching records exist

    // Return true if the doctor is on vacation, false otherwise
    return $vacances;
}



public function isUrgent(Carbon $date, Carbon $startTime, Carbon $endTime): bool
{

    // Query the 'doctor_urgency' table to find any urgency periods for the doctor on the given day
    $urgency = DB::table('doctor_urgency')
        ->where('doctor_id', $this->id)
        ->whereDate('jour', '=', $date->toDateString()) // Check for matching day
        ->whereTime('heurDebut', '<', $endTime->toTimeString())  // Check if time is after or equal to heurDebut
        ->whereTime('heurFin', '>', $startTime->toTimeString())   // Check if time is before or equal to heurFin
        ->exists(); // Check if any matching records exist

    // Return true if the current time is during an urgent period, false otherwise
    Log::info("isUrgent : ", [
        'start_at' => $startTime,
        'ends_at' => $endTime,
        'isUrgent' => $urgency
    ]);


    return $urgency;
}


public function isSameType(Carbon $date, Carbon $startTime, Carbon $endTime, string $typeConsultation, string $mode= "open", $pattern_id=0): bool
{
    // Get the day name in French and capitalize the first letter
    $dayName = ucfirst($date->locale('fr')->dayName);
    $convertDay = [
        "Lundi" => "monday",
        "Mardi" => "tuesday",
        "Mercredi" => "wednesday",
        "Jeudi" => "thursday",
        "Vendredi" => "friday",
        "Samedi" => "saturday",
        "Dimanche" => "sunday",
    ];
      // Log the day name for debugging

    // Query the 'availability_hours' table to find the doctor's availability and online status
    $onlineStatus = false;
    if($mode == "precise" && $pattern_id != 0){
        Log::info("Mode précise");
        $onlineStatus = DB::table('availability_hours')
        ->where('doctor_id', $this->id)
        ->where('day', '=', $convertDay[$dayName])  // Match the day name (with first letter uppercase)
        ->whereTime('start_at', '<=', $endTime->toTimeString())  // Check if end time is after or equal to start_at
        ->whereTime('end_at', '>=', $startTime->toTimeString())  // Check if start time is before or equal to end_at
        ->where('type', '=', $typeConsultation)
        ->where('mode','=', $mode)
        ->where('patern_id', '=', $pattern_id)
	    ->where('is_available', '=', 1)  // Check if the online status matches
        ->exists();  // Check if any matching records exist
        
    }else{
        Log::info("Mode open");
        $onlineStatus = DB::table('availability_hours')
        ->where('doctor_id', $this->id)
        ->where('day', '=', $convertDay[$dayName])  // Match the day name (with first letter uppercase)
        ->whereTime('start_at', '<=', $endTime->toTimeString())  // Check if end time is after or equal to start_at
        ->whereTime('end_at', '>=', $startTime->toTimeString())  // Check if start time is before or equal to end_at
        ->where('type', '=', $typeConsultation)
	    ->where('is_available', '=', 1)  // Check if the online status matches
        ->exists();  // Check if any matching records exist
    }
    


    // Return true if the doctor is available online during the given time, false otherwise
    return $onlineStatus;
}



public function isLastSlotOfTheDay(Carbon $date, Carbon $startTime, Carbon $endTime, string $typeConsultation, $pattern_id=0): bool
{
    // Get the day name in French and capitalize the first letter
    $dayName = ucfirst($date->locale('fr')->dayName);
    $convertDay = [
        "Lundi" => "monday",
        "Mardi" => "tuesday",
        "Mercredi" => "wednesday",
        "Jeudi" => "thursday",
        "Vendredi" => "friday",
        "Samedi" => "saturday",
        "Dimanche" => "sunday",
    ];

    // Récupérer l'heure de fin la plus tardive de la journée
    $isLast = false;
    if($pattern_id != 0){
        $latestEndTime = DB::table('availability_hours')
            ->where('doctor_id', $this->id)
            ->where('type', $typeConsultation)
            ->where('patern_id', $pattern_id)
            ->whereRaw('LOWER(day) = ?', [strtolower($convertDay[$dayName])])
            ->max('end_at');

        $isLast = $startTime->format('H:i') === Carbon::parse($latestEndTime)->format('H:i');
    }else{
        $latestEndTime = DB::table('availability_hours')
            ->where('doctor_id', $this->id)
            ->where('type', $typeConsultation)
            ->whereRaw('LOWER(day) = ?', [strtolower($convertDay[$dayName])])
            ->max('end_at');

        $isLast = $startTime->format('H:i') === Carbon::parse($latestEndTime)->format('H:i');
    }
    

    Log::info("isLastSlotOfTheDay : ", [
        'start_at' => $startTime->format('H:i'),
        'ends_at' => $endTime->format('H:i'),
        'latestEndTime' => $latestEndTime,
        'isLastSlot' => $isLast
    ]);

    return $isLast;
}



public function isFirstSlotOfTheDay(Carbon $date, Carbon $startTime, string $typeConsultation, $pattern_id=0): bool
{
    $dayName = ucfirst($date->locale('fr')->dayName);
    $convertDay = [
        "Lundi" => "monday",
        "Mardi" => "tuesday",
        "Mercredi" => "wednesday",
        "Jeudi" => "thursday",
        "Vendredi" => "friday",
        "Samedi" => "saturday",
        "Dimanche" => "sunday",
    ];


    $isFirst = false;
    if($pattern_id != 0){
        $earliestStartTime = DB::table('availability_hours')
        ->where('doctor_id', $this->id)
        ->where('type', $typeConsultation)
        ->where('patern_id', $pattern_id)
        ->whereRaw('LOWER(day) = ?', [strtolower($convertDay[$dayName])])
        ->min('start_at');

        if (!$earliestStartTime) {
            return false;
        }

        $startTimeHour = $startTime->format('H:i');
        $earliestHour = Carbon::parse($earliestStartTime)->format('H:i');
        $isFirst = $startTimeHour < $earliestHour;
    }else{
        $earliestStartTime = DB::table('availability_hours')
            ->where('doctor_id', $this->id)
            ->where('type', $typeConsultation)
            ->whereRaw('LOWER(day) = ?', [strtolower($convertDay[$dayName])])
            ->min('start_at');

        if (!$earliestStartTime) {
            return false;
        }

        $startTimeHour = $startTime->format('H:i');
        $earliestHour = Carbon::parse($earliestStartTime)->format('H:i');
        $isFirst = $startTimeHour < $earliestHour;
    }
    

    return $isFirst;
}







public function isSessionCollidingWithPause(Carbon $date, Carbon $startTime, Carbon $endTime, string $typeConsultation, $pattern_id=0): bool
{

    $convertDay = [
        "Lundi" => "monday",
        "Mardi" => "tuesday",
        "Mercredi" => "wednesday",
        "Jeudi" => "thursday",
        "Vendredi" => "friday",
        "Samedi" => "saturday",
        "Dimanche" => "sunday",
    ];

    // Get the day name in French with proper capitalization
    $dayName = ucfirst($date->translatedFormat('l'));
    $dayName = $convertDay[$dayName];
    
    // Log the parameters and day name for debugging
    Log::info("Checking pause collision for doctor: {$this->id}, Day: $dayName, Start Time: {$startTime->toTimeString()}, End Time: {$endTime->toTimeString()}, Online: $typeConsultation");


    // Check for collision directly in SQL
    $collisionExists = false;
    if($pattern_id > 0){
        $collisionExists = DB::table('availability_hours')
        ->where('doctor_id', $this->id)
        ->whereRaw('LOWER(day) = ?', [strtolower($dayName)]) // Ensure day is matched correctly
        ->whereNotNull('pause_from')
        ->whereNotNull('pause_to')
        ->where('type', '=', $typeConsultation)
        ->where('patern_id', '=', $pattern_id)
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                // Check if the session time overlaps with the pause period
                $q->where('pause_from', '<', $endTime)
                  ->where('pause_to', '>', $startTime);
            });
        })
        ->exists();

        Log::info("isSessionCollidingWithPause : ", [
            'start_at' => $startTime,
            'ends_at' => $endTime,
            'patern_id' => $pattern_id,
            'isSessionCollidingWithPause' => $collisionExists
        ]);
    }else{
        $collisionExists = DB::table('availability_hours')
        ->where('doctor_id', $this->id)
        ->whereRaw('LOWER(day) = ?', [strtolower($dayName)]) // Ensure day is matched correctly
        ->whereNotNull('pause_from')
        ->whereNotNull('pause_to')
        ->where('type', '=', $typeConsultation)
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where(function ($q) use ($startTime, $endTime) {
                // Check if the session time overlaps with the pause period
                $q->where('pause_from', '<', $endTime)
                  ->where('pause_to', '>', $startTime);
            });
        })
        ->exists();
    }

    // Log the result of the query for debugging
    Log::info("isSessionCollidingWithPause : ", [
        'start_at' => $startTime,
        'ends_at' => $endTime,
        'isSessionCollidingWithPause' => $collisionExists
    ]);

    return $collisionExists;
}







    /**
     * Check if is a favorite for current user
     * @return bool
     */
    public function getIsFavoriteAttribute(): bool
    {
        return $this->favorites()->count() > 0;
    }

    /**
     * @return HasMany
     **/
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'doctor_id')->where('favorites.user_id', auth()->id());
    }

    /**
     * Add Total Reviews to api results
     * @return int
     */
    public function getTotalReviewsAttribute(): int
    {
        return $this->doctorReviews()->count();
    }

    /**
     * @return HasMany
     **/
    public function doctorReviews(): HasMany
    {
        return $this->hasMany(DoctorReview::class, 'doctor_id');
    }

    /**
     * Add Rate to api results
     * @return float
     */
    public function getRateAttribute(): float
    {
        return (float)$this->doctorReviews()->avg('rate');
    }

    /**
     * Doctor available when
     * This Doctor is marked as available
     * and his
     * Provider is ready so he is accepted by admin and marked as available and is open now
     */
    public function getAvailableAttribute(): bool
    {
        return isset($this->attributes['available']) && $this->attributes['available'] && isset($this->clinic) && $this->openingHours()->isOpen();
    }

    /**
     * @return BelongsTo
     **/
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
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
    public function specialities()
    {
        return $this->belongsToMany(Speciality::class, 'doctor_specialities', 'doctor_id', 'speciality_id')->withPivot('description');;
    }
    
    /**
     * @return float
     */
    public function getPrice(): float
    {
        Log::info("getPrice Doctor",[$this]);
        return $this->discount_price > 0 ? $this->discount_price : $this->price;
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->discount_price > 0;
    }

    public function discountables()
    {
        return $this->morphMany('App\Models\Discountable', 'discountable');
    }

    /**
     * @return HasMany
     **/
    public function availabilityHours(): HasMany
    {
        return $this->hasMany(AvailabilityHour::class, 'doctor_id')->orderBy('start_at');
    }

    /**
     * @return BelongsToMany
     **/
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'doctor_patients');
    }

  public function address()
    {
        return $this->hasOne(Address::class, 'user_id', 'user_id');
    }
// Définir la relation avec les diplômes
public function diplomes()
{
    return $this->hasMany(DoctorDiplome::class);

    }
    public function getTotalPourcentage()
    {
        return $this->pourcentage_avatar +
            $this->pourcentage_adresse +
            $this->pourcentage_cv +
            $this->pourcentage_cabinet +
            $this->pourcentage_tags +
            $this->pourcentage_profil;
    }
public function appointments()
{
    return $this->hasMany(Appointment::class);

}


}