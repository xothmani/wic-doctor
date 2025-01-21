<?php
/*
 * File name: DoctorCast.php
 * Last modified: 2024.05.03 at 21:35:24
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Casts;

use App\Models\Doctor;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Log;
/**
 * Class DoctorCast
 * @package App\Casts
 */
class DoctorCast implements CastsAttributes
{

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): ?Doctor
    {
        Log::info("DoctorCast get method called", ['value' => $value]);

        // Decode the value and check if it's valid
        $decodedValue = json_decode($value, true);

        if (!is_array($decodedValue) || !isset($decodedValue['id'])) {
            Log::warning("DoctorCast: Invalid value or missing 'id' key", ['value' => $value]);
            return null; // Return null if the value is not an array or 'id' is missing
        }

        // Find the doctor by ID
        $doctor = Doctor::find($decodedValue['id']);
        if ($doctor) {
            return $doctor;
        }

        Log::error("DoctorCast: No Doctor found with id {$decodedValue['id']}");
        return null;
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): array
    {
//        if (!$value instanceof Doctor) {
//            throw new InvalidArgumentException('The given value is not a Doctor instance.');
//        }
        return [
            'doctor' => json_encode(
                [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'price' => $value['price'],
                    'discount_price' => $value['discount_price'],
                    'enable_appointment' => $value['enable_appointment'],
                ]
            )
        ];
    }
}
