@extends('layouts.app')
@push('css_lib')
    <!-- icheck-bootstrap -->
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-lite.min.css')}}">
    {{--dropzone--}}
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush
@section('content')
    @if($showNewFeaturesModal && $isDoctor)
        <!-- Replace the existing modal content with this -->
        <div id="welcomeModal" class="welcome-modal">
            <div class="modal-content">
                <!-- Header Section -->
                <div class="modal-header">
                    <div class="header-content">
                        <span class="new-badge">MISE À JOUR</span>
                        <h2><i class="fas fa-rocket"></i> Nouvelle Version de l'Agenda</h2>
                    </div>
                </div>

                <!-- Body Section -->
                <div class="modal-body">
                    <div class="welcome-message">
                        <p><i class="fas fa-bell"></i> Découvrez les améliorations majeures de votre agenda médical</p>
                    </div>

                    <!-- Version Comparison Grid -->
                    <div class="version-comparison">
                        <div class="version-column old-version">
                            <div class="version-header">
                                <i class="fas fa-calendar"></i>
                                <h3>Version Actuelle</h3>
                            </div>
                            <div class="feature-list-container">
                                <ul class="feature-list">
                                    <li><i class="fas fa-check"></i> Vue agenda simple</li>
                                    <li><i class="fas fa-check"></i> Gestion basique des rendez-vous</li>
                                    <li><i class="fas fa-check"></i> Calendrier standard</li>
                                    <li><i class="fas fa-check"></i> Mode unique de consultation</li>
                                    <li><i class="fas fa-check"></i> Statistiques limitées</li>
                                    <li><i class="fas fa-times text-muted"></i> Pas de gestion des remplaçants</li>
                                    <li><i class="fas fa-times text-muted"></i> Pas de gestion multi-motifs</li>
                                    <li><i class="fas fa-times text-muted"></i> Interface basique</li>
                                </ul>
                            </div>
                        </div>

                        <div class="version-divider">
                            <div class="arrow-container">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>

                        <div class="version-column new-version">
                            <div class="version-header">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>Nouvelle Version</h3>
                                <span class="new-tag">NEW</span>
                            </div>
                            <div class="feature-list-container">
                                <ul class="feature-list">
                                    <li><i class="fas fa-star"></i> Vue hebdomadaire optimisée</li>
                                    <li><i class="fas fa-star"></i> Gestion avancée des rendez-vous</li>
                                    <li><i class="fas fa-star"></i> Calendrier interactif amélioré</li>
                                    <li><i class="fas fa-star"></i> Trois modes de consultation:
                                        <ul class="sub-features">
                                            <li>Cabinet</li>
                                            <li>Téléconsultation</li>
                                            <li>Visite à domicile</li>
                                        </ul>
                                    </li>
                                    <li><i class="fas fa-star"></i> Statistiques détaillées en temps réel</li>
                                    <li><i class="fas fa-star"></i> Gestion complète des remplaçants</li>
                                    <li><i class="fas fa-star"></i> Motifs de consultation personnalisables</li>
                                    <li><i class="fas fa-star"></i> Interface moderne et intuitive</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Section -->
                <div class="modal-footer">
                    <button id="acceptNewFeatures" class="btn-accept">
                        <i class="fas fa-check"></i> Activer la Nouvelle Version
                    </button>
                    <button id="rejectNewFeatures" class="btn-reject">
                        <i class="fas fa-times"></i> Rester sur l'Ancienne Version
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{!! trans('lang.user_profile') !!} <small>{{trans('lang.media_desc')}}</small></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i>
                                {{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{trans('lang.user_profile')}}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content">
        <div class="container-fluid" style="min-height: 100vh">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i> {{trans('lang.user_about_me')}}</h3>
                        </div>
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img src="{{auth()->user()->getFirstMediaUrl('avatar', 'icon')}}"
                                    class="profile-user-img img-fluid img-circle" alt="{{auth()->user()->name}}">
                            </div>
                            <h3 class="profile-username text-center">{{auth()->user()->name}}</h3>

                            <p class="text-muted text-center">{{implode(', ',$rolesSelected)}}</p>
                            <a class="btn btn-outline-{{setting('theme_color')}} btn-block" href="mailto:{{auth()->user()->email}}"><i class="fas fa-envelope mr-2"></i>{{auth()->user()->email}}</a>
                            @can('doctors.editProfil')
                            <a class="btn btn-outline-{{ setting('theme_color') }} btn-block" href="{{ asset('storage/pdf/Guide modification photo de profil.pdf') }}" target="_blank">
                                <i class="fas fa-info-circle mr-2"></i> Guide pour modifier et compléter votre profil 
                                <span class="badge badge-danger ml-2">Nouveau</span>

                            </a>
                            @endcan



                        </div>

                        <!-- /.card-body -->
                    </div>

 @can('doctors.editProfil')
    <!-- Profile Edit -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user mr-2"></i> {{ trans('lang.edit_profil') }}</h3>
        </div>
        <div class="card-body box-profile">
            <!-- Liste des étapes -->
            @if(auth()->user()->hasRole('doctor'))
    <ul class="task-list">
        <li class="task {{ $doctor->pourcentage_avatar ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_avatar ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.import_avatar') }}
        </li>
        <li class="task {{ $doctor->pourcentage_adresse ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_adresse ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.complet_adresse') }}
        </li>
        <li class="task {{ $doctor->pourcentage_cv ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_cv ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.complet_cv') }}
        </li>
        <li class="task {{ $doctor->pourcentage_cabinet ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_cabinet ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.import_photos') }}
        </li>
        <li class="task {{ $doctor->pourcentage_tags ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_tags ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.check_tags') }}
        </li>
        <li class="task {{ $doctor->pourcentage_profil ? 'completed' : 'pending' }}">
            <i class="{{ $doctor->pourcentage_profil ? 'fas fa-check-circle' : 'far fa-circle' }}"></i>
            {{ trans('lang.final_profil') }}
        </li>
    </ul>
@endif


            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: {{ $progressBar }}%; background-color: #5c6bc0;"></div>
                </div>
            </div>

            <a class="btn btn-outline-{{ setting('theme_color') }} btn-block" href="{{ route('doctors.editProfil') }}">
                <i class="fas fa-edit mr-2"></i> {{ trans('lang.edit_profil') }}
            </a>
            <a class="btn btn-block" 
   style="background-color: #36a78a; color: #ffffff; border-color: #36a78a;" 
   href="{{ route('doctors.generateUrl') }}" 
   target="_blank" 
   rel="noopener noreferrer">
    <i class="fas fa-globe mr-2"></i> {{ trans('lang.voir_profil') }}
</a>



        </div>
    </div>
@endcan



                    <!-- /.card -->

                </div>
                <!-- /.col -->
<div class="col-md-9">
    @include('flash::message')
    @include('adminlte-templates::common.errors')
    <div class="clearfix"></div>
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-cog mr-2"></i>{{trans('lang.app_setting')}}</a>
                </li>
                @hasrole('customer')
                <div class="ml-auto d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link pt-1" href="{{ route('clinics.create') }}"><i class="fas fa-check-o"></i> {{trans('lang.app_setting_become_servicclinic')}}
                        </a>
                    </li>
                </div>
                @endhasrole
            </ul>
        </div>
        <div class="card-body">
            {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}
            <div class="row">
                @include('settings.users.fields')
            </div>
<!-- Vérifier si l'utilisateur a le rôle 'doctor' -->
@hasrole('doctor')
<!-- Checkbox et texte à ajouter sous le bouton de sauvegarde -->
<div class="form-group border p-3 mb-3" style="border: 2px solid #f44336; border-radius: 5px;">
    <div class="custom-control custom-checkbox">
        <!-- Pré-cocher la case si verif_chart est égal à 1 -->
        <input type="checkbox" class="custom-control-input" id="deontologicalCharter" name="deontologicalCharter" 
               onchange="handleCharterAcceptance()" 
               {{ $user->doctor->verif_chart == 1 ? 'checked' : '' }}>
        
        <label class="custom-control-label" for="deontologicalCharter">
            En tant que professionnel de santé, je certifie avoir lu et approuvé les conditions de la Charte Déontologique pour les professionnels de santé adhérant à la plateforme WIC Doctor, et m'engage à les respecter.
        </label>
        
        <!-- Badge Important aligné à droite -->
        <span class="badge badge-danger float-right">Important</span>
        
        <a href="{{ asset('storage/pdf/Charte déontologique pour les professionnels de santé adhérant à la plateforme WIC Doctor.pdf') }}" class="ml-2" target="_blank">
            <i class="fas fa-download"></i> Télécharger la Charte Déontologique
        </a>
    </div>
</div>
@endhasrole

<script>
    // Fonction JavaScript pour traiter l'acceptation de la Charte Déontologique
    function handleCharterAcceptance() {
        var checkbox = document.getElementById("deontologicalCharter");
        var accepted = checkbox.checked ? 1 : 0;  // Déterminer si la case est cochée ou non

        // Appel Ajax pour mettre à jour le champ verif_chart dans la table doctor
        $.ajax({
            url: '{{ route('doctor.updateChartStatus') }}',  // Assurez-vous de créer cette route
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',  // Pour protéger la requête
                accepted: accepted
            },
            success: function(response) {
                console.log('Statut de la Charte Déontologique mis à jour');
            },
            error: function(error) {
                console.error('Erreur lors de la mise à jour du statut de la Charte Déontologique', error);
            }
        });
    }
