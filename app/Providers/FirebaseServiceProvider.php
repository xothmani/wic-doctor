<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
<<<<<<< HEAD
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Firestore;
=======
use Kreait\Firebase\Auth;
>>>>>>> origin/dev

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('firebase', function ($app) {
            $firebaseConfig = [
                'type' => 'service_account',
                'project_id' => 'wic-doctor-b83e0',
                'private_key_id' => '2cdc8fe9b82f04d48b85d0ab7a1b8ec51657a9f3',
                'private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDjFqOi/WIXe50x\nR+nU...",
                'client_email' => 'firebase-adminsdk-y2evs@wic-doctor-b83e0.iam.gserviceaccount.com',
                'client_id' => '110513297809775117364',
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-y2evs%40wic-doctor-b83e0.iam.gserviceaccount.com',
                'universe_domain' => 'googleapis.com',
            ];

            return (new Factory)
                ->withServiceAccount($firebaseConfig)
<<<<<<< HEAD
                ->createFirestore();
=======
                ->create();
>>>>>>> origin/dev
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/dev
