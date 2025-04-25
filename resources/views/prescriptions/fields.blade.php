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

    <!-- Medicaments Fields -->
    <div id="medicament-fields" class="form-group" style="display: none;">
        <div class="col-md-12">
            <div class="border p-3">
                <!-- Titre du champ -->
                <div class="mb-3">
                    <span class="badge bg-danger ms-2">Innover Ensemble</span> <!-- Badge New -->
                    <p>Ces medicaments sont analysés par notre intelligence artificielle offrant une restitution
                        précise
                        et détaillée de la compatibilité des medicaments avec le patient. <b> Pour activer ou
                            desactiver l'aide
                            à la prescription, cliquer sur le bouton ci-dessous </b></p>
                </div>

                <!-- Enregistrement Audio -->
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
                        <div class="flex flex-row" style="display:flex">
                            <div class="d-flex align-items-center" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #28a745;"></span>
                                <span class="ms-1">Ok&nbsp;</span>
                            </div>
                            <div class="d-flex align-items-center me-4" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #F5DF4D;"></span>
                                <span class="ms-1">Warning&nbsp;</span>
                            </div>
                            <div class="d-flex align-items-center me-4" style="margin-inline: 15px;">
                                <span class="legend-box" style="background-color: #F38071;"></span>
                                <span class="ms-1">Danger&nbsp;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row align-items-center medicament-row">
            <div class="col-md">
                {!! Form::label('medicaments[0][CODE_PCT]', trans("lang.prescription_medicament_code")) !!}
                <span class="text-danger">*</span>
                <div style="display:flex flex-direction:row">
                    <div class="loading-indicator-container"></div>
                    <!-- Input gris qui ouvre la modal -->
                    <input type="text" class="form-control bg-light" id="medicamentInput_0"
                        placeholder="{{ trans('lang.prescription_select_medicament') }}" readonly
                        onclick="openMedicamentModal(0)" required>

                    <!-- Champ caché pour stocker la valeur réelle -->
                    <input type="hidden" name="medicaments[0][CODE_PCT]" id="medicamentValue_0">
                </div>

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


