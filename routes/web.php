<?php

use App\Http\Controllers\AppointmentController;
//use App\Http\Controllers\PharmacyController;
//use App\Http\Controllers\PharmacyTypeController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\MessagerieController;
use App\Http\Controllers\PatientDoctorChatController;
use App\Http\Controllers\PatientFileController;
use App\Http\Controllers\TeleseceteriatDoctorsController;

use App\Http\Controllers\HelpDeskController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentEventController;
use App\Http\Controllers\FicheController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\MeetController;
use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DoctorVacationController;
use App\Http\Controllers\DoctorUrgencyController;
use App\Http\Controllers\DoctorRequestController;
use App\Http\Controllers\KonnectController;
use App\Http\Controllers\TelesecretariatController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SpecialityController;
use App\Http\Controllers\DoctorTelesecretariatController;
use App\Http\Controllers\DoctorPermissionController;
use App\Http\Controllers\NewsLatterController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\DoctorTagController;
use App\Http\Controllers\ParrainerController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\DoctorsGalleryController;
use App\Http\Controllers\DoctorBlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PhotosCabinetController;
use App\Http\Controllers\DoctorUserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PersonalizedMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardMedecinController;



Route::get('/payment-success', function () {
    return view('payment.success'); // Your success Blade view
})->name('payment.success');

Route::get('/payment-error', function () {
    return view('payment.error'); // Your error Blade view
})->name('payment.error');

////////////////////////////
Route::prefix('profile_management')->group(function () {
    Route::resource('Doctors_permissions', DoctorPermissionController::class);
    Route::resource('Doctors_users', DoctorUserController::class)->parameters([
        'Doctors_users' => 'user', // Alias Doctors_user to user
    ]);
    Route::post('Doctors_permissions/update', [DoctorPermissionController::class, 'update'])
        ->name('Doctors_permissions.update');
    Route::post('Doctors_permissions/fetchUserRoles', [DoctorPermissionController::class, 'fetchUserRoles'])
        ->name('Doctors_permissions.fetchUserRoles');

    Route::post('Doctors_permissions/storePermissions', [DoctorPermissionController::class, 'storePermissions'])->name('Doctors_permissions.storePermissions');
    Route::post('telesecretary_management/store_telesecretary', [DoctorUserController::class, 'store_telesecretary'])->name('telesecretary_management.store_telesecretary');

});
////////////////////////


Auth::routes();
Route::middleware('auth')->group(function () {
    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/availability', [AvailabilityController::class, 'store'])->name('availability.store');
});

Route::post('/availability/store', [AvailabilityController::class, 'store'])->name('availability.store');
Route::get('/doctor/vacance', [DoctorVacationController::class, 'index'])->name('vacance.index');
Route::post('/availability/store-open', [AvailabilityController::class, 'storeOpen'])
    ->name('availability.store.open');
Route::post('/availability/substitute', [AvailabilityController::class, 'storeSubstitute'])->name('substitute.store');
Route::delete('/availability/substitute/{id}', [AvailabilityController::class, 'deleteSubstitute'])->name('substitute.destroy');
Route::put('/vacances/{id}', [AvailabilityController::class, 'updateVacation'])
    ->name('vacances.update');

Route::delete('vacances/{id}', [DoctorVacationController::class, 'destroy'])->name('vacances.destroy');
Route::get('/availability-tele', [AvailabilityController::class, 'indexTele'])->name('availability.tele');
Route::post('/availability-tele', [AvailabilityController::class, 'storeTele'])->name('availabilityTele.store');
Route::post('/availability-tele/store', [AvailabilityController::class, 'storeTele'])->name('availabilityTele.store');
Route::post('/holidays/store', [DoctorVacationController::class, 'store'])->name('holidays.store');
Route::post('/urgency/store', [DoctorUrgencyController::class, 'store'])->name('urgency.store');
Route::get('/doctor/urgencies', [DoctorUrgencyController::class, 'index'])->name('urgency.index');
Route::delete('urgencies/{id}', [DoctorUrgencyController::class, 'destroy'])->name('urgencies.destroy');
Route::put('/urgencies/{id}', [DoctorUrgencyController::class, 'update'])->name('urgencies.update');
Route::get('payments/failed', 'PayPalController@index')->name('payments.failed');
Route::get('payments/razorpay/checkout', 'RazorPayController@checkout');
Route::post('payments/razorpay/pay-success/{appointmentId}', 'RazorPayController@paySuccess');
Route::get('payments/razorpay', 'RazorPayController@index');

Route::get('payments/stripe/checkout', 'StripeController@checkout');
Route::get('payments/stripe/pay-success/{appointmentId}/{paymentMethodId}', 'StripeController@paySuccess');
Route::get('payments/stripe', 'StripeController@index');

Route::get('payments/paymongo/checkout', 'PayMongoController@checkout');
Route::get('payments/paymongo/processing/{appointmentId}/{paymentMethodId}', 'PayMongoController@processing');
Route::get('payments/paymongo/success/{appointmentId}/{paymentIntentId}', 'PayMongoController@success');
Route::get('payments/paymongo', 'PayMongoController@index');

