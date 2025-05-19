<?php

namespace App\Helpers;

class CustomGrpcCredentials
{
    private static $credentials = null;

    public static function getCredentials()
    {
        if (self::$credentials === null) {
            $credentialsFile = __DIR__ . '/../../firebase-credentials.json';
            if (file_exists($credentialsFile)) {
                $credentialsJson = file_get_contents($credentialsFile);
                self::$credentials = json_decode($credentialsJson, true);
            } else {
                self::$credentials = [];
            }
        }

        return self::$credentials;
    }
}