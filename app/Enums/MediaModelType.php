<?php

namespace App\Enums;

enum MediaModelType: string
{
    case PATIENT = 'App\Models\Patient';
    case DOCTOR = 'App\Models\Doctor';
    case USER = 'App\Models\User';
    case UPLOAD = 'App\Models\Upload';

    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if (strtoupper($case->name) === strtoupper($name)) {
                return $case;
            }
        }
        return null;
    }
}