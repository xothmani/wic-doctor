@extends('layouts.app')

@section('title', 'Demande d\'assistance')
@push('css_lib')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>s
    .form-control {


    
        border-radius: 8px;
        padding: 10px 15px;
    }


    

    /* Réduire la taille des champs de texte (input) */
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    select {
        max-width: 250px; /* Limite la largeur des champs */
        width: 100%;
        font-size: 14px;  /* Taille de police plus petite */
        padding: 8px 12px; /* Ajuste le padding pour minimiser l'espace intérieur */
    }

    /* Optionnel : Réduire la taille des champs de texte dans le formulaire */
    .form-group input {
        max-width: 300px;
        font-size: 14px;
    }

    /* Ajuste le texte dans le formulaire pour mieux s'adapter */
    .form-control {
        font-size: 14px;  /* Taille de police réduite */
        padding: 8px 12px; /* Réduit l'espace intérieur */
    }
</style>

    <style>
        .card.clickble:hover .delete-media {
            display: block !important;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .btn-default {
            background-color: #f8f9fa;
            color: #333;
        }
        .email-input {
            max-width: 250px;
            width: 100%;
        }
        .input-group {
            max-width: 350px;
        }
    </style>
@endpush

@section('content')
    <!-- Section de l'en-tête -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-bold">{{ ('Assistance') }}
                        <small class="mx-3">|</small><small>{{ ('Nouvelle demande') }}</small>
                    </h1>
                </div>
                <div class="col-md-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt mx-1"></i> {{ trans('Tableau de bord') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ ('Demande d\'assistance') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="success-alert" class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div id="error-alert" class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Section de contenu -->
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                <div class="d-flex flex-row">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('helpdesk') ? 'active-tab' : '' }}" href="{{ route('helpdesk.index') }}">
                            <i class="fa fa-list mr-2"></i>{{ trans('Historique des demandes') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('helpdesk/create') ? 'active-tab' : '' }}" href="{{ route('helpdesk.index') }}">
                            <i class="fa fa-plus mr-2"></i>{{trans('Nouvelle demande')}}
                        </a>
                    </li>
                </div>
            </ul>
        </div>

        <div class="container mt-5">
            <div class="card shadow">
                <div class="card-body">
                    <h3 style="margin-bottom: 20px;">Formulaire d'assistance</h3>
                    <p class="mb-4" style="font-size: 1.2em; margin-top: 10px;">
    Merci de remplir ce formulaire pour nous informer de votre besoin 😊. 
    Notre équipe vous répondra dans les plus brefs délais 📩🚀.
</p>


                    <form action="{{ route('helpdesk.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-4">
                <label for="prenom" class="form-label">Votre Nom :</label>
                <input type="text" class="form-control input-sm" placeholder="* " id="prenom" name="prenom" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-4">
                <label for="email" class="form-label">Votre Adresse E-mail :</label>
                <input type="email" placeholder="* " class="form-control" id="email" name="email" required>
            </div>
        </div>
    </div>

    <div class="row">  <!-- Nouvelle ligne pour Priorité et Objet -->
        <div class="col-md-6">
            <div class="form-group mb-4">
                <label for="priorite" class="form-label">Priorité :</label>
                <input type="text" class="form-control" id="priorite" name="priorite" value="Élevée" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-4">
                <label for="objet" class="form-label">Objet de votre Demande :</label>
                <select class="form-control" id="objet" name="objet" required>
                    <option value="">Sélectionnez un objet</option>
                    <option value="Problème technique">Problème technique</option>
                    <option value="Question sur l'utilisation">Question sur l'utilisation</option>
                    <option value="Demande d'information">Demande d'information</option>
                    <option value="Problème de facturation">Problème de facturation</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group mb-4">
        <label for="message" class="form-label">Message :</label>
        <textarea class="form-control" id="message" placeholder="* " name="message" rows="5" required></textarea>
    </div>

    <div class="form-group mb-4">
        <label for="fichier" class="form-label">
            Pièces Jointes <span style="font-weight: normal;">(optionnel)</span>
        </label>
        <input type="file" class="form-control" id="fichier" name="fichier">
        <small class="text-muted">Formats acceptés: PDF, JPG, PNG (max 5MB)</small>
    </div>

    <div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
        <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
            <i class="fas fa-paper-plane"></i> Envoyer la demande
        </button>
        <a href="{{ route('helpdesk.index') }}" class="btn btn-default">
            <i class="fas fa-undo"></i> Annuler
        </a>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<script>
    // Gestion des alertes
    document.addEventListener('DOMContentLoaded', function() {
            // Masquer les alertes après 5 secondes
            setTimeout(function() {
                var successAlert = document.getElementById('success-alert');
                var errorAlert = document.getElementById('error-alert');
                
                if(successAlert) {
                    successAlert.style.display = 'none';
                }
                if(errorAlert) {
                    errorAlert.style.display = 'none';
                }
            }, 5000);
        });
</script>
@endpush