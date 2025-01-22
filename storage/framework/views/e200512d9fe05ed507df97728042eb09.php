<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('dashboard')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('dashboard*') ? 'active' : ''); ?>" href="<?php echo url('dashboard'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-tachometer-alt"></i><?php endif; ?>
            <p><?php echo e(trans('lang.dashboard')); ?></p></a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('modules.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('modules*') ? 'active' : ''); ?>" href="<?php echo route('modules.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-th-large"></i><?php endif; ?>
            <p><?php echo e(trans('lang.module_plural')); ?> <?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">New</span> <?php endif; ?></p></a>
    </li>
<?php endif; ?>
















<li class="nav-header"><?php echo e(trans('lang.app_management')); ?></li>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctor_requests.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('doctor_requests') ? 'active' : ''); ?>" href="<?php echo route('doctor_requests.index'); ?>">
    <?php if($icons): ?>
        <i class="nav-icon fas fa-paper-plane"></i> 
    <?php endif; ?>

                <p><?php echo e(trans('lang.doctor_request_plural')); ?></p>
            </a>
        </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctor_requests.create')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('doctor_requests/create') ? 'active' : ''); ?>" href="<?php echo route('doctor_requests.create'); ?>">
            <?php if($icons): ?>
                <i class="nav-icon fas fa-plus-circle"></i> 
            <?php endif; ?>
            <p><?php echo e(trans('lang.create_doctor_request')); ?></p> 
        </a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('telesecretariats.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('telesecretariats*') ? 'active' : ''); ?>" href="<?php echo route('telesecretariats.index'); ?>">
        <?php if($icons): ?>
    <i class="nav-icon fas fa-headset"></i> 
<?php endif; ?>
            <p><?php echo e(trans('lang.telesecretariat_plural')); ?></p>
        </a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctor_telesecretariat.create')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('doctor_telesecretariat*') ? 'active' : ''); ?>" href="<?php echo route('doctor_telesecretariat.create'); ?>">
        <?php if($icons): ?>
    <i class="nav-icon fas fa-headset"></i> 
<?php endif; ?>
            <p><?php echo e(trans('lang.doctor_telesecretariat')); ?></p> 
        </a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clinics.index')): ?>
    <li class="nav-item has-treeview <?php echo e((Request::is('clinic*') || Request::is('requestedClinics*') || Request::is('galleries*')  || Request::is('awards*')  ) || Request::is('clinicReviews*') && !Request::is('clinicPayouts*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e((Request::is('clinic*') || Request::is('requestedClinics*') || Request::is('galleries*') || Request::is('awards*') ) || Request::is('clinicReviews*') && !Request::is('clinicPayouts*') ? 'active' : ''); ?>"> <?php if($icons): ?>
                <i class="nav-icon fas fa-hospital-alt"></i><?php endif; ?>
            <p><?php echo e(trans('lang.clinic_plural')); ?> <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a class="nav-link <?php echo e(Request::is('clinic*') ? 'active' : ''); ?>" href="<?php echo route('clinics.index'); ?>"><?php if($icons): ?>
                        <i class="nav-icon fas fa-list-alt"></i><?php endif; ?><p><?php echo e(trans('lang.clinic_plural')); ?></p></a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('requestedClinics.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('requestedClinics*') ? 'active' : ''); ?>" href="<?php echo route('requestedClinics.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-list-alt"></i><?php endif; ?><p><?php echo e(trans('lang.requested_clinics_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clinicLevels.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('clinicLevels*') ? 'active' : ''); ?>" href="<?php echo route('clinicLevels.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-list-alt"></i><?php endif; ?><p><?php echo e(trans('lang.clinic_level_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('galleries.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('galleries*') ? 'active' : ''); ?>" href="<?php echo route('galleries.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-image"></i><?php endif; ?><p><?php echo e(trans('lang.gallery_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('awards.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('awards*') ? 'active' : ''); ?>" href="<?php echo route('awards.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-trophy"></i><?php endif; ?><p><?php echo e(trans('lang.award_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clinicReviews.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('clinicReviews*') ? 'active' : ''); ?>" href="<?php echo route('clinicReviews.index'); ?>"><?php if($icons): ?><i class="nav-icon fas fa-comments"></i><?php endif; ?><p><?php echo e(trans('lang.clinic_review_plural')); ?></p></a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('specialities.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('specialities*') ? 'active' : ''); ?>" href="<?php echo route('specialities.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-book-medical"></i><?php endif; ?><p><?php echo e(trans('lang.speciality_plural')); ?></p></a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assurances.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('assurances*') ? 'active' : ''); ?>" href="<?php echo route('assurances.index'); ?>">
            <?php if($icons): ?>
            <i class="nav-icon fas fa-shield-alt"></i>
            <?php endif; ?>
            <p><?php echo e(trans('lang.assurance_plural')); ?></p>
        </a>
    </li>
<?php endif; ?>
<!-- <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('consultations.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('consultations*') ? 'active' : ''); ?>" href="<?php echo route('consultations.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-book-medical"></i><?php endif; ?><p><?php echo e(trans('lang.consultation_plural')); ?></p></a>
    </li>
<?php endif; ?> -->

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctors.index')): ?>
    <li class="nav-item has-treeview <?php echo e(Request::is('doctors*') || Request::is('doctorReviews*')|| Request::is('availabilityHours*')|| Request::is('experiences*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e(Request::is('doctors*') || Request::is('doctorReviews*')|| Request::is('availabilityHours*')|| Request::is('experiences*') ? 'active' : ''); ?>"> <?php if($icons): ?>
                <i class="nav-icon fas fa-user-md"></i><?php endif; ?>
            <p><?php echo e(trans('lang.doctor_plural')); ?> <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctor.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('doctors*') ? 'active' : ''); ?>" href="<?php echo route('doctors.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-user-md"></i><?php endif; ?>
                        <p><?php echo e(trans('lang.doctor_table')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('appointment-events.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('appointment-event*') ? 'active' : ''); ?>" href="<?php echo route('appointment-event.index'); ?>">
                        <?php if($icons): ?>
                            <i class="nav-icon fas fa-calendar-alt"></i>
                        <?php endif; ?>
                        <p><?php echo e(trans('lang.agenda')); ?></p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doctorReviews.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('doctorReviews*') ? 'active' : ''); ?>" href="<?php echo route('doctorReviews.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-comments"></i><?php endif; ?><p><?php echo e(trans('lang.doctor_review_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('experiences.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('experiences*') ? 'active' : ''); ?>" href="<?php echo route('experiences.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-briefcase"></i><?php endif; ?><p><?php echo e(trans('lang.experience_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('availabilityHours.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('availabilityHours*') ? 'active' : ''); ?>" href="<?php echo route('availability.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-business-time"></i><?php endif; ?><p><?php echo e(trans('lang.availability_hour_plural')); ?></p></a>
                </li>
            <?php endif; ?>
	   <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patterns.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('patterns*') ? 'active' : ''); ?>" href="<?php echo route('patterns.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-stethoscope"></i><?php endif; ?><p><?php echo e(trans('lang.patterns_plural')); ?></p></a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('patients*') ? 'active' : ''); ?>" href="<?php echo route('patients.index'); ?>"><?php if($icons): ?><i class="nav-icon fas fa-procedures"></i><?php endif; ?><p><?php echo e(trans('lang.patient_plural')); ?></p></a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('appointments.today.completed')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('appointments/today/completed*') ? 'active' : ''); ?>" href="<?php echo route('appointments.today.completed'); ?>">
            <?php if($icons): ?>
                <i class="nav-icon fas fa-calendar-check"></i>
            <?php endif; ?>
            <p><?php echo e(trans('lang.appointments_completed_today')); ?></p>
        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('teleconsultation.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('teleconsultations*') ? 'active' : ''); ?>" href="<?php echo route('teleconsultations.index'); ?>">
            <?php if($icons): ?>
                <i class="nav-icon fas fa-video"></i> <!-- Remplacez ici par la nouvelle classe d'icône -->
            <?php endif; ?>
            <p><?php echo e(trans('lang.teleconsultation_plural')); ?></p>
        </a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('addresses.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('addresses*') ? 'active' : ''); ?>" href="<?php echo route('addresses.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-map-marked-alt"></i><?php endif; ?><p><?php echo e(trans('lang.address_plural')); ?></p></a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('seo.index')): ?>
<li class="nav-item">
    <a class="nav-link <?php echo e(Request::is('visibiliteSeo*') ? 'active' : ''); ?>" href="<?php echo route('seo.index'); ?>">
        <?php if($icons): ?>
            <i class="nav-icon fas fa-search"></i> <!-- Icône de recherche pour SEO -->
        <?php endif; ?>
        <p><?php echo e(trans('lang.Visibilité_SEO')); ?></p>
    </a>
</li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('coupons.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('coupons*') ? 'active' : ''); ?>" href="<?php echo route('coupons.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-ticket-alt"></i><?php endif; ?><p><?php echo e(trans('lang.coupon_plural')); ?> </p></a>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('faqs.index')): ?>
    <li class="nav-item <?php echo e(Request::is('faqCategories*') || Request::is('faqs*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e(Request::is('faqs*') || Request::is('faqCategories*') ? 'active' : ''); ?>"> <?php if($icons): ?>
                <i class="nav-icon fas fa-question-circle"></i><?php endif; ?>
            <p><?php echo e(trans('lang.faq_plural')); ?> <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('faqCategories.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('faqCategories*') ? 'active' : ''); ?>" href="<?php echo route('faqCategories.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-folder-open"></i><?php endif; ?><p><?php echo e(trans('lang.faq_category_plural')); ?></p></a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('faqs.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('faqs*') ? 'active' : ''); ?>" href="<?php echo route('faqs.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-life-ring"></i><?php endif; ?>
                        <p><?php echo e(trans('lang.faq_plural')); ?></p></a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacies.index')): ?>
    <li class="nav-header"><?php echo e(trans('lang.pharmacy_plural')); ?></li>
        <li class="nav-item has-treeview <?php echo e(Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*')? 'menu-open' : ''); ?>">
            <a href="#" class="nav-link <?php echo e(Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*')? 'active' : ''); ?>"> <?php if($icons): ?><i class="nav-icon fas fa-first-aid"></i><?php endif; ?>
                <p><?php echo e(trans('lang.pharmacy_plural')); ?><i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacies.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('pharmacies/pharmacies*') ? 'active' : ''); ?>" href="<?php echo route('pharmacies.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-first-aid"></i><?php endif; ?><p><?php echo e(trans('lang.pharmacy_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacyTypes.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/pharmacyTypes*') ? 'active' : ''); ?>" href="<?php echo route('pharmacyTypes.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-first-aid"></i><?php endif; ?><p><?php echo e(trans('lang.pharmacy_type_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('availabilityHourPharmacies.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : ''); ?>" href="<?php echo route('availabilityHourPharmacies.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon far fa-clock"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.availability_hour_pharmacy_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmaciesEarnings.index')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(Request::is('pharmacies/pharmaciesEarnings*') ? 'active' : ''); ?>" href="<?php echo route('pharmaciesEarnings.index'); ?>"><?php if($icons): ?>
                                    <i class="nav-icon fas fa-money-bill"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.pharmacy_earning_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                        </li>
                    <?php endif; ?>
            </ul>
        </li>
    <?php endif; ?>
<?php if(Module::isActivated('Subscription')): ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('subscriptionPackages.index')): ?>
        <li class="nav-header"><?php echo e(trans('subscription::lang.subscriptions')); ?></li>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('subscriptionPackages.index')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo e(Request::is('subscription/subscriptionPackages*') ? 'active' : ''); ?>" href="<?php echo route('subscriptionPackages.index'); ?>"><?php if($icons): ?>
                    <i class="nav-icon fa fa-th-list"></i><?php endif; ?>
                <p><?php echo e(trans('subscription::lang.subscription_package_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
        </li>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clinicSubscriptions.index')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo e(Request::is('subscription/clinicSubscriptions*') ? 'active' : ''); ?>" href="<?php echo route('clinicSubscriptions.index'); ?>"><?php if($icons): ?>
                    <i class="nav-icon fa fa-address-card"></i><?php endif; ?><p><?php echo e(trans('subscription::lang.clinic_subscription_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
        </li>
    <?php endif; ?>
<?php endif; ?>

<?php if(Module::isActivated('Pharmacies')): ?>
    <li class="nav-header"><?php echo e(trans('pharmacies::lang.pharmacy_plural')); ?></li>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacies.index')): ?>
        <li class="nav-item has-treeview <?php echo e(Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*')? 'menu-open' : ''); ?>">
            <a href="#" class="nav-link <?php echo e(Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*')? 'active' : ''); ?>"> <?php if($icons): ?><i class="nav-icon fas fa-first-aid"></i><?php endif; ?>
                <p><?php echo e(trans('pharmacies::lang.pharmacy_plural')); ?><i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacies.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('pharmacies/pharmacies*') ? 'active' : ''); ?>" href="<?php echo route('pharmacies.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-first-aid"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.pharmacy_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmacyTypes.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/pharmacyTypes*') ? 'active' : ''); ?>" href="<?php echo route('pharmacyTypes.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-first-aid"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.pharmacy_type_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('availabilityHourPharmacies.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : ''); ?>" href="<?php echo route('availabilityHourPharmacies.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon far fa-clock"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.availability_hour_pharmacy_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('pharmaciesEarnings.index')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(Request::is('pharmacies/pharmaciesEarnings*') ? 'active' : ''); ?>" href="<?php echo route('pharmaciesEarnings.index'); ?>"><?php if($icons): ?>
                                    <i class="nav-icon fas fa-money-bill"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.pharmacy_earning_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                        </li>
                    <?php endif; ?>
            </ul>
        </li>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medicines.index')): ?>
        <li class="nav-item has-treeview <?php echo e(Request::is('pharmacies/medicines*') || Request::is('pharmacies/forms*') || Request::is('pharmacies/medicineOptionGroups*') || Request::is('pharmacies/medicineOptions*') ? 'menu-open' : ''); ?>">
            <a href="#" class="nav-link <?php echo e(Request::is('pharmacies/medicines*') || Request::is('pharmacies/forms*') || Request::is('pharmacies/medicineOptionGroups*') || Request::is('pharmacies/medicineOptions*') ? 'active' : ''); ?>"> <?php if($icons): ?><i class="nav-icon fas fa-capsules"></i><?php endif; ?>
                <p><?php echo e(trans('pharmacies::lang.medicine_plural')); ?><i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medicines.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/medicines*') ? 'active' : ''); ?>" href="<?php echo route('medicines.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-capsules"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.medicine_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('forms.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/forms*') ? 'active' : ''); ?>" href="<?php echo route('forms.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-flask"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.form_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medicineOptionGroups.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/medicineOptionGroups*') ? 'active' : ''); ?>" href="<?php echo route('medicineOptionGroups.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-plus-square"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.medicine_option_group_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medicineOptions.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/medicineOptions*') ? 'active' : ''); ?>" href="<?php echo route('medicineOptions.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon far fa-plus-square"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.medicine_option_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>

            </ul>
        </li>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('categories.index')): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo e(Request::is('pharmacies/categories*') ? 'active' : ''); ?>" href="<?php echo route('categories.index'); ?>"><?php if($icons): ?>
                    <i class="nav-icon fas fa-book-medical"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.category_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
        </li>
    <?php endif; ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('orders.index')): ?>
        <li class="nav-item has-treeview <?php echo e(Request::is('pharmacies/orders*') || Request::is('pharmacies/orderStatuses*') ? 'menu-open' : ''); ?>">
            <a href="#" class="nav-link <?php echo e(Request::is('pharmacies/orders*') || Request::is('pharmacies/orderStatuses*') ? 'active' : ''); ?>"> <?php if($icons): ?><i class="nav-icon fas fa-shopping-bag"></i><?php endif; ?>
                <p><?php echo e(trans('pharmacies::lang.order_plural')); ?><i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('orders.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/orders*') ? 'active' : ''); ?>" href="<?php echo route('orders.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fas fa-shopping-bag"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.order_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('orderStatuses.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('pharmacies/orderStatuses*') ? 'active' : ''); ?>" href="<?php echo route('orderStatuses.index'); ?>"><?php if($icons): ?>
                                <i class="nav-icon fa fa-server"></i><?php endif; ?><p><?php echo e(trans('pharmacies::lang.order_status_plural')); ?><?php if(config('installer.demo_app')): ?> <span class="right badge badge-danger">Addon</span> <?php endif; ?></p></a>
                    </li>
                <?php endif; ?>

            </ul>
        </li>

    <?php endif; ?>

<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.index')): ?>
<li class="nav-header"><?php echo e(trans('lang.payment_plural')); ?></li>

    <li class="nav-item has-treeview <?php echo e(Request::is('payments*') || Request::is('paymentMethods*') || Request::is('paymentStatuses*')|| Request::is('clinicPayouts*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e(Request::is('payments*') || Request::is('paymentMethods*') || Request::is('paymentStatuses*')|| Request::is('clinicPayouts*') ? 'active' : ''); ?>"> <?php if($icons): ?>
                <i class="nav-icon fas fa-money-check-alt"></i><?php endif; ?>
            <p><?php echo e(trans('lang.payment_plural')); ?><i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('payments*') ? 'active' : ''); ?>" href="<?php echo route('payments.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-money-check-alt"></i><?php endif; ?><p><?php echo e(trans('lang.payment_table')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('paymentMethods.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('paymentMethods*') ? 'active' : ''); ?>" href="<?php echo route('paymentMethods.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-credit-card"></i><?php endif; ?><p><?php echo e(trans('lang.payment_method_plural')); ?></p></a>
                </li>
            <?php endif; ?>


            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('paymentStatuses.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('paymentStatuses*') ? 'active' : ''); ?>" href="<?php echo route('paymentStatuses.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-file-invoice-dollar"></i><?php endif; ?><p><?php echo e(trans('lang.payment_status_plural')); ?></p></a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clinicPayouts.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('clinicPayouts*') ? 'active' : ''); ?>" href="<?php echo route('clinicPayouts.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-money-bill-wave"></i><?php endif; ?><p><?php echo e(trans('lang.clinic_payout_plural')); ?></p></a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('wallets.index')): ?>
    <li class="nav-item has-treeview <?php echo e(Request::is('wallet*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e(Request::is('wallet*') ? 'active' : ''); ?>"> <?php if($icons): ?>
                <i class="nav-icon fas fa-wallet"></i><?php endif; ?>
            <p><?php echo e(trans('lang.wallet_plural')); ?><i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a class="nav-link <?php echo e(Request::is('wallets*') ? 'active' : ''); ?>" href="<?php echo route('wallets.index'); ?>"><?php if($icons): ?>
                        <i class="nav-icon fa fa-wallet"></i><?php endif; ?><p><?php echo e(trans('lang.wallet_table')); ?></p></a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('walletTransactions.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('walletTransactions*') ? 'active' : ''); ?>" href="<?php echo route('walletTransactions.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fa fa-list-alt"></i><?php endif; ?><p><?php echo e(trans('lang.wallet_transaction_plural')); ?></p></a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('earnings.index')): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('earnings*') ? 'active' : ''); ?>" href="<?php echo route('earnings.index'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-money-bill"></i><?php endif; ?><p><?php echo e(trans('lang.earning_plural')); ?>  </p></a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('medias')): ?>
<li class="nav-header"><?php echo e(trans('lang.app_setting')); ?></li>
    <li class="nav-item">
        <a class="nav-link <?php echo e(Request::is('medias*') ? 'active' : ''); ?>" href="<?php echo url('medias'); ?>"><?php if($icons): ?>
                <i class="nav-icon fas fa-photo-video"></i><?php endif; ?>
            <p><?php echo e(trans('lang.media_plural')); ?></p></a>
    </li>
<?php endif; ?>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('app-settings')): ?>
    <li class="nav-item has-treeview <?php echo e(Request::is('settings/mobile*') || Request::is('slides*') || Request::is('customPages*') ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e(Request::is('settings/mobile*') || Request::is('slides*') || Request::is('customPages*') ? 'active' : ''); ?>">
            <?php if($icons): ?><i class="nav-icon fas fa-mobile-alt"></i><?php endif; ?>
            <p>
                <?php echo e(trans('lang.mobile_menu')); ?>

                <i class="right fas fa-angle-left"></i>
            </p></a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="<?php echo url('settings/mobile/globals'); ?>" class="nav-link <?php echo e(Request::is('settings/mobile/globals*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-cog"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_globals')); ?>

                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/mobile/colors'); ?>" class="nav-link <?php echo e(Request::is('settings/mobile/colors*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-magic"></i> <?php endif; ?> <p><?php echo e(trans('lang.mobile_colors')); ?>

                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/mobile/authentication'); ?>" class="nav-link <?php echo e(Request::is('settings/mobile/authentication*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-comment-alt"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_authentication')); ?>

                    </p>
                </a>
            </li>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customPages.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('customPages*') ? 'active' : ''); ?>" href="<?php echo route('customPages.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fa fa-file"></i><?php endif; ?><p><?php echo e(trans('lang.custom_page_plural')); ?></p></a>
                </li>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('slides.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('slides*') ? 'active' : ''); ?>" href="<?php echo route('slides.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-images"></i><?php endif; ?><p><?php echo e(trans('lang.slide_plural')); ?> </p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

    </li>
    <li class="nav-item has-treeview <?php echo e((Request::is('settings*') ||
     Request::is('users*')) && !Request::is('settings/mobile*')
        ? 'menu-open' : ''); ?>">
        <a href="#" class="nav-link <?php echo e((Request::is('settings*') ||
         Request::is('users*')) && !Request::is('settings/mobile*')
          ? 'active' : ''); ?>"> <?php if($icons): ?><i class="nav-icon fas fa-cogs"></i><?php endif; ?>
            <p><?php echo e(trans('lang.app_setting')); ?> <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="<?php echo url('settings/app/globals'); ?>" class="nav-link <?php echo e(Request::is('settings/app/globals*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-cog"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_globals')); ?></p>
                </a>
            </li>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('users*') ? 'active' : ''); ?>" href="<?php echo route('users.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-users"></i><?php endif; ?>
                        <p><?php echo e(trans('lang.user_plural')); ?></p></a>
                </li>
            <?php endif; ?>

            <li class="nav-item has-treeview <?php echo e(Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'menu-open' : ''); ?>">
                <a href="#" class="nav-link <?php echo e(Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-user-secret"></i><?php endif; ?>
                    <p>
                        <?php echo e(trans('lang.permission_menu')); ?>

                        <i class="right fas fa-angle-left"></i>
                    </p></a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('settings/permissions') ? 'active' : ''); ?>" href="<?php echo route('permissions.index'); ?>">
                            <?php if($icons): ?><i class="nav-icon fas fa-circle-o"></i><?php endif; ?>
                            <p><?php echo e(trans('lang.permission_table')); ?></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('settings/permissions/create') ? 'active' : ''); ?>" href="<?php echo route('permissions.create'); ?>">
                            <?php if($icons): ?><i class="nav-icon fas fa-circle-o"></i><?php endif; ?>
                            <p><?php echo e(trans('lang.permission_create')); ?></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('settings/roles') ? 'active' : ''); ?>" href="<?php echo route('roles.index'); ?>">
                            <?php if($icons): ?><i class="nav-icon fas fa-circle-o"></i><?php endif; ?>
                            <p><?php echo e(trans('lang.role_table')); ?></p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(Request::is('settings/roles/create') ? 'active' : ''); ?>" href="<?php echo route('roles.create'); ?>">
                            <?php if($icons): ?><i class="nav-icon fas fa-circle-o"></i><?php endif; ?>
                            <p><?php echo e(trans('lang.role_create')); ?></p>
                        </a>
                    </li>
                </ul>

            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo e(Request::is('settings/customFields*') ? 'active' : ''); ?>" href="<?php echo route('customFields.index'); ?>"><?php if($icons): ?>
                        <i class="nav-icon fas fa-list"></i><?php endif; ?><p><?php echo e(trans('lang.custom_field_plural')); ?></p></a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/app/localisation'); ?>" class="nav-link <?php echo e(Request::is('settings/app/localisation*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-language"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_localisation')); ?></p></a>
            </li>
            <li class="nav-item">
                <a href="<?php echo url('settings/translation/en'); ?>" class="nav-link <?php echo e(Request::is('settings/translation*') ? 'active' : ''); ?>">
                    <?php if($icons): ?> <i class="nav-icon fas fa-language"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_translation')); ?></p></a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('currencies.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('settings/currencies*') ? 'active' : ''); ?>" href="<?php echo route('currencies.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-dollar-sign"></i><?php endif; ?><p><?php echo e(trans('lang.currency_plural')); ?></p></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('taxes.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('settings/taxes*') ? 'active' : ''); ?>" href="<?php echo route('taxes.index'); ?>"><?php if($icons): ?>
                            <i class="nav-icon fas fa-coins"></i><?php endif; ?>
                        <p><?php echo e(trans('lang.tax_plural')); ?></p></a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="<?php echo url('settings/payment/payment'); ?>" class="nav-link <?php echo e(Request::is('settings/payment*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-credit-card"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_payment')); ?></p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/app/social'); ?>" class="nav-link <?php echo e(Request::is('settings/app/social*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-globe"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_social')); ?></p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/app/notifications'); ?>" class="nav-link <?php echo e(Request::is('settings/app/notifications*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-bell"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_notifications')); ?></p>
                </a>
            </li>

            <li class="nav-item">
                <a href="<?php echo url('settings/mail/smtp'); ?>" class="nav-link <?php echo e(Request::is('settings/mail*') ? 'active' : ''); ?>">
                    <?php if($icons): ?><i class="nav-icon fas fa-envelope"></i> <?php endif; ?> <p><?php echo e(trans('lang.app_setting_mail')); ?></p>
                </a>
            </li>

        </ul>
    </li>
<?php endif; ?>






<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/layouts/menu.blade.php ENDPATH**/ ?>