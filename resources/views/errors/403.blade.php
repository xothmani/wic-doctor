<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Accès Interdit</title>
    <!-- Lien vers Bootstrap (version 4 ou 5, au choix) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Lien vers Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Carte principale -->
                <div class="card border-danger">
                    <!-- En-tête rouge -->
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Accès interdit
                        </h3>
                    </div>
                    <!-- Corps de la carte -->
                    <div class="card-body text-center">
                        <p class="lead mb-4">
                            L’abonnement est inactif ou expiré.
                        </p>

                        <!-- Bouton de déconnexion -->
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-out-alt"></i>
                                {{ __('Retourner à l\'accueil') }}
                            </button>
                        </form>


                        <!-- Bouton de retour à l’accueil -->
                        <a href="https://wic-doctor.com/inscription-professionnel/offres.html"
                            class="btn btn-secondary">
                            <i class="fas fa-home"></i>
                            Consulter notre plateforme
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JS Bootstrap et dépendances (optionnels pour la démo) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>