Route::get('payments/stripe-fpx/checkout', 'StripeFPXController@checkout');
Route::get('payments/stripe-fpx/pay-success/{appointmentId}', 'StripeFPXController@paySuccess');
Route::get('payments/stripe-fpx', 'StripeFPXController@index');

Route::get('payments/flutterwave/checkout', 'FlutterWaveController@checkout');
Route::get('payments/flutterwave/pay-success/{appointmentId}/{transactionId}', 'FlutterWaveController@paySuccess');
Route::get('payments/flutterwave', 'FlutterWaveController@index');

Route::get('payments/paystack/checkout', 'PayStackController@checkout');
Route::get('payments/paystack/pay-success/{appointmentId}/{reference}', 'PayStackController@paySuccess');
Route::get('payments/paystack', 'PayStackController@index');

Route::get('payments/paypal/express-checkout', 'PayPalController@getExpressCheckout')->name('paypal.express-checkout');
Route::get('payments/paypal/express-checkout-success', 'PayPalController@getExpressCheckoutSuccess');
Route::get('payments/paypal', 'PayPalController@index')->name('paypal.index');

Route::get('firebase/sw-js', 'AppSettingController@initFirebase');

Route::post('paypal/payment', [PayPalController::class, 'payment'])->name('paypal.payment');
Route::get('paypal/payment/success', [PayPalController::class, 'paymentSuccess'])->name('paypal.payment.success');
Route::get('paypal/payment/cancel', [PayPalController::class, 'paymentCancel'])->name('paypal.payment.cancel');
/////////
Route::get('/konnect/payment/success', [KonnectController::class, 'paymentSuccess'])
    ->withoutMiddleware(['auth', 'permissions', 'web'])
    ->name('konnect.payment.success');

Route::get('/konnect/payment/fail', [KonnectController::class, 'paymentFail'])
    ->withoutMiddleware(['auth', 'permissions', 'web'])
    ->name('konnect.payment.fail');

Route::resource('clinicReviews', 'ClinicReviewController');

Route::resource('clinicLevels', 'ClinicLevelController')->except([
    'show'
]);


Route::post('/consultation/report', [ConsultationController::class, 'addReport']);

