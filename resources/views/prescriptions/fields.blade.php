
<form action="{{ route('prescriptions.store') }}" method="POST" class="d-flex flex-column align-items-center col-12 col-md-6 mx-auto">
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
                <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_analyse') }}" href="#" onclick="deleteAnalyse(this)" class="btn btn-link p-1">
                    <i class="fas fa-trash-alt"></i>
                </a>
                <!-- Add Icon -->
                <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_analyse') }}" href="#" onclick="addAnalyse()" class="btn btn-link p-1">
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
                <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_radio') }}" href="#" onclick="deleteRadio(this)" class="btn btn-link p-1">
                    <i class="fas fa-trash-alt"></i>
                </a>
                <!-- Add Icon -->
                <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_radio') }}" href="#" onclick="addRadio()" class="btn btn-link p-1">
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

    <select name="medicaments[0][CODE_PCT]" required class="form-control">
        <option value="" disabled selected>{{ trans('lang.prescription_select_medicament') }}</option>
        @foreach($medicaments as $medicament)
            <option value="{{ $isFrance ? $medicament->name : $medicament->CODE_PCT }}">
                {{ $isFrance ? $medicament->name : $medicament->NOM_COMMERCIAL }}
            </option>
        @endforeach
    </select>
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
        <input type="number" name="medicaments[0][nb_de_fois]" class="form-control" min="1" max="10" required placeholder="{{ trans('lang.prescription_medicament_frequency') }}" />
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
                <input type="number" min="1" name="medicaments[0][nb_de_jours]" required class="form-control" placeholder="{{ trans('lang.prescription_medicament_number') }}">
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
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_medicament') }}" href="#" onclick="removeMedicament(this)" class="btn btn-link p-1 mt-4">
            <i class="fas fa-trash-alt"></i>
        </a>
        
        <!-- Add Icon -->
        <a id="add-medicament-button" data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_medicament') }}" href="#" onclick="addMedicament()" class="btn btn-link p-1 mt-4">
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
            <input type="text" name="nom_traitement[]" class="form-control" placeholder="{{ trans('lang.prescription_nom_traitement_placeholder') }}">
            <div class="input-group-append">
                                <!-- Delete Icon -->
                                <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.delete_autre') }}" href="#" onclick="removeTraitement(this)" class="btn btn-link p-1 ml-2">
                    <i class="fas fa-trash-alt"></i>
                </a>
                <!-- Add Icon -->
                <a id="add-traitement-button" data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_autre') }}" href="#" onclick="addTraitement()" class="btn btn-link p-1">
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
    <div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
        <button type="button" id="add-medicament-button" class="btn btn-primary mt-2" style="display: none;" onclick="addMedicament()">
            {{ trans('lang.prescription_add_medicament') }}
        </button>

     <!-- Submit Field -->
    <button type="button" id="submit-btn" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.prescription') }}
    </button>
    <a href="{!! route('dashboard') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{ trans('lang.cancel') }}</a>

    </div>

    
</form>


<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
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
    document.getElementById('submit-btn').addEventListener('click', function() {
        $('#confirmationModal').modal('show'); // Show the modal
    });

    document.getElementById('confirmSave').addEventListener('click', function() {
        this.closest('form').submit(); // Submit the form if confirmed
    });

    // Automatically hide the modal after 5 seconds
    $('#confirmationModal').on('shown.bs.modal', function () {
        setTimeout(function() {
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


    function addMedicament() {
        let newMedicament = document.querySelector('.medicament-row').cloneNode(true);
        newMedicament.querySelectorAll('input, select').forEach((input) => {
            input.name = input.name.replace(/\[\d+\]/, `[${medicamentCount}]`);
            input.value = '';
        });
        document.getElementById('medicament-fields').appendChild(newMedicament);
        medicamentCount++;
    }

    function removeMedicament(element) {
    // Sélectionne toutes les lignes de médicaments dans le conteneur parent
    var medicamentRows = document.querySelectorAll('#medicament-fields .form-row');
    
    // Si il y a plus d'une ligne, on peut supprimer
    if (medicamentRows.length > 1) {
        element.closest('.form-row').remove();
    } else {
        alert('Vous devez conserver au moins un médicament.');
    }
}



function removeTraitement(element) {
    // Vérifie s'il reste plus d'un champ de traitement
    if (document.querySelectorAll('.form-row-traitement').length > 1) {
        element.closest('.form-row-traitement').remove();
    } else {
        alert('Vous devez conserver au moins un traitement.');
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



    }else if (['Radio'].includes(typeSelect.value)) {
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
    
</script>