</script>

                
            </div>
        </div>
    </section>
    @include('layouts.media_modal', ['collection' => null])
@endsection
@push('scripts_lib')
    <!-- select2 -->
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('vendor/summernote/summernote-lite.min.js')}}"></script>
    {{--dropzone--}}
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>

    <script>
        $(document).ready(function () {
            @if($showNewFeaturesModal)
                $('#newFeaturesModal').modal('show'); // Show the modal if user has not accepted new features
            @endif

            $('#acceptNewFeatures').click(function () {
                $.ajax({
                    url: '{{ route("users.acceptNewFeatures") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        window.location.href = '/availability'; // Redirect to new agenda
                    }
                });
            });

            $('#rejectNewFeatures').click(function () {
                $.ajax({
                    url: '{{ route("users.rejectNewFeatures") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function () {
                        $('#newFeaturesModal').modal('hide'); // Close modal
                    }
                });
            });
        });
    </script>

@endpush

<style>
    /* Liste des tâches */
    .task-list {
        list-style: none;
        padding: 0;
        margin-bottom: 15px;
    }

    .task {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .task i {
        font-size: 16px;
        margin-right: 10px;
    }

    .task.completed i {
        color: #5c6bc0;
        /* mauve */
    }

    .task.pending i {
        color: #aaa;
        /* Gris */
    }

    /* Style pour la barre de progression générale */
.progress-container {
    margin-top: 10px;
    width: 100%;
    margin-bottom: 15px;
}

.progress-bar-container {
    width: 100%;
    height: 12px;
    background-color: #ddd;
    border-radius: 10px;
    margin-top: 10px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.4s ease-in-out;
}

    .progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease-in-out;
    }
