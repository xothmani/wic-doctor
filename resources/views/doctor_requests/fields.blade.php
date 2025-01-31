<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Nom Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="nom" class="col-md-3 control-label text-md-right mx-1">Nom</label>
        <div class="col-md-9">
            <input type="text" id="nom" name="nom" class="form-control" placeholder="Entrez votre nom" required>
        </div>
    </div>

    <!-- Prénom Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="prenom" class="col-md-3 control-label text-md-right mx-1">Prénom</label>
        <div class="col-md-9">
            <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Entrez votre prénom" required>
        </div>
    </div>

    <!-- Sexe Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="sexe" class="col-md-3 control-label text-md-right mx-1">Vous êtes</label>
        <div class="col-md-9">
            <select id="sexe" name="sexe" class="form-control" required>
                <option value="" disabled selected>Vous êtes</option>
                <option value="homme">Homme</option>
                <option value="femme">Femme</option>
            </select>
        </div>
    </div>
<!-- Téléphone Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    <label for="phone_number" class="col-md-3 control-label text-md-right mx-1">Numéro de téléphone</label>
    <div class="col-md-9">
    <!-- Champ caché pour le code pays -->
    <input type="hidden" id="country_code" name="country_code">
    <input type="tel" id="phone_number" name="phone_number" class="form-control" placeholder="Entrez votre numéro de téléphone" required>
    </div>
</div>


    <!-- Email Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="email" class="col-md-3 control-label text-md-right mx-1">Email</label>
        <div class="col-md-9">
            <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
        </div>
    </div>

<!-- Pays Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    <label for="pays" class="col-md-3 control-label text-md-right mx-1">Pays</label>
    <div class="col-md-9">
        <select id="pays" name="pays" class="form-control" onchange="updateProvinces(); toggleTypeField();" required>
            <option value="" disabled selected>Sélectionnez un pays</option>
            <option value="france">France</option>
            <option value="tunisie">Tunisie</option>
        </select>
    </div>
</div>

    <!-- Region (France) / Gouvernorat (Tunisie) Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="region" class="col-md-3 control-label text-md-right mx-1">Région / Gouvernorat</label>
        <div class="col-md-9">
            <select id="region" name="region" class="form-control" required>
                <option value="" disabled selected>Sélectionnez la région / gouvernorat</option>
                <!-- Regions will be populated dynamically -->
            </select>
        </div>
    </div>

    <!-- Ville Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="ville" class="col-md-3 control-label text-md-right mx-1">Ville</label>
        <div class="col-md-9">
            <select id="ville" name="ville" class="form-control" required>
                <option value="" disabled selected>Sélectionnez la ville</option>
                <!-- Cities will be populated dynamically -->
            </select>
        </div>
    </div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Adresse Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="adresse" class="col-md-3 control-label text-md-right mx-1">Adresse</label>
        <div class="col-md-9">
            <input type="text" id="adresse" name="adresse" class="form-control" placeholder="Entrez votre adresse" required>
        </div>
    </div>


<!-- Type Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    <label for="type" class="col-md-3 control-label text-md-right mx-1">Vous êtes</label>
    <div class="col-md-9">
        <select id="type" name="type" class="form-control" disabled required>
            <option value="" disabled selected>Vous êtes</option>
            <option value="Docteur">Docteur</option>
            <option value="Pharmacie">Pharmacie</option>
            <option value="Banque de sang">Banque de sang</option>
            <option value="Parapharmacie">Parapharmacie</option>
            <option value="Ambulance privée">Ambulance privée</option>
            <option value="Infirmierie privée">Infirmierie privée</option>
            <option value="Clinique">Clinique</option>
            <option value="hopital">Hôpital</option>
            <option value="CentreDialyse">Centre de Dialyse</option>
            <option value="Autre">Autre</option>
        </select>
    </div>
</div>



<!-- Spécialité Field -->
<div  class="form-group align-items-baseline d-flex flex-column flex-md-row">
    <label id="specialite-container1" style="display: none;" for="specialite" class="col-md-3 control-label text-md-right mx-1">Spécialité</label>
    <div id="specialite-container" style="display: none;" class="col-md-9">
        <select id="specialite" name="specialite" class="form-control">
            <option value="" disabled selected>Sélectionnez une spécialité</option>
        </select>
    </div>
