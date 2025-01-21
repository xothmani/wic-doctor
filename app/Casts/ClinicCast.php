<?php
/*
 * File name: ClinicCast.php
 * Last modified: 2024.05.03 at 19:14:22
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Casts;

use App\Models\Clinic;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Class ClinicCast
 * @package App\Casts
 */
class ClinicCast implements CastsAttributes
{

    /**
     * @inheritDoc
     */
  public function get($model, string $key, $value, array $attributes): Clinic
{
    // Decode the JSON value
    $decodedValue = json_decode($value, true);

    // Check if the decoded value is null or doesn't contain the 'id' key
    if (is_null($decodedValue) || !isset($decodedValue['id'])) {
        // Handle the error or return a default value if necessary
        throw new \Exception("Invalid JSON value or missing 'id' key in ClinicCast.");
    }

    // Attempt to find the Clinic by ID
    $clinic = Clinic::find($decodedValue['id']);
    
    if ($clinic) {
        return $clinic;
    }

    // If the clinic is not found, create a new Clinic instance
    $clinic = new Clinic($decodedValue);
    $clinic->fillable[] = 'id';
    $clinic->id = $decodedValue['id'];

    return $clinic;
}

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
//        if (!$value instanceof Clinic) {
//            throw new InvalidArgumentException('The given value is not an Clinic instance.');
//        }
        return [
            'clinic' => json_encode([
                'id' => $value['id'],
                'name' => $value['name'],
                'phone_number' => $value['phone_number'],
                'mobile_number' => $value['mobile_number'],
            ])
        ];
    }
}

