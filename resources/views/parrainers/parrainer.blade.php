@extends('layouts.app')

@section('title', 'Parrainage et Docteurs')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Section de l'en-tête -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-bold">{{ ('Parrain') }} 
                        <small class="mx-3">|</small><small>{{ ('Envoyer Parrain') }}</small>
                    </h1>
                </div><!-- /.col -->
                <div class="col-md-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt mx-1"></i> {{ trans('Tableau de bord') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                        </li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
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
                    <a class="nav-link {{ request()->is('parrainer') ? 'active-tab' : '' }}" href="parrainer" id="list-tab">
                    <i class="fa fa-list mr-2"></i>{{ trans('Tableau des Parrains') }}
                        </a>
                    </li>

                    @can('parrainers.parrainer') 
    <li class="nav-item">
        <a class="nav-link {{ request()->is('parrainers/parrainer') ? 'active-tab' : '' }}" href="{!! route('parrainers.parrainer') !!}">
            <i class="fa fa-plus mr-2"></i>{{trans('Créer un parrain')}}
        </a>
    </li>
@endcan
                </div>
            </ul>
        </div>
        <div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <div class="mb-5">
    <h3 style="margin-bottom: 20px;">Parrainage</h3> <!-- Ajout d'un espacement au bas du titre -->
    <p class="mb-4" style="font-size: 1.2em; margin-top: 10px;">Le parrainage vous permet de partager un lien unique avec vos contacts. Cela leur donnera la possibilité de s'inscrire tout en vous associant en tant que parrain.</p>

                @if(isset($link))
                    <div class="mb-4">
                        <label for="parrain-link" class="form-label">Votre lien de parrainage</label>
<div class="input-group mb-3" style="max-width: 800px;">
    <input 
        id="parrain-link"
        type="text"
        class="form-control"
        value="{{ $link }}"
        readonly
        style="font-size: 1rem; padding: 10px;"
    >
    <span 
        class="input-group-text" 
        style="cursor: pointer; padding: 0.5rem;"
        onclick="copyToClipboard()"
        id="copy-icon"
        title="Copier le lien"
    >
        <i class="bi bi-clipboard" id="clipboard-icon"></i>
    </span>
</div>

                        
                        <p id="copy-feedback" class="text-success mt-2" style="display: none;">Lien copié !</p>
                    </div>
                @else
                    <p>Aucun lien de parrainage disponible.</p>
                @endif

                <!-- Formulaire "Envoyer e-mail" -->
                <form action="/envoyer-email" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">Envoyer e-mail</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control email-input" 
                            placeholder="Entrez votre email" 
                            required
                        >
                    </div>
                    <div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
                        <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
                            <i class="fas fa-save"></i> Sauver Parrain
                        </button>
                        <a href="/parrainer" class="btn btn-default">
                            <i class="fas fa-undo"></i> Annuler
                        </a>
                    </div>
                </form>

                <style>
                    .email-input {
                        max-width: 250px; /* Set the max width as per your requirement */
                        width: 100%; /* This ensures it remains responsive, but does not exceed the max width */
                    }

                    .input-group {
                        max-width: 350px;
                    }

                    /* Optional: Add margin between the email input and the link */
                    .mb-3 {
                        margin-bottom: 20px;
                    }
                </style>
            </div>
        </div>
    </div>
</div>

    </div>
@endsection
