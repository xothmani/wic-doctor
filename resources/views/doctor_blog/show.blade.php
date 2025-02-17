@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <!-- Carte blanche pour le contenu du blog -->
            <div class="col-md-10">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="row">
                            <!-- Texte à gauche -->
                            <div class="col-md-8">
                                <span class="badge bg-primary text-white mb-2">{{ $blog->titre_court }}</span>
                                <h1 class="fw-bold">{!! $blog->titre !!}</h1>

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
                            <a href="{{ route('doctor_blog.index') }}" class="btn btn-default">
                                <i class="fa fa-undo"></i> {{ trans('lang.back') }}
                            </a>

                            <!-- Vérifier si l'utilisateur est connecté et s'il a le rôle commercial -->
                            @if(Auth::check() && Auth::user()->hasRole('commercial'))
                                <a class="btn btn-success">
                                    <i class="fa fa-check"></i> {{ trans('lang.validate') }}
                                </a>
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
