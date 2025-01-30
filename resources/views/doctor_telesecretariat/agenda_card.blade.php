<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Médecins</title>
    <style>
        /* Bloque le scroll de la page */
        body, html {
            overflow: hidden;
            height: 100%;
        }

        .card {
            min-height: 80vh;
        }

        .card-body {
            display: flex;
            flex-direction: row;
            height: 100%;
            padding: 10px;
        }

        /* Section des médecins à gauche */
        .col-md-2 {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding-right: 10px;
            overflow: hidden;
        }

        .doctor-list-container {
            flex-grow: 1;
            overflow-y: auto;  /* Scroll uniquement pour cette zone */
            max-height: 300px;  /* Hauteur maximale pour la liste des médecins */
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0;
        }

        /* Nouvelle sous-section scrollable sous la liste des médecins */
        .doctor-details-container {
            flex-grow: 1;
            overflow-y: auto;  /* Scroll uniquement pour cette nouvelle zone */
            max-height: 200px;  /* Hauteur maximale pour la sous-section */
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 10px;
            padding: 10px;
        }

        /* Section droite - agenda */
        .col-md-10 {
            height: 100%;
            padding-left: 10px;
        }

        .p-3 {
            padding: 15px;
        }

        .border {
            border: 1px solid #ddd;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

    </style>
</head>
<body>

<div class="card">
    <div class="card-body">
        <!-- Section 1/5 à gauche -->
        <div class="col-md-2">
            <div class="p-1 border" style="display: flex; flex-direction: column; height: 100%;">
                <!-- Barre de recherche -->
                <div style="flex-shrink: 0; margin-bottom: 10px;">
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Chercher un médecin" 
                        id="searchDoctor" 
                        onkeyup="filterDoctors()"
                    >
                </div>

                <!-- Liste des médecins scrollable -->
                <div class="doctor-list-container">
                    <ul class="list-group doctor-list" style="margin: 0; padding: 0;">
                        @foreach ($doctorTelesecretariats as $doctorTelesecretariat)
                            <li 
                                class="list-group-item doctor-item" 
                                data-doctor-id="{{ $doctorTelesecretariat->doctor->id ?? '' }}" 
                                style="cursor: pointer;">
                                {{ $doctorTelesecretariat->doctor->name ?? 'Non spécifié' }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Nouvelle sous-section scrollable sous la liste des médecins -->
                <div class="doctor-details-container">
                    <h6>Détails du médecin</h6>
                    <p>Informations supplémentaires sur le médecin sélectionné seront affichées ici.</p>
                    <!-- Ajoutez ici d'autres informations qui pourraient être affichées pour chaque médecin -->
                </div>
                <!-- Nouvelle sous-section scrollable sous la liste des médecins -->
                <div class="doctor-details-container">
                    <h6>Détails du RDV</h6>
                    <p>Informations supplémentaires sur le patient sélectionné depuis l'agenda seront affichées ici.</p>
                    <!-- Ajoutez ici d'autres informations qui pourraient être affichées pour chaque médecin -->
                </div>
            </div>
        </div>

        <!-- Section 4/5 à droite -->
        <div class="col-md-10">
            <div class="p-3 border bg-light">
                <h5>Agenda s'affiche ici</h5>
                <p>Partie agenda</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>


<script>
    function filterDoctors() {
        // Récupérer la valeur de l'input
        const query = document.getElementById('searchDoctor').value.toLowerCase();

        // Récupérer tous les éléments de la liste des médecins
        const doctorItems = document.querySelectorAll('.doctor-item');

        // Parcourir chaque élément et vérifier si le nom du médecin correspond à la recherche
        doctorItems.forEach(item => {
            const doctorName = item.textContent.toLowerCase();

            if (doctorName.indexOf(query) !== -1) {
                // Si le nom du médecin contient la requête, afficher l'élément
                item.style.display = '';
            } else {
                // Sinon, masquer l'élément
                item.style.display = 'none';
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const doctorItems = document.querySelectorAll('.doctor-item');

        doctorItems.forEach(item => {
            item.addEventListener('click', function () {
                // Retirer la classe "active" de tous les éléments
                doctorItems.forEach(i => i.classList.remove('bg-{{ setting("theme_color") }}', 'text-white'));

                // Ajouter la classe "active" à l'élément cliqué
                this.classList.add('bg-{{ setting("theme_color") }}', 'text-white');

                // Optionnel : afficher ou utiliser l'ID du médecin sélectionné
                const selectedDoctorId = this.getAttribute('data-doctor-id');
                console.log('Médecin sélectionné :', selectedDoctorId);
            });
        });
    });
</script>