</style>
<style>
    .welcome-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .modal-content {
        background: #fff;
        border-radius: 15px;
        width: 90%;
        max-width: 800px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.5s ease-out;
    }

    .modal-header {
        background: linear-gradient(135deg, #4a90e2, #2c3e50);
        color: white;
        padding: 20px;
        border-radius: 15px 15px 0 0;
        text-align: center;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }

    .new-badge {
        background: #e74c3c;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        animation: pulse 2s infinite;
    }

    .modal-body {
        padding: 30px;
        color: #2c3e50;
    }

    .welcome-message {
        text-align: center;
        margin-bottom: 30px;
        font-size: 18px;
        color: #34495e;
    }

    .features-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin: 20px 0;
    }

    .feature-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .feature-section h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-list li {
        margin: 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .feature-list li i {
        color: #27ae60;
    }

    .modal-footer {
        padding: 20px;
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .btn-accept,
    .btn-reject {
        padding: 12px 25px;
        border-radius: 25px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-accept {
        background: #27ae60;
        color: white;
    }

    .btn-reject {
        background: #e74c3c;
        color: white;
    }

    .btn-accept:hover,
    .btn-reject:hover {
        transform: translateY(-2px);
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @media (max-width: 768px) {
        .features-grid {
            grid-template-columns: 1fr;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn-accept,
        .btn-reject {
            width: 100%;
            justify-content: center;
        }
    }

    /* Add to your existing modal styles */
    .version-comparison {
        display: flex;
        align-items: stretch;
        gap: 20px;
        margin: 30px 0;
    }

    .version-column {
        flex: 1;
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        position: relative;
    }

    .version-header {
        text-align: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #dee2e6;
    }

    .version-header h3 {
        margin: 10px 0;
        color: #2c3e50;
        font-size: 1.5rem;
    }

    .version-header i {
        font-size: 2rem;
        color: #4a90e2;
    }

    .new-version {
        background: #f0f7ff;
        border: 2px solid #4a90e2;
    }

    .new-tag {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #e74c3c;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        animation: pulse 2s infinite;
    }

    .feature-list-container {
        height: 100%;
    }

    .feature-list li {
        margin: 15px 0;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.4;
    }

    .old-version .feature-list i {
        color: #7f8c8d;
    }

    .new-version .feature-list i {
        color: #f39c12;
    }

    .sub-features {
        list-style: none;
        padding-left: 25px;
        margin-top: 5px;
        font-size: 0.9em;
        color: #666;
    }

    .sub-features li:before {
        content: "•";
        color: #4a90e2;
        margin-right: 5px;
    }

    .version-divider {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .arrow-container {
        width: 40px;
        height: 40px;
        background: #4a90e2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .arrow-container i {
        color: white;
        font-size: 1.2rem;
    }

    @media (max-width: 768px) {
        .version-comparison {
            flex-direction: column;
        }

        .version-divider {
            transform: rotate(90deg);
            margin: 20px 0;
        }
    }

</style>