<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\API\PayPalAPIController;
use App\Http\Controllers\API\DoctorAPIController;
use App\Http\Controllers\API\NotificationAPIController;
use App\Http\Controllers\API\PrescriptionsApiController;
use App\Http\Controllers\API\RoomAPIController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\PatientFileController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\API\TranslationAPIController;
use App\Http\Controllers\API\AvailabilityHourAPIController;
use App\Http\Controllers\API\AppointmentAPIController ;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\CustomMediaApiController;


/*********************** Route ajouté par Hamza ********************* */
Route::get('doctors/search','API\DoctorAPIController@indexFiltreHamza');
Route::get('doctors/recomended','API\DoctorAPIController@recommandedDoctor');
Route::get('appointment/{id}','API\AppointmentAPIController@getAppointmentById');
Route::middleware('auth:api')->patch('update-appoi-hamza/{id}','API\AppointmentAPIController@updateDateAndTime');
Route::middleware('auth:api')->patch('update-appoi-status/{id}','API\AppointmentAPIController@updateStatus');
Route::post('/send-notification', [NotificationAPIController::class, 'send']);
Route::post('/store-notification', [NotificationAPIController::class, 'storeReminderAppointment']);
//khater notification feha patch w mé najmouch na3mlou 2 patch fi nafes l url
Route::middleware('auth:api')->patch('/notif/read-all/{id_user}', [NotificationAPIController::class, 'readAll']);

Route::prefix('rooms')->middleware('auth:api')->group(function () {
    Route::get('/', [RoomAPIController::class, 'index']); // GET all rooms with appointment details
    Route::get('/{id}', [RoomAPIController::class, 'show']); // GET single room
    Route::post('/', [RoomAPIController::class, 'store']); // CREATE a new room
    Route::put('/{id}', [RoomAPIController::class, 'update']); // UPDATE an existing room
    Route::delete('/{id}', [RoomAPIController::class, 'destroy']); // DELETE a room
});
Route::middleware('auth:api')->post('/set-locale', [TranslationAPIController::class, 'setUserLocale']);
Route::get('/get-type-consultation/{id}', [AvailabilityHourAPIController::class, 'getTypeConsultation']);
Route::put('update-user-email/{id}', [UserAPIController::class, 'updateUserEmail']);
Route::get('/consultations/prescriptions/{id}', [PrescriptionsApiController::class, 'show']);
Route::delete('appointments/delete/{id}', [AppointmentApiController::class, 'delete']);
Route::post('/media/upload', [CustomMediaApiController::class, 'upload']);
Route::delete('/media/delete/{id}', [CustomMediaApiController::class, 'destroy']);
Route::delete('doctor_reviews/{id}', 'API\DoctorReviewAPIController@delete');
Route::get('get-user/{id}', 'API\UserAPIController@getUser');
/*********************** End Route ajouté par Hamza ********************* */



Route::prefix('clinic_owner')->group(function () {
    Route::post('login', 'API\ClinicOwner\UserAPIController@login');
    Route::post('register', 'API\ClinicOwner\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\ClinicOwner\UserAPIController@user');
    Route::get('logout', 'API\ClinicOwner\UserAPIController@logout');
    Route::get('settings', 'API\ClinicOwner\UserAPIController@settings');
    Route::get('translations', 'API\TranslationAPIController@translations');
    Route::get('supported_locales', 'API\TranslationAPIController@supportedLocales');
    Route::middleware('auth:api')->group(function () {
        Route::resource('clinics', 'API\ClinicOwner\ClinicAPIController')->only(['index', 'show']);
        Route::get('doctors', 'API\ClinicOwner\DoctorAPIController@index');
        Route::resource('availability_hours', 'API\AvailabilityHourAPIController')->only(['store', 'update', 'destroy']);
        Route::resource('awards', 'API\AwardAPIController')->only(['store', 'update', 'destroy']);
        Route::resource('experiences', 'API\ExperienceAPIController')->only(['store', 'update', 'destroy']);
        Route::get('clinic_levels', 'API\ClinicLevelAPIController@index');
        Route::get('taxes', 'API\ClinicOwner\TaxAPIController@index');
        Route::get('employees', 'API\ClinicOwner\UserAPIController@employees');
        Route::get('patients', 'API\ClinicOwner\PatientAPIController@index');
    });
});




