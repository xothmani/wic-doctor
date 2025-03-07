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
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{!! trans('lang.user_profile') !!} <small>{{trans('lang.media_desc')}}</small></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i> {{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{trans('lang.user_profile')}}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content" >
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
                                <img src="{{auth()->user()->getFirstMediaUrl('avatar','icon')}}" class="profile-user-img img-fluid img-circle" alt="{{auth()->user()->name}}">
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
<!-- Vérifier si l'utilisateur a le rôle 'doctor' -->
@hasrole('doctor')
<!-- Checkbox et texte à ajouter sous le bouton de sauvegarde -->
<div class="form-group border p-3 mb-3" style="border: 2px solid #f44336; border-radius: 5px;">
    <div class="custom-control custom-checkbox">
        <!-- Pré-cocher la case si verif_chart est égal à 1 -->
        <input type="checkbox" class="custom-control-input" id="deontologicalCharter" name="deontologicalCharter" 
               onchange="handleCharterAcceptance()" 
               {{ $user->doctor->verif_chart == 1 ? 'checked' : '' }}
               {{ $user->doctor->verif_chart == 1 ? 'disabled' : '' }}>
        
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
<div class="form-group border p-3 mb-3" style="border: 2px solid #f44336; border-radius: 5px;">
    <i class="fas fa-calendar-alt mr-2" style="font-size: 1.2rem; color: #f39c12;"></i> <!-- Icône du calendrier -->
    <b>Votre abonnement expire dans:</b>
    <span style="background-color: 
        {{ $badgeColor === 'red' ? ' #f44336' : ($badgeColor === 'green' ? '#28a745' : '#f9c74f') }}; 
        color: black; 
        padding: 2px 8px; 
        border-radius: 10px; 
        font-size: 15px;">
        {{ $subscriptionStatus }}
    </span>
</div>


@endhasrole





<!-- Modale de confirmation -->
<div class="modal fade" id="charterModal" tabindex="-1" role="dialog" aria-labelledby="charterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="charterModalLabel">Confirmation</h5>
                
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir accepter la Charte ?  
                <br><b>Une fois acceptée, vous ne pourrez plus revenir en arrière.</b> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelButton">Annuler</button>
                <button type="button" class="btn bg-{{setting('theme_color')}}" id="confirmCharter">Confirmer</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        var checkbox = document.getElementById("deontologicalCharter");
        var confirmButton = document.getElementById("confirmCharter");
        var cancelButton = document.getElementById("cancelButton");

        // Quand l'utilisateur coche la case, afficher la modale
        checkbox.addEventListener("change", function () {
            if (checkbox.checked && checkbox.disabled !== true) { // Ajouter une condition pour s'assurer que la case n'est pas déjà désactivée
                $("#charterModal").modal("show"); // Affiche la modale Bootstrap
            }
        });

        // Quand l'utilisateur clique sur "Confirmer" dans la modale
        confirmButton.addEventListener("click", function () {
            // Appel Ajax pour mettre à jour le statut dans la base de données
            $.ajax({
                url: '{{ route('doctor.updateChartStatus') }}', // Assurez-vous que cette route existe
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    accepted: 1
                },
                success: function (response) {
                    $("#charterModal").modal("hide"); // Fermer la modale
                    checkbox.disabled = true; // Désactiver la case pour empêcher la modification
                    checkbox.parentElement.style.opacity = '0.5'; // Griser la case à cocher
                },
                error: function (error) {
                    console.error("Erreur lors de la mise à jour du statut de la Charte Déontologique", error);
                }
            });
        });

        // Quand l'utilisateur clique sur "Annuler" dans la modale
        cancelButton.addEventListener("click", function () {
            checkbox.checked = false; // Décocher la case
            $("#charterModal").modal("hide"); // Fermer la modale
        });
    });
</script>

                
            </div>
        </div>
    </section>
    @include('layouts.media_modal',['collection'=>null])
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
        color:#5c6bc0; /* mauve */
    }

    .task.pending i {
        color: #aaa; /* Gris */
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

</style>