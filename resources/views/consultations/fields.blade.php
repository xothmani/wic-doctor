@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif


<style>
    #recordButton, #playButton {
        min-width: 150px;
        margin: 5%;
        font-weight: bold;
        border-radius: 8px;
        transition: all 0.3s ease-in-out;
    }

    #recordButton:hover {
        background-color: #5c6bc0 !important;
    }

    #playButton:hover {
        background-color: #6c757d !important;
    }

    #audioPreview {
        background: #f8f9fa;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
    }
</style>

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
            'rows' => 6,
            'style' => 'padding-right: 50px;' // Ajout d'espace pour le bouton
        ]) !!}
        <div class="form-text text-muted">{{ trans("lang.consultation_reason_help") }}</div>

        <!-- Microphone Button -->
        <button type="button" 
                class="btn bg-{{setting('theme_color')}}" 
                id="microphoneButton" 
                style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
            <i name="microphone-raison" class="fas fa-microphone-slash"></i>
        </button>
    </div>
</div>


<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('audio_recording', trans("Enregistrement Audio"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9 d-flex flex-column align-items-start">
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="recordButton" class="btn bg-{{setting('theme_color')}} d-flex align-items-center">
                <i class="fas fa-microphone mr-2"></i> Enregistrer
            </button>
            <button type="button" id="playButton" class="btn btn-secondary d-flex align-items-center" disabled>
                <i class="fas fa-play mr-2"></i> Écouter
            </button>
        </div>

        <audio id="audioPreview" class=" rounded shadow-sm" controls style="display: none;"></audio>
        <div class="">
        </div>
        <input type="hidden" id="audioBlob" name="audio_blob">
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
            'rows' => 6, 
            'style' => 'padding-right: 50px;' // Ajout d'espace pour le bouton
        ]) !!}
        <div class="form-text text-muted">{{ trans("lang.consultation_motif_help") }}</div>

        <!-- Microphone Button -->
        <button type="button" 
                class="btn bg-{{setting('theme_color')}}" 
                id="microphoneButtonMotif" 
                style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
            <i name="microphone-motif" class="fas fa-microphone-slash"></i>
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

<!-- Audio Confirmation Modal -->
<div class="modal fade" id="audioConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="audioConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="audioConfirmationModalLabel">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Voulez-vous envoyer cet enregistrement pour générer un rapport de consultation ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                <button type="button" class="btn bg-{{setting('theme_color')}}" id="confirmSendAudio">Oui</button>
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
<script type="text/javascript">
    document.getElementById('submit-btn').addEventListener('click', function() {
        $('#confirmationModal').modal('show'); // Show the modal
    });

    // Automatically hide the modal after 5 seconds
    $('#confirmationModal').on('shown.bs.modal', function () {
        setTimeout(function() {
            $('#confirmationModal').modal('hide');
        }, 5000);
    });
</script>
@endpush

@push('scripts_lib')
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
                            icon.classList.remove('fa-microphone');
                            icon.classList.add('fa-microphone-slash');
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
                    btnIcon.classList.remove('fa-microphone-slash');
                    btnIcon.classList.add('fa-microphone');
                }
            });
        }
        
        setupMic('microphoneButtonMotif', 'motif', 'microphone-motif');
        setupMic('microphoneButton', 'raison', 'microphone-raison');
    });
</script>
@endpush

@push('scripts_lib')
<script>
    let mediaRecorder;
    let audioChunks = [];
    
    const patient_id = document.querySelector('input[name="patient_id"]').value;
    const doctor_id = document.querySelector('input[name="user_id"]').value;

    document.getElementById('recordButton').addEventListener('click', async function () {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            
            mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
            
            mediaRecorder.onstop = async function () {
                if (audioChunks.length === 0) {
                    alert("Aucun enregistrement détecté !");
                    return;
                }

                const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                const audioUrl = URL.createObjectURL(audioBlob);
                
                document.getElementById('audioBlob').value = audioUrl;
                document.getElementById('audioPreview').src = audioUrl;
                document.getElementById('audioPreview').style.display = 'block';
                document.getElementById('playButton').disabled = false;
            };
            
            audioChunks = [];
            mediaRecorder.start();
            this.innerHTML = '<i class="fas fa-stop mr-2"></i> Arrêter';
            this.classList.replace('bg-purple', 'btn-danger');
        } else {
            mediaRecorder.stop();
            this.innerHTML = '<i class="fas fa-microphone mr-2"></i> Enregistrer';
            this.classList.replace('btn-danger', 'bg-purple');
        }
    });

    document.getElementById('playButton').addEventListener('click', function () {
        document.getElementById('audioPreview').play();
    });

    document.getElementById('confirmSave').addEventListener('click', async function () {
        const audioBlobElement = document.getElementById('audioBlob');

        if (!audioBlobElement.value) {
            // alert("Aucun enregistrement disponible à envoyer !");
            console.log('Erreur:', "Aucun enregistrement disponible à envoyer !");
            this.closest('form').submit();
        }else{
            $('#confirmationModal').modal('hide');
            $('#audioConfirmationModal').modal('show');
        }
    });

    document.getElementById('confirmSendAudio').addEventListener('click', async function () {
        $('#audioConfirmationModal').modal('hide');

        const formData = new FormData();
        formData.append('patient_id', patient_id);
        formData.append('doctor_id', doctor_id);
        
        const audioBlob = await fetch(document.getElementById('audioBlob').value).then(res => res.blob());
        formData.append('file', audioBlob, `recording-${doctor_id}-${patient_id}.wav`);
        
        fetch('https://wicdialer.com/report', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            // alert("Rapport de consultation envoyé avec succès !");
            console.log('Succès:', data);
        })
        .catch(error => {
            // alert("Erreur lors de l'envoi du rapport. Veuillez réessayer.");
            console.error('Erreur:', error);
        })
        .finally(() => {
            this.closest('form').submit();
        });
    });
</script>
@endpush

