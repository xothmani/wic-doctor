@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.view';
    $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
        ->with('readable')
        ->first();

    $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@section('content')
    @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-bold">
                            WIC Partage Médical                        
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex shadow-sm">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                    {{ trans('lang.dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{ trans('lang.patients_plural') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ trans('lang.select_patient') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                
                <!-- Section de présentation WIC Partage Médical -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="wic-presentation-card">
                            <div class="wic-presentation-content">
                                <div class="wic-header">
                                    <div class="wic-icon">
                                        <i class="fas fa-share-alt"></i>
                                    </div>
                                    <div class="wic-title">
                                        <h2>WIC Partage Médical</h2>
                                        <p class="wic-subtitle">Connectez vos expertises, pas vos boîtes mail.</p>
                                    </div>
                                </div>
                                <div class="wic-description">
                                    <p>Chez WIC Doctor, nous savons que la qualité des soins repose aussi sur une communication fluide et efficace entre professionnels de santé. C'est pourquoi nous avons créé un service de partage de fichiers patients qui vous permet de travailler ensemble plus facilement, plus rapidement, et en toute confiance.
<a href="https://wic-doctor.com/inscription-professionnel/wic-partage-medical.html" target="_blank" class="info-badge">
    En savoir plus
</a>
                                    </p>
                                </div>
                      <div class="wic-features">
    <div class="feature-item">
        <a href="https://wic-doctor.com/inscription-professionnel/wic-partage-medical.html" target="_blank" class="feature-link">
            <i class="fas fa-shield-alt"></i>
            <span>Sécurisé et confidentiel</span>
        </a>
    </div>
    <div class="feature-item">
        <a href="https://wic-doctor.com/inscription-professionnel/wic-partage-medical.html" target="_blank" class="feature-link">
            <i class="fas fa-bolt"></i>
            <span>Rapide et efficace</span>
        </a>
    </div>
    <div class="feature-item">
        <a href="https://wic-doctor.com/inscription-professionnel/wic-partage-medical.html" target="_blank" class="feature-link">
            <i class="fas fa-users"></i>
            <span>Collaboration simplifiée</span>
        </a>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section des patients avec nouveau design -->
                <div class="row">
                    <div class="col-12">
                        <div class="patients-section">
                            <div class="patients-header">
                                <h3 class="patients-title" data-count="{{ $myPatients->total() }}">
                                    <i class="fas fa-users mr-2"></i>
                                    Mes Patients
                                </h3>
                                <div class="patients-search">
                                    <form action="{{ route('patient_files.index') }}" method="GET" id="searchForm">
                                        <div class="search-box">
                                            <i class="fas fa-search search-icon"></i>
                                            <input type="text" 
                                                   class="search-input" 
                                                   name="search"
                                                   id="patientSearch"
                                                   value="{{ request()->input('search') }}"
                                                   placeholder="Rechercher un patient...">
                                            @if(request()->input('search'))
                                                <a href="{{ route('patient_files.index') }}" 
                                                   class="clear-search"
                                                   title="Effacer la recherche">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if($myPatients->isEmpty())
                                <div class="empty-patients">
                                    <div class="empty-illustration">
                                        <i class="fas fa-user-friends"></i>
                                    </div>
                                    <h4>
                                        @if(request()->input('search'))
                                            Aucun patient ne correspond à votre recherche
                                        @else
                                            Aucun patient assigné
                                        @endif
                                    </h4>
                                    <p>
                                        @if(request()->input('search'))
                                            Essayez avec d'autres termes de recherche
                                        @else
                                            Vous n'avez pas encore de patients assignés. Commencez par consulter tous les patients disponibles.
                                        @endif
                                    </p>
                                    <a href="{{ route('patients.index') }}" class="btn-primary-custom">
                                        <i class="fas fa-plus mr-2"></i>
                                        @if(request()->input('search'))
                                            Voir tous les patients
                                        @else
                                            Rechercher des patients
                                        @endif
                                    </a>
                                </div>
                            @else
                                <div class="patients-grid">
                                    @foreach($myPatients as $patient)
                                        <div class="patient-card" 
                                             data-display="{{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->user && $patient->user->email ? $patient->user->email : '' }}">
                                            <div class="patient-card-body d-flex align-items-center">
                                                <div class="patient-avatar-new small-avatar mr-3">
                                                    @if($patient->user && $patient->user->media->isNotEmpty())
                                                        <img src="{{ $patient->user->media->first()->getUrl() }}" alt="{{ $patient->first_name }} {{ $patient->last_name }}" class="avatar-img-new">
                                                    @else
                                                        <div class="avatar-placeholder-new">
                                                            <span class="avatar-initials">
                                                                {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h5 class="patient-name-new mb-1">{{ $patient->first_name }} {{ $patient->last_name }}</h5>
                                                    @if($patient->user && $patient->user->email)
                                                        <p class="patient-email mb-0">{{ $patient->user->email }}</p>
                                                    @endif
                                                    <div class="patient-meta mt-2">
                                                        <div class="meta-item">
                                                            <i class="fas fa-folder"></i>
                                                            <span>Dossiers partagés</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="patient-card-footer">
                                                <a href="{{ route('patient_files.index', $patient) }}" class="btn-view-files">
                                                    <i class="fas fa-folder-open mr-2"></i>
                                                    Ouvrir dossier
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="patients-pagination">
                                    {{ $myPatients->appends(request()->except('page'))->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    {{ __('Vous n\'avez pas la permission d\'accéder à cette page.', ['permission' => $readablePermission]) }}
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
<script>
   $(document).ready(function () {
    const searchField = $('#patientSearch');
    
    // Soumission normale du formulaire (sans AJAX)
    searchField.on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            $('#searchForm').submit();
        }, 500);
    });

    // Après rechargement, restaure le focus et la position
    if (searchField.val()) {
        const cursorPos = searchField.val().length;
        searchField.focus();
        searchField[0].setSelectionRange(cursorPos, cursorPos);
    }

    // Focus initial
    setTimeout(() => searchField.focus(), 300);
});
</script>
@endpush

