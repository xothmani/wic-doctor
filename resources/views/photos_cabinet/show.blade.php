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
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Empêcher le comportement par défaut du bouton

        // Récupérer les données de l'image et du docteur
        var imageName = this.getAttribute('data-image');
        var doctorId = this.getAttribute('data-id');

        // Afficher le modal de confirmation pour le rejet
        var rejectModal = new bootstrap.Modal(document.getElementById('confirmRejectModal'));
        rejectModal.show();

        // Gestion du clic sur le bouton "Rejeter" dans le modal
        document.getElementById('confirmRejectModal').querySelector('form#rejectForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher la soumission du formulaire

            // Envoyer la requête AJAX pour rejeter l'image
            fetch('/photos-cabinet/rejet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    doctorId: doctorId,
                    imageName: imageName
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server Response:', data);
                if (data.error) {
                    alert(data.error); // Afficher un message d'erreur
                } else {
                    alert(data.message); // Afficher un message de succès
                    // Rediriger vers la page "photos_cabinet.show" après la réussite
                    window.location.href = `/photos-cabinet/${doctorId}`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });

            // Fermer le modal après la soumission
            rejectModal.hide();
        });
    });
});

// Gestion du clic sur le bouton d'acceptation
document.querySelectorAll('.accept-btn').forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Empêcher le comportement par défaut du bouton

        // Récupérer les données de l'image et du docteur
        var imageName = this.getAttribute('data-image');
        var doctorId = this.getAttribute('data-id');

        // Afficher le modal de confirmation pour l'acceptation
        var acceptModal = new bootstrap.Modal(document.getElementById('confirmAcceptModal'));
        acceptModal.show();

        // Gestion du clic sur le bouton "Accepter" dans le modal
        document.getElementById('confirmAcceptModal').querySelector('form#acceptForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher la soumission du formulaire

            // Envoyer la requête AJAX pour accepter l'image
            fetch('/photos-cabinet/accept', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    doctorId: doctorId,
                    imageName: imageName
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server Response:', data);
                if (data.error) {
                    alert(data.error); // Afficher un message d'erreur
                } else {
                    alert(data.message); // Afficher un message de succès
                    // Rediriger vers la page "photos_cabinet.show" après la réussite
                    window.location.href = `/photos-cabinet/${doctorId}`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });

            // Fermer le modal après la soumission
            acceptModal.hide();
        });
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
                <form id="acceptForm" method="POST" class="d-inline">
                    @csrf
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
                <form id="rejectForm" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Rejeter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