</div>






    <!-- Description Field (not required) -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <label for="description" class="col-md-3 control-label text-md-right mx-1">Description</label>
        <div class="col-md-9">
            <textarea id="description" name="description" class="form-control" placeholder="Entrez une description"></textarea>
        </div>
    </div>
</div>
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}}
    </button>
    <a href="{!! route('doctor_requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>



<script>
   const provinces = {
        france: ["Auvergne-Rhône-Alpes",
        "Bourgogne-Franche-Comté",
        "Bretagne",
        "Centre-Val de Loire",
        "Corse",
        "Grand Est",
        "Hauts-de-France",
        "Île-de-France",
        "Normandie",
        "Nouvelle-Aquitaine",
        "Occitanie",
        "Pays de la Loire",
        "Provence-Alpes-Côte d'Azur",
        "Guadeloupe",
        "Guyane",
        "La Réunion",
        "Martinique",
        "Mayotte"
    ],
        
        tunisie: ["Ariana",
        "Béja",
        "Ben Arous",
        "Bizerte",
        "Gabès",
        "Gafsa",
        "Jendouba",
        "Kairouan",
        "Kasserine",
        "Kébili",
        "La Manouba",
        "Le Kef",
        "Mahdia",
        "Médenine",
        "Monastir",
        "Nabeul",
        "Sfax",
        "Sidi Bouzid",
        "Siliana",
        "Sousse",
        "Tataouine",
        "Tozeur",
        "Tunis",
        "Zaghouan"]
    };

    const cities = {
        "Auvergne-Rhône-Alpes": ["Lyon", "Saint-Étienne", "Grenoble", "Clermont-Ferrand", "Annecy"],
    "Bourgogne-Franche-Comté": ["Dijon", "Besançon", "Belfort", "Auxerre", "Chalon-sur-Saône"],
    "Bretagne": ["Rennes", "Brest", "Quimper", "Lorient", "Vannes"],
    "Centre-Val de Loire": ["Orléans", "Tours", "Chartres", "Bourges", "Châteauroux"],
    "Corse": ["Ajaccio", "Bastia", "Calvi", "Corte", "Porto-Vecchio"],
    "Grand Est": ["Strasbourg", "Reims", "Metz", "Nancy", "Mulhouse"],
    "Hauts-de-France": ["Lille", "Amiens", "Dunkerque", "Calais", "Roubaix"],
    "Île-de-France": ["Paris", "Boulogne-Billancourt", "Saint-Denis", "Argenteuil", "Versailles"],
    "Normandie": ["Rouen", "Caen", "Le Havre", "Évreux", "Cherbourg"],
    "Nouvelle-Aquitaine": ["Bordeaux", "Limoges", "Pau", "La Rochelle", "Poitiers"],
    "Occitanie": ["Toulouse", "Montpellier", "Nîmes", "Perpignan", "Albi"],
    "Pays de la Loire": ["Nantes", "Angers", "Le Mans", "Saint-Nazaire", "Cholet"],
    "Provence-Alpes-Côte d'Azur": ["Marseille", "Nice", "Toulon", "Aix-en-Provence", "Avignon"],
    "Guadeloupe": ["Pointe-à-Pitre", "Basse-Terre", "Le Gosier", "Sainte-Anne", "Saint-François"],
    "Guyane": ["Cayenne", "Kourou", "Saint-Laurent-du-Maroni", "Roura", "Matoury"],
    "La Réunion": ["Saint-Denis", "Saint-Pierre", "Saint-Paul", "Le Tampon", "Saint-André"],
    "Martinique": ["Fort-de-France", "Le Lamentin", "Schoelcher", "Le Robert", "Ducos"],
    "Mayotte": ["Mamoudzou", "Dzaoudzi", "Koungou", "Bandraboua", "Sada"],
    "Ariana": ["Ariana Ville", "Raoued", "Soukra", "Mnihla", "Kalaat El Andalous"],
    "Béja": ["Béja Ville", "Medjez el-Bab", "Nefza", "Téboursouk", "Testour"],
    "Ben Arous": ["Ben Arous", "Hammam-Lif", "Mégrine", "Mornag", "Radès"],
    "Bizerte": ["Bizerte", "Menzel Bourguiba", "Ras Jebel", "Mateur", "Sejnane"],
    "Gabès": ["Gabès Ville", "Métouia", "El Hamma", "Mareth", "Chenini Nahal"],
    "Gafsa": ["Gafsa Ville", "Métlaoui", "Redeyef", "Mdhila", "El Ksar"],
    "Jendouba": ["Jendouba Ville", "Bou Salem", "Tabarka", "Aïn Draham", "Fernana"],
    "Kairouan": ["Kairouan Ville", "Hajeb El Ayoun", "Sbikha", "Bou Hajjla", "Nasrallah"],
    "Kasserine": ["Kasserine Ville", "Sbeitla", "Fériana", "Thala", "Hassi El Ferid"],
    "Kébili": ["Kébili Ville", "Douz", "Souk Lahad", "El Faouar", "Blidet"],
    "La Manouba": ["Manouba Ville", "Douar Hicher", "Oued Ellil", "Tebourba", "Borj El Amri"],
    "Le Kef": ["Le Kef Ville", "Dahmani", "Tajerouine", "Jerissa", "Sakiet Sidi Youssef"],
    "Mahdia": ["Mahdia Ville", "Chebba", "Ksour Essef", "Bou Merdes", "El Jem"],
    "Médenine": ["Médenine Ville", "Ben Gardane", "Zarzis", "Beni Khedache", "Sidi Makhlouf"],
    "Monastir": ["Monastir Ville", "Jemmal", "Ksar Hellal", "Sahline", "Teboulba"],
    "Nabeul": ["Nabeul Ville", "Hammamet", "Kelibia", "Korba", "Dar Chaabane"],
    "Sfax": ["Sfax Ville", "Agareb", "Bir Ali Ben Khalifa", "El Hencha", "Mahrès"],
    "Sidi Bouzid": ["Sidi Bouzid Ville", "Regueb", "Meknassy", "Jilma", "Cebbala"],
    "Siliana": ["Siliana Ville", "El Krib", "Bou Arada", "Gaâfour", "Bargou"],
    "Sousse": ["Sousse Ville", "Akouda", "Hammam Sousse", "Kalaa Kebira", "Msaken"],
    "Tataouine": ["Tataouine Ville", "Bir Lahmar", "Ghomrassen", "Dehiba", "Smar"],
    "Tozeur": ["Tozeur Ville", "Nefta", "Degache", "Tameghza", "Hazoua"],
    "Tunis": ["Tunis Centre", "Le Bardo", "La Marsa", "Carthage", "Sidi Hassine"],
    "Zaghouan": ["Zaghouan Ville", "El Fahs", "Nadhour", "Bir Mcherga", "Saouaf"]
    };

   // Function to update the regions based on the selected country
