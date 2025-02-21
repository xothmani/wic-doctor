@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5">
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');

            if (errorAlert) {
                errorAlert.style.transition = "opacity 1s";
                errorAlert.style.opacity = "0";
                setTimeout(() => errorAlert.remove(), 1000); // Supprime l'élément après l'animation
            }

            if (successAlert) {
                successAlert.style.transition = "opacity 1s";
                successAlert.style.opacity = "0";
                setTimeout(() => successAlert.remove(), 1000); // Supprime l'élément après l'animation
            }
        }, 5000); // 5 secondes
    });
</script>
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

document.querySelectorAll('.accept-btn').forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Empêcher le comportement par défaut du bouton

        // Récupérer les données de l'image et du docteur
        var imageName = this.getAttribute('data-image');
        var doctorId = this.getAttribute('data-id');

        // Remplir les champs cachés du formulaire
        document.getElementById('doctorIdInput').value = doctorId;
        document.getElementById('imageNameInput').value = imageName;

        // Afficher le modal de confirmation pour le rejet
        var acceptModal = new bootstrap.Modal(document.getElementById('confirmAcceptModal'));
        acceptModal.show();
    });
});

document.querySelectorAll('.reject-btn').forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Empêcher le comportement par défaut du bouton

        // Récupérer les données de l'image et du docteur
        var imageName = this.getAttribute('data-image');
        var doctorId = this.getAttribute('data-id');

        // Remplir les champs cachés du formulaire
        document.getElementById('doctorIdInput1').value = doctorId;
        document.getElementById('imageNameInput1').value = imageName;

        // Afficher le modal de confirmation pour le rejet
        var rejectModal = new bootstrap.Modal(document.getElementById('confirmRejectModal'));
        rejectModal.show();
    });
});

// Gestion du clic sur le bouton "Annuler" ou "Fermer" dans les modals
document.querySelectorAll('.btn-close, .btn-secondary').forEach(function(button) {
    button.addEventListener('click', function() {
        // Fermer le modal parent
        var modalElement = this.closest('.modal');
        var modal = new bootstrap.Modal(modalElement);
        modal.hide();
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
                    <i class="fas fa-times mr-2"></i> Annuler
                </button>
                <form id="acceptForm" method="POST" action="{{ route('photos_cabinet.accept') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="doctorId" id="doctorIdInput">
                    <input type="hidden" name="imageName" id="imageNameInput">
                    <button type="submit" class="btn btn-success">Accepter</button>
                </form>
            </div>
        </div>
    </div>
</div>


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
                    <i class="fas fa-times mr-2"></i> Annuler
                </button>
                <form id="acceptForm" method="POST" action="{{ route('photos_cabinet.rejet') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="doctorId" id="doctorIdInput1">
                    <input type="hidden" name="imageName" id="imageNameInput1">
                    <button type="submit" class="btn btn-danger">Rejeter</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
