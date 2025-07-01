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
        background-color: #001f3f !important;
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
    .large-badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    .error-field {
        border: 1px solid red !important;
    }
    .error-message {
        color: red;
        font-size: 0.8rem;
        margin-top: 5px;
        display: none;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>
<!-- Timer Display -->
<div class="form-group col-12 border-top pt-3 border-bottom pb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap timer-container w-100">

        <!-- Gauche : Informations patient -->
        <div class="d-flex flex-wrap align-items-center gap-2">
           <!-- Nom du patient -->
<span class="badge large-badge patient-info badge-unified">
    @if(optional($selectedPatient)->gender == 'femme')
        <i class="fas fa-venus fa-lg me-1"></i>
    @elseif(optional($selectedPatient)->gender == 'homme')
        <i class="fas fa-mars fa-lg me-1"></i>
    @endif
    {{ optional($selectedPatient)->full_name }}
</span>

<!-- Date de naissance -->
<span class="badge large-badge patient-info badge-unified">
    <i class="fas fa-calendar-alt fa-lg me-1"></i>
    {{ optional($selectedPatient)->date_naissance ? \Carbon\Carbon::parse($selectedPatient->date_naissance)->format('d/m/Y') : '' }}
</span>

<!-- Âge -->
<span class="badge large-badge patient-info badge-unified">
    <i class="fas fa-birthday-cake fa-lg me-1"></i>
    {{ optional($selectedPatient)->age }}
</span>
        </div>

        <!-- Droite : Timer + Bouton pause/reprise + Label -->
        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">

            <!-- Label -->
            <small class="text-muted timer-label mr-2">Durée de la consultation: </small>
            <!-- Timer -->
<div class="timer-display mr-2">
    <span class="badge badge-unified">
        <i class="fas fa-clock me-2"></i>
        <span id="consultationTimer">00:00:00</span>
    </span>
</div>

<!-- Bouton Pause/Reprendre -->
<button type="button" id="pauseResumeBtn" class="btn pause-btn badge-unified">
    <i class="fas fa-pause"></i>
</button>

        </div>
    </div>
</div>


<input type="hidden" name="duree" id="consultationDuration" value="0">

<div class="d-flex flex-column col-sm-12 col-md-6 ">
    <!-- Hidden Patient ID Field -->
    {!! Form::hidden('patient_id', optional($selectedPatient)->id) !!}

    <!-- Hidden User ID Field -->
    {!! Form::hidden('user_id', auth()->user()->id) !!}

    

    <!-- Weight Field -->
    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('weight', trans("lang.patient_weight"), ['class' => 'control-label mx-1']) !!}
        <div>
            {!! Form::number('weight', optional($selectedPatient)->weight, [
                'class' => 'form-control',
                'placeholder' => 'Insérer le poids'
            ]) !!}
        </div>
    </div>

    <!-- Height Field -->
    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('height', trans("lang.patient_height"), ['class' => 'control-label mx-1']) !!}
        <div>
            {!! Form::number('height', optional($selectedPatient)->height, [
                'class' => 'form-control',
                'placeholder' => 'Insérer la taille'
            ]) !!}
        </div>
    </div>

    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('groupe_sanguin', trans("lang.patient_groupe_sanguin"), ['class' => 'control-label mx-1']) !!}
        <div>
            {!! Form::select('groupe_sanguin', [
                '' => trans('lang.select_groupe_sanguin'),
                'A+' => 'A+',
                'A-' => 'A-',
                'B+' => 'B+',
                'B-' => 'B-',
                'AB+' => 'AB+',
                'AB-' => 'AB-',
                'O+' => 'O+',
                'O-' => 'O-'
            ], optional($selectedPatient)->groupe_sanguin, ['class' => 'select2 form-control']) !!}
        </div>
    </div>

    <!-- Allergie Field -->
    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('allergie', trans("lang.patient_allergie"), ['class' => 'control-label mx-1']) !!}
        <div>
            {!! Form::textarea('allergie', optional($selectedPatient)->allergie, [
                'class' => 'form-control',
                'rows' => 5,
                'placeholder' => 'Indiquer les allergies du patient'
            ]) !!}
        </div>
    </div>

    <!-- Antécédent Field -->
    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('antecedent', trans("lang.patient_antecedent"), ['class' => 'control-label mx-1']) !!}
        <div>
            {!! Form::textarea('antecedent', optional($selectedPatient)->antecedent, [
                'class' => 'form-control',
                'rows' => 5,
                'placeholder' => 'Indiquer les antécédents médicaux du patient'
            ]) !!}
        </div>
    </div>
    
    <!-- Medical History Field -->
    <div class="form-group d-flex flex-column mb-2">
        {!! Form::label('medical_history', trans("lang.medical_history"), ['class' => 'control-label mx-1']) !!}
        <div class="border p-2" style="min-height: 100px; max-height: 200px; overflow-y: auto; background-color: #f9f9f9;">
            {!! $historiqueMedical !!}
        </div>
    </div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">
    <p class="text-left mb-2" style="font-size: 14px; color: red; font-weight: bold;">
        * {{trans('lang.required_fields')}}
    </p>

    <!-- Date Consultation Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('dateConsultation', trans("lang.consultation_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <span class="text-danger">*</span>
        <div class="col-md-9">
            {!! Form::date('dateConsultation', date('Y-m-d'), ['class' => 'form-control']) !!}
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
                'style' => 'padding-right: 50px;',
                'required' => 'required'
            ]) !!}
            <div id="raison-error" class="error-message">Ce champ est obligatoire</div>
            <button type="button" class="btn bg-{{setting('theme_color')}}" id="microphoneButton" style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
                <i name="microphone-raison" class="fas fa-microphone-slash"></i>
            </button>
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
                'style' => 'padding-right: 50px;',
                'required' => 'required'
            ]) !!}
            <div id="motif-error" class="error-message">Ce champ est obligatoire</div>
            <button type="button" class="btn bg-{{setting('theme_color')}}" id="microphoneButtonMotif" style="position: absolute; top: 10px; right: 15px; height: 35px; width: 50px; padding: 0 10px; display: flex; align-items: center; justify-content: center;">
                <i name="microphone-motif" class="fas fa-microphone-slash"></i>
            </button>
        </div>
    </div> 
    
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <div class="col-md-12">
            <div class="border p-3">
                <div class="mb-3">
                    <span class="badge bg-danger ms-2">Innover Ensemble</span>
                    <p>Cet enregistrement audio est analysé par notre intelligence artificielle pour générer automatiquement un rapport médical structuré au format PDF, offrant une restitution précise et détaillée de la consultation. <b>Cliquez sur le button micro pour commencer à enregistrer.</b></p>
                </div>

                <div class="d-flex flex-column">
                    <div class="col-md-9 d-flex flex-column align-items-start">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="recordButton" class="btn bg-{{setting('theme_color')}} d-flex align-items-center">
                                <i class="fas fa-microphone mr-2"></i> Micro
                            </button>
                            <button type="button" id="playButton" class="btn btn-secondary d-flex align-items-center" disabled>
                                <i class="fas fa-play mr-2"></i> Écouter
                            </button>
                        </div>
                        <audio id="audioPreview" class="rounded shadow-sm" controls style="display: none;"></audio>
                        <input type="hidden" id="audioBlob" name="audio_blob">
                    </div>
                </div>
            </div>
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
                Voulez-vous sauvegarder et créer une prescription ?
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
                <button type="button" class="btn btn-secondary" id="declineSendAudio">Non</button>
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
    // Validation des champs avant soumission
    function validateForm() {
        let isValid = true;
        
        // Validation du champ raison
        const raison = document.querySelector('textarea[name="raison"]');
        const raisonError = document.getElementById('raison-error');
        if (!raison.value.trim()) {
            raison.classList.add('is-invalid');
            raisonError.style.display = 'block';
            isValid = false;
        } else {
            raison.classList.remove('is-invalid');
            raisonError.style.display = 'none';
        }
        
        // Validation du champ motif
        const motif = document.querySelector('textarea[name="motif"]');
        const motifError = document.getElementById('motif-error');
        if (!motif.value.trim()) {
            motif.classList.add('is-invalid');
            motifError.style.display = 'block';
            isValid = false;
        } else {
            motif.classList.remove('is-invalid');
            motifError.style.display = 'none';
        }
        
        return isValid;
    }

    document.getElementById('submit-btn').addEventListener('click', function() {
        if (validateForm()) {
            $('#confirmationModal').modal('show');
        }
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
            console.log('Erreur:', "Aucun enregistrement disponible à envoyer !");
            this.closest('form').submit();
        } else {
            $('#confirmationModal').modal('hide');
            $('#audioConfirmationModal').modal('show');
        }
    });

    document.getElementById('declineSendAudio').addEventListener('click', async function () {
        this.closest('form').submit();
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
            body: formData,
            headers: {
                'Accept' : 'application/json'
            },
            mode: 'cors'
        }).then(response => response.json())
        .then(data => {
            console.log('Succès:', data);
        })
        .catch(error => {
            console.error('Erreur:', error);
        })
        .finally(() => {
            this.closest('form').submit();
        });
    });