@push('scripts')
    <script>
        let medicamentCount = 1;
        let traitementCount = 1;
        let analyseCount = 1;
        let radioCount = 1;
        let aiAnalysisResults = new Map();
        let debounceTimers = {};
        let aiAnalyzerActive = false;
        let pendingAnalyses = new Map();

        document.addEventListener('DOMContentLoaded', function () {
            // Set today's date
            if (document.getElementById('date')) {
                document.getElementById('date').value = new Date().toISOString().split('T')[0];
            }

            // Configure prescription type toggle
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                typeSelect.addEventListener('change', toggleMedicamentFields);
                toggleMedicamentFields(); // Initial state setup
            }

            // Set up AI toggle listener
            const aiToggle = document.getElementById('aiHelpToggle');
            if (aiToggle) {
                aiToggle.addEventListener('change', handleAIToggleChange);
                handleAIToggleChange.call(aiToggle); // Initial state setup
            }

            // Set up confirmation modal
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.addEventListener('click', function () {
                    $('#confirmationModal').modal('show');
                });
            }

            const confirmSaveBtn = document.getElementById('confirmSave');
            if (confirmSaveBtn) {
                confirmSaveBtn.addEventListener('click', function () {
                    document.querySelector('form').submit();
                });
            }

            // Configure auto-hide for confirmation modal
            $('#confirmationModal').on('shown.bs.modal', function () {
                setTimeout(function () {
                    $('#confirmationModal').modal('hide');
                }, 5000);
            });

            // Set up medicament search and update structure
            setupMedicamentSearch();
            updateMedicamentRows();

            // Start DOM observer for dynamic elements
            observeDOM();

            // Add required styles
            addRequiredStyles();
        });

        // Function to toggle fields based on prescription type
        function toggleMedicamentFields() {
            const typeSelect = document.getElementById('type');
            if (!typeSelect) return;

            const medicamentFields = document.getElementById('medicament-fields');
            const addMedicamentButton = document.getElementById('add-medicament-button');
            const addTraitementButton = document.getElementById('add-traitement-button');
            const analyseFields = document.getElementById('analyse-fields');
            const radioFields = document.getElementById('radio-fields');
            const observationField = document.getElementById('observation-field');
            const nomTraitementField = document.getElementById('nom-traitement-fields');

            // Hide all fields initially
            [medicamentFields, addMedicamentButton, addTraitementButton, observationField,
                analyseFields, radioFields, nomTraitementField].forEach(el => {
                    if (el) el.style.display = 'none';
                });

            // Show appropriate fields based on selected type
            switch (typeSelect.value) {
                case 'Médicament':
                    if (medicamentFields) medicamentFields.style.display = 'block';
                    if (addMedicamentButton) addMedicamentButton.style.display = 'block';
                    break;
                case 'Analyse':
                    if (analyseFields) analyseFields.style.display = 'block';
                    break;
                case 'Radio':
                    if (radioFields) radioFields.style.display = 'block';
                    break;
                case 'Vaccin':
                case 'Autre':
                    if (observationField) observationField.style.display = 'block';
                    if (nomTraitementField) nomTraitementField.style.display = 'block';
                    if (addTraitementButton) addTraitementButton.style.display = 'block';
                    break;
            }
        }

        // Function to add a new analysis row
        function addAnalyse() {
            // Clone the first analysis row
            let analyseRow = document.querySelector('.analyse-row').cloneNode(true);

            // Update field names with new index
            analyseRow.querySelectorAll('input, select').forEach((input) => {
                input.name = input.name.replace(/\[\d+\]/, `[${analyseCount}]`);
                input.value = ''; // Reset values
            });

            // Add to container
            document.getElementById('analyse-fields').appendChild(analyseRow);
            analyseCount++;
        }

        // Function to delete an analysis row
        function deleteAnalyse(button) {
            if (document.querySelectorAll('.analyse-row').length > 1) {
                button.closest('.analyse-row').remove();
            } else {
                alert("Il doit y avoir au moins une analyse.");
            }
        }

        // Function to add a new radio row
        function addRadio() {
            // Clone the first radio row
            let radioRow = document.querySelector('.radio-row').cloneNode(true);

            // Update field names with new index
            radioRow.querySelectorAll('input, select').forEach((input) => {
                input.name = input.name.replace(/\[\d+\]/, `[${radioCount}]`);
                input.value = ''; // Reset values
            });

            // Add to container
            document.getElementById('radio-fields').appendChild(radioRow);
            radioCount++;
        }

        // Function to delete a radio row
        function deleteRadio(button) {
            if (document.querySelectorAll('.radio-row').length > 1) {
                button.closest('.radio-row').remove();
            } else {
                alert("Il doit y avoir au moins un radio.");
            }
        }

        // Function to add a new treatment row
        function addTraitement() {
            // Clone the first treatment row
            let newTraitement = document.querySelector('.form-row-traitement').cloneNode(true);

            // Reset input value
            newTraitement.querySelector('input').value = '';

            // Update name attribute for array
            newTraitement.querySelector('input').name = `nom_traitement[${traitementCount}]`;

            // Add to container
            document.getElementById('nom-traitement-container').appendChild(newTraitement);
            traitementCount++;
        }

        // Function to remove a treatment row
        function removeTraitement(element) {
            if (document.querySelectorAll('.form-row-traitement').length > 1) {
                element.closest('.form-row-traitement').remove();
            } else {
                alert('Vous devez conserver au moins un traitement.');
            }
        }

        // Function to remove a medicament row
        function removeMedicament(element) {
            const medicamentRows = document.querySelectorAll('#medicament-fields .medicament-row');

            if (medicamentRows.length > 1) {
                const row = element.closest('.medicament-row');

                // Clean up AI analysis results
                const inputElement = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
                if (inputElement && inputElement.id) {
                    aiAnalysisResults.delete(inputElement.id);
                }

                row.remove();
            } else {
                alert("Il doit y avoir au moins un médicament.");
            }
        }

        // Function to setup medicament search
        function setupMedicamentSearch() {
            document.querySelectorAll('[id^=medicamentSearch_]').forEach(searchField => {
                // Clone to remove old event listeners
                const oldSearchField = searchField.cloneNode(true);
                searchField.parentNode.replaceChild(oldSearchField, searchField);

                // Add new event listener
                oldSearchField.addEventListener('input', function () {
                    filterMedicamentList(this);
                });
            });
        }

        // Function to filter medicament list
        function filterMedicamentList(searchField) {
            const index = searchField.id.split('_')[1];
            const searchText = searchField.value.toLowerCase().trim();

            // Reset display for all items
            document.querySelectorAll(`#medicamentList_${index} a`).forEach(item => {
                item.style.display = '';
            });

            // Apply filter if search text exists
            if (searchText.length > 0) {
                document.querySelectorAll(`#medicamentList_${index} a`).forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (!text.includes(searchText)) {
                        item.style.display = 'none';
                    }
                });
            }
        }

        // Function to reset medicament filter
        function resetMedicamentFilter(index) {
            const searchField = document.getElementById(`medicamentSearch_${index}`);
            if (searchField) {
                searchField.value = '';
                filterMedicamentList(searchField);
            }
        }

        // Function to open medicament modal - fixed version
        function openMedicamentModal(index) {
            let modal = document.getElementById(`medicamentModal_${index}`);

            if (!modal) {
                // Clone the original modal
                let originalModal = document.getElementById('medicamentModal_0');
                if (originalModal) {
                    modal = originalModal.cloneNode(true);
                    modal.id = `medicamentModal_${index}`;

                    // Update IDs in cloned modal
                    modal.querySelectorAll('[id]').forEach(el => {
                        if (el.id.includes('_0')) {
                            el.id = el.id.replace('_0', `_${index}`);
                        }
                    });

                    // Update onclick attributes for all list items
                    modal.querySelectorAll('.list-group-item').forEach(item => {
                        item.setAttribute('onclick', `selectMedicament(${index}, this)`);
                    });

                    // Add new modal to document
                    document.body.appendChild(modal);

                    // Make all list items visible
                    modal.querySelectorAll('.list-group-item').forEach(item => {
                        item.style.display = '';
                    });
                }
            }

            // Show modal
            $(`#medicamentModal_${index}`).modal('show');

            // Reset search and attach events after delay
            setTimeout(() => {
                resetMedicamentFilter(index);
                const searchField = document.getElementById(`medicamentSearch_${index}`);
                if (searchField) {
                    // Clone to remove old event listeners
                    const newSearchField = searchField.cloneNode(true);
                    searchField.parentNode.replaceChild(newSearchField, searchField);

                    newSearchField.value = '';
                    newSearchField.addEventListener('input', function () {
                        filterMedicamentList(this);
                    });

                    // Focus on search field
                    newSearchField.focus();
                }
            }, 100);
        }

        // Function to select a medicament - fixed version
        function selectMedicament(index, element) {
            // Get data from clicked element
            const value = element.getAttribute('data-value');
            const displayText = element.getAttribute('data-display');

            // Update input fields
            const inputField = document.getElementById(`medicamentInput_${index}`);
            const valueField = document.getElementById(`medicamentValue_${index}`);

            if (inputField) inputField.value = displayText;
            if (valueField) valueField.value = value;

            // Close modal
            $(`#medicamentModal_${index}`).modal('hide');

            // Trigger row check if AI is active
            if (aiAnalyzerActive && valueField) {
                const row = valueField.closest('.medicament-row');
                if (row) {
                    checkRowCompleteness(row);
                }
            }
        }

        // Function to add a new medicament row - fixed version
        function addMedicament() {
            const medicamentCount = document.querySelectorAll('.medicament-row').length;
            const newIndex = medicamentCount;

            // Clone the first medicament row
            let newMedicament = document.querySelector('.medicament-row').cloneNode(true);

            // Update field names with new index and reset values
            newMedicament.querySelectorAll('input, select').forEach((input) => {
                const oldName = input.name;
                input.name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
                input.value = '';

                // Handle special ID updates for medicament selection fields
                if (input.id && (input.id.startsWith('medicamentInput_') || input.id.startsWith('medicamentValue_'))) {
                    const idPrefix = input.id.split('_')[0];
                    input.id = `${idPrefix}_${newIndex}`;
                }
            });

            // Update modal references
            const modal = newMedicament.querySelector('.modal');
            if (modal) {
                // Remove old modal to avoid duplicate IDs
                const oldModalId = modal.id;
                if (document.getElementById(oldModalId)) {
                    modal.parentNode.removeChild(modal);
                }

                // Update input that opens modal
                const modalTrigger = newMedicament.querySelector('input[readonly][onclick]');
                if (modalTrigger) {
                    modalTrigger.setAttribute('onclick', `openMedicamentModal(${newIndex})`);
                }
            }

            // Reset AI indicator if present
            const indicatorContainer = newMedicament.querySelector('.ai-indicator-container');
            if (indicatorContainer) {
                indicatorContainer.innerHTML = '';
                indicatorContainer.style.display = 'none';
            }

            // Add to container
            document.getElementById('medicament-fields').appendChild(newMedicament);

            // Update structure and attach listeners
            updateMedicamentRowStructure(newMedicament);
            attachEventListenersToRow(newMedicament);

            // Global variable should be updated after adding a row
            medicamentCount++;
        }

        // Function to update medicament rows
        function updateMedicamentRows() {
            document.querySelectorAll('.medicament-row').forEach(row => {
                updateMedicamentRowStructure(row);
            });
        }

        // Function to update structure of medicament row
        function updateMedicamentRowStructure(row) {
            // Find the medicament select column
            const selectColumn = row.querySelector('.col-md:first-child');
            if (!selectColumn) return;

            // Skip if already structured
            if (selectColumn.querySelector('.select-with-indicator')) return;

            // Get input elements
            const inputField = selectColumn.querySelector('input[id^="medicamentInput_"]');
            const valueField = selectColumn.querySelector('input[id^="medicamentValue_"]');
            if (!inputField || !valueField) return;

            // Create unique ID for value field if needed
            if (!valueField.id) {
                const index = inputField.id.split('_')[1];
                valueField.id = `medicamentValue_${index}`;
            }

            // Create structure
            const selectWithIndicator = document.createElement('div');
            selectWithIndicator.className = 'select-with-indicator';

            const indicatorContainer = document.createElement('div');
            indicatorContainer.className = 'ai-indicator-container';
            indicatorContainer.style.display = 'none';

            const selectContainer = document.createElement('div');
            selectContainer.className = 'select-container';

            // Clone inputs to remove event listeners
            const newInputField = inputField.cloneNode(true);
            const newValueField = valueField.cloneNode(true);

            // Replace original inputs
            inputField.parentNode.replaceChild(newInputField, inputField);
            valueField.parentNode.replaceChild(newValueField, valueField);

            // Move inputs to new container
            newInputField.remove();
            newValueField.remove();
            selectContainer.appendChild(newInputField);
            selectContainer.appendChild(newValueField);

            selectWithIndicator.appendChild(indicatorContainer);
            selectWithIndicator.appendChild(selectContainer);

            // Find insertion point
            const searchContainer = selectColumn.querySelector('.loading-indicator-container');
            if (searchContainer) {
                searchContainer.after(selectWithIndicator);
            } else {
                selectColumn.appendChild(selectWithIndicator);
            }

            // Attach listeners
            attachInputListenersToRow(row);
        }

        // Function to observe DOM for changes
        function observeDOM() {
            const targetNode = document.getElementById('medicament-fields');
            if (!targetNode) return;

            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1 && node.classList.contains('medicament-row')) {
                                attachEventListenersToRow(node);
                            }
                        });
                    }
                });
            });

            const config = { childList: true, subtree: true };
            observer.observe(targetNode, config);
        }

        // Function to attach event listeners to row
        function attachEventListenersToRow(row) {
            attachInputListenersToRow(row);
        }

        // Function to attach input listeners to row
        function attachInputListenersToRow(row) {
            if (!row.dataset.rowId) {
                row.dataset.rowId = `row-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            }

            const rowId = row.dataset.rowId;
            const fields = row.querySelectorAll('input, select');

            fields.forEach(field => {
                // Clone to remove old event listeners
                const newField = field.cloneNode(true);
                field.parentNode.replaceChild(newField, field);

                // Add new event listener
                newField.addEventListener('change', () => {
                    if (debounceTimers[rowId]) {
                        clearTimeout(debounceTimers[rowId]);
                    }

                    debounceTimers[rowId] = setTimeout(() => {
                        checkRowCompleteness(row);
                        delete debounceTimers[rowId];
                    }, 300);
                });
            });
        }

        // Function to handle AI toggle change
        function handleAIToggleChange() {
            aiAnalyzerActive = this.checked;

            document.querySelectorAll('.ai-indicator-container').forEach(container => {
                const valueField = container.closest('.select-with-indicator')
                    ?.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');

                if (valueField && valueField.id && valueField.value) {
                    if (aiAnalyzerActive && aiAnalysisResults.has(valueField.id)) {
                        // Set display flex, use opacity for animation
                        container.style.display = 'flex';

                        // Apply visibility after display is set
                        setTimeout(() => {
                            container.classList.add('visible');
                            displayAnalysisResult(container, aiAnalysisResults.get(valueField.id));
                        }, 200);
                    } else {
                        // Remove visible class first
                        container.classList.remove('visible');

                        // Hide after animation completes
                        setTimeout(() => {
                            if (!aiAnalyzerActive || !aiAnalysisResults.has(valueField.id)) {
                                container.style.display = 'none';
                            }
                        }, 300);
                    }
                } else {
                    container.classList.remove('visible');
                    setTimeout(() => {
                        container.style.display = 'none';
                    }, 300);
                }
            });

            // If activated, check all rows
            if (aiAnalyzerActive) {
                document.querySelectorAll('.medicament-row').forEach(row => {
                    checkRowCompleteness(row);
                });
            }
        }

        // Function to check row completeness
        function checkRowCompleteness(row) {
            if (!aiAnalyzerActive) return;

            const valueField = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
            if (!valueField || !valueField.value) return;

            const requiredFields = row.querySelectorAll('input[required], select[required]');
            let allFilled = true;

            requiredFields.forEach(field => {
                if (!field.value) {
                    allFilled = false;
                }
            });

            if (allFilled) {
                triggerAnalysis(row);
            }
        }

        // Function to trigger analysis
        function triggerAnalysis(row) {
            const valueField = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
            if (!valueField || !valueField.value) return;

            const selectWithIndicator = valueField.closest('.select-with-indicator');
            if (!selectWithIndicator) return;

            const indicatorContainer = selectWithIndicator.querySelector('.ai-indicator-container');
            if (!indicatorContainer) return;

            const data = extractMedicamentData(row);
            if (!data) return;

            indicatorContainer.style.display = 'flex';
            indicatorContainer.innerHTML = `<div class="ai-loading-spinner"></div>`;
            setTimeout(() => {
                indicatorContainer.classList.add('visible');
            }, 200);

            // Clear any pending analysis
            if (valueField.dataset.pendingAnalysis) {
                pendingAnalyses.delete(valueField.dataset.pendingAnalysis);
                delete valueField.dataset.pendingAnalysis;
            }

            analyzeMedication(data, indicatorContainer, valueField);
        }

        // Function to extract medicament data
        function extractMedicamentData(row) {
            const valueField = row.querySelector('input[name^="medicaments"][name$="[CODE_PCT]"]');
            if (!valueField || !valueField.value) return null;

            const medicationId = valueField.value;
            const consultation_id = document.querySelector('input[name="consultation_id"]').value;

            const dosageInput = row.querySelector('input[name^="medicaments"][name$="[dosage]"]');
            const dosage = dosageInput?.value || '';

            const frequencyInput = row.querySelector('input[name^="medicaments"][name$="[nb_de_fois]"]');
            const frequencyUnitSelect = row.querySelector('select[name^="medicaments"][name$="[frequency_unit]"]');
            const frequency = frequencyInput?.value || '';
            const frequencyUnit = frequencyUnitSelect?.value || '';

            const durationInput = row.querySelector('input[name^="medicaments"][name$="[nb_de_jours]"]');
            const durationUnitSelect = row.querySelector('select[name^="medicaments"][name$="[duration_unit]"]');
            const duration = durationInput?.value || '';
            const durationUnit = durationUnitSelect?.value || '';

            const scheduleSelect = row.querySelector('select[name^="medicaments"][name$="[horaire]"]');
            const schedule = scheduleSelect?.value || '';

            return {
                medicationId,
                consultation_id,
                dosage,
                frequency: `${frequency} ${frequencyUnit}`,
                duration: `${duration} ${durationUnit}`,
                schedule
            };
        }

        // Function to analyze medication
        async function analyzeMedication(data, resultContainer, valueField) {
            try {
                const analysisKey = `${data.medicationId}-${Date.now()}`;

                valueField.dataset.pendingAnalysis = analysisKey;
                pendingAnalyses.set(analysisKey, true);

                const payload = {
                    drug_id: data.medicationId,
                    consultation_id: data.consultation_id,
                    data: {
                        Dosage: data.dosage,
                        frequence: data.frequency,
                        duration: data.duration,
                        time: data.schedule
                    }
                };

                try {
                    const response = await fetch('https://wicdialer.com/api/analyze/medicine/', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    // Check if analysis is still relevant
                    if (!pendingAnalyses.has(analysisKey)) return;

                    if (!response.ok) {
                        throw new Error(`API error: ${response.status}`);
                    }

                    const result = await response.json();

                    // Parse response
                    let analysisResult;
                    try {
                        analysisResult = typeof result.response === 'string'
                            ? JSON.parse(result.response)
                            : result.response;
                    } catch (e) {
                        analysisResult = {
                            rating: result.rating || 5,
                            reason: result.reason || "Analysis completed",
                            visual_indication: result.visual_indication || "yellow"
                        };
                    }

                    // Store result
                    if (valueField.id) {
                        aiAnalysisResults.set(valueField.id, analysisResult);
                    }

                    // Display result if AI is active
                    if (aiAnalyzerActive) {
                        displayAnalysisResult(resultContainer, analysisResult);
                    } else {
                        resultContainer.style.display = 'none';
                    }

                } catch (error) {
                    console.error('API call failed:', error);

                    // Use mock response
                    const mockResult = {
                        rating: 0,
                        reason: "Une erreur est survenue, veuillez essayer plus tard.",
                        visual_indication: "grey"
                    };

                    if (valueField.id) {
                        aiAnalysisResults.set(valueField.id, mockResult);
                    }

                    if (aiAnalyzerActive) {
                        displayAnalysisResult(resultContainer, mockResult);
                    } else {
                        resultContainer.style.display = 'none';
                    }
                }

                // Clean up
                pendingAnalyses.delete(analysisKey);
                delete valueField.dataset.pendingAnalysis;

            } catch (error) {
                console.error('Error analyzing medication:', error);

                if (aiAnalyzerActive) {
                    resultContainer.innerHTML = `
                                    <div class="ai-result-icon" style="color: #dc3545;" data-toggle="tooltip" title="Error analyzing medication">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                `;
                } else {
                    resultContainer.style.display = 'none';
                }

                // Clean up
                if (valueField.dataset.pendingAnalysis) {
                    pendingAnalyses.delete(valueField.dataset.pendingAnalysis);
                    delete valueField.dataset.pendingAnalysis;
                }
            }
        }

        // Function to display analysis result
        function displayAnalysisResult(container, result) {
            let indicatorColor, indicatorIcon;

            switch (result.visual_indication.toLowerCase()) {
                case 'green':
                    indicatorColor = '#28a745';
                    indicatorIcon = 'fa-check-circle';
                    break;
                case 'yellow':
                    indicatorColor = '#F5DF4D';
                    indicatorIcon = 'fa-exclamation-triangle';
                    break;
                case 'red':
                    indicatorColor = '#F38071';
                    indicatorIcon = 'fa-times-circle';
                    break;
                default:
                    indicatorColor = '#6c757d';
                    indicatorIcon = 'fa-info-circle';
            }

            container.innerHTML = `
                                <div class="ai-result-icon" style="color: ${indicatorColor};" data-rating="${result.rating}" data-reason="${result.reason}">
                                    <i class="fas ${indicatorIcon}"></i>
                                </div>
                            `;

            const iconElement = container.querySelector('.ai-result-icon');

            // Clone to remove old event listeners
            const newIcon = iconElement.cloneNode(true);
            iconElement.parentNode.replaceChild(newIcon, iconElement);

            // Add hover event for tooltip
            newIcon.addEventListener('mouseenter', function (e) {
                const existingTooltip = document.querySelector('.ai-tooltip');
                if (existingTooltip) existingTooltip.remove();

                const tooltip = document.createElement('div');
                tooltip.className = 'ai-tooltip';
                tooltip.innerHTML = `
                                <div class="ai-tooltip-header">
                                    Compatibilité: ${result.rating}/10
                                </div>
                                <div class="ai-tooltip-body">
                                    ${result.reason}
                                </div>
                            `;

                document.body.appendChild(tooltip);

                const rect = newIcon.getBoundingClientRect();
                tooltip.style.left = `${rect.left - (tooltip.offsetWidth / 2) + (rect.width / 2)}px`;
                tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;

                setTimeout(() => tooltip.classList.add('show'), 10);
            });

            newIcon.addEventListener('mouseleave', function () {
                const tooltip = document.querySelector('.ai-tooltip');
                if (tooltip) {
                    tooltip.classList.remove('show');
                    setTimeout(() => tooltip.remove(), 200);
                }
            });

            container.style.display = 'flex';
        }

        function addRequiredStyles() {
            if (!document.getElementById('ai-analysis-styles')) {
                const styleElement = document.createElement('style');
                styleElement.id = 'ai-analysis-styles';
                styleElement.textContent = `
                                            /* Container for select with indicator */
                                            .select-with-indicator {
                                                display: flex;
                                                align-items: center;
                                                width: 100%;
                                            }

                                            /* Indicator container */
                                            .ai-indicator-container {
                                                width: 24px;
                                                height: 24px;
                                                margin-right: 8px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                flex-shrink: 0;
                                                opacity: 1;
                                                transform: scale(0.6);
                                                transition: opacity 0.3s ease, transform 0.3s ease;
                                            }

                                            .ai-indicator-container.visible {
                                                opacity: 1;
                                                transform: scale(1);
                                            }

                                            /* Select container - takes remaining width */
                                            .select-container {
                                                flex-grow: 1;
                                                width: calc(100% - 32px);
                                            }

                                            /* Modern Loading Spinner */
                                            .ai-loading-spinner {
                                                width: 16px;
                                                height: 16px;
                                                border: 2px solid #f3f3f3;
                                                border-radius: 50%;
                                                border-top: 2px solid #5c6bc0;
                                                animation: ai-spin 1s linear infinite;
                                            }

                                            @keyframes ai-spin {
                                                0% { transform: rotate(0deg); }
                                                100% { transform: rotate(360deg); }
                                            }

                                            /* Result indicators */
                                            .ai-result-icon {
                                                font-size: 16px;
                                                cursor: help;
                                            }

                                            /* Tooltip styles */
                                            .ai-tooltip {
                                                position: absolute;
                                                z-index: 1070;
                                                display: block;
                                                max-width: 276px;
                                                font-family: var(--bs-font-sans-serif);
                                                font-size: 0.875rem;
                                                background-color: #fff;
                                                background-clip: padding-box;
                                                border: 1px solid rgba(0, 0, 0, 0.2);
                                                border-radius: 0.3rem;
                                                box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.2);
                                                padding: 0.5rem 0.75rem;
                                                opacity: 0;
                                                transition: opacity 0.2s ease;
                                                pointer-events: none;
                                            }

                                            .ai-tooltip.show {
                                                opacity: 1;
                                            }

                                            .ai-tooltip-header {
                                                padding-bottom: 0.5rem;
                                                margin-bottom: 0.5rem;
                                                border-bottom: 1px solid #dee2e6;
                                                font-weight: bold;
                                            }
                                        `;
                document.head.appendChild(styleElement);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            addRequiredStyles();
        });
    </script>
@endpush

@section('styles')
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
            cursor: pointer;
        }

        .ai-analysis-result {
            margin-top: 10px;
            transition: all 0.3s ease;
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