Route::prefix('doctor')->group(function () {
    Route::post('login', 'API\Doctor\UserAPIController@login');
    Route::post('register', 'API\Doctor\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\Doctor\UserAPIController@user');
    Route::get('logout', 'API\Doctor\UserAPIController@logout');
    Route::get('settings', 'API\Doctor\UserAPIController@settings');
    Route::get('translations', 'API\TranslationAPIController@translations');
    Route::get('supported_locales', 'API\TranslationAPIController@supportedLocales');
    Route::middleware('auth:api')->group(function () {
        Route::resource('clinics', 'API\Doctor\ClinicAPIController')->only(['index', 'show']);
        Route::get('doctors', 'API\Doctor\DoctorAPIController@index');
        Route::resource('availability_hours', 'API\AvailabilityHourAPIController')->only(['store', 'update', 'destroy']);
        Route::resource('awards', 'API\AwardAPIController')->only(['store', 'update', 'destroy']);
        Route::resource('experiences', 'API\ExperienceAPIController')->only(['store', 'update', 'destroy']);
        Route::get('clinic_levels', 'API\ClinicLevelAPIController@index');
        Route::get('taxes', 'API\Doctor\TaxAPIController@index');
        Route::get('employees', 'API\Doctor\UserAPIController@employees');
        Route::get('patients', 'API\Doctor\PatientAPIController@index');

    });
});

Route::get('availability-pattern-nom', 'API\AvailabilityHourAPIController@getPatternNom');
Route::get('paypal/payment-static', 'API\PayPalAPIController@paymentStatic')->name('paypal.payment.static');
Route::get('matching-availability', 'API\AvailabilityHourAPIController@getMatchingAvailabilityAndAppointments');

//Route::get('paypal', [PayPalAPIController::class, 'index'])->name('paypal');
//Route::post('paypal/payment', [PayPalAPIController::class, 'payment'])->name('paypal.payment');
Route::get('paypal/payment/success', [PayPalAPIController::class, 'paymentSuccess'])->name('paypal.payment.success');
Route::get('paypal/payment/cancel', [PayPalAPIController::class, 'paymentCancel'])->name('paypal.payment.cancel');


Route::post('login', 'API\UserAPIController@login');
Route::post('register', 'API\UserAPIController@register');
Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
Route::get('user', 'API\UserAPIController@user');
Route::get('logout', 'API\UserAPIController@logout');
Route::get('settings', 'API\UserAPIController@settings');
Route::get('translations', 'API\TranslationAPIController@translations');
Route::get('supported_locales', 'API\TranslationAPIController@supportedLocales');
Route::get('modules', 'API\ModuleAPIController@index');
Route::post('reset-password/{phoneNumber}','API\UserAPIController@resetPassword');
Route::post('check-phone-number', 'API\UserAPIController@checkPhoneNumber');
Route::resource('clinics', 'API\ClinicAPIController')->only(['index', 'show']);
Route::resource('availability_hours', 'API\AvailabilityHourAPIController')->only(['index', 'show']);
Route::resource('awards', 'API\AwardAPIController')->only(['index', 'show']);
Route::resource('experiences', 'API\ExperienceAPIController')->only(['index', 'show']);



Route::get('getRecentDoctors/{patient_id}','API\DoctorPatientsAPIController@getRecentDoctors');


Route::get('totalAppointments/{patient_id}', 'API\PatientAPIController@totalAppointments');


Route::get('/doctor/{id}/urgency-hours', [DoctorAPIController::class, 'getUrgencyHours']);






Route::resource('faq_categories', 'API\FaqCategoryAPIController');
Route::resource('faqs', 'API\FaqAPIController');
Route::resource('custom_pages', 'API\CustomPageAPIController');

Route::resource('specialities', 'API\SpecialityAPIController');

Route::resource('doctor_patients', 'API\DoctorPatientsAPIController');
Route::resource('doctors', 'API\DoctorAPIController');
Route::resource('galleries', 'API\GalleryAPIController');

Route::get('doctor_reviews/{id}', 'API\DoctorReviewAPIController@show');
Route::get('doctor_reviews', 'API\DoctorReviewAPIController@index');

Route::get('clinic_reviews/{id}', 'API\ClinicReviewAPIController@show');
Route::get('clinic_reviews', 'API\ClinicReviewAPIController@index');

Route::resource('currencies', 'API\CurrencyAPIController');
Route::resource('slides', 'API\SlideAPIController')->except([
    'show'
]);
Route::resource('appointment_statuses', 'API\AppointmentStatusAPIController')->except([
    'show'
]);


Route::resource('patients', 'API\PatientAPIController');

Route::post('patients/{id}', 'API\PatientAPIController@update');
Route::get('availability-pattern-nom', 'API\AvailabilityHourAPIController@getPatternNom');
Route::get('patterns', 'API\PatternAPIController@index');
Route::get('patterns/{id}', 'API\PatternAPIController@show');

Route::get('patterns-by-doctor/{id}', 'API\PatternAPIController@getPatternsByDoctor');
Route::get('patterns-by-doctor-and-type', 'API\PatternAPIController@getPatternsByDoctorAndType');