Route::get('storage/app/public/{id}/{conversion}/{filename?}', 'UploadController@storage');
//Route::middleware('auth')->group(function () {
Route::group(['middleware' => ['auth', 'check.membership']], function () {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::resource('patterns', PatternController::class);
    Route::get('/teleconsultations', [MeetController::class, 'index'])->name('teleconsultations.index');
    Route::get('/meet', [MeetController::class, 'index'])->name('meet.index');
    Route::post('/meet/create', [MeetController::class, 'createMeet']);
    Route::post('/meet/send-sms', [MeetController::class, 'sendSms'])->name('meet.send-sms');
    Route::post('uploads/store', 'UploadController@store')->name('medias.create');
    Route::get('users/profile', 'UserController@profile')->name('users.profile');
    Route::post('users/remove-media', 'UserController@removeMedia');
    Route::resource('users', 'UserController');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::group(['middleware' => ['permission:medias']], function () {
        Route::get('uploads/all/{collection?}', 'UploadController@all');
        Route::get('uploads/collectionsNames', 'UploadController@collectionsNames');
        Route::post('uploads/clear', 'UploadController@clear')->name('medias.delete');
        Route::get('medias', 'UploadController@index')->name('medias');
        Route::get('uploads/clear-all', 'UploadController@clearAll');
    });
    //////////////// doctor gallery
    Route::group(['prefix' => 'doctors-gallery', 'middleware' => ['auth']], function () {
        Route::get('/', 'DoctorsGalleryController@index')->name('doctors_gallery.index');
        //Route::get('/collections', 'DoctorsGalleryController@collections')->name('doctors_gallery.collections');
        Route::get('/categories', [DoctorsGalleryController::class, 'categories'])->name('doctors_gallery.categories'); // Fetch available categories
        Route::get('/collections', [DoctorsGalleryController::class, 'collections'])->name('doctors_gallery.collections'); // Fetch collections (old version)
        Route::get('/all/{category?}', [DoctorsGalleryController::class, 'all'])->name('doctors_gallery.all'); // Fetch images in a specific category
        Route::post('/store', 'DoctorsGalleryController@store')->name('doctors_gallery.store');
        //Route::get('/all/{collection?}', 'DoctorsGalleryController@all')->name('doctors_gallery.all');
        Route::post('/clear', 'DoctorsGalleryController@clear')->name('doctors_gallery.clear');
        Route::post('/store-cabinet', [DoctorsGalleryController::class, 'storeCabinet'])
            ->name('doctors_gallery.store_cabinet');

        Route::get('/all-cabinet', [DoctorsGalleryController::class, 'allCabinet'])
            ->name('doctors_gallery.all_cabinet');
        Route::post('/clear-file', [DoctorsGalleryController::class, 'clearFile'])
            ->name('doctors_gallery.clear_file');
    });


    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::get('permissions/role-has-permission', 'PermissionController@roleHasPermission');
        Route::get('permissions/refresh-permissions', 'PermissionController@refreshPermissions');
    });
    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::post('permissions/give-permission-to-role', 'PermissionController@givePermissionToRole');
        Route::post('permissions/revoke-permission-to-role', 'PermissionController@revokePermissionToRole');
    });

    Route::get('modules', 'ModuleController@index')->name('modules.index');
    Route::put('modules/{id}', 'ModuleController@enable')->name('modules.enable');
    Route::post('modules/{id}/install', 'ModuleController@install')->name('modules.install');
    Route::post('modules/{id}/update', 'ModuleController@update')->name('modules.update');
    // Route indépendante pour updateLanguage avec son propre middleware ou sans middleware
    Route::patch('settings/updateLanguage', 'AppSettingController@updateLanguage')
        ->middleware(['permission:update-language']) // Remplacez ou supprimez le middleware selon vos besoins
        ->name('update-language');


    Route::group(['middleware' => ['permission:app-settings']], function () {
        Route::prefix('settings')->group(function () {
            Route::resource('permissions', 'PermissionController');
            Route::resource('roles', 'RoleController');
            Route::resource('customFields', 'CustomFieldController');
            Route::resource('currencies', 'CurrencyController')->except([
                'show'
            ]);
            Route::resource('taxes', 'TaxController')->except([
                'show'
            ]);
            Route::get('users/login-as-user/{id}', 'UserController@loginAsUser')->name('users.login-as-user');
            Route::patch('update', 'AppSettingController@update');
            // Route::patch('updateLanguage', 'AppSettingController@updateLanguage');
            Route::patch('translate', 'AppSettingController@translate');
            Route::get('sync-translation', 'AppSettingController@syncTranslation');
            Route::get('clear-cache', 'AppSettingController@clearCache');
            Route::get('check-update', 'AppSettingController@checkForUpdates');
            // disable special character and number in route params
            Route::get('/{type?}/{tab?}', 'AppSettingController@index')
                ->where('type', '[A-Za-z]*')->where('tab', '[A-Za-z]*')->name('app-settings');
        });
    });

    Route::post('clinics/remove-media', 'ClinicController@removeMedia');
    Route::resource('clinics', 'ClinicController')->except([
        'show'
    ]);

    Route::post('patients/remove-media', 'PatientController@removeMedia');
    Route::resource('patients', 'PatientController');

    Route::get('requestedClinics', 'ClinicController@requestedClinics')->name('requestedClinics.index');

    Route::resource('addresses', 'AddressController')->except([
        'show'
    ]);
    Route::resource('awards', 'AwardController');
    Route::resource('experiences', 'ExperienceController');

    Route::resource('availabilityHours', 'AvailabilityHourController')->except([
        'show'
    ]);
    Route::post('doctors/remove-media', 'DoctorController@removeMedia');
    Route::resource('doctors', 'DoctorController')->except([
        'show'
    ]);
    Route::resource('doctorPatients', 'DoctorPatientsController');
    Route::resource('faqCategories', 'FaqCategoryController')->except([
        'show'
    ]);
    Route::post('specialities/remove-media', 'SpecialityController@removeMedia');
    Route::resource('specialities', 'SpecialityController')->except([
        'show'
    ]);
    Route::resource('appointmentStatuses', 'AppointmentStatusController')->except([
        'show',
    ]);
    Route::post('galleries/remove-media', 'GalleryController@removeMedia');
    Route::resource('galleries', 'GalleryController')->except([
        'show'
    ]);


    Route::resource('doctorReviews', 'DoctorReviewController')->except([
        'show'
    ]);
    Route::resource('payments', 'PaymentController')->except([
        'create',
        'store',
        'edit',
        'update',
        'destroy'
    ]);
    Route::post('paymentMethods/remove-media', 'PaymentMethodController@removeMedia');
    Route::resource('paymentMethods', 'PaymentMethodController')->except([
        'show'
    ]);
    Route::resource('paymentStatuses', 'PaymentStatusController')->except([
        'show'
    ]);
    Route::resource('faqs', 'FaqController')->except([
        'show'
    ]);
    Route::resource('favorites', 'FavoriteController')->except([
        'show'
    ]);
    Route::resource('notifications', 'NotificationController')->except([
        'create',
        'store',
        'update',
        'edit',
    ]);
    // Route::resource('appointments', 'AppointmentController');

    Route::resource('earnings', 'EarningController')->except([
        'show',
        'edit',
        'update'
    ]);

    Route::get('clinicPayouts/create/{id}', 'ClinicPayoutController@create')->name('clinicPayouts.create');
    Route::resource('clinicPayouts', 'ClinicPayoutController')->except([
        'show',
        'edit',
        'update',
        'create'
    ]);

    Route::resource('optionGroups', 'OptionGroupController')->except([
        'show'
    ]);
    Route::post('options/remove-media', 'OptionController@removeMedia');
    Route::resource('options', 'OptionController')->except([
        'show'
    ]);
    Route::resource('coupons', 'CouponController')->except([
        'show'
    ]);
    Route::post('slides/remove-media', 'SlideController@removeMedia');
    Route::resource('slides', 'SlideController')->except([
        'show'
    ]);
    Route::resource('customPages', 'CustomPageController');

    Route::resource('wallets', 'WalletController')->except([
        'show'
    ]);
    Route::resource('walletTransactions', 'WalletTransactionController')->except([
        'show',
        'edit',
        'update',
        'destroy'
    ]);

    Route::post('patients/remove-media', 'PatientController@removeMedia');
    Route::resource('patients', 'PatientController');

    Route::get('consultations/create', [ConsultationController::class, 'create'])->name('consultations.create');
    Route::resource('consultations', 'ConsultationController');
    // Route pour créer une prescription avec un ID de consultation
    Route::get('prescriptions/create/{consultation_id}', [PrescriptionController::class, 'create'])->name('prescriptions.create');

    // Ressource pour gérer les prescriptions
    Route::resource('prescriptions', PrescriptionController::class);

    Route::post('/parrainer', [ParrainerController::class, 'store']);

    Route::get('/doctors/index2', [DoctorRequestController::class, 'index2']);

    Route::get('patients/{id}/email', [PatientController::class, 'openEmailClient'])->name('patients.email');
    Route::get('patients/{id}/whatsapp', 'PatientController@openWhatsAppClient')->name('patients.whatsapp');
    Route::get('/fiche/{id}', [FicheController::class, 'show'])->name('fiche.show');
    //route pour rayen
    //Route::resource('pharmacies', PharmacyController::class);
    //Route::resource('pharmacyTypes', PharmacyTypeController::class);
    Route::get('paypal', [PayPalController::class, 'index'])->name('paypal');
    Route::get('appointment-event', [AppointmentEventController::class, 'index'])->name('appointment-events.index');
    Route::post('appointment-event/action', [AppointmentEventController::class, 'action']);
    Route::get('/appointment-event/search', [AppointmentEventController::class, 'getPatients'])->name('patients.search');
    Route::post('/appointment-event/store', [AppointmentEventController::class, 'saveAppointment'])->name('appointments.store');
    Route::post('/appointment-event/status', [AppointmentEventController::class, 'updateStatus']);
    Route::get('/appointments-for-date', [AppointmentEventController::class, 'getAppointmentsForDate']);
    //Route::get('/appointments-for-date', [AppointmentEventController::class, 'getAppointmentsForDate'])->name('appointments.taken_slots');
    Route::get('/appointment-event/create', [AppointmentEventController::class, 'create'])->name('appointment-event.create');
    Route::post('/appointments/store-patient-passage', [AppointmentEventController::class, 'storePatientPassage'])->name('appointments.storePatientPassage');
    Route::get('/doctor-availability', [AppointmentEventController::class, 'getDoctorAvailability'])->name('appointment-event.getDoctorAvailability');
    Route::get('/patterns/search', [PatternController::class, 'getPatterns'])->name('patterns.search');
    Route::get('/teleconsultations', [MeetController::class, 'index'])->name('teleconsultations.index');
    Route::get('/teleconsultations/create', [MeetController::class, 'createMeet'])->name('teleconsultations.createMeet');
    Route::post('/teleconsultations/send-meeting-info', [MeetController::class, 'sendMeetingInfo'])->name('send.meeting.info');

    Route::get('/teleconsultations/send-meeting-info-form', [MeetController::class, 'showSendMeetingInfoForm'])->name('show.meeting.info.form');
    Route::post('/teleconsultations/create-specific-meeting', [MeetController::class, 'createSpecificMeeting'])->name('create.specific.meeting');
    Route::post('/meet/{id}/status', [MeetController::class, 'updateStatus'])->name('meet.update.status');

    Route::get('/meet', [MeetController::class, 'index'])->name('meet.index');
    Route::post('/meet/create', [MeetController::class, 'createMeet']);
    Route::post('/meet/send-sms', [MeetController::class, 'sendSms'])->name('meet.send-sms');
    Route::resource('patterns', PatternController::class);
    Route::get('/get-available-time-slots', [AppointmentEventController::class, 'getAvailableTimeSlots'])->name(name: 'appointments.getAvailableTimeSlots');
    Route::get('/get-available-time-slots-presice', [AppointmentEventController::class, 'getAvailableTimeSlotsPresice'])->name('appointments.getAvailableTimeSlotsPresice');
    Route::get('/get-available-For-open', [AppointmentEventController::class, 'getAvailableForOpen'])->name('appointments.getAvailableForOpen');
    Route::get('/get-available-time-slots-open', [AppointmentEventController::class, 'getAvailableTimeSlotsForOpen'])->name('appointments.getAvailableTimeSlotsForOpen');
    Route::get('/get-teleconsultation-time-slots', [AppointmentEventController::class, 'getTeleconsultationTimeSlots'])->name('get.teleconsultation.slots');
    Route::get('/get-available-days', [AppointmentEventController::class, 'getAvailableDays'])->name('get.available.days');

    // Route pour afficher toutes les prescriptions liées à une consultation
    Route::get('/consultation/{consultation}/prescriptions', [ConsultationController::class, 'showPrescriptions'])->name('consultation.prescriptions');
    Route::get('/prescriptions/{prescription}/pdf', [PrescriptionController::class, 'generatePrescriptionPdf'])->name('prescriptions.pdf');

    Route::get('/prescriptions/details/{prescriptionId}', 'PrescriptionController@showDetails');

    // Route::get('send-mail', [MailController::class, 'index']);

    Route::get('/appointments/today/completed', [AppointmentController::class, 'getTodayCompletedAppointments'])
        ->name('appointments.today.completed');



    Route::get('/send-whatsapp/{prescription_id}', [PrescriptionController::class, 'sendWhatsAppMessage'])->name('send.whatsapp');



    Route::get('/prescriptions/details/{prescriptionId}', 'PrescriptionController@showDetails');
    Route::get('/assurances', [AssuranceController::class, 'index'])->name('assurances.index');
    Route::resource('assurances', AssuranceController::class);

    Route::resource('doctor_requests', DoctorRequestController::class);
    Route::post('/doctor-request/{id}/create-user', [DoctorRequestController::class, 'createUserFromDoctorRequest'])->name('doctor_requests.createUserFromDoctorRequest');
    Route::get('/doctor-requests/{id}', [DoctorRequestController::class, 'show']);
    Route::post('/doctor-requests', [DoctorRequestController::class, 'store']);
    Route::post('/doctor-requests', [DoctorRequestController::class, 'store']);

    Route::resource('telesecretariats', TelesecretariatController::class);
    Route::get('/telesecretariats/show/{id}', [TelesecretariatController::class, 'show']);

    Route::get('/profil-doctor', [DoctorController::class, 'profileDoctor'])->name('fieldsDoctor');

    Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');



    Route::get('telesecretariats/relation', [TelesecretariatController::class, 'relation'])->name('telesecretariats.relation');

    Route::get('/specialitiesByPays', [SpecialityController::class, 'getSpecialitiesByCountry']);

    Route::prefix('doctor_telesecretariat')->name('doctor_telesecretariat.')->group(function () {
        Route::post('store-profile-management', [DoctorTelesecretariatController::class, 'storeProfileManagment'])->name('store_profile_management');
    });
    Route::resource('doctor_telesecretariat', DoctorTelesecretariatController::class);

    ////////////////////////////
    //teleagenda
    //////////////////////////
    Route::get('tele-doctor-agenda', [DoctorTelesecretariatController::class, 'index'])->name('tele-appointment-events.index');
    Route::get('/tele-doctor-agenda-data', [DoctorTelesecretariatController::class, 'getDoctorData']);
    Route::get('/get-doctor-appointments', [DoctorTelesecretariatController::class, 'getDoctorAppointments']);
    // Save a new appointment for a specific doctor
    Route::post('/tele-save-appointment', [DoctorTelesecretariatController::class, 'saveAppointment'])->name('tele_save_appointment');

    // Store patient passage (walk-in) appointment
    Route::post('/tele-store-patient-passage', [DoctorTelesecretariatController::class, 'storePatientPassage'])->name('tele_store_patient_passage');

    // Update appointment status
    Route::post('/tele-appointment-event/status', [DoctorTelesecretariatController::class, 'updateStatus'])->name('tele_update_appointment_status');

    // Fetch available time slots for a specific doctor
    Route::get('/tele-get-available-time-slots', [DoctorTelesecretariatController::class, 'getAvailableTimeSlots'])->name('tele_get_available_time_slots');

    // Fetch teleconsultation time slots for a specific doctor
    Route::get('/tele-get-teleconsultation-time-slots', [DoctorTelesecretariatController::class, 'getTeleconsultationTimeSlots'])->name('tele_get_teleconsultation_time_slots');

    // Fetch patients related to a specific doctor
    Route::get('/tele-patients/search', [DoctorTelesecretariatController::class, 'getPatients'])->name('tele_patients_search');

    // Fetch appointments for a specific date
    Route::get('/tele-get-appointments-for-date', [DoctorTelesecretariatController::class, 'getAppointmentsForDate'])->name('tele_get_appointments_for_date');

    Route::get('/tele-patterns', [DoctorTelesecretariatController::class, 'getPatterns'])->name('tele_patterns');

    Route::get('/tele-get-doctor-availability-data', [DoctorTelesecretariatController::class, 'getDoctorAvailabilityData']);
    // In routes/web.php


    Route::resource('newsletters', NewsLatterController::class);




    Route::get('/generer-link', [PatientController::class, 'genererLink'])->name('generer.link');
    Route::resource('/tags', TagController::class);
    Route::get('/tags/{id}/edit', [TagController::class, 'edit']);

    Route::resource('doctor_tag', DoctorTagController::class);
    Route::post('/doctor-tags', [DoctorTagController::class, 'store'])->name('doctor_tags.store');



    Route::post('/envoyer-email', [ParrainerController::class, 'envoyerEmail'])
        ->name('envoyer-email')
        ->middleware('auth');
    Route::get('/doctors/parrainage/{codeParrain}', [DoctorController::class, 'getDoctors'])->name('doctors.parrainage');
    Route::get('/parrainer', [ParrainerController::class, 'index'])
        ->name('parrainers.index')
        ->middleware('auth');
    Route::get('/messagerie', [MessagerieController::class, 'index'])->name('messagerie.index');


    Route::get('/parrainer2', [ParrainerController::class, 'parrainer'])
        ->name('parrainers.parrainer')
        ->middleware('auth');

    Route::get('/listdoctors', [ParrainerController::class, 'listDoctors'])->name('parrainers.listdoctors');

    Route::post('/adresse/store', [AddressController::class, 'store']);
    Route::get('editProfil', [DoctorController::class, 'editProfil'])->name('doctors.editProfil');
    Route::post('/edit-info-personnelle', [DoctorController::class, 'editInfoPersonnelle'])->name('editInfoPersonnelle');
    Route::post('/edit-cv', [DoctorController::class, 'editCV'])->name('editCV');

    Route::middleware(['auth'])->group(function () {
        Route::get('/messagerie', [MessagerieController::class, 'index'])->name('messagerie.index');
        Route::get('/messagerie/create', [MessagerieController::class, 'create'])->name('messagerie.create');
        Route::post('/messagerie/send', [MessagerieController::class, 'send'])->name('messagerie.send');
        Route::get('/messagerie/conversation/{id}', [MessagerieController::class, 'showConversation'])->name('messagerie.showConversation');
    });



    Route::get('/doctor-blog', [DoctorBlogController::class, 'index'])->name('doctor_blog.index');
    Route::get('/doctor-blog/create', [DoctorBlogController::class, 'create'])->name('doctor_blog.create');
    Route::post('/doctor-blog', [DoctorBlogController::class, 'store'])->name('doctor_blog.store');

    Route::post('uploads/storeImage', [DoctorBlogController::class, 'storeImage'])->name('uploads.storeImage');
    Route::get('doctor_blog/{id}', [DoctorBlogController::class, 'show'])->name('doctor_blog.show');
    Route::delete('/doctor_blog/{id}', [DoctorBlogController::class, 'destroy'])->name('doctor_blog.destroy');
    Route::get('doctor_blog/{id}/edit', [DoctorBlogController::class, 'edit'])->name('doctor_blog.edit');
    Route::patch('doctor_blog/{id}', [DoctorBlogController::class, 'update'])->name('doctor_blog.update');
    Route::post('uploads/deleteImage', [DoctorBlogController::class, 'deleteImage'])->name('uploads.deleteImage');
    Route::get('doctor_blogs/accepted', [DoctorBlogController::class, 'acceptedBlogs'])->name('doctor_blog.accepted');
    Route::get('doctor_blogs/rejected', [DoctorBlogController::class, 'rejectedBlogs'])->name('doctor_blog.rejected');

    Route::get('/doctor_blog/accept/{id}', [DoctorBlogController::class, 'accepterBlog'])
        ->name('doctor_blog.accept');
    Route::post('/doctor_blog/rejet/{id}', [DoctorBlogController::class, 'rejeterBlog'])
        ->name('doctor_blog.rejet');

    Route::get('/photos-cabinet', [PhotosCabinetController::class, 'index'])->name('photos_cabinet.index');
    Route::get('photos-cabinet/{id}', [PhotosCabinetController::class, 'show'])->name('photos_cabinet.show');
    Route::post('photos-cabinet/accept', [PhotosCabinetController::class, 'accept'])->name('photos_cabinet.accept');
    Route::post('photos-cabinet/rejet', [PhotosCabinetController::class, 'rejet'])->name('photos_cabinet.rejet');
    Route::post('/doctor/update-chart-status', [DoctorController::class, 'updateChartStatus'])->name('doctor.updateChartStatus');
    Route::get('/suivi-doctors', [DoctorController::class, 'SuiviDoctorsIndex'])->name('suivi_doctors.index');
    Route::get('/doctor/total-pourcentage', [DoctorController::class, 'getTotalPourcentage'])
        ->name('doctor.total-pourcentage');
    Route::get('/generate-doctor-url/{doctorId}', [DoctorController::class, 'generateDoctorUrl'])->name('generateDoctorUrl');
    Route::get('/medecin/generer-url', [DoctorController::class, 'generateConnectedDoctorUrl'])->name('doctors.generateUrl');

    Route::post('/prescriptions/{prescription}/send-email', [PrescriptionController::class, 'sendEmail'])
        ->name('prescriptions.sendEmail');
    Route::post('/teleconsultations/create-specific-meeting', [MeetController::class, 'createSpecificMeeting'])->name('create.specific.meeting');


    Route::get('/serve-file/{doctorId}/{category}/{status}/{fileName}', function ($doctorId, $category, $status, $fileName) {
        $filePath = "/mnt/doctor/{$doctorId}/{$category}/{$status}/{$fileName}";

        if (!file_exists($filePath)) {
            abort(404);
        }

        return Response::file($filePath);
    })->name('serveFile');


    Route::get('/chatDP', [PatientDoctorChatController::class, 'index'])->name('chatDP.index');
    Route::get('/chatDP/{doctorUserId}/{patientUserId}', [PatientDoctorChatController::class, 'showChat'])
        ->name('chatDP.show')
        ->where(['doctorUserId' => '[0-9]+', 'patientUserId' => '[0-9]+']);
    Route::get('/get-patients-by-letter', [ChatController::class, 'getPatientsByLetter']);
    // routes/web.php
    Route::post('/chatDP/send', [PatientDoctorChatController::class, 'sendMessage'])->name('chatDP.send');
    // Afficher la page d'index du chat (pour les médecins)
    Route::get('/chatDP', [PatientDoctorChatController::class, 'index'])->name('chatDP.index');
    Route::get('/chatDP/{doctorUserId}/{patientUserId}', [PatientDoctorChatController::class, 'showChat'])
        ->name('chatDP.show');
    // Envoyer un message (pour les médecins et les patients)
    // Chemin corrigé avec 'chats'
    Route::delete('/messages/{chatId}/chats/{messageId}', [PatientDoctorChatController::class, 'deleteMessage']);


    Route::get('/get-pattern-for-time-slot', [AppointmentEventController::class, 'getPatternForTimeSlot'])->name('get.pattern.for.time.slot');
    Route::post('/appointmentsEvent/store', [AppointmentEventController::class, 'store'])
        ->name('appointmentsEvent.store');
    Route::get('/get-available-time-slots-for-update', [AppointmentEventController::class, 'getAvailableTimeSlotsForUpdate']);

    Route::get('/get-pattern-for-time-slot-without-type', [AppointmentEventController::class, 'getPatternForTimeSlotWithoutType'])->name('get.slot.no.type');
    Route::post('/appointmentsEvent/storeForced', [AppointmentEventController::class, 'storeForced'])
        ->name('appointmentsEvent.storeForced');

    Route::get('/availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::post('/availability/store', [AvailabilityController::class, 'store'])->name('availability.store');
    Route::post('/availability/vacation/store', [AvailabilityController::class, 'storeVacation'])->name('holidays.store');
    Route::delete('/availability/vacation/{id}', [AvailabilityController::class, 'deleteVacation'])->name('vacances.destroy');

    Route::get('/substitutes/{doctorId}', [AppointmentEventController::class, 'getSubstitutes'])->name('get.substitutes');
    Route::get('/appointments/stats/{doctorId}/{selectedDate?}', [AppointmentEventController::class, 'getAppointmentStats'])
        ->name('appointment.stats');

    Route::prefix('availability')->group(function () {
        // ... existing availability routes ...

        // Add these new closure routes
        Route::post('/closures/store', [AvailabilityController::class, 'storeClosures'])
            ->name('availability.closures.store');
        Route::delete('/closures/{id}', [AvailabilityController::class, 'destroyClosures'])
            ->name('availability.closures.destroy');
        Route::get('/closures', [AvailabilityController::class, 'getClosures'])
            ->name('availability.closures.index');
        Route::put('/closures/{id}', [AvailabilityController::class, 'updateClosures'])
            ->name('availability.closures.update');  // Add this line
    });



    Route::get('/messages/{doctorId}', [ChatController::class, 'getMessagesForDoctor']);
    Route::get('/chat/messages/{doctorId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/sendMessage', [ChatController::class, 'sendMessage'])->name('chat.sendMessage');
    Route::get('/chat', [ChatController::class, 'showForm']);

    Route::get('storage/{file}', function ($file) {
        $path = storage_path('app/public/' . $file);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->file($path);
    });
    Route::get('/download/{filename}', function ($filename) {
        $path = storage_path('app/public/chat_files/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    })->name('download.file');


    Route::get('/chat/patients', [ChatController::class, 'getPatientsByLetter']);
    Route::get('/last-message', [ChatController::class, 'getLastMessage']);
    Route::delete('/messages/{chatId}/{messageId}', [ChatController::class, 'deleteMessage'])->name('chat.deleteMessage');
    Route::get('/chat/messages/{doctorId}', [ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat', [ChatController::class, 'showForm'])->name('chat.showForm');

    Route::get('/chat/{userId}/{doctorId}', [ChatController::class, 'showChat']);

    Route::get('/fetch-messages/{userId}', [ChatController::class, 'fetchMessages']);

    Route::post('/mark-notifications-as-read', [ChatController::class, 'markNotificationsAsRead']);




    Route::post('/users/accept-new-features', [UserController::class, 'acceptNewFeatures'])->name('users.acceptNewFeatures');
    Route::post('/users/reject-new-features', [UserController::class, 'rejectNewFeatures'])->name('users.rejectNewFeatures');

    Route::get('/tele-get-background-color-agenda', [DoctorTelesecretariatController::class, 'BackgroundColorForAgenda'])->name('get.background.color.agenda');
    Route::get('/tele-substitutes/{doctorId}', [DoctorTelesecretariatController::class, 'getSubstitutes'])->name('get.tele.substitutes');
    Route::get('/tele-appointments/stats/{doctorId}/{selectedDate?}', [DoctorTelesecretariatController::class, 'getAppointmentStats'])
        ->name(name: 'get.tele.AppointmentStats');
    Route::get('/tele-get-pattern-for-time-slot', [DoctorTelesecretariatController::class, 'telegetPatternForTimeSlot'])->name('tele.get.pattern.for.time.slot');
    Route::get('/tele-get-pattern-for-time-slot-without-type', [DoctorTelesecretariatController::class, 'telegetPatternForTimeSlotWithoutType'])->name('tele.get.slot.no.type');
    Route::post('/set-active-doctor', function (Request $request) {
        $doctorId = $request->input('doctorId');
        if ($doctorId) {
            session(['selectedDoctorId' => $doctorId]);
        }
        return response()->json(['success' => true]);
    })->middleware('auth');
    Route::get('/refresh-active-doctor', function () {
        $activeDoctor = null;
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('Telesecretary')) {
                $doctorId = session('selectedDoctorId');
                if ($doctorId) {
                    $activeDoctor = \App\Models\Doctor::find($doctorId);
                }
            } else {
                $activeDoctor = \App\Models\Doctor::find($user->getDoctorId());
            }
        }
        return view('components.active-doctor', compact('activeDoctor'));
    })->middleware('auth');

    // Drug-drug interactions page (requires login)
    Route::get('/drug_drug_interactions', [DrugController::class, 'index'])->name('drug_drug_interactions.index');

    Route::prefix('patient_files')->name('patient_files.')->group(function () {
        Route::get('/{patient?}', [PatientFileController::class, 'index'])->name('index');
        Route::get('/{patient}/create', [PatientFileController::class, 'create'])->name('create');
        Route::get('/{patient}/{file}', [PatientFileController::class, 'show'])->name('show');
        Route::post('/{patient}', [PatientFileController::class, 'store'])->name('store');
        Route::get('/{patient}/{file}/download', [PatientFileController::class, 'download'])->name('download');
        Route::delete('/{patient}/{file}', [PatientFileController::class, 'destroy'])->name('destroy');
        Route::post('/{patient}/assign-doctor', [PatientFileController::class, 'assignDoctor'])->name('assign_doctor');
        Route::post('/{patient}/{file}/assign-access', [PatientFileController::class, 'assignAccess'])->name('assign_access');
        Route::post('/{patient}/{file}/revoke-access', [PatientFileController::class, 'revokeAccess'])->name('revoke_access');
    });

});
Route::get('/chatTE', [TeleseceteriatDoctorsController::class, 'showChat']);
Route::get('/chatTe', [TeleseceteriatDoctorsController::class, 'showForm'])->name('chat.form');
Route::get('/chatTE/{doctorUserId}/{teleSecretariatUserId}', [TeleseceteriatDoctorsController::class, 'showChat'])
    ->name('chatT.show');
// Routes
Route::get('/chatT/{doctorUserId}/{teleSecretariatUserId}', [TeleseceteriatDoctorsController::class, 'showChat'])
    ->name('chatT.show')
    ->whereNumber(['doctorUserId', 'teleSecretariatUserId']);
Route::delete('/chatT/messages/{messageId}', [TeleseceteriatDoctorsController::class, 'deleteMessage'])->name('chatT.deleteMessage');
Route::get('/chatT/{doctorUserId}/{teleSecretariatUserId}', [TeleseceteriatDoctorsController::class, 'showChat'])->name('chatT.show');
Route::post('/chatT/send', [TeleseceteriatDoctorsController::class, 'sendMessage'])->name('chatT.send');
Route::get('/chatT/fetch-messages/{receiverId}', [TeleseceteriatDoctorsController::class, 'fetchMessages'])->name('chat.fetch');

Route::get('/chatTe', [TeleseceteriatDoctorsController::class, 'showForm'])->name('chat.form');

Route::delete('/chatT/messages/{messageId}', [TeleseceteriatDoctorsController::class, 'deleteMessage'])->name('chatT.deleteMessage');
Route::get('/chatT/{doctorUserId}/{teleSecretariatUserId}', [TeleseceteriatDoctorsController::class, 'showChat'])->name('chatT.show');
Route::post('/chatT/send', [TeleseceteriatDoctorsController::class, 'sendMessage'])->name('chatT.send');


Route::get('/helpdesk', [HelpDeskController::class, 'index'])->name('helpdesk.index');
Route::post('/helpdesk', [HelpDeskController::class, 'store'])->name('helpdesk.store');

Route::get('/dashboard-medecin', [DashboardMedecinController::class, 'index'])->name('dashboard.medecin');
// Update appointment (PUT request)
Route::put('/update-appointments/{id}', [AppointmentEventController::class, 'update'])->name('appointments.update');

// Delete appointment (DELETE request)/
Route::delete('/update-appointments/{id}', [AppointmentEventController::class, 'destroy'])->name('appointments.destroy');

// web.php
Route::post('patients/store-secondary-profile', [PatientController::class, 'storeSecondaryProfile'])->name('patients.associate');
Route::get('/patients/related/{mainPatientId}/{relation}', [PatientController::class, 'getRelatedPatients']);
Route::post('/sms/send', [PersonalizedMessageController::class, 'store'])->name('sms.store');
Route::get('/patients/{patientId}/messages/history', [PersonalizedMessageController::class, 'history'])
    ->name('messages.history');
Route::post('/doctors/edit-param', [DoctorController::class, 'editParam'])->name('doctors.editParam');


Route::get('/get-slots-for-pattern', 'AppointmentEventController@getSlotsForPattern');
use App\Http\Controllers\MedicamentPrescriptionController;

Route::resource('medicament-prescriptions', MedicamentPrescriptionController::class)
    ->names([
        'index' => 'medicament_prescriptions.index',

    ]);
Route::patch('/medicament-prescriptions/{id}/mark-treated', [App\Http\Controllers\MedicamentPrescriptionController::class, 'markAsTreated'])->name('medicament_prescriptions.markAsTreated');