function updateProvinces() {
    const pays = document.getElementById('pays').value;
    const regionSelect = document.getElementById('region');
    const villeSelect = document.getElementById('ville');

    // Clear previous options
    regionSelect.innerHTML = '';
    villeSelect.innerHTML = '';

    // Populate regions based on the selected country
    provinces[pays].forEach(region => {
        const option = document.createElement('option');
        option.value = region;
        option.textContent = region;
        regionSelect.appendChild(option);
    });

    // Trigger the city population function if the country is France or Tunisia
    if (pays) {
        updateCities(); // Populate cities once the region is chosen
    }
}

// Function to update the cities based on the selected region
function updateCities() {
    const region = document.getElementById('region').value;
    const villeSelect = document.getElementById('ville');

    // Clear previous city options
    villeSelect.innerHTML = '';

    if (region && cities[region]) {
        cities[region].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            villeSelect.appendChild(option);
        });
    }
}

// Attach event listeners to the pays and region fields
document.getElementById('pays').addEventListener('change', updateProvinces);
document.getElementById('region').addEventListener('change', updateCities);

</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeField = document.getElementById('type');
        const specialiteContainer = document.getElementById('specialite-container');
        const specialiteContainer1 = document.getElementById('specialite-container1');

        // Fonction pour afficher ou masquer la spécialité en fonction du type
        function toggleSpecialite() {
            console.log("Type sélectionné: ", typeField.value); // Débogage pour voir la valeur sélectionnée
            if (typeField.value === 'Docteur') {
                console.log("Affichage de la spécialité"); // Vérifie que la logique fonctionne
                specialiteContainer.style.display = 'block'; // Assurez-vous qu'il s'affiche
                specialiteContainer1.style.display = 'block'; // Assurez-vous qu'il s'affiche

            } else {
                specialiteContainer.style.display = 'none'; // Assurez-vous qu'il soit masqué
                specialiteContainer1.style.display = 'none'; // Assurez-vous qu'il soit masqué

                console.log("Masquage de la spécialité"); // Vérifie que la logique fonctionne

            }
        }

        // Initialiser l'état de l'affichage dès le chargement de la page
        toggleSpecialite();

        // Ajouter un écouteur d'événements pour réagir au changement de sélection du type
        typeField.addEventListener('change', toggleSpecialite);
    });

    function toggleTypeField() {
    const pays = document.getElementById('pays').value;
    const typeField = document.getElementById('type');
    if (pays) {
        typeField.disabled = false; // Activer le champ
    } else {
        typeField.disabled = true; // Désactiver le champ
        typeField.value = ""; // Réinitialiser le champ
    }
}

