@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

    <div class="container py-5">
        <div class="row justify-content-center">
            <!-- Carte blanche pour le contenu du blog -->
            <div class="col-md-10">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="row">
                            <!-- Texte à gauche -->
                            <div class="col-md-8">
                                <span class="badge text-white mb-2" style="color: #001f3f;">{{ $blog->titre_court }}</span>
                                <h2 class="fw-bold">{!! $blog->titre !!}</h2>

                                <!-- Contenu du blog avec un texte masqué -->
                                <div id="blog-content" class="text-muted" style="max-height: 180px; overflow: hidden; position: relative;">
                                    {!! $blog->contenu !!}
                                </div>

                                <!-- Bouton pour afficher plus -->
                                <button id="read-more-btn" class="btn btn-link" onclick="toggleContent()">
                                    <i class="fa fa-chevron-down"></i> 
                                </button>
                            </div>

                            <!-- Image à droite -->
                            <div class="col-md-4 text-center ">
                                @if($imagePath)
                                    <img src="{{ $imagePath }}" alt="Image du blog" class="img-fluid rounded" style="max-width: 100%; height: auto; object-fit: cover;">
                                @endif
                            </div>
                        </div>

                       <!-- Bouton de retour en bas à droite de la carte -->
<div class="text-end mt-3">
@if($blog->status == 'en cours')
    <a href="{{ route('doctor_blog.index') }}" class="btn btn-secondary">
        <i class="fa fa-undo"></i> {{ trans('lang.back') }}
    </a>
@elseif($blog->status == 'accepté')
    <a href="{{ route('doctor_blog.accepted') }}" class="btn btn-secondary">
        <i class="fa fa-undo"></i> {{ trans('lang.back') }}
    </a>
@elseif($blog->status == 'ajourné')
    <a href="{{ route('doctor_blog.rejected') }}" class="btn btn-secondary">
        <i class="fa fa-undo"></i> {{ trans('lang.back') }}
    </a>
@endif
@if(Auth::check() && Auth::user()->hasRole('commercial') && $blog->status == 'en cours')
    <!-- Bouton pour ouvrir le modal -->
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejetModal{{ $blog->id }}">
    <i class="fa fa-times"></i> {{ trans('lang.blog_rejeter') }}
</button>
   <!-- Modal de confirmation -->
<div class="modal fade" id="rejetModal{{ $blog->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Voulez-vous vraiment rejeter la publication de ce blog?
                
                <!-- Liste déroulante des raisons -->
                <form action="{{ route('doctor_blog.rejet', $blog->id) }}" method="POST" id="rejetForm">
                    @csrf
                    <div class="mb-3 mt-2">
                        <select id="raisonRejet" name="raisonRejet" class="form-select" required>
                            <option value="" disabled selected>Sélectionnez une raison</option>
                            <option value="Contenu Sensible ou Inapproprié">Contenu Sensible ou Inapproprié</option>
                            <option value="Publicité ou Promotion Inappropriée">Publicité ou Promotion Inappropriée</option>
                            <option value="Critiques Non Constructives">Critiques Non Constructives</option>
                            <option value="Informations Erronées ou Non Fondées">Informations Erronées ou Non Fondées</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="rejetForm" class="btn btn-danger">Rejeter</button>
            </div>
        </div>
    </div>
</div>

@endif

@if(Auth::check() && Auth::user()->hasRole('commercial') && $blog->status == 'en cours')
    <!-- Bouton pour ouvrir le modal -->
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $blog->id }}">
        <i class="fa fa-check"></i> {{ trans('lang.validate') }}
    </button>

    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmModal{{ $blog->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Voulez-vous vraiment accepter la publication de ce blog!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="{{ route('doctor_blog.accept', $blog->id) }}" class="btn btn-success">Accepter</a>
                </div>
            </div>
        </div>
    </div>
@endif




                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour afficher/masquer le contenu
        function toggleContent() {
            const content = document.getElementById("blog-content");
            const btn = document.getElementById("read-more-btn");

            if (content.style.maxHeight === "none") {
                content.style.maxHeight = "180px"; // Retirer l'affichage complet
                btn.innerHTML = '<i class="fa fa-chevron-down"></i>';
            } else {
                content.style.maxHeight = "none"; // Afficher tout le texte
                btn.innerHTML = '<i class="fa fa-chevron-up"></i>';
            }
        }
    </script>
@endsection