</script>
<script>
    let isPaused = false;
    let timerInterval;
    let startTime = new Date();
    const durationInput = document.getElementById('consultationDuration');
    const timerDisplay = document.getElementById('consultationTimer');
    const pauseResumeBtn = document.getElementById('pauseResumeBtn');

    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return [hrs, mins, secs].map(v => String(v).padStart(2, '0')).join(':');
    }

    function formatReadableTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        let result = '';
        if (hrs > 0) result += `${hrs}h `;
        if (mins > 0) result += `${mins}min `;
        if (secs > 0 || result === '') result += `${secs}s`;
        return result.trim();
    }

    function updateTimer() {
        const now = new Date();
        const elapsedSeconds = Math.floor((now - startTime) / 1000);
        timerDisplay.textContent = formatTime(elapsedSeconds);
        if (durationInput) durationInput.value = formatReadableTime(elapsedSeconds);
    }

    // Initial start
    document.addEventListener('DOMContentLoaded', function() {
        timerInterval = setInterval(updateTimer, 1000);

        pauseResumeBtn.addEventListener('click', function () {
    if (isPaused) {
        startTime = new Date(new Date() - elapsedPausedTime * 1000);
        timerInterval = setInterval(updateTimer, 1000);
        pauseResumeBtn.innerHTML = '<i class="fas fa-pause"></i>';
        timerDisplay.parentElement.classList.remove('paused');
    } else {
        clearInterval(timerInterval);
        const now = new Date();
        elapsedPausedTime = Math.floor((now - startTime) / 1000);
        pauseResumeBtn.innerHTML = '<i class="fas fa-play"></i>';
        timerDisplay.parentElement.classList.add('paused');
    }
    isPaused = !isPaused;
});


        document.querySelector('form')?.addEventListener('submit', function () {
            clearInterval(timerInterval);
        });

        window.addEventListener('beforeunload', function () {
            clearInterval(timerInterval);
        });
    });

    let elapsedPausedTime = 0;
