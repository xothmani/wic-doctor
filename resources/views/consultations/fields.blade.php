@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif

<div class="d-flex flex-column col-sm-12 col-md-6">
<p class="text-left mb-2" style="font-size: 14px; color: red; font-weight: bold;">
  * {{trans('lang.required_fields')}}
</p>
    <!-- Hidden Patient ID Field -->
    {!! Form::hidden('patient_id', optional($selectedPatient)->id) !!}

<!-- Hidden User ID Field -->
{!! Form::hidden('user_id', auth()->user()->id) !!}


    <!-- Patient Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('patient_name', trans("lang.consultation_patient"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('patient_name', optional($selectedPatient)->full_name, ['class' => 'form-control', 'disabled' => 'disabled'])  !!}
        </div>
    </div>

    <!-- Age Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('age', trans("lang.patient_age"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('age', optional($selectedPatient)->age, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
        </div>
    </div>

    <!-- Weight Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('weight', trans("lang.patient_weight"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('weight', optional($selectedPatient)->weight, ['class' => 'form-control', 'disabled' => 'disabled'])  !!}
        </div>
    </div>

        <!-- groupe sanguin Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('groupe_sanguin', trans("lang.patient_groupe_sanguin"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('groupe_sanguin', optional($selectedPatient)->groupe_sanguin, ['class' => 'form-control', 'disabled' => 'disabled'])  !!}
        </div>
    </div>

<!-- Medical History Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('medical_history', trans("lang.medical_history"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <div class="form-control" style="min-height: 120px; background-color: #e9ecef; padding: 10px; overflow-wrap: break-word;">
            {!! optional($selectedPatient)->medical_history !!}
        </div>
    </div>
</div>

<!-- Raison Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row" style="position: relative;">
    {!! Form::label('raison', trans("lang.consultation_reason"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <span class="text-danger">*</span>

    <div class="col-md-9" style="position: relative;">
        {!! Form::textarea('raison', null, [
            'class' => 'form-control',
            'placeholder' => trans("lang.consultation_reason_placeholder"),
            'style' => 'padding-right: 50px;' // Ajout d'espace pour le bouton
        ]) !!}
        <div class="form-text text-muted">{{ trans("lang.consultation_reason_help") }}</div>

        <!-- Microphone Button -->
        <button type="button" 
                class="btn bg-{{setting('theme_color')}}" 
                id="microphoneButton" 
                style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
            <i name="microphone-raison" class="fas fa-microphone"></i>
        </button>
    </div>
</div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">
<!-- Date Consultation Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('dateConsultation', trans("lang.consultation_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::date('dateConsultation', date('Y-m-d'), ['class' => 'form-control']) !!}
    </div>
</div>


<!-- Gender Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('gender', trans("lang.patient_gender"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
    {!! Form::text('patient_gender', optional($selectedPatient)->gender, ['class' => 'form-control', 'disabled' => 'disabled'])  !!}
    </div>
</div>

    <!-- Height Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('height', trans("lang.patient_height"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('height', optional($selectedPatient)->height, ['class' => 'form-control', 'disabled' => 'disabled'])  !!}
        </div>
    </div>

   <!-- allergie Field -->
   <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('allergie', trans("lang.patient_allergie"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <div class="form-control" style="min-height: 120px; background-color: #e9ecef; padding: 10px; overflow-wrap: break-word;">
            {!! optional($selectedPatient)->allergie !!}
        </div>
    </div>
</div>

   <!-- antecedent Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('antecedent', trans("lang.patient_antecedent"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <div class="form-control" style="min-height: 120px; background-color: #e9ecef; padding: 10px; overflow-wrap: break-word;">
            {!! optional($selectedPatient)->antecedent !!}
        </div>
    </div>
</div>


    <!-- Motif Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row" style="position: relative;">
    {!! Form::label('motif', trans("lang.consultation_motif"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <span class="text-danger">*</span>

    <div class="col-md-9" style="position: relative;">
        {!! Form::textarea('motif', null, [
            'class' => 'form-control', 
            'placeholder' => trans("lang.consultation_motif_placeholder"), 
            'rows' => 4, 
            'style' => 'padding-right: 50px;' // Ajout d'espace pour le bouton
        ]) !!}
        <div class="form-text text-muted">{{ trans("lang.consultation_motif_help") }}</div>

        <!-- Microphone Button -->
        <button type="button" 
                class="btn bg-{{setting('theme_color')}}" 
                id="microphoneButtonMotif" 
                style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
            <i name="microphone-motif" class="fas fa-microphone"></i>
        </button>
    </div>
</div>
    
</div>

@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
        {!! $customFields !!}
    </div>
@endif



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
                Voulez-vous sauvegarder et créer une préscription ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                <button type="button" class="btn bg-{{setting('theme_color')}}" id="confirmSave">Oui</button>
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="button" id="submit-btn" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.consultation') }}
    </button>
    <a href="{!! route('dashboard') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{ trans('lang.cancel') }}</a>
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
<!-- Font Awesome pour l'icône du micro -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<!-- Azure Speech SDK -->
<script src="https://cdn.jsdelivr.net/npm/microsoft-cognitiveservices-speech-sdk/distrib/browser/microsoft.cognitiveservices.speech.sdk.bundle.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let activeRecognizer = null;
        let activeButton = null;

        async function stopActiveMic() {
            return new Promise((resolve) => {
                if (activeRecognizer) {
                    activeRecognizer.stopContinuousRecognitionAsync(() => {
                        activeRecognizer.close();
                        activeRecognizer = null;
                        if (activeButton) {
                            activeButton.classList.remove('active', 'bg-red');
                            const icon = activeButton.querySelector('i');
                            icon.classList.remove('fa-microphone-slash');
                            icon.classList.add('fa-microphone');
                            activeButton = null;
                        }
                        resolve();
                    });
                } else {
                    resolve();
                }
            });
        }

        function setupMic(buttonId, textAreaName, iconName) {
            const button = document.getElementById(buttonId);
            const transcriptionText = document.querySelector(`textarea[name="${textAreaName}"]`);
            const btnIcon = document.querySelector(`i[name="${iconName}"]`);
            const speechKey = @json(env('SPEECH_KEY'));
            const speechRegion = @json(env('SPEECH_REGION'));

            if (!speechKey || !speechRegion) {
                console.error("Azure Speech SDK keys are not configured in the environment.");
                return;
            }

            const speechConfig = SpeechSDK.SpeechConfig.fromSubscription(speechKey, speechRegion);
            speechConfig.speechRecognitionLanguage = "fr-FR";

            button.addEventListener('click', async function () {
                if (activeButton === button) {
                    await stopActiveMic();
                } else {
                    await stopActiveMic();
                    
                    const audioConfig = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();
                    activeRecognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);

                    let fullParagraph = transcriptionText.value || "";
                    let currentParagraph = "";

                    activeRecognizer.recognizing = (s, e) => {
                        currentParagraph = e.result.text.trim();
                        transcriptionText.value = fullParagraph + " " + currentParagraph;
                    };

                    activeRecognizer.recognized = (s, e) => {
                        if (e.result.reason === SpeechSDK.ResultReason.RecognizedSpeech) {
                            currentParagraph = e.result.text.trim();
                            fullParagraph += " " + currentParagraph;
                            transcriptionText.value = fullParagraph;
                        } else if (e.result.reason === SpeechSDK.ResultReason.NoMatch) {
                            transcriptionText.setAttribute("placeholder", "Nous n'avons pas pu comprendre votre demande.");
                            console.log("No speech could be recognized.");
                        }
                    };

                    activeRecognizer.canceled = (s, e) => {
                        console.error(`Recognition canceled: ${e.reason}`);
                        if (e.reason === SpeechSDK.CancellationReason.Error) {
                            console.error(`Error details: ${e.errorDetails}`);
                        }
                    };
                    activeRecognizer.startContinuousRecognitionAsync();

                    activeButton = button;
                    button.classList.add('active', 'bg-red');
                    btnIcon.classList.remove('fa-microphone');
                    btnIcon.classList.add('fa-microphone-slash');
                }
            });
        }
        
        setupMic('microphoneButtonMotif', 'motif', 'microphone-motif');
        setupMic('microphoneButton', 'raison', 'microphone-raison');
    });
</script>