@push('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #943B5A 0%, #943B5A 100%);
            --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
            --wic-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Styles pour la section de présentation WIC */
        .wic-presentation-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(148, 59, 90, 0.1);
            position: relative;
            overflow: hidden;
        }

        .wic-presentation-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--info-gradient);
        }

        .wic-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .wic-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 8px 25px rgba(148, 59, 90, 0.3);
        }

        .wic-title h2 {
            color: #2d3436;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            margin-bottom: 5px;
        }

        .wic-subtitle {
            color: #636e72;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
            font-style: italic;
        }

        .wic-description {
            margin-bottom: 25px;
        }

        .wic-description p {
            color: #2d3436;
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        .wic-features {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .feature-link {
    color: inherit;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.feature-link:hover {
    color: inherit;
    text-decoration: none;
}


        .feature-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: rgba(148, 59, 90, 0.1);
            border-radius: 25px;
            color: #943B5A;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(148, 59, 90, 0.2);
        }

        .feature-item:hover {
            background: rgba(148, 59, 90, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(148, 59, 90, 0.2);
        }

        .feature-item i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Styles pour la nouvelle section des patients */
        .patients-section {
            background: #fff;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .patients-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .patients-title {
            color: #2d3436;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patients-title::after {
            content: attr(data-count);
            background: #943B5A;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }

        .patients-title i {
            color: #943B5A;
            font-size: 1.3rem;
        }

        .patients-search {
            flex: 1;
            max-width: 400px;
            min-width: 250px;
        }

        .search-box {
            position: relative;
            width: 100%;
            display: flex;
        }

        .search-box form {
            width: 100%;
        }

        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #001f3f;
            font-size: 1.1rem;
            z-index: 2;
        }

        .search-box .search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
        }

        .search-box .search-input:focus {
            outline: none;
            border-color: #001f3f;
            box-shadow: 0 0 20px rgba(116, 185, 255, 0.2);
        }

        .clear-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
        }

        .clear-search:hover {
            color: #943B5A;
        }

        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            padding: 30px;
        }

        .patient-card {
            background: #fff;
            border: 1px solid #943B5A;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .patient-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #943B5A;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .patient-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            border-color: #943B5A;
        }

        .patient-card:hover::before {
            opacity: 1;
        }

        .patient-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .patient-avatar-new {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .avatar-img-new {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }
        .small-avatar {
            width: 50px !important;
            height: 50px !important;
            border-radius: 12px;
        }

        .small-avatar img,
        .small-avatar .avatar-placeholder-new {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .avatar-placeholder-new {
            width: 100%;
            height: 100%;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .patient-card-body {
            margin-bottom: 20px;
        }

        .patient-name-new {
            color: #2d3436;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .patient-email {
            color: #636e72;
            font-size: 0.95rem;
            margin: 0 0 15px 0;
        }

        .patient-meta {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #636e72;
            font-size: 0.9rem;
        }

        .meta-item i {
            color: #001f3f;
            font-size: 1rem;
        }

        .patient-card-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f3f4;
        }

        .btn-view-files {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 12px;
            background: white;
            color: #001f3f;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 400;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 1px solid #001f3f;
            cursor: pointer;
            width: 100%;
        }

        .btn-view-files:hover {
            background: #001f3f;
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .empty-patients {
            text-align: center;
            padding: 80px 30px;
            color: #636e72;
        }

        .empty-illustration {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #943B5A;
            opacity: 0.7;
        }

        .empty-patients h4 {
            color: #2d3436;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .empty-patients p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            padding: 15px 30px;
            background: #001f3f;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(148, 59, 90, 0.4);
            color: white;
            text-decoration: none;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Styles pour la pagination */
        .patients-pagination {
            padding: 20px 30px;
            display: flex;
            justify-content: center;
            border-top: 1px solid #f1f3f4;
        }

        .pagination {
            display: flex;
            gap: 10px;
            list-style: none;
            padding: 0;
        }

        .page-item {
            list-style: none;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8f9fa;
            color: #2d3436;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .page-link:hover {
            background: #001f3f;
            color: white;
            border-color: #001f3f;
        }

        .page-item.active .page-link {
            background: #001f3f;
            color: white;
            border-color: #001f3f;
        }

        .page-item.disabled .page-link {
            opacity: 0.5;
            pointer-events: none;
        }

  .info-badge {
    display: inline-block;
    margin-top: 8px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    color: white;
    background-color: #943B5A;
    font-weight: bold;
    transition: transform 0.3s ease;
    cursor: pointer;
    text-decoration: none; /* retire soulignement */
}

.info-badge:hover {
    color: white; /* empêche le bleu au hover */
    text-decoration: none; /* évite soulignement */
}

        


        /* Responsive Design pour les patients */
        @media (max-width: 768px) {
            .patients-header {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .patients-title {
                font-size: 1.3rem;
                justify-content: center;
            }

            .patients-search {
                max-width: none;
                min-width: none;
            }

            .patients-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }

            .patient-card {
                padding: 20px;
            }

            .patient-avatar-new {
                width: 60px;
                height: 60px;
            }

            .patient-name-new {
                font-size: 1.2rem;
            }

            .empty-patients {
                padding: 60px 20px;
            }

            .empty-illustration {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .patients-pagination {
                padding: 15px;
            }

            .page-link {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
        }
    </style>
@endpush