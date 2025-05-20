<form action="{{ route('prescriptions.store') }}" method="POST"
    class="d-flex flex-column align-items-center col-12 col-md-6 mx-auto">
    @csrf
    {!! Form::hidden('consultation_id', $consultation_id) !!}

    <!-- Date Field -->
    <div class="form-group d-flex flex-row align-items-baseline">
        <div class="col-md-6">
            {!! Form::label('date', trans("lang.prescription_date"), ['class' => 'text-md-right']) !!}
            <span class="text-danger">*</span>

            <input type="date" name="date" id="date" required class="form-control w-100">
        </div>

        <div class="col-md-6">
            {!! Form::label('type', trans("lang.prescription_type"), ['class' => 'text-md-right']) !!}
            <span class="text-danger">*</span>

            <select name="type" id="type" required class="form-control w-100" onchange="toggleMedicamentFields()">
                <option value="">{{ trans('lang.prescription_type_select') }}</option>
                <option value="Médicament">{{ trans('lang.prescription_type_medicament') }}</option>
                <option value="Analyse">{{ trans('lang.prescription_type_analyse') }}</option>
                <option value="Radio">{{ trans('lang.prescription_type_radio') }}</option>
                <option value="Vaccin">{{ trans('lang.prescription_type_vaccin') }}</option>
                <option value="Autre">{{ trans('lang.prescription_type_autre') }}</option>
            </select>
        </div>
    </div>

    <!-- Analyse Fields -->
    <div id="analyse-fields" class="form-group" style="display: none;">
        <div class="form-row align-items-center analyse-row">
            <div class="col-md d-flex justify-content-between align-items-center">
                <div>
                    {!! Form::label('analyses[0][Code_Analyse]', trans("lang.prescription_analyse_code")) !!}
                    <span class="text-danger">*</span>

                    <select name="analyses[0][Code_Analyse]" required class="form-control" style="width: 280px;">
                        <option value="" disabled selected>{{ trans('lang.prescription_select_analyse') }}</option>
                        @foreach($analyses as $analyse)
                            <option value="{{ $analyse->Code_Analyse }}">{{ $analyse->Code_Analyse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ml-2 mt-4">
                    <!-- Delete Icon -->
                    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_analyse') }}" href="#"
                        onclick="deleteAnalyse(this)" class="btn btn-link p-1">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                    <!-- Add Icon -->
                    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_analyse') }}" href="#"
                        onclick="addAnalyse()" class="btn btn-link p-1">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- Radio Fields -->
    <div id="radio-fields" class="form-group" style="display: none;">
        <div class="form-row align-items-center radio-row">
            <div class="col-md d-flex justify-content-between align-items-center">
                <div>
                    {!! Form::label('radios[0][Nom]', trans("lang.prescription_radio_nom")) !!}
                    <span class="text-danger">*</span>

                    <select name="radios[0][Nom]" required class="form-control" style="width: 280px;">
                        <option value="" disabled selected>{{ trans('lang.prescription_select_radio') }}</option>
                        @foreach($radios as $radio)
                            <option value="{{ $radio->Nom }}">{{ $radio->Nom }}</option> <!-- Correction ici -->
                        @endforeach
                    </select>
                </div>
                <div class="ml-2 mt-4">
                    <!-- Delete Icon -->
                    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_radio') }}" href="#"
                        onclick="deleteRadio(this)" class="btn btn-link p-1">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                    <!-- Add Icon -->
                    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_radio') }}" href="#"
                        onclick="addRadio()" class="btn btn-link p-1">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div id="medicament-fields" class="form-group" style="display: none;">
        <div class="form-row align-items-center medicament-row">
            <div class="col-md">
                {!! Form::label('medicaments[0][CODE_PCT]', trans("lang.prescription_medicament_code")) !!}
                <span class="text-danger">*</span>

                <!-- Input gris qui ouvre la modal -->
                <input type="text" class="form-control bg-light" id="medicamentInput_0"
                    placeholder="{{ trans('lang.prescription_select_medicament') }}" readonly
                    onclick="openMedicamentModal(0)" required>

                <!-- Champ caché pour stocker la valeur réelle -->
                <input type="hidden" name="medicaments[0][CODE_PCT]" id="medicamentValue_0">

                <!-- Modal pour la sélection des médicaments -->
                <div class="modal fade" id="medicamentModal_0" tabindex="-1" role="dialog"
                    aria-labelledby="medicamentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="medicamentModalLabel">
                                    {{ trans('lang.prescription_select_medicament') }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Barre de recherche -->
                                <div class="form-group">
                                    <input type="text" class="form-control" id="medicamentSearch_0"
                                        placeholder="{{ trans('lang.search') }}...">
                                </div>
                                <!-- Champ pour ajout manuel de médicament -->
<div class="form-group mt-3">
    <label for="manualMedicament_0">{{ trans('lang.prescription_add_manual_medicament') }}</label>
    <div class="input-group">
        <input type="text" class="form-control" id="manualMedicament_0"
               placeholder="{{ trans('lang.prescription_enter_medicament_name') }}"
               value=""> <!-- Ajout explicite de value="" -->
               <div class="input-group-append">
    <button class="btn btn-primary" type="button" 
            onclick="addManualMedicament(0)">
        {{ trans('lang.add') }}
    </button>
</div>
    </div>
</div>

                                <!-- Liste des médicaments -->
                                <div class="list-group" id="medicamentList_0"
                                    style="max-height: 400px; overflow-y: auto;">
                                    @foreach($medicaments as $medicament)
                                                                    <a href="javascript:void(0)" class="list-group-item list-group-item-action"
                                                                        data-value="{{ $isFrance ? $medicament->name : $medicament->CODE_PCT }}"
                                                                        data-display="{{ $isFrance ? $medicament->name : "{$medicament->NOM_COMMERCIAL} - {$medicament->category} - {$medicament->format} - {$medicament->form}" }}"
                                                                        onclick="selectMedicament(0, this)">

                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                {!! 
                                                $isFrance
                                        ? "<strong>{$medicament->name}</strong>"
                                        : "<span style='color: black; font-size: 0.85em;'>" . ($medicament->drugClass?->dci_code ?? '') . ":</span>
                                                       <span style='color:#2E86C1; font-weight: bold;'> {$medicament->NOM_COMMERCIAL}</span> 
                                                       <span style='color: black;'> - {$medicament->category} - {$medicament->format} - {$medicament->form}</span>"
                                            !!}
                                                                            </div>

                                                                            <span class="badge rounded-pill text-white ms-2"
                                                                                style="background-color: #2E86C1;" aria-hidden="true">
                                                                                <i class="fas fa-coins me-1"></i>
                                                                                {{ $medicament->ttc_price == 0 ? 'Indisponible' : number_format($medicament->ttc_price, 2, ',', ' ') . ' DT' }}
                                                                            </span>

                                                                        </div>

                                                                    </a>



                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{ trans('lang.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




            <div class="col-md">
                {!! Form::label('medicaments[0][dosage]', trans("lang.prescription_medicament_dosage")) !!}
                <span class="text-danger">*</span>

                <input type="text" name="medicaments[0][dosage]" required class="form-control">
            </div>

            <div class="col-md">
                {!! Form::label('medicaments[0][nb_de_fois]', trans("lang.prescription_medicament_frequency")) !!}
                <span class="text-danger">*</span>

                <div class="input-group">
                    <input type="number" name="medicaments[0][nb_de_fois]" class="form-control" min="1" max="10"
                        required placeholder="{{ trans('lang.prescription_medicament_frequency') }}" />
                    <select name="medicaments[0][frequency_unit]" required class="form-control">
                        <option value="fois par jour">{{ trans('lang.prescription_medicament_per_day') }}</option>
                        <option value="fois par semaine">{{ trans('lang.prescription_medicament_per_week') }}</option>
                        <option value="fois par mois">{{ trans('lang.prescription_medicament_per_month') }}</option>
                        <option value="fois par an">{{ trans('lang.prescription_medicament_per_year') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md">
                {!! Form::label('medicaments[0][nb_de_jours]', trans("lang.prescription_medicament_duration")) !!}
                <span class="text-danger">*</span>

                <div class="input-group">
                    <input type="number" min="1" name="medicaments[0][nb_de_jours]" required class="form-control"
                        placeholder="{{ trans('lang.prescription_medicament_number') }}">
                    <select name="medicaments[0][duration_unit]" required class="form-control">
                        <option value="jours">{{ trans('lang.prescription_medicament_days') }}</option>
                        <option value="semaines">{{ trans('lang.prescription_medicament_weeks') }}</option>
                        <option value="mois">{{ trans('lang.prescription_medicament_months') }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md">
                {!! Form::label('medicaments[0][horaire]', trans("lang.prescription_medicament_schedule")) !!}

                <select name="medicaments[0][horaire]" required class="form-control">
                    <option value="" disabled selected>-- Choisir un horaire --</option>
                    <option value="avant repas">Avant repas</option>
                    <option value="après repas">Après repas</option>
                </select>
            </div>




            <!-- Delete Icon -->
            <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_medicament') }}" href="#"
                onclick="removeMedicament(this)" class="btn btn-link p-1 mt-4">
                <i class="fas fa-trash-alt"></i>
            </a>

            <!-- Add Icon -->
            <a id="add-medicament-button" data-toggle="tooltip" data-placement="left"
                title="{{ trans('lang.add_medicament') }}" href="#" onclick="addMedicament()"
                class="btn btn-link p-1 mt-4">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>

    <!-- Nom Traitement Field (to appear when prescription type is not medicament) -->
    <div id="nom-traitement-fields" class="form-group" style="display: none;">
        {!! Form::label('nom_traitement', trans("lang.prescription_nom_traitement"), ['class' => 'text-md-right']) !!}
        <span class="text-danger">*</span>

        <div class="input-group" id="nom-traitement-container">
            <div class="form-row-traitement d-flex align-items-center">
                <input type="text" name="nom_traitement[]" class="form-control"
                    placeholder="{{ trans('lang.prescription_nom_traitement_placeholder') }}">
                <div class="input-group-append">
                    <!-- Delete Icon -->
                    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_autre') }}" href="#"
                        onclick="removeTraitement(this)" class="btn btn-link p-1 ml-2">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                    <!-- Add Icon -->
                    <a id="add-traitement-button" data-toggle="tooltip" data-placement="left"
                        title="{{ trans('lang.add_autre') }}" href="#" onclick="addTraitement()"
                        class="btn btn-link p-1">
                        <i class="fas fa-plus"></i>
                    </a>

                </div>
            </div>
        </div>
    </div>



    <!-- Observation Field -->
    <div id="observation-field" class="form-group d-flex flex-column align-items-center" style="display: none;">
        {!! Form::label('observation', trans("lang.prescription_observation"), ['class' => 'text-md-right']) !!}
        <textarea name="observation" id="observation" class="form-control w-100"></textarea>
        <div class="form-text text-muted">{{ trans("lang.prescription_observation_help") }}</div>
    </div>

    <!-- Submit Field -->
    <div
        class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
        <button type="button" id="add-medicament-button" class="btn btn-primary mt-2" style="display: none;"
            onclick="addMedicament()">
            {{ trans('lang.prescription_add_medicament') }}
        </button>

        <!-- Submit Field -->
        <button type="button" id="submit-btn"
            class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
            <i class="fa fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.prescription') }}
        </button>
        <a href="{!! route('dashboard') !!}" class="btn btn-default"><i class="fa fa-undo"></i>
            {{ trans('lang.cancel') }}</a>

    </div>


</form>


<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Voulez-vous sauvegarder la préscription ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                <button type="button" class="btn bg-{{setting('theme_color')}}" id="confirmSave">Oui</button>
            </div>
        </div>
    </div>
</div>




@push('scripts_lib')
    <script typessssssss="text/javascript">
        document.getElementById('submit-btn').addEventListener('click', function () {
            $('#confirmationModal').modal('show'); // Show the modal
        });

        document.getElementById('confirmSave').addEventListener('click', function () {
            this.closest('form').submit(); // Submit the form if confirmed
        });

        // Automatically hide the modal after 5 seconds
        $('#confirmationModal').on('shown.bs.modal', function () {
            setTimeout(function () {
                $('#confirmationModal').modal('hide');
            }, 5000);
        });
    </script>
@endpush



<script>
    let medicamentCount = 1;
    let traitementCount = 1; // To keep track of the number of traitements
    let analyseCount = 1; // Initialiser le compteur des analyses
    let radioCount = 1; // Initialiser le compteur des radios

    // Fonction pour ajouter un champ d'analyse
    function addAnalyse() {
        // Cloner la ligne d'analyse
        let analyseRow = document.querySelector('.analyse-row').cloneNode(true);

        // Réinitialiser les valeurs des champs clonés
        analyseRow.querySelectorAll('input, select').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, `[${analyseCount}]`);
            input.value = ''; // Réinitialiser les valeurs
        });

        // Ajouter le nouveau champ d'analyse au conteneur
        document.getElementById('analyse-fields').appendChild(analyseRow);

        // Incrémenter le compteur d'analyse
        analyseCount++;

    }

    // Fonction pour supprimer un champ d'analyse
    function deleteAnalyse(button) {
        // Ne pas supprimer si c'est le seul champ restant
        if (document.querySelectorAll('.analyse-row').length > 1) {
            button.closest('.analyse-row').remove();
        } else {
            alert("Il doit y avoir au moins une analyse.");
        }
    }

    // Fonction pour ajouter un champ radio
    function addRadio() {
        // Cloner la ligne radio
        let radioRow = document.querySelector('.radio-row').cloneNode(true);

        // Réinitialiser les valeurs des champs clonés
        radioRow.querySelectorAll('input, select').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, `[${radioCount}]`);
            input.value = ''; // Réinitialiser les valeurs
        });

        // Ajouter le nouveau champ radio au conteneur
        document.getElementById('radio-fields').appendChild(radioRow);

        // Incrémenter le compteur radio
        radioCount++;

    }
    // Fonction pour supprimer un champ radio
    function deleteRadio(button) {
        // Ne pas supprimer si c'est le seul champ restant
        if (document.querySelectorAll('.radio-row').length > 1) {
            button.closest('.radio-row').remove();
        } else {
            alert("Il doit y avoir au moins un radio.");
        }
    }
    function addTraitement() {
        // Clone the first "form-row-traitement" and reset its values
        let newTraitement = document.querySelector('.form-row-traitement').cloneNode(true);

        // Clear the input value for the cloned row
        newTraitement.querySelector('input').value = '';

        // Optionally, if you need to update the name attribute for arrays (if applicable)
        newTraitement.querySelector('input').name = `nom_traitement[${traitementCount}]`;

        // Append the new field to the container
        document.getElementById('nom-traitement-container').appendChild(newTraitement);

        traitementCount++; // Increment the counter for future added treatments
    }

    document.getElementById('date').value = new Date().toISOString().split('T')[0];

    function removeTraitement(element) {
        // Vérifie s'il reste plus d'un champ de traitement
        if (document.querySelectorAll('.form-row-traitement').length > 1) {
            element.closest('.form-row-traitement').remove();
        } else {
            alert('Vous devez conserver au moins un traitement.');
        }
    }






    function removeMedicament(button) {
        // Only remove if there's more than one row
        if (document.querySelectorAll('.medicament-row').length > 1) {
            button.closest('.medicament-row').remove();
        } else {
            alert("Il doit y avoir au moins un médicament.");
        }
    }

    function toggleMedicamentFields() {
        const typeSelect = document.getElementById('type');
        const medicamentFields = document.getElementById('medicament-fields');
        const addMedicamentButton = document.getElementById('add-medicament-button');
        const addTraitementButton = document.getElementById('add-traitement-button');
        const analyseFields = document.getElementById('analyse-fields');
        const radioFields = document.getElementById('radio-fields');

        const observationField = document.getElementById('observation-field');
        const nomTraitementField = document.getElementById('nom-traitement-fields');

        // Show/hide fields based on prescription type
        if (typeSelect.value === 'Médicament') {
            medicamentFields.style.display = 'block';
            addMedicamentButton.style.display = 'block';
            addTraitementButton.style.display = 'none';
            observationField.style.display = 'none'; // Hide observation field
            analyseFields.style.display = 'none'; // Hide 'nom traitement' field
            radioFields.style.display = 'none'; // Hide 'nom traitement' field
            nomTraitementField.style.display = 'none'; // Hide 'nom traitement' field
        } else if (['Analyse'].includes(typeSelect.value)) {
            medicamentFields.style.display = 'none'; // Hide medicament fields
            addMedicamentButton.style.display = 'none'; // Hide add medicament button
            nomTraitementField.style.display = 'none'; // Show 'nom traitement' field
            addTraitementButton.style.display = 'none';
            analyseFields.style.display = 'block';
            radioFields.style.display = 'none'; // Hide 'nom traitement' field



        } else if (['Radio'].includes(typeSelect.value)) {
            medicamentFields.style.display = 'none'; // Hide medicament fields
            addMedicamentButton.style.display = 'none'; // Hide add medicament button
            nomTraitementField.style.display = 'none'; // Show 'nom traitement' field
            addTraitementButton.style.display = 'none';
            analyseFields.style.display = 'none';
            radioFields.style.display = 'block';



        }

        else if (['Vaccin', 'Autre'].includes(typeSelect.value)) {
            medicamentFields.style.display = 'none'; // Hide medicament fields
            addMedicamentButton.style.display = 'none'; // Hide add medicament button
            observationField.style.display = 'block'; // Show observation field
            nomTraitementField.style.display = 'block'; // Show 'nom traitement' field
            addTraitementButton.style.display = 'block';
            analyseFields.style.display = 'none'; // Hide 'nom traitement' field
            radioFields.style.display = 'none'; // Hide 'nom traitement' field



        } else {
            medicamentFields.style.display = 'none'; // Hide medicament fields
            addMedicamentButton.style.display = 'none'; // Hide add medicament button
            observationField.style.display = 'none'; // Hide observation field
            nomTraitementField.style.display = 'none'; // Hide 'nom traitement' field
            addTraitementButton.style.display = 'none';
            analyseFields.style.display = 'none'; // Hide 'nom traitement' field
            radioFields.style.display = 'none'; // Hide 'nom traitement' field



        }
    }



    function showConfirmationModal() {
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'), {});
        modal.show();
    }

    function submitForm() {
        document.querySelector('form').submit();
    }







    // Variable globale pour suivre les médicaments
    if (typeof medicamentCount === 'undefined') {
        window.medicamentCount = 1; // Commencer à 1 car 0 est le premier
    }

    // Fonction principale pour configurer la recherche de médicaments
    function setupMedicamentSearch() {
    // Utiliser la délégation d'événements pour gérer les champs de recherche dynamiques
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id && e.target.id.startsWith('medicamentSearch_')) {
            filterMedicamentList(e.target);
        }
    });
}

    // Fonction dédiée au filtrage pour une meilleure réutilisation
    function filterMedicamentList(searchField) {
    const index = searchField.id.split('_')[1];
    const searchText = searchField.value.toLowerCase().trim();
    console.log(`Filtering modal ${index} with text: "${searchText}"`);

    const listContainer = document.getElementById(`medicamentList_${index}`);
    if (!listContainer) return;

    // Réinitialiser d'abord l'affichage de tous les éléments
    listContainer.querySelectorAll('a').forEach(item => {
        item.style.display = '';
    });

    // Appliquer le filtre seulement si du texte est entré
    if (searchText.length > 0) {
        listContainer.querySelectorAll('a').forEach(item => {
            const text = item.textContent.toLowerCase();
            if (!text.includes(searchText)) {
                item.style.display = 'none';
            }
        });
    }

    console.log(`Items visible after filtering: ${listContainer.querySelectorAll('a:not([style*="display: none"])').length}`);
}

    // Fonction pour réinitialiser le filtrage lors de l'ouverture d'une modale
    function resetMedicamentFilter(index) {
        const searchField = document.getElementById(`medicamentSearch_${index}`);
        if (searchField) {
            searchField.value = '';
            filterMedicamentList(searchField);
        }
    }

    // Fonction pour ouvrir le modal avec gestion améliorée des événements
    function openMedicamentModal(index) {
    console.log(`Opening modal for index ${index} (vérifié)`);
    
    // Vérification supplémentaire
    const clickedElement = document.getElementById(`medicamentInput_${index}`);
    if (!clickedElement) {
        console.error(`Élément medicamentInput_${index} non trouvé!`);
        return;
    }
    
    let modal = document.getElementById(`medicamentModal_${index}`);

    if (!modal) {
        console.log(`Creating new modal for index ${index}`);
        let originalModal = document.getElementById('medicamentModal_0');
        if (originalModal) {
            // Cloner profondément le modal original
            modal = originalModal.cloneNode(true);
            modal.id = `medicamentModal_${index}`;

            // Mise à jour des IDs dans le modal cloné
            modal.querySelectorAll('[id]').forEach(el => {
                if (el.id.includes('_0')) {
                    el.id = el.id.replace('_0', `_${index}`);
                }
            });

            // Mise à jour des attributs onclick
            modal.querySelectorAll('[onclick]').forEach(el => {
                const onclick = el.getAttribute('onclick');
                if (onclick) {
                    if (onclick.includes('addManualMedicament(0)')) {
                        el.setAttribute('onclick', onclick.replace('addManualMedicament(0)', `addManualMedicament(${index})`));
                    }
                    if (onclick.includes('selectMedicament(0,')) {
                        el.setAttribute('onclick', onclick.replace('selectMedicament(0,', `selectMedicament(${index},`));
                    }
                }
            });

            // Ajouter le nouveau modal au document
            document.body.appendChild(modal);
            
            // Initialiser la recherche pour ce nouveau modal
            setupMedicamentSearch();
        }
    }

    // Afficher le modal
    $(`#medicamentModal_${index}`).modal('show');

    // Réinitialiser la recherche
    setTimeout(() => {
        const searchField = document.getElementById(`medicamentSearch_${index}`);
        if (searchField) {
            searchField.value = '';
            searchField.focus();
        }
        
        // Réinitialiser le champ manuel
        const manualInput = document.getElementById(`manualMedicament_${index}`);
        if (manualInput) {
            manualInput.value = '';
        }
    }, 100);
}
    // Fonction révisée pour ajouter un médicament
    function addMedicament() {
    console.log(`Adding new medicament row with index ${medicamentCount}`);
    
    // Cloner la première ligne de médicament
    let newMedicament = document.querySelector('.medicament-row').cloneNode(true);
    
    // Mettre à jour tous les IDs et names
    newMedicament.querySelectorAll('[id], [name], [onclick]').forEach(element => {
        // Mettre à jour l'ID
        if (element.id) {
            element.id = element.id.replace(/_0(_|$)/, `_${medicamentCount}$1`);
        }
        
        // Mettre à jour le name
        if (element.name) {
            element.name = element.name.replace(/\[\d+\]/, `[${medicamentCount}]`);
        }
        
        // Mettre à jour les onclick
        if (element.onclick) {
            const onclickStr = element.getAttribute('onclick').toString();
            if (onclickStr.includes('openMedicamentModal(0)')) {
                element.setAttribute('onclick', onclickStr.replace('openMedicamentModal(0)', `openMedicamentModal(${medicamentCount})`));
            }
            if (onclickStr.includes('addManualMedicament(0)')) {
                element.setAttribute('onclick', onclickStr.replace('addManualMedicament(0)', `addManualMedicament(${medicamentCount})`));
            }
        }
        
        // Réinitialiser les valeurs
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0;
        } else if (element.type !== 'button' && !element.classList.contains('no-reset')) {
            element.value = '';
        }
    });
    
    // Supprimer l'ancien modal s'il existe dans la ligne clonée
    const oldModal = newMedicament.querySelector('.modal');
    if (oldModal) {
        oldModal.remove();
    }
    
    // Ajouter la nouvelle ligne au conteneur
    document.getElementById('medicament-fields').appendChild(newMedicament);
    
    // Incrémenter le compteur
    medicamentCount++;
}
    // Fonction révisée pour sélectionner un médicament
    function selectMedicament(index, element) {
        // Obtenir les données de l'élément cliqué
        const value = element.getAttribute('data-value');
        const displayText = element.getAttribute('data-display');

        // Trouver et mettre à jour les champs d'entrée
        const inputField = document.getElementById(`medicamentInput_${index}`);
        const valueField = document.getElementById(`medicamentValue_${index}`);

        if (inputField) inputField.value = displayText;
        if (valueField) valueField.value = value;

        // Fermer le modal
        $(`#medicamentModal_${index}`).modal('hide');
    }

    // Initialisation du document
    // Initialisation du document