</script>


@endpush
<style>
/* Conteneur principal */
.timer-container {
    background-color:rgb(255, 255, 255);
    border-radius: 10px;
    padding: 15px 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    justify-content: space-between;
    gap: 10px;
}

/* Badge général uniforme */
.badge-unified {
    font-size: 0.9rem;
    padding: 8px 14px;
    border-radius: 25px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Badges patient */
.large-badge.patient-info {
    background-color: rgb(190, 150, 147);
    color: #fff;
    margin-right: 8px;
}

/* Timer */
.timer-display .badge {
    background-color:   #45b39d   !important;
    color: #212529 !important;
    font-family: 'Courier New', monospace;
}

/* Bouton pause/reprendre */
.pause-btn {
    background-color: #d4dfea;
    border: none;
    border-radius: 25px;
    padding: 8px 14px;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pause-btn i {
    color: #2c3e50;
    font-size: 1rem;
}

/* Label */
.timer-label {
    font-size: 0.85rem;
    color: #7f8c8d;
}

/* Responsive */
@media (max-width: 768px) {
    .timer-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .pause-btn {
        margin-top: 8px;
        margin-left: 3px;
    }

    .timer-label {
        margin-top: 5px;
        margin-right: 3px;
    }
}
.timer-display .paused {
    background-color: #dc3545 !important; /* Rouge */
    color: white !important;
}


</style>