Route::middleware('auth:api')->group(function () {
    Route::group(['middleware' => ['role:clinic_owner']], function () {
        Route::prefix('clinic_owner')->group(function () {
            Route::post('users/{user}', 'API\UserAPIController@update');
            Route::get('dashboard', 'API\DashboardAPIController@clinic');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::put('payments/{id}', 'API\PaymentAPIController@update')->name('payments.update');
        });
    });

    Route::group(['middleware' => ['role:doctor']], function () {
        Route::prefix('doctor')->group(function () {
            Route::post('users/{user}', 'API\UserAPIController@update');
            Route::get('dashboard', 'API\DashboardAPIController@doctor');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::put('payments/{id}', 'API\PaymentAPIController@update')->name('payments.update');
        });
    });


    Route::resource('clinics', 'API\ClinicAPIController')->only([
        'store', 'update', 'destroy'
    ]);
    Route::post('uploads/store', 'API\UploadAPIController@store');
    Route::post('uploads/clear', 'API\UploadAPIController@clear');
    Route::get('uploads/clear/{id}', 'API\UploadAPIController@clearMediaById');
    Route::post('users/{user}', 'API\UserAPIController@update');
    Route::delete('users', 'API\UserAPIController@destroy');


    Route::get('payments/byMonth', 'API\PaymentAPIController@byMonth')->name('payments.byMonth');
    Route::post('payments/wallets/{id}', 'API\PaymentAPIController@wallets')->name('payments.wallets');
    Route::post('payments/cash', 'API\PaymentAPIController@cash')->name('payments.cash');
    Route::resource('payment_methods', 'API\PaymentMethodAPIController')->only([
        'index'
    ]);
    Route::post('doctor_reviews', 'API\DoctorReviewAPIController@store')->name('doctor_reviews.store');
    
    Route::post('clinic_reviews', 'API\ClinicReviewAPIController@store')->name('clinic_reviews.store');


    Route::resource('favorites', 'API\FavoriteAPIController');
    Route::resource('addresses', 'API\AddressAPIController');


	



    Route::get('notifications/count', 'API\NotificationAPIController@count');
    Route::resource('notifications', 'API\NotificationAPIController');
    Route::resource('appointments', 'API\AppointmentAPIController');

    Route::resource('earnings', 'API\EarningAPIController');

    Route::resource('clinic_payouts', 'API\ClinicPayoutAPIController');

    Route::resource('coupons', 'API\CouponAPIController')->except([
        'show'
    ]);
    Route::resource('wallets', 'API\WalletAPIController')->except([
        'show', 'create', 'edit'
    ]);
    Route::get('wallet_transactions', 'API\WalletTransactionAPIController@index')->name('wallet_transactions.index');

});

// Analyze compatibility, a proxy route in ordre to make dispatching bd from dev to prod dynamic
Route::post('/proxy/analyze-compatibility', [PrescriptionController::class, 'proxyAnalyzeCompatibility']);

// Drug API routes
Route::prefix('/drugs')->group(function () {
    Route::get('/', [DrugController::class, 'index']);
    Route::get('/search', [DrugController::class, 'search']);
    Route::get('/{medicament_id}', [DrugController::class, 'show']);
    Route::post('/check-interactions', [DrugController::class, 'checkInteractions']);
});

Route::prefix('/drug-interactions')->group(function () {
    Route::post('/check', [DrugController::class, 'checkInteractions']);
    Route::get('/backup-stats', [DrugController::class, 'getBackupStats']);
    Route::post('/toggle-backup-mode', [DrugController::class, 'toggleBackupMode']);
    Route::get('/search-backup', [DrugController::class, 'searchBackup']);
});

Route::prefix('patient_files')->name('api.patient_files.')->group(function () {
    Route::get('/{patient}', [PatientFileController::class, 'apiIndex'])->name('index');
    Route::get('/{patient}/{file}', [PatientFileController::class, 'apiShow'])->name('show');
    Route::get('/{patient}/{file}/download', [PatientFileController::class, 'apiDownload'])->name('download');
    Route::delete('/{patient}/{file}', [PatientFileController::class, 'apiDestroy'])->name('destroy');
    Route::post('/{patient}/{file}/give-access', [PatientFileController::class, 'apiGiveAccess'])->name('give_access');
    Route::post('/{patient}/upload', [PatientFileController::class, 'apiUpload'])->name('upload');
    Route::post('/{patient}/{file}/revoke-access', [PatientFileController::class, 'apiRevokeAccess'])->name('revoke_access');
    Route::get('/{patient}/assigned_users/{file}', [PatientFileController::class, 'apiGetAssignedUsers'])->name('assigned_users');
    Route::post('/{patient}/{file}/generate-qr-code', [PatientFileController::class, 'apiGenerateFileQrCode'])->name('generate_qr_code');
    Route::get('/{patient}/{file}/api-download', [PatientFileController::class, 'apiDownloadExternal'])->name('api_download');
    Route::post('/{patient}/generate-public-upload-link', [PatientFileController::class, 'apiGeneratePublicUploadLink'])->name('generate_public_upload_link');
});