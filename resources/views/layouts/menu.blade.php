@can('dashboard')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('dashboard*') ? 'active' : '' }}" href="{!! url('dashboard') !!}"
            style="{{ Request::is('dashboard*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-tachometer-alt"
                    style="color: {{ Request::is('dashboard*') ? '#ffffff' : '#001f3f' }};"></i>
            @endif
            <p style="color: {{ Request::is('dashboard*') ? '#ffffff' : '#001f3f' }};">{{trans('lang.dashboard')}}</p>
        </a>
    </li>
@endcan

@can('modules.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('modules*') ? 'active' : '' }}" href="{!! route('modules.index') !!}"
            style="{{ Request::is('modules*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-th-large" style="color: {{ Request::is('modules*') ? '#ffffff' : '#001f3f' }};"></i>
            @endif
            <p style="color: {{ Request::is('modules*') ? '#ffffff' : '#001f3f' }};">
                {{trans('lang.module_plural')}}
                @if (config('installer.demo_app'))
                    <span class="right badge badge-danger">New</span>
                @endif
            </p>
        </a>
    </li>
@endcan

{{--@can('notifications.index')--}}
{{-- <li class="nav-item">--}}
    {{-- <a class="nav-link {{ Request::is('notifications*') ? 'active' : '' }}"
        href="{!! route('notifications.index') !!}">@if($icons)--}}
        {{-- <i class="nav-icon fas fa-bell"></i>@endif<p>{{trans('lang.notification_plural')}}</p></a>--}}
    {{-- </li>--}}
{{--@endcan--}}
{{--@can('favorites.index')--}}
{{-- <li class="nav-item">--}}
    {{-- <a class="nav-link {{ Request::is('favorites*') ? 'active' : '' }}"
        href="{!! route('favorites.index') !!}">@if($icons)--}}
        {{-- <i class="nav-icon fas fa-heart"></i>@endif<p>{{trans('lang.favorite_plural')}}</p></a>--}}
    {{-- </li>--}}
{{--@endcan--}}



<li class="nav-header" style="font-size: 1rem; color: #001f3f; font-weight: bold;">{{trans('lang.app_management')}}</li>

@php
    $doctorMenuOpen = Request::is('doctors*') || Request::is('doctorReviews*') || Request::is('availabilityHours*') || Request::is('experiences*') || Request::is('patterns*');
@endphp



@can('dashboard.medecin')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('dashboard.medecin*') ? 'active' : '' }}"
            href="{!! route('dashboard.medecin') !!}"
            style="{{ Request::is('dashboard.medecin*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-chart-line"
                    style="color: {{ Request::is('dashboard.medecin*') ? '#ffffff' : '#001f3f' }};"></i>

            @endif
            <p style="color: {{ Request::is('dashboard.medecin*') ? '#ffffff' : '#001f3f' }};">
                {{ trans('lang.stat') }}
            </p>
        </a>
    </li>
@endcan
{{-- Médecins --}}
@can('doctor.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctors*') ? 'active' : '' }}" href="{!! route('doctors.index') !!}"
            style="{{ Request::is('doctors*') ? 'background-color: #001f3f;' : '' }}">

            @if($icons)
                <i class="nav-icon fas fa-user-md" style="color: {{ Request::is('doctors*') ? '#ffffff' : '#001f3f' }};"></i>
            @endif
            <p style="color: {{ Request::is('doctors*') ? '#ffffff' : '#001f3f' }};">
                {{ trans('lang.doctor_table') }}
            </p>
        </a>
    </li>
@endcan

{{-- Agenda des médecins pour les télésecrétaires --}}
@if(Auth::check() && Auth::user()->hasRole('Telesecretary'))
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor-telesecretariat*') ? 'active' : '' }}"
            href="{!! route('doctor_telesecretariat.index') !!}">
            @if($icons)
                <i class="nav-icon fas fa-calendar-alt" style="color: #001f3f;"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.agenda_des_medecins') }}</p>
        </a>
    </li>
@elseif(Auth::check() && Gate::allows('appointment-events.index'))
    {{-- Agenda général --}}
    <li class="nav-item">
        <a class="nav-link {{ Request::is('appointment-event*') ? 'active' : '' }}"
            href="{!! route('appointment-events.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-calendar-alt"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.agenda') }}</p>
        </a>
    </li>
@endif

{{-- Avis des médecins --}}
@can('doctorReviews.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctorReviews*') ? 'active' : '' }}" href="{!! route('doctorReviews.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-comments"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.doctor_review_plural') }}</p>
        </a>
    </li>
@endcan

