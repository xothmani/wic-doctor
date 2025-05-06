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


    <!-- Medicament Fields -->
    <div id="medicament-fields" class="form-group" style="display: none;">
        <div class="col-md-12 mb-3">
            <div class="border p-3 rounded">
                <!-- Titre du champ -->
                <div class="mb-3">
                    <span class="badge bg-danger ms-2">Innover Ensemble</span> <!-- Badge New -->
                    <p>Ces medicaments sont analysés par notre intelligence artificielle offrant une restitution
                        précise
                        et détaillée de la compatibilité des medicaments avec le patient. <b> Pour activer ou
                            desactiver l'aide
                            à la prescription, cliquer sur le bouton ci-dessous </b></p>
                </div>

                <!-- Switch -->
                <div class="d-flex flex-column">
                    <div class="form-row ">
                        <div class="col-md-9 d-flex flex-column align-items-start">
                            <div class="d-flex align-items-center">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="aiHelpToggle">
                                    <span class="slider"></span>
                                </label>
                                <span class="switch-label">Activer l'aide à la prescription</span>
                            </div>
                        </div>
                        <!-- Hints Field -->
                        <!-- <div class="flex flex-row" style="display:flex">
                            <div class="d-flex align-items-center" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #28a745;"></span>
                                <span class="ms-1">Comp&nbsp;</span>
                            </div>
                            <div class="d-flex align-items-center me-4" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #F5DF4D;"></span>
                                <span class="ms-1">Warning&nbsp;</span>
                            </div>
                            <div class="d-flex align-items-center me-4" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #F38071;"></span>
                                <span class="ms-1">Danger&nbsp;</span>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row align-items-center medicament-row mt-3">
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
                                    {{ trans('lang.prescription_select_medicament') }}
                                </h5>
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
    // Global counters
    let medicamentCount = 1;
    let traitementCount = 1;
    let analyseCount = 1;
    let radioCount = 1;

    let isAIActive = false;

    // Analysis state tracking
    let analysisDebounceTimer;
    let lastAnalysisRequest = null;
    let isAnalysisInProgress = false;

    // Wait for document to be fully loaded
    document.addEventListener('DOMContentLoaded', function () {
        console.log("Document fully loaded - initializing system");

        // Set default date to today
        if (document.getElementById('date')) {
            document.getElementById('date').value = new Date().toISOString().split('T')[0];
        }

        // Setup type selector change handler
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleMedicamentFields);
            // Trigger initial state
            toggleMedicamentFields();
        }

        // Set up AI toggle listener
        const aiToggle = document.getElementById('aiHelpToggle');
        if (aiToggle) {
            aiToggle.addEventListener('change', handleAIToggleChange);
            handleAIToggleChange.call(aiToggle); // Initial state setup
        }

        // Initialize compatibility analyzer
        initializeCompatibilityAnalyzer();

        // Setup initial medicament search functionality
        setupMedicamentSearch();
    });

    // SECTION: TOGGLE VISIBILITY OF DIFFERENT FORM SECTIONS

    function toggleMedicamentFields() {
        console.log("Toggling form fields based on type selection");
        const typeSelect = document.getElementById('type');
        if (!typeSelect) return;

        const medicamentFields = document.getElementById('medicament-fields');
        const addMedicamentButton = document.getElementById('add-medicament-button');
        const addTraitementButton = document.getElementById('add-traitement-button');
        const analyseFields = document.getElementById('analyse-fields');
        const radioFields = document.getElementById('radio-fields');
        const observationField = document.getElementById('observation-field');
        const nomTraitementField = document.getElementById('nom-traitement-fields');

        // Hide all sections first
        if (medicamentFields) medicamentFields.style.display = 'none';
        if (addMedicamentButton) addMedicamentButton.style.display = 'none';
        if (addTraitementButton) addTraitementButton.style.display = 'none';
        if (analyseFields) analyseFields.style.display = 'none';
        if (radioFields) radioFields.style.display = 'none';
        if (observationField) observationField.style.display = 'none';
        if (nomTraitementField) nomTraitementField.style.display = 'none';

        // Show relevant sections based on selected type
        switch (typeSelect.value) {
            case 'Médicament':
                if (medicamentFields) medicamentFields.style.display = 'block';
                if (addMedicamentButton) addMedicamentButton.style.display = 'block';
                // Initialize compatibility analysis if it's a medication
                setTimeout(triggerCompatibilityAnalysis, 500);
                showCompatibilityWidget();
                break;

            case 'Analyse':
                if (analyseFields) analyseFields.style.display = 'block';
                hideCompatibilityWidget();
                hideCompatibilityResult();
                break;

            case 'Radio':
                if (radioFields) radioFields.style.display = 'block';
                hideCompatibilityWidget();
                hideCompatibilityResult();
                break;

            case 'Vaccin':
            case 'Autre':
                if (observationField) observationField.style.display = 'block';
                if (nomTraitementField) nomTraitementField.style.display = 'block';
                if (addTraitementButton) addTraitementButton.style.display = 'block';
                hideCompatibilityWidget();
                hideCompatibilityResult();
                break;

            default:
                hideCompatibilityWidget();
                hideCompatibilityResult();
                break;
        }
    }

    // SECTION: MEDICAMENT MANAGEMENT

    function addMedicament() {
        console.log(`Adding new medicament row with index ${medicamentCount}`);

        // Clone the first medicament row
        const templateRow = document.querySelector('.medicament-row');
        if (!templateRow) {
            console.error("Cannot find template medicament row to clone");
            return;
        }

        const newRow = templateRow.cloneNode(true);

        // Update all field names, ids and reset values
        newRow.querySelectorAll('input, select').forEach(element => {
            if (element.name) {
                element.name = element.name.replace(/\[\d+\]/, `[${medicamentCount}]`);
            }

            if (element.id) {
                element.id = element.id.replace(/\_\d+/, `_${medicamentCount}`);
            }

            // Reset values except for special fields
            if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            } else if (!element.classList.contains('no-reset')) {
                element.value = '';
            }
        });

        // Update medicament input specifically
        const medicamentInput = newRow.querySelector('[id^="medicamentInput_"]');
        if (medicamentInput) {
            medicamentInput.id = `medicamentInput_${medicamentCount}`;
            medicamentInput.value = '';
            medicamentInput.setAttribute('onclick', `openMedicamentModal(${medicamentCount})`);
        }

        // Update hidden input
        const hiddenInput = newRow.querySelector('[name^="medicaments"][name$="[CODE_PCT]"]');
        if (hiddenInput) {
            hiddenInput.id = `medicamentValue_${medicamentCount}`;
            hiddenInput.name = `medicaments[${medicamentCount}][CODE_PCT]`;
            hiddenInput.value = '';
        }

        // Remove any modals in the cloned row
        const oldModal = newRow.querySelector('.modal');
        if (oldModal) oldModal.remove();

        // Update onclick attributes
        newRow.querySelectorAll('[onclick]').forEach(element => {
            const onclickAttr = element.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('openMedicamentModal')) {
                element.setAttribute('onclick', `openMedicamentModal(${medicamentCount})`);
            }
            if (onclickAttr && onclickAttr.includes('removeMedicament')) {
                element.setAttribute('onclick', `removeMedicament(this)`);
            }
        });

        // Add the new row to the container
        const container = document.getElementById('medicament-fields');
        if (container) {
            container.appendChild(newRow);

            // Setup event listeners for the new row
            attachEventListenersToMedicamentRow(newRow, medicamentCount);

            // Increment counter for the next addition
            medicamentCount++;

            // Trigger compatibility analysis
            debounceAnalysisRequest();
        } else {
            console.error("Cannot find medicament-fields container");
        }
    }

    function removeMedicament(button) {
        const medicamentRows = document.querySelectorAll('.medicament-row');
        if (medicamentRows.length > 1) {
            const row = button.closest('.medicament-row');
            if (row) {
                row.remove();
                debounceAnalysisRequest();
            }
        } else {
            alert("Il doit y avoir au moins un médicament.");
        }
    }

    // SECTION: MEDICAMENT SEARCH AND SELECTION

    function setupMedicamentSearch() {
        console.log("Setting up medicament search");

        // Set up search for the initial medicament field
        document.querySelectorAll('[id^=medicamentSearch_]').forEach(searchField => {
            // Remove existing listeners and recreate
            const newSearchField = searchField.cloneNode(true);
            if (searchField.parentNode) {
                searchField.parentNode.replaceChild(newSearchField, searchField);

                newSearchField.addEventListener('input', function () {
                    filterMedicamentList(this);
                });
            }
        });
    }

    function filterMedicamentList(searchField) {
        if (!searchField || !searchField.id) return;

        const index = searchField.id.split('_')[1];
        const searchText = searchField.value.toLowerCase().trim();
        console.log(`Filtering modal ${index} with text: "${searchText}"`);

        const listItems = document.querySelectorAll(`#medicamentList_${index} a`);
        listItems.forEach(item => {
            // Reset visibility first
            item.style.display = '';

            // Apply filter if there's search text
            if (searchText.length > 0) {
                const itemText = item.textContent.toLowerCase();
                if (!itemText.includes(searchText)) {
                    item.style.display = 'none';
                }
            }
        });
    }

    function openMedicamentModal(index) {
        console.log(`Opening modal for index ${index}`);
        let modal = document.getElementById(`medicamentModal_${index}`);

        if (!modal) {
            console.log(`Creating new modal for index ${index}`);
            const originalModal = document.getElementById('medicamentModal_0');
            if (originalModal) {
                // Clone the original modal
                modal = originalModal.cloneNode(true);
                modal.id = `medicamentModal_${index}`;

                // Update IDs in the cloned modal
                modal.querySelectorAll('[id]').forEach(el => {
                    if (el.id.includes('_0')) {
                        el.id = el.id.replace('_0', `_${index}`);
                    }
                });

                // Update onclick of list items
                modal.querySelectorAll('.list-group-item').forEach(item => {
                    item.setAttribute('onclick', `selectMedicament(${index}, this)`);
                });

                // Add modal to document
                document.body.appendChild(modal);
            } else {
                console.error("Original modal not found");
                return;
            }
        }

        // Show the modal using Bootstrap
        try {
            $(`#medicamentModal_${index}`).modal('show');

            // Setup search field after showing modal
            setTimeout(() => {
                const searchField = document.getElementById(`medicamentSearch_${index}`);
                if (searchField) {
                    // Reset and setup the search field
                    searchField.value = '';

                    // Remove old events and add new one
                    const newSearchField = searchField.cloneNode(true);
                    searchField.parentNode.replaceChild(newSearchField, searchField);

                    newSearchField.addEventListener('input', function () {
                        filterMedicamentList(this);
                    });

                    // Focus on search field
                    newSearchField.focus();

                    // Show all items initially
                    document.querySelectorAll(`#medicamentList_${index} a`).forEach(item => {
                        item.style.display = '';
                    });
                }
            }, 100);
        } catch (error) {
            console.error("Error showing modal:", error);
            alert("Une erreur est survenue lors de l'ouverture de la fenêtre de sélection de médicament.");
        }
    }

    function selectMedicament(index, element) {
        if (!element) return;

        // Get data from the clicked element
        const value = element.getAttribute('data-value');
        const displayText = element.getAttribute('data-display');

        if (!value || !displayText) {
            console.error("Missing data attributes on medicament list item");
            return;
        }

        // Update input and hidden fields
        const inputField = document.getElementById(`medicamentInput_${index}`);
        const valueField = document.getElementById(`medicamentValue_${index}`);

        if (inputField) inputField.value = displayText;
        if (valueField) valueField.value = value;

        // Close the modal
        try {
            $(`#medicamentModal_${index}`).modal('hide');

            // Trigger compatibility analysis after selection
            debounceAnalysisRequest();
        } catch (error) {
            console.error("Error hiding modal:", error);
        }
    }

    // SECTION: ANALYSES MANAGEMENT

    function addAnalyse() {
        const templateRow = document.querySelector('.analyse-row');
        if (!templateRow) return;

        const newRow = templateRow.cloneNode(true);

        // Update field names and reset values
        newRow.querySelectorAll('input, select').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${analyseCount}]`);
            input.value = '';
        });

        // Update delete button
        const deleteButton = newRow.querySelector('a[onclick*="deleteAnalyse"]');
        if (deleteButton) {
            deleteButton.setAttribute('onclick', 'deleteAnalyse(this)');
        }

        // Add to container
        const container = document.getElementById('analyse-fields');
        if (container) {
            container.appendChild(newRow);
            analyseCount++;
        }
    }

    function deleteAnalyse(button) {
        const analyseRows = document.querySelectorAll('.analyse-row');
        if (analyseRows.length > 1) {
            const row = button.closest('.analyse-row');
            if (row) row.remove();
        } else {
            alert("Il doit y avoir au moins une analyse.");
        }
    }

    // SECTION: RADIOS MANAGEMENT

    function addRadio() {
        const templateRow = document.querySelector('.radio-row');
        if (!templateRow) return;

        const newRow = templateRow.cloneNode(true);

        // Update field names and reset values
        newRow.querySelectorAll('input, select').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${radioCount}]`);
            input.value = '';
        });

        // Update delete button
        const deleteButton = newRow.querySelector('a[onclick*="deleteRadio"]');
        if (deleteButton) {
            deleteButton.setAttribute('onclick', 'deleteRadio(this)');
        }

        // Add to container
        const container = document.getElementById('radio-fields');
        if (container) {
            container.appendChild(newRow);
            radioCount++;
        }
    }

    function deleteRadio(button) {
        const radioRows = document.querySelectorAll('.radio-row');
        if (radioRows.length > 1) {
            const row = button.closest('.radio-row');
            if (row) row.remove();
        } else {
            alert("Il doit y avoir au moins un radio.");
        }
    }

    // SECTION: TRAITEMENT MANAGEMENT

    function addTraitement() {
        const templateRow = document.querySelector('.form-row-traitement');
        if (!templateRow) return;

        const newRow = templateRow.cloneNode(true);

        // Update input and reset value
        const input = newRow.querySelector('input');
        if (input) {
            input.name = `nom_traitement[${traitementCount}]`;
            input.value = '';
        }

        // Add to container
        const container = document.getElementById('nom-traitement-container');
        if (container) {
            container.appendChild(newRow);
            traitementCount++;
        }
    }

    function removeTraitement(element) {
        const traitementRows = document.querySelectorAll('.form-row-traitement');
        if (traitementRows.length > 1) {
            const row = element.closest('.form-row-traitement');
            if (row) row.remove();
        } else {
            alert('Vous devez conserver au moins un traitement.');
        }
    }

    // SECTION: CONFIRMATION MODAL

    function showConfirmationModal() {
        try {
            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            modal.show();
        } catch (error) {
            console.error("Error showing confirmation modal:", error);

            // Fallback for jQuery-based Bootstrap
            try {
                $('#confirmationModal').modal('show');
            } catch (jqError) {
                console.error("jQuery fallback also failed:", jqError);
                alert("Voulez-vous confirmer cette prescription?");
            }
        }
    }

    function submitForm() {
        const form = document.querySelector('form');
        if (form) form.submit();
    }


    // SECTION: AI toggle

    // Function to handle AI toggle change
    function handleAIToggleChange() {
        aiAnalyzerActive = this.checked;
        console.log("AI analyzer active:", aiAnalyzerActive);

        const resultContainer = document.getElementById('compatibility-result');
        if (!resultContainer) return;

        isAIActive = aiAnalyzerActive;

        // Display result if AI is active
        if (aiAnalyzerActive) {
            resultContainer.style.display = 'block';
        } else {
            resultContainer.style.display = 'none';
        }
    }

    // SECTION: COMPATIBILITY ANALYZER

    function initializeCompatibilityAnalyzer() {
        console.log("Initializing compatibility analyzer...");

        // Create widget container if it doesn't exist
        if (!document.getElementById('compatibility-widget')) {
            const formContainer = document.querySelector('form');
            if (formContainer) {
                // Create compatibility widget
                const compatibilityWidget = document.createElement('div');
                compatibilityWidget.id = 'compatibility-widget';
                compatibilityWidget.className = 'compatibility-widget';
                compatibilityWidget.style.display = 'none';

                // Create result container
                const resultContainer = document.createElement('div');
                resultContainer.id = 'compatibility-result';
                resultContainer.className = 'compatibility-result';
                resultContainer.style.display = 'none';

                // Insert at the beginning of the form
                const firstElement = formContainer.querySelector('div');
                formContainer.insertBefore(compatibilityWidget, firstElement);
                formContainer.insertBefore(resultContainer, firstElement.nextSibling);

                // Initialize with empty state
                updateCompatibilityWidget(null);

                console.log("Compatibility widget added to form");
            }
        }

        // Add compatibility styles
        addCompatibilityStyles();

        // Set up observers for medicament fields
        observeMedicamentFields();

        // Check initial type
        const typeSelect = document.getElementById('type');
        if (typeSelect && typeSelect.value === 'Médicament') {
            showCompatibilityWidget();
            setTimeout(triggerCompatibilityAnalysis, 500);
        }
    }

    function observeMedicamentFields() {
        const medicamentFields = document.getElementById('medicament-fields');
        if (!medicamentFields) {
            console.error("Medicament fields container not found!");
            return;
        }

        console.log("Setting up observer for medicament fields");

        // Create observer for added rows
        const observer = new MutationObserver(function (mutations) {
            for (const mutation of mutations) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    console.log("New medicament row detected by observer");

                    // We'll handle event attachment elsewhere to avoid duplication
                    setTimeout(triggerCompatibilityAnalysis, 300);
                }
            }
        });

        // Start observing
        observer.observe(medicamentFields, { childList: true, subtree: false });
    }

    function attachEventListenersToMedicamentRow(row, rowIndex) {
        console.log(`Attaching event listeners to medicament row ${rowIndex}`);

        // Attach listeners to inputs and selects
        row.querySelectorAll('input, select').forEach(input => {
            // Skip hidden inputs
            if (input.type === 'hidden' && !input.id?.includes('medicamentValue_')) return;

            // Add change event
            input.addEventListener('change', function () {
                console.log(`Input changed in row ${rowIndex}:`, this.name);
                debounceAnalysisRequest();
            });

            // For text inputs, add input event too
            if (input.type === 'text' || input.type === 'number') {
                input.addEventListener('input', function () {
                    debounceAnalysisRequest();
                });
            }
        });

        // Special handling for medicament selection field
        const medicamentInput = row.querySelector(`#medicamentInput_${rowIndex}`);
        if (medicamentInput) {
            // Create a MutationObserver for value changes
            const observer = new MutationObserver(function () {
                debounceAnalysisRequest();
            });

            observer.observe(medicamentInput, {
                attributes: true,
                attributeFilter: ['value']
            });
        }

        // Handle delete button
        const deleteButton = row.querySelector('a[onclick*="removeMedicament"]');
        if (deleteButton) {
            deleteButton.addEventListener('click', function () {
                setTimeout(triggerCompatibilityAnalysis, 300);
            });
        }
    }

    function collectAllMedicaments() {
        const medicaments = [];
        const medicamentRows = document.querySelectorAll('.medicament-row');

        medicamentRows.forEach((row, index) => {
            const medicamentValueField = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
            if (medicamentValueField && medicamentValueField.value) {
                const medicamentData = extractMedicamentData(row);
                if (medicamentData) {
                    medicaments.push(medicamentData);
                }
            }
        });

        return medicaments;
    }

    function extractMedicamentData(row) {
        const medicamentValueField = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
        if (!medicamentValueField || !medicamentValueField.value) return null;

        const medicamentDisplayField = row.querySelector('input[id^="medicamentInput_"]');
        const medicamentName = medicamentDisplayField ? medicamentDisplayField.value : 'Unknown Medication';

        const dosageField = row.querySelector('input[name^="medicaments"][name$="[dosage]"]');
        const dosage = dosageField ? dosageField.value : '';

        const frequencyField = row.querySelector('input[name^="medicaments"][name$="[nb_de_fois]"]');
        const frequencyValue = frequencyField ? frequencyField.value : '';

        const frequencyUnitField = row.querySelector('select[name^="medicaments"][name$="[frequency_unit]"]');
        const frequencyUnit = frequencyUnitField ? frequencyUnitField.value : '';

        const durationField = row.querySelector('input[name^="medicaments"][name$="[nb_de_jours]"]');
        const durationValue = durationField ? durationField.value : '';

        const durationUnitField = row.querySelector('select[name^="medicaments"][name$="[duration_unit]"]');
        const durationUnit = durationUnitField ? durationUnitField.value : '';

        const scheduleField = row.querySelector('select[name^="medicaments"][name$="[horaire]"]');
        const schedule = scheduleField ? scheduleField.value : '';

        return {
            medicationId: medicamentValueField.value,
            medicationName: medicamentName,
            dosage: dosage,
            frequency: `${frequencyValue} ${frequencyUnit}`,
            duration: `${durationValue} ${durationUnit}`,
            schedule: schedule
        };
    }

    function debounceAnalysisRequest() {
        console.log("Debouncing analysis request");
        clearTimeout(analysisDebounceTimer);
        analysisDebounceTimer = setTimeout(triggerCompatibilityAnalysis, 800);
    }

    function triggerCompatibilityAnalysis() {
        console.log("Triggering compatibility analysis");

        // Check if analysis is enabled
        if (!isAIActive) {
            console.log("Analysis is disabled, skipping analysis");
            hideCompatibilityWidget();
            hideCompatibilityResult();
            return;
        }

        // Check if on medications tab
        const typeSelect = document.getElementById('type');
        if (!typeSelect || typeSelect.value !== 'Médicament') {
            console.log("Not a medication prescription, skipping analysis");
            hideCompatibilityWidget();
            hideCompatibilityResult();
            return;
        }

        // Collect medicaments
        const medicaments = collectAllMedicaments();
        console.log("Collected medicaments:", medicaments);

        // Only analyze if we have medicaments
        if (medicaments.length >= 1) {
            showCompatibilityWidget();
            analyzeCompatibility(medicaments);
        } else {
            updateCompatibilityWidget({
                status: "waiting",
                message: "Veuillez ajouter des médicaments pour l'analyse",
                details: "Une fois que vous aurez ajouté au moins un médicament avec un code valide, nous pourrons analyser la compatibilité."
            });
            hideCompatibilityResult();
        }
    }

    async function analyzeCompatibility(medicaments) {
        if (isAnalysisInProgress) {
            console.log("Analysis already in progress, queuing request");
            lastAnalysisRequest = medicaments;
            return;
        }

        isAnalysisInProgress = true;
        updateCompatibilityWidget({ status: 'loading' });

        try {
            const requestId = Date.now().toString();
            lastAnalysisRequest = requestId;

            // Get consultation ID if available
            let consultation_id = "unknown";
            const consultationIdField = document.querySelector('input[name="consultation_id"]');
            if (consultationIdField && consultationIdField.value) {
                consultation_id = consultationIdField.value;
            }

            // Prepare API payload
            const payload = {
                consultation_id: consultation_id,
                medications: medicaments.map(med => ({
                    drug_id: med.medicationId,
                    data: {
                        dosage: med.dosage,
                        frequence: med.frequency,
                        duration: med.duration,
                        time: med.schedule
                    }
                }))
            };

            console.log("Sending API request with payload:", payload);

            try {
                // Make API request
                const response = await fetch('https://wicdialer.com/api/analyze/compatibility/', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                console.log("API response status:", response.status);

                // Check if this is still the most recent request
                if (lastAnalysisRequest !== requestId) {
                    console.log("A newer request has been made, discarding results");
                    isAnalysisInProgress = false;

                    // Process newer request if waiting
                    if (typeof lastAnalysisRequest === 'object') {
                        const pendingMedicaments = lastAnalysisRequest;
                        lastAnalysisRequest = null;
                        analyzeCompatibility(pendingMedicaments);
                    }
                    return;
                }

                // Parse response
                let result;
                try {
                    result = await response.json();
                    console.log("API response data:", result);
                } catch (e) {
                    console.error("Failed to parse API response:", e);

                    // Use mock data for error
                    result = {
                        compatibility: {
                            rating: 7,
                            status: "minor",
                            message: "Interactions mineures détectées entre les médicaments",
                            details: "Cette analyse est basée sur des données simulées car l'API n'a pas répondu correctement."
                        }
                    };
                }

                // Process result
                let analysisResult;
                try {
                    analysisResult = typeof result.response === 'string'
                        ? JSON.parse(result.response)
                        : result.response || result.compatibility;

                    // Fallback for unexpected format
                    if (!analysisResult) {
                        analysisResult = {
                            rating: result.rating || 5,
                            status: result.status || "unknown",
                            message: result.message || "Analyse de compatibilité terminée",
                            details: result.details || "Aucun détail spécifique disponible"
                        };
                    }
                } catch (e) {
                    console.error("Error processing compatibility results:", e);

                    // Handle parse error
                    analysisResult = {
                        rating: result.rating || 5,
                        status: "unknown",
                        message: "Impossible de traiter les résultats de compatibilité",
                        details: "Le système a renvoyé un format de réponse inattendu"
                    };
                }

                // Update UI
                updateCompatibilityWidget(analysisResult);
                showCompatibilityResult(analysisResult);

            } catch (networkError) {
                console.error("Network error during API call:", networkError);

                // Use mock data for network error
                const mockResult = {
                    rating: 7,
                    status: "minor",
                    message: "Interactions mineures détectées (données simulées)",
                    details: "Nous n'avons pas pu contacter le serveur d'analyse. Cette évaluation est simulée à des fins de test uniquement."
                };

                updateCompatibilityWidget(mockResult);
                showCompatibilityResult(mockResult);
            }

        } catch (error) {
            console.error('Compatibility analysis failed:', error);

            // Show error in UI
            const errorResult = {
                status: "error",
                message: "L'analyse a échoué",
                details: "Impossible d'analyser la compatibilité des médicaments pour le moment. " + error.message
            };

            updateCompatibilityWidget(errorResult);
            showCompatibilityResult(errorResult);

        } finally {
            isAnalysisInProgress = false;

            // Process pending request if available
            if (typeof lastAnalysisRequest === 'object') {
                const pendingMedicaments = lastAnalysisRequest;
                lastAnalysisRequest = null;
                analyzeCompatibility(pendingMedicaments);
            }
        }
    }

    // SECTION: COMPATIBILITY UI MANAGEMENT


    // Function to update the compatibility widget
    function updateCompatibilityWidget(result) {
        const widgetEl = document.getElementById('compatibility-widget');
        if (!widgetEl) {
            console.error("Compatibility widget element not found!");
            return;
        }

        console.log("Updating compatibility widget with:", result);

        if (!result) {
            // Initial state
            widgetEl.innerHTML = `
            <div class="compatibility-widget-inner">
                <div class="compatibility-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="compatibility-content">
                    <div class="compatibility-title">Compatibilité médicamenteuse</div>
                    <div class="compatibility-message">Ajoutez des médicaments pour analyser leur compatibilité</div>
                </div>
            </div>
        `;
            return;
        }

        if (result.status === 'loading') {
            widgetEl.innerHTML = `
            <div class="compatibility-widget-inner">
                <div class="compatibility-icon loading">
                    <i class="fas fa-spinner fa-pulse"></i>
                </div>
                <div class="compatibility-content">
                    <div class="compatibility-title">Analyse en cours</div>
                    <div class="compatibility-message">Vérification de la compatibilité des médicaments...</div>
                </div>
            </div>
        `;
            return;
        }

        let iconClass, statusColor, statusText;

        // Determine style based on status or rating
        if (result.status === 'error') {
            iconClass = 'fa-exclamation-circle';
            statusColor = '#6c757d'; // Grey for error
            statusText = 'Erreur';
        } else if (result.status === 'waiting') {
            iconClass = 'fa-clock';
            statusColor = '#6c757d'; // Grey for waiting
            statusText = 'En attente';
        } else {
            const rating = parseInt(result.rating || 0);

            if (rating >= 8) {
                iconClass = 'fa-check-circle';
                statusColor = '#28a745'; // Green for good compatibility
                statusText = 'Bonne compatibilité';
            } else if (rating >= 5) {
                iconClass = 'fa-exclamation-triangle';
                statusColor = '#ffc107'; // Yellow for medium compatibility
                statusText = 'Compatibilité moyenne';
            } else {
                iconClass = 'fa-times-circle';
                statusColor = '#dc3545'; // Red for poor compatibility
                statusText = 'Compatibilité faible';
            }

            // Override with status if available
            if (result.status) {
                switch (result.status.toLowerCase()) {
                    case 'good':
                    case 'success':
                        iconClass = 'fa-check-circle';
                        statusColor = '#28a745';
                        statusText = 'Bonne compatibilité';
                        break;
                    case 'warning':
                    case 'minor':
                        iconClass = 'fa-exclamation-triangle';
                        statusColor = '#ffc107';
                        statusText = 'Attention';
                        break;
                    case 'danger':
                    case 'severe':
                        iconClass = 'fa-times-circle';
                        statusColor = '#dc3545';
                        statusText = 'Risque élevé';
                        break;
                }
            }
        }

        // Update the widget HTML
        widgetEl.innerHTML = `
        <div class="compatibility-widget-inner" style="border-left-color: ${statusColor}">
            <div class="compatibility-icon" style="color: ${statusColor}">
                <i class="fas ${iconClass}"></i>
            </div>
            <div class="compatibility-content">
                <div class="compatibility-title">${statusText}</div>
                <div class="compatibility-message">${result.message || 'Analyse des interactions médicamenteuses'}</div>
                ${result.details ? `<div class="compatibility-details">${result.details}</div>` : ''}
            </div>
            <div class="compatibility-actions">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCompatibilityDetails(this)">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
    `;

        // Initialize as collapsed
        const detailsEl = widgetEl.querySelector('.compatibility-details');
        if (detailsEl) {
            detailsEl.style.display = 'none';
        }
    }

    // Function to toggle compatibility details
    function toggleCompatibilityDetails(button) {
        const widgetEl = document.getElementById('compatibility-widget');
        const detailsEl = widgetEl.querySelector('.compatibility-details');
        const iconEl = button.querySelector('i');

        if (detailsEl) {
            if (detailsEl.style.display === 'none') {
                detailsEl.style.display = 'block';
                iconEl.classList.remove('fa-chevron-down');
                iconEl.classList.add('fa-chevron-up');
            } else {
                detailsEl.style.display = 'none';
                iconEl.classList.remove('fa-chevron-up');
                iconEl.classList.add('fa-chevron-down');
            }
        }
    }

    // Function to show the compatibility widget
    function showCompatibilityWidget() {
        const widgetEl = document.getElementById('compatibility-widget');
        if (widgetEl) {
            console.log("Showing compatibility widget");
            widgetEl.style.display = 'block';

            // Ensure it's visible
            setTimeout(() => {
                widgetEl.classList.add('visible');
            }, 10);
        } else {
            console.error("Widget element not found when trying to show it");
        }
    }

    // Function to hide the compatibility widget
    function hideCompatibilityWidget() {
        const widgetEl = document.getElementById('compatibility-widget');
        if (widgetEl) {
            console.log("Hiding compatibility widget");
            widgetEl.classList.remove('visible');

            // Wait for animation to complete
            setTimeout(() => {
                widgetEl.style.display = 'none';
            }, 300);
        }
    }

    // Function to show compatibility result
    function showCompatibilityResult(result) {
        const resultEl = document.getElementById('compatibility-result');
        if (!resultEl) {
            console.error("Compatibility result element not found!");
            return;
        }

        console.log("Showing compatibility result:", result);

        let iconClass, statusColor, statusText, bgColor, borderColor;

        // Determine style based on status or rating
        const rating = parseInt(result.rating || 0);

        if (rating >= 8) {
            iconClass = 'fa-check-circle';
            statusColor = '#28a745'; // Green for good compatibility
            bgColor = 'rgba(40, 167, 69, 0.05)';
            borderColor = 'rgba(40, 167, 69, 0.3)';
            statusText = 'Bonne compatibilité';
        } else if (rating >= 5) {
            iconClass = 'fa-exclamation-triangle';
            statusColor = '#ffc107'; // Yellow for medium compatibility
            bgColor = 'rgba(255, 193, 7, 0.05)';
            borderColor = 'rgba(255, 193, 7, 0.3)';
            statusText = 'Compatibilité moyenne';
        } else {
            iconClass = 'fa-times-circle';
            statusColor = '#dc3545'; // Red for poor compatibility
            bgColor = 'rgba(220, 53, 69, 0.05)';
            borderColor = 'rgba(220, 53, 69, 0.3)';
            statusText = 'Compatibilité faible';
        }

        // Override with status if available
        if (result.status) {
            switch (result.status.toLowerCase()) {
                case 'good':
                case 'success':
                    iconClass = 'fa-check-circle';
                    statusColor = '#28a745';
                    bgColor = 'rgba(40, 167, 69, 0.05)';
                    borderColor = 'rgba(40, 167, 69, 0.3)';
                    statusText = 'Bonne compatibilité';
                    break;
                case 'warning':
                case 'minor':
                    iconClass = 'fa-exclamation-triangle';
                    statusColor = '#ffc107';
                    bgColor = 'rgba(255, 193, 7, 0.05)';
                    borderColor = 'rgba(255, 193, 7, 0.3)';
                    statusText = 'Attention';
                    break;
                case 'danger':
                case 'severe':
                    iconClass = 'fa-times-circle';
                    statusColor = '#dc3545';
                    bgColor = 'rgba(220, 53, 69, 0.05)';
                    borderColor = 'rgba(220, 53, 69, 0.3)';
                    statusText = 'Risque élevé';
                    break;
                case 'error':
                    iconClass = 'fa-exclamation-circle';
                    statusColor = '#6c757d';
                    bgColor = 'rgba(108, 117, 125, 0.05)';
                    borderColor = 'rgba(108, 117, 125, 0.3)';
                    statusText = 'Erreur';
                    break;
            }
        }

        // Create the result content
        resultEl.innerHTML = `
        <div class="result-content" style="background-color: ${bgColor}; border: 1px solid ${borderColor}; border-left: 5px solid ${statusColor}; padding: 10px;border-radius: 15px;margin: 10px;">
            
            <div class="result-text">
                <div class="result-title-container" style="color: ${statusColor}">
                    <div class="result-icon" style="color: ${statusColor}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="result-title" style="color: ${statusColor}">
                        ${statusText}
                    </div>
                </div>
                <div class="result-message">${result.message || 'Analyse des interactions médicamenteuses'}</div>
                ${result.details ? `<div class="result-details">${result.details}</div>` : ''}
            </div>
        </div>
    `;

        // Show the result element
        resultEl.style.display = 'block';
    }

    // Function to hide compatibility result
    function hideCompatibilityResult() {
        const resultEl = document.getElementById('compatibility-result');
        if (resultEl) {
            console.log("Hiding compatibility result");
            resultEl.style.display = 'none';
        }
    }

    // Function to add required styles
    function addCompatibilityStyles() {
        console.log("Adding compatibility styles");

        // Check if styles already exist
        if (document.getElementById('compatibility-styles')) {
            console.log("Compatibility styles already exist");
            return;
        }

        // Create a style element
        const styleEl = document.createElement('style');
        styleEl.id = 'compatibility-styles';
        styleEl.type = 'text/css';

        // Define the CSS
        styleEl.innerHTML = `
    
    `;

        // Add the style to the document head
        document.head.appendChild(styleEl);
        console.log("Compatibility styles added");
    }
</script>



@section('styles')
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
            cursor: pointer;
        }

        /* Compatibility Widget Styles */
        .compatibility-widget {
            margin: 20px 0;
            width: 100%;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: none;
        }

        .compatibility-widget.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .compatibility-widget-inner {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #6c757d;
        }

        .compatibility-icon {
            font-size: 24px;
            padding-right: 15px;
            flex-shrink: 0;
        }

        .compatibility-content {
            flex: 1;
        }

        .compatibility-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .compatibility-message {
            font-size: 14px;
            color: #6c757d;
        }

        .compatibility-details {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            font-size: 13px;
            color: #6c757d;
        }

        .compatibility-actions {
            flex-shrink: 0;
            padding-left: 10px;
        }

        /* Compatibility Result Styles */
        .compatibility-result {
            margin: 20px 0;
            width: 100%;
            display: none;
        }

        .result-content {
            display: flex;
            align-items: flex-start;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .result-text {
            flex: 1;
        }

        .result-title-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .result-icon {
            font-size: 28px;
            flex-shrink: 0;
        }

        .result-title {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 0;
            /* remove if not needed */
        }

        .result-message {
            font-size: 15px;
            color: #4a4a4a;
            margin-bottom: 10px;
        }

        .result-details {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
            line-height: 1.5;
        }

        /* Loading animation */
        .compatibility-icon.loading {
            animation: pulse 1.5s infinite ease-in-out;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .legend-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 3px;
            margin-right: 5px;
            vertical-align: middle;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        .switch-label {
            margin-left: 10px;
            font-weight: 500;
        }
    </style>
@endsection