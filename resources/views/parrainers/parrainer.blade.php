@extends('layouts.app')

@section('title', 'Parrainage et Docteurs')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Section de l'en-tête -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-bold">{{ ('Parrain') }} 
                        <small class="mx-3">|</small><small>{{ ('Envoyer Parrain') }}</small>
                    </h1>
                </div>
                <div class="col-md-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt mx-1"></i> {{ trans('Tableau de bord') }}
                            </a>
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
                        <a class="nav-link {{ request()->is('parrainer') ? 'active-tab' : '' }}" href="parrainer">
                            <i class="fa fa-list mr-2"></i>{{ trans('Tableau des Parrains') }}
                        </a>
                    </li>
                    @can('parrainers.parrainer') 
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('parrainers/parrainer') ? 'active-tab' : '' }}" href="{!! route('parrainers.parrainer') !!}">
                                <i class="fa fa-plus mr-2"></i>{{trans('Créer un filleul')}}
                            </a>
                        </li>
                    @endcan
                </div>
            </ul>
        </div>

        <div class="container mt-5">
            <div class="card shadow">
                <div class="card-body">
                    <h3 style="margin-bottom: 20px;">Parrainage</h3>
                    <p class="mb-4" style="font-size: 1.2em; margin-top: 10px;">
                    Cher Docteur,</br>

Nous vous invitons à partager ce lien avec vos collègues afin qu'ils puissent également découvrir les avantages exclusifs de WIC Doctor . En transmettant ce lien, vous leur offrez l’opportunité de profiter d’une offre promotionnelle spéciale 🎁.
Grâce à notre programme de parrainage, chaque médecin que vous recommandez pourra accéder à des services innovants tels que la téléconsultation, la prise de rendez-vous simplifiée, la gestion optimisée de leur visibilité en ligne et bien plus encore.
N’hésitez pas à en parler autour de vous et à encourager vos confrères à rejoindre WIC Doctor, afin qu’ils puissent eux aussi bénéficier de cette offre avantageuse.

Votre réseau mérite le meilleur de la technologie médicale ! 🌟

                    </p>

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

                        <!-- QR Code Section -->
                        <div class="text-center mt-4">
                            <h5>Scannez le QR Code pour accéder au lien</h5>
                            <div id="qrcode" style="margin-bottom: 10px;"></div>
                            <div class="btn-container">
                                <a href="{{ $link }}" download>
                                </a>
                            </div>
                        </div>
                    @else
                        <p>Aucun lien de parrainage disponible.</p>
                    @endif

                    <!-- Formulaire "Envoyer e-mail" -->
                    <form action="/envoyer-email" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label">Inviter un filleul par e-mail</label>
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
                            max-width: 250px;
                            width: 100%;
                        }

                        .input-group {
                            max-width: 350px;
                        }

                        .mb-3 {
                            margin-bottom: 20px;
                        }

                        #qrcode {
                            margin-top: 10px;
                            display: inline-block;
                        }
                        .custom-icon-size {
                            font-size: 1.5rem; /* Vous pouvez ajuster cette valeur */
                        }
                        .text-center.mt-4 {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                        }

                        .btn-container {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            margin-top: 10px;
                            width: 100px;
                        }

                        .btn-container a {
                            margin-top: 10px;
                        }
                    </style>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            var copyText = document.getElementById("parrain-link");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 

            document.execCommand("copy");

            var feedback = document.getElementById("copy-feedback");
            feedback.style.display = "block";

            setTimeout(function() {
                feedback.style.display = "none";
            }, 2000);
        }

        // Générer le QR Code avec le lien de parrainage
        document.addEventListener("DOMContentLoaded", function() {
    var link = "{{ $link }}";
    if (link) {
        // Crée un QR Code
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: link,
            width: 150,
            height: 150,
            correctLevel: QRCode.CorrectLevel.H
        });

        // Ajoute un bouton de téléchargement
        var downloadButton = document.createElement('a');
        downloadButton.href = document.getElementById("qrcode").querySelector('img').src;  // Prendre l'image générée
        downloadButton.download = "qrcode.png";  // Nom du fichier à télécharger
        downloadButton.innerHTML = '<i class="bi bi-download custom-icon-size"></i> ';
        document.querySelector('.btn-container').appendChild(downloadButton);
    }
});

    </script>

@endsection