{{-- Expériences --}}
@can('experiences.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('experiences*') ? 'active' : '' }}" href="{!! route('experiences.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-briefcase"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.experience_plural') }}</p>
        </a>
    </li>
@endcan

{{-- Disponibilités --}}
@can('availabilityHours.index')
    <li class="nav-item nav-availability">
        <a class="nav-link {{ Request::is('availabilityHours*') ? 'active' : '' }}"
            href="{!! route('availability.index') !!}">
            @if($icons)
                <i class="nav-icon fas fa-business-time"></i>
            @endif
            <p>{{ trans('lang.availability_hour_plural') }}</p>
        </a>
    </li>
@endcan

{{-- Modèles (patterns) --}}
@can('patterns.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('patterns*') ? 'active' : '' }}" href="{!! route('patterns.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-stethoscope"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.patterns_plural') }}</p>
        </a>
    </li>
@endcan


@can('patients.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('patients*') ? 'active' : '' }}"
            href="{!! route('patients.index') !!}">@if($icons)<i style="color: #001f3f;"
            class="nav-icon fas fa-procedures"></i>@endif
            <p style="color: #001f3f;">
                {{trans('lang.patient_plural')}}
            </p>
        </a>
    </li>
@endcan
@can('profile.index')
    <li
        class="nav-item has-treeview {{ Request::is('profile_management/Doctors_roles*') || Request::is('profile_management/Doctors_permissions*') || Request::is('profile_management/Doctors_users*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ Request::is('profile_management/Doctors_roles*') || Request::is('profile_management/Doctors_permissions*') || Request::is('profile_management/Doctors_users*') ? 'active' : '' }}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-user-cog"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.profile_management') }} <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            @can('Doctors_users.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('profile_management/Doctors_users*') ? 'active' : '' }}"
                        href="{!! route('Doctors_users.index') !!}">
                        @if($icons)
                            <i style="color: #001f3f;" class="nav-icon fas fa-users"></i>
                        @endif
                        <p style="color: #001f3f;">{{ trans('lang.user_management') }}</p>
                    </a>
                </li>
            @endcan
            @can('Doctors_permissions.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('profile_management/Doctors_permissions*') ? 'active' : '' }}"
                        href="{!! route('Doctors_permissions.index') !!}">
                        @if($icons)
                            <i style="color: #001f3f;" class="nav-icon fas fa-lock"></i>
                        @endif
                        <p style="color: #001f3f;">{{ trans('lang.permission_management') }}</p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
@can('appointments.today.completed')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('appointments/today/completed*') ? 'active' : '' }}"
            href="{!! route('appointments.today.completed') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-calendar-check"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.appointments_completed_today') }}</p>
        </a>
    </li>
@endcan
@can('teleconsultation.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('teleconsultations*') ? 'active' : '' }}"
            href="{!! route('teleconsultations.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-video"></i>
                <!-- Remplacez ici par la nouvelle classe d'icône -->
            @endif
            <p style="color: #001f3f;">{{ trans('lang.teleconsultation_plural') }}</p>
        </a>
    </li>
@endcan

@can('doctor_requests.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor_requests') ? 'active' : '' }}"
            href="{!! route('doctor_requests.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-paper-plane"></i> {{-- Icône pour une demande --}}
            @endif

            <p style="color: #001f3f;">{{ trans('lang.doctor_request_plural') }}</p>
        </a>
    </li>
@endcan

@can('doctor_requests.create')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor_requests/create') ? 'active' : '' }}"
            href="{!! route('doctor_requests.create') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-plus-circle"></i> {{-- Icône pour créer une demande --}}
            @endif
            <p style="color: #001f3f;">{{ trans('lang.create_doctor_request') }}</p>
        </a>
    </li>
@endcan



{{--@can('doctor_telesecretariat.index')
<li class="nav-item">
    <a class="nav-link {{ Request::is('doctor_telesecretariat') && !Request::is('doctor_telesecretariat/create') ? 'active' : '' }}"
        href="{!! route('doctor_telesecretariat.index') !!}">
        @if($icons)
        <i class="nav-icon fas fa-calendar-check"></i>
        @endif
        <p>{{ trans('lang.agenda_des_medecins') }}</p>
    </a>
</li>
@endcan --}}

