<?php
/**
 * File name: auth.php
 * Last modified: 2021.01.03 at 15:46:37
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
'doctors' => [
    'driver' => 'eloquent',
    'model' => App\Models\Doctor::class,
],




    // Correction ici : Un seul tableau 'guards'
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
        // Ajout du guard pour les médecins
        'doctor' => [
        'driver' => 'token',
'provider' => 'doctors',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        // Ajout du provider pour les médecins
        'doctors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Doctor::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];