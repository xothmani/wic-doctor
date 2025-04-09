<?php

namespace App\Providers;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(AuditLogService::class, function ($app) {
            return new AuditLogService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Listen for authentication events
        Event::listen(Login::class, function ($event) {
            app(AuditLogService::class)->logLogin(
                $event->user->id,
                'User logged in successfully'
            );
        });

        Event::listen(Logout::class, function ($event) {
            app(AuditLogService::class)->logLogout(
                $event->user->id,
                'User logged out successfully'
            );
        });

        // Set up model observers
        Patient::observe(new class {
            public function updated($model)
            {
                app(AuditLogService::class)->logPatientModification(
                    $model->id,
                    'Patient record updated',
                    $model->getOriginal(),
                    $model->getChanges(),
                    $model->doctor_id
                );
            }
        });

        Appointment::observe(new class {
            public function created($model)
            {
                app(AuditLogService::class)->logAppointment(
                    $model->id,
                    'appointment_created',
                    'New appointment scheduled',
                    [],
                    $model->toArray(),
                    $model->doctor_id
                );
            }

            public function updated($model)
            {
                app(AuditLogService::class)->logAppointment(
                    $model->id,
                    'appointment_updated',
                    'Appointment details modified',
                    $model->getOriginal(),
                    $model->getChanges(),
                    $model->doctor_id
                );
            }
        });

        User::observe(new class {
            public function updated($model)
            {
                if ($model->wasChanged(['name', 'email', 'phone'])) {
                    app(AuditLogService::class)->logProfileUpdate(
                        'user',
                        $model->id,
                        'User profile updated',
                        $model->getOriginal(),
                        $model->getChanges(),
                        $model->doctor_id
                    );
                }
            }
        });
    }
}