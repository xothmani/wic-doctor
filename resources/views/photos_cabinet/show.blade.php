@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h3 class="text-center mb-4">Photos du cabinet du docteur {{ $doctorName }}</h3>
                    <!-- Affichage des images -->
                    <div class="row">
                        @forelse($imageUrls as $image)
                            <div class="col-md-4 mb-3 position-relative">
                                <div class="card">
                                    <!-- Appliquer la hauteur fixe aux images -->
                                    <img src="{{ $image }}" class="card-img-top img-fluid image-height" alt="Photo du cabinet">

                                    <!-- Bouton de rejet (icône x en haut à gauche) -->
                                    <button class="btn btn-danger btn-sm reject-btn position-absolute top-0 start-0 m-2" data-image="{{ basename($image) }}" data-id="{{ $id }}">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <!-- Bouton d'acceptation (icône tick en haut à droite) -->
                                    <button class="btn btn-success btn-sm accept-btn position-absolute top-0 end-0 m-2" data-image="{{ basename($image) }}" data-id="{{ $id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-center">Aucune image trouvée.</p>
                        @endforelse
                    </div>

                    <!-- Modal de confirmation pour Rejeter -->
                    <div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmRejectModalLabel">Confirmer le rejet</h5>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir rejeter cette photo ? Cette action est irréversible.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border cancel-btn" data-dismiss="modal">
                                        <i class="fas fa-times mr-2"></i> {{ __('lang.cancel') }}
                                    </button>    
                                    <form id="rejectForm" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Rejeter</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmation pour Accepter -->
                    <div class="modal fade" id="confirmAcceptModal" tabindex="-1" aria-labelledby="confirmAcceptModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmAcceptModalLabel">Confirmer l'acceptation</h5>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir accepter cette photo ?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light border cancel-btn" data-dismiss="modal">
                                        <i class="fas fa-times mr-2"></i> {{ __('lang.cancel') }}
                                    </button>           
                                    <form id="acceptForm" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Accepter</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de retour -->
                    <div class="text-end mt-3">
                        <a href="{{ route('photos_cabinet.index') }}" class="btn btn-secondary">Retour à la liste</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Gestion du clic sur le bouton de rejet
    document.querySelectorAll('.reject-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var imageName = this.getAttribute('data-image');
            var doctorId = this.getAttribute('data-id');
            
            console.log('Rejet: doctorId =', doctorId, ', imageName =', imageName); // Vérifier si les bonnes valeurs sont extraites

            // Mettre à jour le formulaire de rejet avec l'ID du docteur et le nom de l'image
            var form = document.getElementById('rejectForm');
            form.action = '/photos_cabinet/reject/' + doctorId + '/' + imageName;


            console.log('Form action set to:', form.action); // Vérifier si l'action du formulaire est bien mise à jour
            // Afficher la fenêtre modale de rejet
            var modal = new bootstrap.Modal(document.getElementById('confirmRejectModal'));
            modal.show();
        });
    });

    // Gestion du clic sur le bouton d'acceptation
    document.querySelectorAll('.accept-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var imageName = this.getAttribute('data-image');
            var doctorId = this.getAttribute('data-id');

            console.log('Acceptation: doctorId =', doctorId, ', imageName =', imageName); // Vérifier les valeurs extraites
            
            // Mettre à jour le formulaire d'acceptation avec l'ID du docteur et le nom de l'image
            var form = document.getElementById('acceptForm');
            form.action = '/photos_cabinet/accept/' + doctorId + '/' + imageName;


            console.log('Form action set to:', form.action); // Vérifier l'action du formulaire

            // Afficher la fenêtre modale d'acceptation
            var modal = new bootstrap.Modal(document.getElementById('confirmAcceptModal'));
            modal.show();
        });
    });

    // Code pour fermer les modals avec le bouton "Annuler" ou "Fermer" (croix)
    document.querySelectorAll('.btn-close, .btn-secondary').forEach(function(button) {
        button.addEventListener('click', function() {
            // Cibler le modal parent et fermer en utilisant bootstrap.Modal
            var modalElement = this.closest('.modal');
            var modal = new bootstrap.Modal(modalElement);
            modal.hide(); // Fermer le modal
        });
    });
</script>

<style>
    /* Définir la hauteur des images */
    .image-height {
        height: 200px;
        object-fit: cover; /* Assurer que l'image conserve son aspect tout en remplissant l'espace */
    }
</style>

@endsection