document.addEventListener('DOMContentLoaded', function() {
    console.log("Document loaded, setting up medicament search");

    // Configuration initiale des recherches
    setupMedicamentSearch();

    // Écouteur pour les modales qui s'ouvrent
    $(document).on('shown.bs.modal', function(e) {
        const modalId = e.target.id;
        if (modalId && modalId.startsWith('medicamentModal_')) {
            const index = modalId.split('_')[1];
            console.log(`Modal ${modalId} shown, resetting filter for index ${index}`);
            resetMedicamentFilter(index);
        }
    });
});
    // Fonction pour ajouter un médicament manuellement
    function addManualMedicament(index) {
    const manualInput = document.getElementById(`manualMedicament_${index}`);
    if (!manualInput) {
        console.error(`Element manualMedicament_${index} not found`);
        return;
    }
    
    const medicamentName = manualInput.value.trim();
    console.log(`Adding manual medicament for index ${index}:`, medicamentName);
    
    if (!medicamentName) {
        alert("Veuillez entrer un nom de médicament");
        return;
    }
    
    // Mettre à jour les champs
    const inputField = document.getElementById(`medicamentInput_${index}`);
    const valueField = document.getElementById(`medicamentValue_${index}`);
    
    if (inputField) inputField.value = medicamentName;
    if (valueField) valueField.value = 'manual_' + medicamentName;
    
    // Fermer le modal et réinitialiser
    $(`#medicamentModal_${index}`).modal('hide');
    manualInput.value = '';
}
</script>


<style>
    .bg-light {
        background-color: #f8f9fa !important;
        cursor: pointer;
    }
</style>