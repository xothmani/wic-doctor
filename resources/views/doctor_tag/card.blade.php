<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
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

        .doctor-tag-list-container {
            flex-grow: 1;
            overflow-y: auto;  /* Scroll uniquement pour cette zone */
            max-height: 500px;  /* Hauteur maximale pour la liste des médecins */
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0;
        }

        /* Nouvelle sous-section scrollable sous la liste des médecins */
        .doctor-tag-details-container {
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

        .doctor-tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Espacement entre les tags */
            padding: 10px;
        }

        .tag-item {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px; /* Bord arrondi */
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background 0.3s ease;
        }

        /* Effet hover */
        .tag-item:hover {
            opacity: 0.8;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;  /* Aligne l'icône et le texte verticalement */
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background 0.3s ease;
        }

        .icon {
            margin-right: 8px;  /* Espace entre l'icône et le texte */
        }

        /* Tags par défaut en bleu */
        .tag-item {
            background-color: rgb(181, 181, 181);
            color: white;
        }

        /* Tags sélectionnés en rouge */
        .tag-item.selected {
            background-color: #001f3f;
            color: white;
        }

        /* Effet hover */
        .tag-item:hover {
            opacity: 0.8;
        }

        .etiquette {
            padding: 10px;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="card">
        <div class="card-body">
            <div class="col-md-12">
                <div class="etiquette">
                    <h3 style="font-weight: bold;">À propos des étiquettes</h3>
                    <p style="font-size: 20px;">Les étiquettes sont des expertises et des actes qui permettent d’indiquer vos spécialités, vos domaines d’expertise et les services que vous proposez. Elles aident les patients à vous trouver plus facilement en fonction de leurs besoins médicaux. Ajoutez des étiquettes pertinentes pour mieux présenter votre activité.</p>
                </div>

                <div class="p-1 border" style="display: flex; flex-direction: column; height: 80%;">
                    <!-- Formulaire classique -->
                    <form id="tagForm" action="{{ route('doctor_tags.store') }}" method="POST">
                        @csrf

                        <!-- Barre de recherche -->
                        <div style="flex-shrink: 0; margin-bottom: 10px;">
                            <input type="text" class="form-control" id="searchInput" placeholder="Chercher une étiquette" onkeyup="filterTags()">
                        </div>

                        <!-- Liste des médecins scrollable -->
                        <div class="doctor-tag-list-container" style="height: 75%;">
                            <div class="doctor-tag-list">
                                @foreach ($tags as $tag)
                                    <span class="tag-item {{ in_array($tag->id, $doctorTags) ? 'selected' : '' }}" onclick="toggleTagSelection(this)">
                                        <i class="fas {{ in_array($tag->id, $doctorTags) ? 'fa-check' : 'fa-plus' }} icon"></i> 
                                        <span class="tag-name">{{ $tag->name ?? 'Non spécifié' }}</span>
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="d-none tag-checkbox" {{ in_array($tag->id, $doctorTags) ? 'checked' : '' }}>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div style="margin-top: 15px;" class="d-flex justify-content-between">
    <div>
        <a class="btn btn-default" onclick="selectAllTags()">
            <i class="fa fa-check"></i> Cocher tous
        </a>
        <a class="btn btn-default" onclick="deselectAllTags()">
            <i class="fa fa-times"></i> Décocher tous
        </a>
    </div>
<!-- Modification du bouton enregistrer pour ouvrir la modal -->
<button type="button" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2" data-toggle="modal" data-target="#confirmAcceptModal">Enregistrer</button></div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<!-- Ajout de la modal -->
<div class="modal fade" id="confirmAcceptModal" tabindex="-1" aria-labelledby="confirmAcceptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmAcceptModalLabel">Confirmer l'enregistrement</h5>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir enregistrer ces étiquettes ?<br><b>Vos expertises et actes seront visibles sur votre profil.</b> </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border cancel-btn" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i> Annuler
                </button>
                <button type="button" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2" id="confirmSave">Confirmer</button>
            </div>
        </div>
    </div>
</div>




<script>
document.getElementById("confirmSave").addEventListener("click", function() {
    document.getElementById("tagForm").submit();
});
</script>

<script>
function filterTags() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let tags = document.querySelectorAll(".tag-item");

    tags.forEach(tag => {
        let tagText = tag.querySelector('.tag-name').textContent.toLowerCase();
        if (tagText.includes(input)) {
            tag.style.display = "inline-block"; // Affiche les tags correspondants
        } else {
            tag.style.display = "none"; // Cache les tags qui ne correspondent pas
        }
    });
}
function toggleTagSelection(tagElement) {
    let checkbox = tagElement.querySelector("input[type='checkbox']");
    
    // Inverser l'état de la case à cocher
    checkbox.checked = !checkbox.checked;

    // Mettre à jour l'apparence du tag
    if (checkbox.checked) {
        tagElement.classList.add('selected');
        tagElement.querySelector('.icon').classList.remove('fa-plus');
        tagElement.querySelector('.icon').classList.add('fa-check');
    } else {
        tagElement.classList.remove('selected');
        tagElement.querySelector('.icon').classList.remove('fa-check');
        tagElement.querySelector('.icon').classList.add('fa-plus');
    }
}


function selectAllTags() {
    let checkboxes = document.querySelectorAll(".tag-checkbox");
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        let tagElement = checkbox.closest('.tag-item');
        tagElement.classList.add('selected');
        tagElement.querySelector('.icon').classList.remove('fa-plus');
        tagElement.querySelector('.icon').classList.add('fa-check');
    });
}

function deselectAllTags() {
    let checkboxes = document.querySelectorAll(".tag-checkbox");
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        let tagElement = checkbox.closest('.tag-item');
        tagElement.classList.remove('selected');
        tagElement.querySelector('.icon').classList.remove('fa-check');
        tagElement.querySelector('.icon').classList.add('fa-plus');
    });
}


</script>
</html>