@can('doctor_telesecretariat.create')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor_telesecretariat/create') ? 'active' : '' }}"
            href="{!! route('doctor_telesecretariat.create') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-headset"></i> {{-- Icône représentant un télésecrétariat
                (centre d'appel) --}}
            @endif
            <p style="color: #001f3f;">{{ trans('lang.doctor_telesecretariat') }}</p>
        </a>
    </li>
@endcan
@can('telesecretariats.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('telesecretariats') ? 'active' : '' }}"
            href="{!! route('telesecretariats.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-headset"></i> {{-- Icône représentant un télésecrétariat
                (centre d'appel) --}}
            @endif

            <p style="color: #001f3f;">{{ trans('lang.telesecretariat_plural') }}</p>
        </a>
    </li>
@endcan




@can('newsletters.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('newsletters') ? 'active' : '' }}" href="{!! route('newsletters.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-envelope"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.newsletters_plural') }}</p>
        </a>
    </li>
@endcan



@can('clinics.index')
    <li
        class="nav-item has-treeview {{ (Request::is('clinic*') || Request::is('requestedClinics*') || Request::is('galleries*') || Request::is('awards*')) || Request::is('clinicReviews*') && !Request::is('clinicPayouts*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ (Request::is('clinic*') || Request::is('requestedClinics*') || Request::is('galleries*') || Request::is('awards*')) || Request::is('clinicReviews*') && !Request::is('clinicPayouts*') ? 'active' : '' }}">
            @if($icons)
            <i style="color: #001f3f;" class="nav-icon fas fa-hospital-alt"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.clinic_plural')}} <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('clinic*') ? 'active' : '' }}"
                    href="{!! route('clinics.index') !!}">@if($icons)
                    <i style="color: #001f3f;" class="nav-icon fas fa-list-alt"></i>@endif<p style="color: #001f3f;">
                        {{trans('lang.clinic_plural')}}</p></a>
            </li>
            @can('requestedClinics.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('requestedClinics*') ? 'active' : '' }}"
                        href="{!! route('requestedClinics.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-list-alt"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.requested_clinics_plural')}}</p></a>
                </li>
            @endcan
            @can('clinicLevels.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('clinicLevels*') ? 'active' : '' }}"
                        href="{!! route('clinicLevels.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-list-alt"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.clinic_level_plural')}}</p></a>
                </li>
            @endcan
            @can('galleries.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('galleries*') ? 'active' : '' }}"
                        href="{!! route('galleries.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-image"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.gallery_plural')}}</p></a>
                </li>
            @endcan
            @can('awards.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('awards*') ? 'active' : '' }}"
                        href="{!! route('awards.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-trophy"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.award_plural')}}</p></a>
                </li>
            @endcan
            @can('clinicReviews.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('clinicReviews*') ? 'active' : '' }}"
                        href="{!! route('clinicReviews.index') !!}">@if($icons)<i style="color: #001f3f;"
                        class="nav-icon fas fa-comments"></i>@endif
                        <p style="color: #001f3f;">
                            {{trans('lang.clinic_review_plural')}}
                        </p>
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endcan

@can('specialities.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('specialities*') ? 'active' : '' }}"
            href="{!! route('specialities.index') !!}">@if($icons)
            <i style="color: #001f3f;" class="nav-icon fas fa-book-medical"></i>@endif<p style="color: #001f3f;">
                {{trans('lang.speciality_plural')}}</p></a>
    </li>
@endcan


@can('assurances.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('assurances*') ? 'active' : '' }}" href="{!! route('assurances.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-shield-alt"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.assurance_plural') }}</p>
        </a>
    </li>
@endcan
<!-- @can('consultations.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('consultations*') ? 'active' : '' }}" href="{!! route('consultations.index') !!}">@if($icons)
                <i class="nav-icon fas fa-book-medical"></i>@endif<p>{{trans('lang.consultation_plural')}}</p></a>
    </li>
@endcan -->

@can('patient_files.index')
<li class="nav-item">
    <a class="nav-link {{ Request::is('patient_files') ? 'active' : '' }}" href="{!! route('patient_files.index') !!}">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-share-alt"></i>
                @endif
                <p style="color: #001f3f;" class="mb-0">{{ trans('lang.shared_files_plural') }}</p>
            </div>
            
            {{-- Badge NEW animé --}}
            <span class="badge badge-new">
                NEW
            </span>
        </div>
    </a>
</li>

{{-- Styles CSS pour le badge animé --}}
<style>
.badge-new {
    background: linear-gradient(45deg,rgb(223, 70, 70),rgb(218, 56, 56));
    color: white;
    font-size: 9px;
    font-weight: bold;
    padding: 4px 6px;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    animation: pulse-glow 10s infinite;
}

.badge-new::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: shine 3s infinite;
    transform: rotate(45deg);
}

@keyframes pulse-glow {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(255, 107, 107, 0.5);
    }
}

@keyframes shine {
    0% {
        transform: translateX(-100%) translateY(-100%) rotate(45deg);
    }
    50% {
        transform: translateX(100%) translateY(100%) rotate(45deg);
    }
    100% {
        transform: translateX(-100%) translateY(-100%) rotate(45deg);
    }
}

/* Animation au survol */
.nav-link:hover .badge-new {
    animation: bounce 1s ease-in-out;
}

@keyframes bounce {
    0%, 20%, 60%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-8px);
    }
    80% {
        transform: translateY(-4px);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .badge-new {
        font-size: 8px;
        padding: 1px 4px;
    }
}
</style>
@endcan

@can('chatA.index')
    <li
        class="nav-item has-treeview {{ Request::is('chat*') || Request::is('chatDP*') || Request::is('chatTE*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ Request::is('chat*') || Request::is('chatDP*') || Request::is('chatTE*') ? 'active' : '' }}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-comments"></i>
            @endif
            <p style="color: #001f3f;">Messagerie <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            @can('chat.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('chat') ? 'active' : '' }}" href="{{ url('/chat') }}">
                        @if($icons)

                            <i style="color: #001f3f;" class="nav-icon fas fa-user-md"></i>
                        @endif
                        <p style="color: #001f3f;">Docteur & Docteur</p>
                    </a>
                </li>
            @endcan
            @can('chatDP.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('chatDP*') ? 'active' : '' }}" href="{{ url('/chatDP') }}">
                        @if($icons)
                            <i style="color: #001f3f;" class="nav-icon fas fa-hospital-user"></i>
                        @endif
                        <p style="color: #001f3f;">Docteur & Patient</p>
                    </a>
                </li>
            @endcan
            @can('chatTE.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('chatTE*') ? 'active' : '' }}" href="{{ url('/chatTE') }}">
                        @if($icons)
                            <i style="color: #001f3f;" class="nav-icon fas fa-headset"></i>
                        @endif
                        <p style="color: #001f3f;" style="font-size: 13.5px;">Télésecrétariat & Docteur</p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
<style>
    .nav-icon {
        width: 1.25rem;
        /* Assurez-vous que toutes les icônes ont la même largeur */
        text-align: center;
    }
</style>


@can('assistance.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('assistance*') ? 'active' : '' }}" href="{{ route('helpdesk.index') }}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-wrench"></i> <!-- Icône de service d'assistance -->
            @endif
            <p style="color: #001f3f;">Service d'assistance</p> <!-- Texte modifié ici -->
        </a>
    </li>
@endcan

<!-- @can('addresses.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('addresses*') ? 'active' : '' }}"
            href="{!! route('addresses.index') !!}">@if($icons)
            <i class="nav-icon fas fa-map-marked-alt"></i>@endif<p>{{trans('lang.address_plural')}}</p></a>
    </li>
@endcan -->


@can('tags.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('tags') ? 'active' : '' }}" href="{!! route('tags.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-tags"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.tag_plural') }}</p>
        </a>
    </li>
@endcan
@can('consultation_perso.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('consultation_perso') ? 'active' : '' }}" href="{!! route('consultation_perso.index') !!}">
            @if($icons)
<i style="color: #001f3f;" class="nav-icon fas fa-notes-medical"></i>
            @endif
            <p style="color: #001f3f;">Consult. Spécialisée</p>
        </a>
    </li>
@endcan
@can('doctor_tag.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor_tag') && !Request::is('doctor_tag/create') ? 'active' : '' }}"
            href="{!! route('doctor_tag.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-tags"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.my_tag_plural') }}</p>
        </a>
    </li>
@endcan
@can('doctor_blog.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('doctor_blog') ? 'active' : '' }}" href="{!! route('doctor_blog.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-blog"></i> <!-- Icône de blog -->
            @endif
            <p style="color: #001f3f;">{{ trans('lang.my_blog_plural') }}</p>
        </a>
    </li>
@endcan
@can('seo.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('visibiliteSeo*') ? 'active' : '' }}" href="{!! route('seo.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-search"></i> <!-- Icône de recherche pour SEO -->

            @endif
            <p style="color: #001f3f;">{{ trans('lang.Visibilité_SEO') }}</p>
        </a>
    </li>
@endcan

@can('parrainers.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('parrainer*') ? 'active' : '' }}" href="{!! route('parrainers.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-users"></i> <!-- Remplacez par l'icône de votre choix -->
            @endif
            <p style="color: #001f3f;">Parrainage</p> <!-- Texte directement modifié ici -->
        </a>
    </li>
@endcan

@can('photos_cabinet.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('photos_cabinet') ? 'active' : '' }}"
            href="{!! route('photos_cabinet.index') !!}">
            @if($icons)
                <i style="color: #001f3f;" class="nav-icon fas fa-images"></i> <!-- Icône de galerie -->
            @endif
            <p style="color: #001f3f;">{{ trans('lang.photos_cabinet') }}</p>
        </a>
    </li>
@endcan
@can('suivi_doctors.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('suivi_doctors') ? 'active' : '' }}" href="{!! route('suivi_doctors.index') !!}">
            @if($icons)
                <!-- Remplacer l'icône de la galerie par une icône de suivi des médecins -->
                <i style="color: #001f3f;" class="nav-icon fas fa-user-md"></i> <!-- Icône représentant un médecin -->
            @endif
            <p style="color: #001f3f;">{{ trans('lang.suivi_doctors') }}</p>
        </a>
    </li>
@endcan
@can('messenger.index')
    <li class="nav-item {{ Request::is('messenger*') ? 'active' : '' }}">
    <a class="nav-link {{ Request::is('messenger*') ? 'active' : '' }}" href="{{ route('messenger.index') }}">
        @if($icons)
            <i class="nav-icon fas fa-comments"></i>
        @endif
        <p>
            WIC Messenger
            <span class="right badge badge-primary" id="messenger-unread-count" style="display: none;">0</span>
        </p>
    </a>
</li>
@endcan
@can('medicament_prescriptions.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('medicament_prescriptions') ? 'active' : '' }}"
            href="{!! route('medicament_prescriptions.index') !!}">
            @if($icons)
                <!-- Icône de pilule pour représenter les médicaments -->
                <i style="color: #001f3f;" class="nav-icon fas fa-pills"></i>
            @endif
            <p style="color: #001f3f;">{{ trans('lang.liste_medicament') }}</p>
        </a>
    </li>
@endcan
<li class="nav-header" style="font-size: 1rem; color: #11b8aa; font-weight: bold;">
    <span style="font-weight: bold;">Boîte à outils IA </span> <!-- Texte en gras -->
</li>
@can('SpeechToText.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('SpeechToText*') ? 'active' : '' }}" href="{!! route('SpeechToText.index') !!}"
            style="{{ Request::is('SpeechToText*') ? 'background-color: #001f3f;' : '' }}">
            <i class="nav-icon fas fa-microphone-alt"
                style="color: {{ Request::is('SpeechToText*') ? '#ffffff' : '#11b8aa' }};"></i>
            <p style="color: {{ Request::is('SpeechToText*') ? '#ffffff' : '#001f3f' }};">Rapport IA</p>
        </a>
    </li>
@endcan



@can('extractIA.index')

    <li class="nav-item">
        <a class="nav-link {{ Request::is('extractIA*') ? 'active' : '' }}"
            href="{!! route('extractIA.index') !!}"
            style="{{ Request::is('extractIA*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-cogs"
                    style="color: {{ Request::is('extractIA*') ? '#ffffff' : '#11b8aa' }};"></i>
            @endif
            <p style="color: {{ Request::is('extractIA*') ? '#ffffff' : '#001f3f' }};">
Extracteur IA            </p>
        </a>
    </li>
@endcan


@can('drug_drug_interactions.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('drug_drug_interactions*') ? 'active' : '' }}"
            href="{!! route('drug_drug_interactions.index') !!}"
            style="{{ Request::is('drug_drug_interactions*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-robot"
                    style="color: {{ Request::is('drug_drug_interactions*') ? '#ffffff' : '#11b8aa' }};"></i>
            @endif
            <p style="color: {{ Request::is('drug_drug_interactions*') ? '#ffffff' : '#001f3f' }};">
                {{ trans('lang.drug_drug_interactions') }}
            </p>
        </a>
    </li>
@endcan



@can('imagerie.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('imagerie*') ? 'active' : '' }}" href="{!! route('imagerie.index') !!}"
            style="{{ Request::is('imagerie*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-x-ray" style="color: {{ Request::is('imagerie*') ? '#ffffff' : '#11b8aa' }};"></i>
            @endif
            <p style="color: {{ Request::is('imagerie*') ? '#ffffff' : '#001f3f' }};">Imagerie Intelligente</p>
        </a>
    </li>
@endcan

@can('Lap.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('Lap*') ? 'active' : '' }}" href="{!! route('Lap.index') !!}"
            style="{{ Request::is('Lap*') ? 'background-color: #001f3f;' : '' }}">
            @if($icons)
                <i class="nav-icon fas fa-file-prescription"
                    style="color: {{ Request::is('Lap*') ? '#ffffff' : '#11b8aa' }};"></i>
            @endif
            <p style="color: {{ Request::is('Lap*') ? '#ffffff' : '#001f3f' }};">Aide à la Prescription</p>
        </a>
    </li>
@endcan






<!-- @can('pharmacies.index')
    <li class="nav-header">{{trans('lang.pharmacy_plural')}}</li>
    <li
        class="nav-item has-treeview {{ Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : '' }}">
            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-first-aid"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.pharmacy_plural')}}<i style="color: #001f3f;" class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            @can('pharmacies.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('pharmacies/pharmacies*') ? 'active' : '' }}"
                        href="{!! route('pharmacies.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-first-aid"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.pharmacy_plural')}}@if (config('installer.demo_app')) <span
                            class="right badge badge-danger">Addon</span> @endif
                        </p></a>
                </li>
            @endcan
            @can('pharmacyTypes.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('pharmacies/pharmacyTypes*') ? 'active' : '' }}"
                        href="{!! route('pharmacyTypes.index') !!}">@if($icons)
                        <i class="nav-icon fas fa-first-aid"></i>@endif<p>
                            {{trans('lang.pharmacy_type_plural')}}@if (config('installer.demo_app')) <span
                            class="right badge badge-danger">Addon</span> @endif
                        </p></a>
                </li>
            @endcan
            @can('availabilityHourPharmacies.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : '' }}"
                        href="{!! route('availabilityHourPharmacies.index') !!}">@if($icons)
                        <i class="nav-icon far fa-clock"></i>@endif<p>
                            {{trans('pharmacies::lang.availability_hour_pharmacy_plural')}}@if (config('installer.demo_app'))
                            <span class="right badge badge-danger">Addon</span> @endif
                        </p></a>
                </li>
            @endcan
            @can('pharmaciesEarnings.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('pharmacies/pharmaciesEarnings*') ? 'active' : '' }}"
                        href="{!! route('pharmaciesEarnings.index') !!}">@if($icons)
                        <i class="nav-icon fas fa-money-bill"></i>@endif<p>
                            {{trans('pharmacies::lang.pharmacy_earning_plural')}}@if (config('installer.demo_app')) <span
                            class="right badge badge-danger">Addon</span> @endif
                        </p></a>
                </li>
            @endcan
        </ul>
    </li>
@endcan -->
@if(Module::isActivated('Subscription'))
    @can('subscriptionPackages.index')
        <li class="nav-header">{{trans('subscription::lang.subscriptions')}}</li>
    @endcan
    @can('subscriptionPackages.index')
        <li class="nav-item">
            <a class="nav-link {{ Request::is('subscription/subscriptionPackages*') ? 'active' : '' }}"
                href="{!! route('subscriptionPackages.index') !!}">@if($icons)
                <i class="nav-icon fa fa-th-list"></i>@endif
                <p>{{trans('subscription::lang.subscription_package_plural')}}@if (config('installer.demo_app')) <span
                class="right badge badge-danger">Addon</span> @endif</p>
            </a>
        </li>
    @endcan

    @can('clinicSubscriptions.index')
        <li class="nav-item">
            <a class="nav-link {{ Request::is('subscription/clinicSubscriptions*') ? 'active' : '' }}"
                href="{!! route('clinicSubscriptions.index') !!}">@if($icons)
                <i class="nav-icon fa fa-address-card"></i>@endif<p>
                    {{trans('subscription::lang.clinic_subscription_plural')}}@if (config('installer.demo_app')) <span
                    class="right badge badge-danger">Addon</span> @endif
                </p></a>
        </li>
    @endcan
@endif

@if(Module::isActivated('Pharmacies'))
    <li class="nav-header">{{trans('pharmacies::lang.pharmacy_plural')}}</li>
    @can('pharmacies.index')
        <li
            class="nav-item has-treeview {{ Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*') ? 'menu-open' : '' }}">
            <a href="#"
                class="nav-link {{ Request::is('pharmacies/pharmacies*') || Request::is('pharmacies/pharmacyTypes*') || Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : '' }}">
                @if($icons)<i class="nav-icon fas fa-first-aid"></i>@endif
                <p>{{trans('pharmacies::lang.pharmacy_plural')}}<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('pharmacies.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/pharmacies*') ? 'active' : '' }}"
                            href="{!! route('pharmacies.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-first-aid"></i>@endif<p>
                                {{trans('pharmacies::lang.pharmacy_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('pharmacyTypes.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/pharmacyTypes*') ? 'active' : '' }}"
                            href="{!! route('pharmacyTypes.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-first-aid"></i>@endif<p>
                                {{trans('pharmacies::lang.pharmacy_type_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('availabilityHourPharmacies.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/availabilityHourPharmacies*') ? 'active' : '' }}"
                            href="{!! route('availabilityHourPharmacies.index') !!}">@if($icons)
                            <i class="nav-icon far fa-clock"></i>@endif<p>
                                {{trans('pharmacies::lang.availability_hour_pharmacy_plural')}}@if (config('installer.demo_app'))
                                <span class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('pharmaciesEarnings.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/pharmaciesEarnings*') ? 'active' : '' }}"
                            href="{!! route('pharmaciesEarnings.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-money-bill"></i>@endif<p>
                                {{trans('pharmacies::lang.pharmacy_earning_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcan
    @can('medicines.index')
        <li
            class="nav-item has-treeview {{ Request::is('pharmacies/medicines*') || Request::is('pharmacies/forms*') || Request::is('pharmacies/medicineOptionGroups*') || Request::is('pharmacies/medicineOptions*') ? 'menu-open' : '' }}">
            <a href="#"
                class="nav-link {{ Request::is('pharmacies/medicines*') || Request::is('pharmacies/forms*') || Request::is('pharmacies/medicineOptionGroups*') || Request::is('pharmacies/medicineOptions*') ? 'active' : '' }}">
                @if($icons)<i class="nav-icon fas fa-capsules"></i>@endif
                <p>{{trans('pharmacies::lang.medicine_plural')}}<i class="right fas fa-angle-left"></i></p>
            </a>
            <ul class="nav nav-treeview">
                @can('medicines.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/medicines*') ? 'active' : '' }}"
                            href="{!! route('medicines.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-capsules"></i>@endif<p>
                                {{trans('pharmacies::lang.medicine_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('forms.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/forms*') ? 'active' : '' }}"
                            href="{!! route('forms.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-flask"></i>@endif<p>
                                {{trans('pharmacies::lang.form_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('medicineOptionGroups.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/medicineOptionGroups*') ? 'active' : '' }}"
                            href="{!! route('medicineOptionGroups.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-plus-square"></i>@endif<p>
                                {{trans('pharmacies::lang.medicine_option_group_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('medicineOptions.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/medicineOptions*') ? 'active' : '' }}"
                            href="{!! route('medicineOptions.index') !!}">@if($icons)
                            <i class="nav-icon far fa-plus-square"></i>@endif<p>
                                {{trans('pharmacies::lang.medicine_option_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan

            </ul>
        </li>
    @endcan
    @can('categories.index')
        <li class="nav-item">
            <a class="nav-link {{ Request::is('pharmacies/categories*') ? 'active' : '' }}"
                href="{!! route('categories.index') !!}">@if($icons)
                <i class="nav-icon fas fa-book-medical"></i>@endif<p>
                    {{trans('pharmacies::lang.category_plural')}}@if (config('installer.demo_app')) <span
                    class="right badge badge-danger">Addon</span> @endif
                </p></a>
        </li>
    @endcan
    @can('orders.index')
        <li
            class="nav-item has-treeview {{ Request::is('pharmacies/orders*') || Request::is('pharmacies/orderStatuses*') ? 'menu-open' : '' }}">
            <a href="#"
                class="nav-link {{ Request::is('pharmacies/orders*') || Request::is('pharmacies/orderStatuses*') ? 'active' : '' }}">
                @if($icons)<i class="nav-icon fas fa-shopping-bag"></i>@endif
                <p>{{trans('pharmacies::lang.order_plural')}}<i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @can('orders.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/orders*') ? 'active' : '' }}"
                            href="{!! route('orders.index') !!}">@if($icons)
                            <i class="nav-icon fas fa-shopping-bag"></i>@endif<p>
                                {{trans('pharmacies::lang.order_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan
                @can('orderStatuses.index')
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('pharmacies/orderStatuses*') ? 'active' : '' }}"
                            href="{!! route('orderStatuses.index') !!}">@if($icons)
                            <i class="nav-icon fa fa-server"></i>@endif<p>
                                {{trans('pharmacies::lang.order_status_plural')}}@if (config('installer.demo_app')) <span
                                class="right badge badge-danger">Addon</span> @endif
                            </p></a>
                    </li>
                @endcan

            </ul>
        </li>

    @endcan

@endif

@can('payments.index')
    <li style="color: #001f3f; font-size: 1rem; font-weight: bold;" class="nav-header">{{trans('lang.payment_plural')}}</li>

    <li
        class="nav-item has-treeview {{ Request::is('payments*') || Request::is('paymentMethods*') || Request::is('paymentStatuses*') || Request::is('clinicPayouts*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ Request::is('payments*') || Request::is('paymentMethods*') || Request::is('paymentStatuses*') || Request::is('clinicPayouts*') ? 'active' : '' }}">
            @if($icons)
            <i style="color: #001f3f;" class="nav-icon fas fa-money-check-alt"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.payment_plural')}}<i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">

            @can('payments.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('payments*') ? 'active' : '' }}"
                        href="{!! route('payments.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-money-check-alt"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.payment_table')}}</p></a>
                </li>
            @endcan
            @can('paymentMethods.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('paymentMethods*') ? 'active' : '' }}"
                        href="{!! route('paymentMethods.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-credit-card"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.payment_method_plural')}}</p></a>
                </li>
            @endcan


            @can('paymentStatuses.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('paymentStatuses*') ? 'active' : '' }}"
                        href="{!! route('paymentStatuses.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-file-invoice-dollar"></i>@endif<p
                            style="color: #001f3f;">{{trans('lang.payment_status_plural')}}</p>
                    </a>
                </li>
            @endcan

            @can('clinicPayouts.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('clinicPayouts*') ? 'active' : '' }}"
                        href="{!! route('clinicPayouts.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-money-bill-wave"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.clinic_payout_plural')}}</p></a>
                </li>
            @endcan

        </ul>
    </li>
@endcan
@can('wallets.index')
    <li class="nav-item has-treeview {{ Request::is('wallet*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ Request::is('wallet*') ? 'active' : '' }}"> @if($icons)
        <i style="color: #001f3f;" class="nav-icon fas fa-wallet"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.wallet_plural')}}<i style="color: #001f3f;"
                    class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a class="nav-link {{ Request::is('wallets*') ? 'active' : '' }}"
                    href="{!! route('wallets.index') !!}">@if($icons)
                    <i style="color: #001f3f;" class="nav-icon fa fa-wallet"></i>@endif<p style="color: #001f3f;">
                        {{trans('lang.wallet_table')}}</p></a>
            </li>
            @can('walletTransactions.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('walletTransactions*') ? 'active' : '' }}"
                        href="{!! route('walletTransactions.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fa fa-list-alt"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.wallet_transaction_plural')}}</p></a>
                </li>
            @endcan

        </ul>
    </li>
@endcan
@can('earnings.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('earnings*') ? 'active' : '' }}"
            href="{!! route('earnings.index') !!}">@if($icons)
            <i style="color: #001f3f;" class="nav-icon fas fa-money-bill"></i>@endif<p style="color: #001f3f;">
                {{trans('lang.earning_plural')}} </p></a>
    </li>
@endcan

@can('medias')
    <li class="nav-header" style="font-size: 1rem; color: #001f3f; font-weight: bold;">{{trans('lang.app_setting')}}</li>
    <li class="nav-item">
        <a class="nav-link {{ Request::is('medias*') ? 'active' : '' }}" href="{!! url('medias') !!}">@if($icons)
        <i style="color: #001f3f;" class="nav-icon fas fa-photo-video"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.media_plural')}}</p>
        </a>
    </li>
@endcan

@can('app-settings')
    <li
        class="nav-item has-treeview {{ Request::is('settings/mobile*') || Request::is('slides*') || Request::is('customPages*') ? 'menu-open' : '' }}">
        <a href="#"
            class="nav-link {{ Request::is('settings/mobile*') || Request::is('slides*') || Request::is('customPages*') ? 'active' : '' }}">
            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-mobile-alt"></i>@endif
            <p style="color: #001f3f;">
                {{trans('lang.mobile_menu')}}
                <i style="color: #001f3f;" class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{!! url('settings/mobile/globals') !!}"
                    class="nav-link {{  Request::is('settings/mobile/globals*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-cog"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_globals')}}
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/mobile/colors') !!}"
                    class="nav-link {{  Request::is('settings/mobile/colors*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-magic"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.mobile_colors')}}
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/mobile/authentication') !!}"
                    class="nav-link {{  Request::is('settings/mobile/authentication*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-comment-alt"></i> @endif <p
                        style="color: #001f3f;">
                        {{trans('lang.app_setting_authentication')}}
                    </p>
                </a>
            </li>

            @can('customPages.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('customPages*') ? 'active' : '' }}"
                        href="{!! route('customPages.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fa fa-file"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.custom_page_plural')}}</p></a>
                </li>
            @endcan

            @can('slides.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('slides*') ? 'active' : '' }}"
                        href="{!! route('slides.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-images"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.slide_plural')}} </p>
                    </a>
                </li>
            @endcan
        </ul>

    </li>
    <li class="nav-item has-treeview {{
            (Request::is('settings*') ||
                Request::is('users*')) && !Request::is('settings/mobile*')
            ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{
            (Request::is('settings*') ||
                Request::is('users*')) && !Request::is('settings/mobile*')
            ? 'active' : '' }}"> @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-cogs"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.app_setting')}} <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{!! url('settings/app/globals') !!}"
                    class="nav-link {{  Request::is('settings/app/globals*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-cog"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_globals')}}</p>
                </a>
            </li>

            @can('users.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}"
                        href="{!! route('users.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-users"></i>@endif
                        <p style="color: #001f3f;">{{trans('lang.user_plural')}}</p>
                    </a>
                </li>
            @endcan

            <li
                class="nav-item has-treeview {{ Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'menu-open' : '' }}">
                <a href="#"
                    class="nav-link {{ Request::is('settings/permissions*') || Request::is('settings/roles*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-user-secret"></i>@endif
                    <p style="color: #001f3f;">
                        {{trans('lang.permission_menu')}}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('settings/permissions') ? 'active' : '' }}"
                            href="{!! route('permissions.index') !!}">
                            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-circle-o"></i>@endif
                            <p style="color: #001f3f;">{{trans('lang.permission_table')}}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('settings/permissions/create') ? 'active' : '' }}"
                            href="{!! route('permissions.create') !!}">
                            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-circle-o"></i>@endif
                            <p style="color: #001f3f;">{{trans('lang.permission_create')}}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('settings/roles') ? 'active' : '' }}"
                            href="{!! route('roles.index') !!}">
                            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-circle-o"></i>@endif
                            <p style="color: #001f3f;">{{trans('lang.role_table')}}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('settings/roles/create') ? 'active' : '' }}"
                            href="{!! route('roles.create') !!}">
                            @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-circle-o"></i>@endif
                            <p style="color: #001f3f;">{{trans('lang.role_create')}}</p>
                        </a>
                    </li>
                </ul>

            </li>

            <li class="nav-item">
                <a class="nav-link {{ Request::is('settings/customFields*') ? 'active' : '' }}"
                    href="{!! route('customFields.index') !!}">@if($icons)
                    <i style="color: #001f3f;" class="nav-icon fas fa-list"></i>@endif<p style="color: #001f3f;">
                        {{trans('lang.custom_field_plural')}}</p></a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/app/localisation') !!}"
                    class="nav-link {{  Request::is('settings/app/localisation*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-language"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_localisation')}}
                    </p></a>
            </li>
            <li class="nav-item">
                <a href="{!! url('settings/translation/en') !!}"
                    class="nav-link {{ Request::is('settings/translation*') ? 'active' : '' }}">
                    @if($icons) <i style="color: #001f3f;" class="nav-icon fas fa-language"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_translation')}}
                    </p></a>
            </li>
            @can('currencies.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('settings/currencies*') ? 'active' : '' }}"
                        href="{!! route('currencies.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-dollar-sign"></i>@endif<p style="color: #001f3f;">
                            {{trans('lang.currency_plural')}}</p></a>
                </li>
            @endcan
            @can('taxes.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('settings/taxes*') ? 'active' : '' }}"
                        href="{!! route('taxes.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-coins"></i>@endif
                        <p style="color: #001f3f;">{{trans('lang.tax_plural')}}</p>
                    </a>
                </li>
            @endcan

            <li class="nav-item">
                <a href="{!! url('settings/payment/payment') !!}"
                    class="nav-link {{  Request::is('settings/payment*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-credit-card"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_payment')}}
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/app/social') !!}"
                    class="nav-link {{  Request::is('settings/app/social*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-globe"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_social')}}</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/app/notifications') !!}"
                    class="nav-link {{  Request::is('settings/app/notifications*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-bell"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_notifications')}}
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{!! url('settings/mail/smtp') !!}"
                    class="nav-link {{ Request::is('settings/mail*') ? 'active' : '' }}">
                    @if($icons)<i style="color: #001f3f;" class="nav-icon fas fa-envelope"></i> @endif <p
                        style="color: #001f3f;">{{trans('lang.app_setting_mail')}}</p>
                </a>
            </li>

        </ul>
    </li>
@endcan


@can('coupons.index')
    <li class="nav-item">
        <a class="nav-link {{ Request::is('coupons*') ? 'active' : '' }}" href="{!! route('coupons.index') !!}">@if($icons)
        <i style="color: #001f3f;" class="nav-icon fas fa-ticket-alt"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.coupon_plural')}} </p>
        </a>
    </li>
@endcan
@can('faqs.index')
    <li class="nav-item {{ Request::is('faqCategories*') || Request::is('faqs*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ Request::is('faqs*') || Request::is('faqCategories*') ? 'active' : '' }}">
            @if($icons)
            <i style="color: #001f3f;" class="nav-icon fas fa-question-circle"></i>@endif
            <p style="color: #001f3f;">{{trans('lang.faq_plural')}} <i class="right fas fa-angle-left"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            @can('faqCategories.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('faqCategories*') ? 'active' : '' }}"
                        href="{!! route('faqCategories.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-folder-open"></i>@endif
                        <p style="color: #001f3f;">{{trans('lang.faq_category_plural')}}</p>
                    </a>
                </li>
            @endcan

            @can('faqs.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('faqs*') ? 'active' : '' }}"
                        href="{!! route('faqs.index') !!}">@if($icons)
                        <i style="color: #001f3f;" class="nav-icon fas fa-life-ring"></i>@endif
                        <p style="color: #001f3f;">{{trans('lang.faq_plural')}}</p>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcan
{{--@can('doctorPatients.index')--}}
{{--<li class="nav-item">--}}
    {{-- <a class="nav-link {{ Request::is('doctorPatients*') ? 'active' : '' }}"
        href="{!! route('doctorPatients.index') !!}">@if($icons)<i class="nav-icon fa fa-file"></i>@endif<p>
            {{trans('lang.doctor_patients_plural')}}</p></a>--}}
    {{--</li>--}}
{{--@endcan--}}
<style>
    /* Style pour l'état actif */
    .nav-sidebar .nav-item .nav-link.active {
        background-color: #001f3f !important;
        border-left: 3px solid #11b8aa;
        /* Accent turquoise */
    }

    .nav-sidebar .nav-item .nav-link.active .nav-icon,
    .nav-sidebar .nav-item .nav-link.active p {
        color: #ffffff !important;
    }

    /* Style spécifique pour l'icône IA */
    .nav-sidebar .nav-item .nav-link .fa-robot {
        color: #11b8aa;
        /* Couleur turquoise par défaut */
    }

    .nav-sidebar .nav-item .nav-link.active .fa-robot {
        color: #ffffff !important;
        /* Blanc en mode actif */
    }

    /* Effet de hover */
    .nav-sidebar .nav-item .nav-link:hover {
        background-color: #003366 !important;
    }

    .nav-sidebar .nav-item .nav-link:hover .nav-icon,
    .nav-sidebar .nav-item .nav-link:hover p {
        color: #ffffff !important;
    }

    /* Dans votre fichier CSS */
    .nav-availability .nav-link.active {
        background-color: #001f3f !important;
    }

    .nav-availability .nav-link.active .nav-icon,
    .nav-availability .nav-link.active p {
        color: #ffffff !important;
    }
</style>