</script>
<script>
  function updateSpecialities() {
    const pays = document.getElementById('pays').value;
    const specialiteSelect = document.getElementById('specialite');
    
    // Effacer les anciennes options
    specialiteSelect.innerHTML = '<option value="" disabled selected>Sélectionnez une spécialité</option>';

    if (!pays) {
        return;
    }

    fetch(`/specialitiesByPays?pays=${pays}`)
    .then(response => response.text())  // Récupère la réponse sous forme de texte brut
    .then(text => {
        console.log("Réponse brute du serveur:", text);  // Affiche la réponse brute dans la console
        try {
            const data = JSON.parse(text);  // Essaie de parser la réponse en JSON
            console.log("Réponse JSON:", JSON.stringify(data, null, 2));  // Affiche l'objet sous forme de chaîne JSON bien formatée
            if (data.error) {
                console.error(data.error);
                return;
            }
            if (data.length === 0) {
                specialiteSelect.innerHTML = '<option value="" disabled>Aucune spécialité disponible pour ce pays</option>';
                return;
            }
            // Remplir le sélecteur de spécialités
            data.forEach(speciality => {
                const option = document.createElement('option');
                option.value = speciality.id; // ou autre champ représentant l'id
                option.textContent = speciality.name.fr; // Accède à la propriété 'fr' pour obtenir le nom de la spécialité en français
                specialiteSelect.appendChild(option);
            });

        } catch (error) {
            console.error("Erreur de parsing JSON:", error);
        }
    })
    .catch(error => {
        console.error('Erreur lors de la récupération des spécialités:', error);
    });

    }




</script>
     
<script>
// Attacher l'événement au champ "Pays"
document.getElementById('pays').addEventListener('change', updateSpecialities);

document.addEventListener("DOMContentLoaded", function() {
    var phoneInput = document.querySelector("#phone_number");
    var iti = window.intlTelInput(phoneInput, {
        preferredCountries: ["tn", "fr"], // Pays préférés
        separateDialCode: true, // Afficher le code pays séparé
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Utilitaires
    });

    // Quand le formulaire est soumis, remplir le champ phone_number
    document.getElementById("myForm").addEventListener("submit", function() {
        var phoneNumberWithCode = iti.getNumber(); // Par exemple: +21697799411
        phoneInput.value = phoneNumberWithCode; // Mettre à jour le champ avec le numéro formaté

        // Vous pouvez aussi ajouter un champ caché pour envoyer le code pays séparément si nécessaire
        var countryCode = iti.getSelectedCountryData().dialCode; // Récupérer le code pays
        document.getElementById('country_code').value = countryCode; // Ajouter le code pays au champ caché

        console.log('Numéro avec code pays:', phoneInput.value);
        console.log('Code pays:', countryCode);
    });
});
</script>
