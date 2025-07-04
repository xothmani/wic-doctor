@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse d'Images Gynécologiques</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #8e44ad; /* Pink for gynecology theme */
            --secondary-color: #8e44ad;
            --accent-color:rgb(164, 126, 180);
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --success-color: #4bb543;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:rgb(245, 223, 255); /* Light pink tint */
            color: #333;
        }
        
   
            .container {
            max-width: 1200px;
            padding: 10px;
        }
     
        
     
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .upload-area {
            border: 2px dashedrgb(169, 132, 185);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: var(--light-gray);
        }
        
        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(232, 62, 140, 0.05);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .preview-area {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 1.5rem;
        }
        
        .preview-item {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 255, 255, 0.9);
            color: #dc3545;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-left-color: var(--primary-color);
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        #results-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .result-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        
        .analysis-section {
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }
        
        .impression {
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 1.1rem;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .findings-list, .diagnosis-list, .recommendations-list {
            margin-top: 15px;
        }
        
        .findings-list h5, .diagnosis-list h5, .recommendations-list h5 {
            color: var(--primary-color);
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .findings-list ul, .diagnosis-list ul, .recommendations-list ul {
            padding-left: 20px;
        }
        
        .findings-list li, .diagnosis-list li, .recommendations-list li {
            margin-bottom: 5px;
        }
        
        /* Custom file input */
        .custom-file-input {
            opacity: 0;
            width: 0.1px;
            height: 0.1px;
            position: absolute;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .file-input-label:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .file-input-label i {
            margin-right: 0.5rem;
        }
        
        /* Gynecology-specific elements */
        .study-type-selector {
            margin-bottom: 20px;
        }
        
        .patient-data-section {
            background-color: #fff9fb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stage-indicator {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        .stage-indicator .stage {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f8cde0;
            margin: 0 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .stage-indicator .stage.active {
            background-color: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
        <div class="container">
             <div class="container text-center custom-top-spacing" >
    <h3 class="fw-bold" style="color: var(--primary-color);">
                <i class="fas fa-female me-2"></i>
                Analyseur d'Images Gynécologiques
    </h3>
    <p class="lead text-muted">Analyse d’imagerie gynécologique pour un suivi précis de la santé féminine.</p>
</div>



           
   
        </div>

    <div class="container main-content">
        <div class="row">
            <div class="col-md-12">
                <div id="upload-section">
                    <form id="upload-form" enctype="multipart/form-data">
                        <div class="card">
                            <div class="card-header">
                                Type d'Examen Gynécologique
                            </div>
                            <div class="card-body">
                                <div class="study-type-selector">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeUltrasound" value="ultrasound" checked>
                                        <label class="form-check-label" for="studyTypeUltrasound">Échographie Pelvienne</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeTransvaginal" value="transvaginal">
                                        <label class="form-check-label" for="studyTypeTransvaginal">Échographie Transvaginale</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeColposcopy" value="colposcopy">
                                        <label class="form-check-label" for="studyTypeColposcopy">Colposcopie</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeMammography" value="mammography">
                                        <label class="form-check-label" for="studyTypeMammography">Mammographie</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                Informations sur la Patiente
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patientAge" class="form-label">Âge</label>
                                            <input type="number" class="form-control" id="patientAge" name="patient_age" min="0" max="120">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patientGravidity" class="form-label">Gestité/Parité</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="patientGravidity" name="patient_gravidity" min="0" placeholder="G">
                                                <span class="input-group-text">/</span>
                                                <input type="number" class="form-control" id="patientParity" name="patient_parity" min="0" placeholder="P">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lastMenstrualPeriod" class="form-label">Date des dernières règles</label>
                                            <input type="date" class="form-control" id="lastMenstrualPeriod" name="last_menstrual_period">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patientStatus" class="form-label">Statut</label>
                                            <select class="form-select" id="patientStatus" name="patient_status">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="non-pregnant">Non enceinte</option>
                                                <option value="pregnant">Enceinte</option>
                                                <option value="postpartum">Post-partum</option>
                                                <option value="menopausal">Ménopausée</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card pregnancy-info" style="display: none;">
                            <div class="card-header">
                                Information de Grossesse
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gestationalAge" class="form-label">Âge gestationnel (semaines)</label>
                                            <input type="number" class="form-control" id="gestationalAge" name="gestational_age" min="1" max="42">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pregnancyType" class="form-label">Type de grossesse</label>
                                            <select class="form-select" id="pregnancyType" name="pregnancy_type">
                                                <option value="singleton">Unique</option>
                                                <option value="twin">Gémellaire</option>
                                                <option value="multiple">Multiple</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                Télécharger des Images
                            </div>
                            <div class="card-body">
                                <div class="upload-area" id="drop-area">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h5>Glissez-déposez vos images gynécologiques ici</h5>
                                    <p class="text-muted">ou</p>
                                    <input type="file" id="fileInput" class="custom-file-input" name="files[]" multiple accept="image/*">
                                    <label for="fileInput" class="file-input-label">
                                        <i class="fas fa-images"></i>
                                        Sélectionner des Images
                                    </label>
                                    <p class="mt-3 text-muted small">Formats pris en charge : JPG, PNG, JPEG</p>
                                </div>
                                <div class="preview-area" id="preview"></div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                Informations Cliniques
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="clinicalIndication" class="form-label">Indication clinique :</label>
                                    <select class="form-select mb-3" id="clinicalIndication" name="clinical_indication">
                                        <option value="" selected>Sélectionner l'indication principale</option>
                                        <option value="routine_checkup">Examen de routine</option>
                                        <option value="abnormal_bleeding">Saignements anormaux</option>
                                        <option value="pelvic_pain">Douleurs pelviennes</option>
                                        <option value="ovarian_cyst">Suspicion de kyste ovarien</option>
                                        <option value="fibroid">Suspicion de fibrome</option>
                                        <option value="pregnancy_monitoring">Suivi de grossesse</option>
                                        <option value="other">Autre</option>
                                    </select>
                                    
                                    <div class="mb-3">
                                        <label for="textInput" class="form-label">Informations cliniques supplémentaires :</label>
                                        <div class="input-group">
                                            <textarea class="form-control" id="textInput" name="text_input" rows="3" placeholder="Exemple : Patiente de 35 ans présentant des métrorragies depuis 2 mois avec suspicion de polypes..."></textarea>
                                            <button class="btn btn-outline-secondary" type="button" id="voiceInputBtn" title="Entrée vocale">
                                                <i class="fas fa-microphone"></i>
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <span id="recordingStatus" class="text-muted" style="display: none;">Écoute en cours...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="analyze-btn">
                                <i class="fas fa-search-plus me-2"></i>
                                Analyser les Images
                            </button>
                        </div>
                    </form>
                    
                    <div class="loading text-center mt-4" id="loading">
                        <div class="spinner"></div>
                        <h5>Analyse des images gynécologiques</h5>
                        <p class="text-muted">Cela peut prendre quelques instants...</p>
                    </div>
                </div>
                
                <div id="results-section" class="mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            Résultats de l'Analyse Médicale
                            <button id="edit-mode-toggle" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Mode Édition
                            </button>
                        </div>
                        <div class="card-body" id="content">
                            <div class="row" >
                                <div class="col-md-5">
                                    <img id="result-image" src="" alt="Image médicale" class="result-image">
                                    <div id="clinical-query" class="editable-section" contenteditable="false">
                                        Requête clinique
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="analysis-section">
                                        <div id="impression" class="editable-section" contenteditable="false">
                                            Impression générale
                                        </div>
                                        
                                        <div class="findings-list">
                                            <h5><i class="fas fa-list me-2"></i>Observations</h5>
                                            <ul id="findings" class="editable-section list-group" contenteditable="false">
                                                <li class="list-group-item">Observation 1</li>
                                            </ul>
                                        
                                        </div>
                                        
                                        <div class="diagnosis-list">
                                            <h5><i class="fas fa-stethoscope me-2"></i>Diagnostic</h5>
                                            <ul id="diagnosis" class="editable-section list-group" contenteditable="false">
                                                <li class="list-group-item">Diagnostic 1</li>
                                            </ul>
                                         
                                        </div>
                                        
                                        <div class="recommendations-list">
                                            <h5><i class="fas fa-clipboard-list me-2"></i>Recommandations</h5>
                                            <ul id="recommendations" class="editable-section list-group" contenteditable="false">
                                                <li class="list-group-item">Recommandation 1</li>
                                            </ul>
                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
                        <button class="btn btn-outline-primary btn-lg px-4" id="save-analysis-btn" style="display:none;">
                            <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                        </button>
                        <button class="btn btn-outline-secondary btn-lg px-4" id="new-analysis-btn">
                            <i class="fas fa-redo me-2"></i>
                            Analyser d'Autres Images
                        </button>     
                        <button class="btn btn-outline-secondary btn-lg px-4" id="download-pdf-btn" onclick="downloadPDF()">
                            <i class="fas fa-file-pdf me-2"></i>Télécharger en PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');
    const uploadForm = document.getElementById('upload-form');
    const loadingIndicator = document.getElementById('loading');
    const uploadSection = document.getElementById('upload-section');
    const resultsSection = document.getElementById('results-section');
    const newAnalysisBtn = document.getElementById('new-analysis-btn');
    const patientStatus = document.getElementById('patientStatus');
    const pregnancyInfo = document.querySelector('.pregnancy-info');
    const studyTypeRadios = document.querySelectorAll('input[name="studyType"]');
    const MAX_FILES = 6; // Set maximum files limit
    
    // Show pregnancy info section when patient status is set to pregnant
    if (patientStatus) {
        patientStatus.addEventListener('change', function() {
            if (this.value === 'pregnant') {
                pregnancyInfo.style.display = 'block';
            } else {
                pregnancyInfo.style.display = 'none';
            }
        });
    }
    
    // Update UI based on study type
    for (const radio of studyTypeRadios) {
        radio.addEventListener('change', function() {
            updateUIForStudyType(this.value);
        });
    }
    
    function updateUIForStudyType(studyType) {
        // You can customize UI elements based on study type
        const clinicalIndication = document.getElementById('clinicalIndication');
        
        // Clear existing options
        while (clinicalIndication.options.length > 1) {
            clinicalIndication.remove(1);
        }
        
        // Add type-specific options
        if (studyType === 'ultrasound' || studyType === 'transvaginal') {
            addOption(clinicalIndication, 'routine_checkup', 'Examen de routine');
            addOption(clinicalIndication, 'abnormal_bleeding', 'Saignements anormaux');
            addOption(clinicalIndication, 'pelvic_pain', 'Douleurs pelviennes');
            addOption(clinicalIndication, 'ovarian_cyst', 'Suspicion de kyste ovarien');
            addOption(clinicalIndication, 'fibroid', 'Suspicion de fibrome');
            addOption(clinicalIndication, 'pregnancy_monitoring', 'Suivi de grossesse');
            addOption(clinicalIndication, 'endometriosis', 'Endométriose');
            addOption(clinicalIndication, 'infertility', 'Infertilité');
        } else if (studyType === 'colposcopy') {
            addOption(clinicalIndication, 'routine_screening', 'Dépistage de routine');
            addOption(clinicalIndication, 'abnormal_pap', 'Frottis anormal');
            addOption(clinicalIndication, 'hpv_positive', 'HPV positif');
            addOption(clinicalIndication, 'cervical_lesion', 'Lésion cervicale visible');
            addOption(clinicalIndication, 'follow_up', 'Suivi post-traitement');
        } else if (studyType === 'mammography') {
            addOption(clinicalIndication, 'screening', 'Dépistage de routine');
            addOption(clinicalIndication, 'palpable_mass', 'Masse palpable');
            addOption(clinicalIndication, 'breast_pain', 'Douleur mammaire');
            addOption(clinicalIndication, 'nipple_discharge', 'Écoulement du mamelon');
            addOption(clinicalIndication, 'follow_up', 'Suivi d\'anomalie');
            addOption(clinicalIndication, 'high_risk', 'Patiente à haut risque');
        }
        
        addOption(clinicalIndication, 'other', 'Autre');
    }
    
    function addOption(selectElement, value, text) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        selectElement.add(option);
    }
    
    // Voice input elements
    const textInput = document.getElementById('textInput');
    const voiceInputBtn = document.getElementById('voiceInputBtn');
    
    let selectedFiles = [];
    
    // Initialize voice input functionality
    if (voiceInputBtn) {
        let isRecording = false;
        let recognition = null;
        
        // Create status elements if they don't exist
        let recordingStatus = document.getElementById('recordingStatus');
        if (!recordingStatus) {
            recordingStatus = document.createElement('div');
            recordingStatus.id = 'recordingStatus';
            recordingStatus.className = 'alert alert-info py-1 mt-2';
            recordingStatus.style.display = 'none';
            recordingStatus.textContent = 'Écoute en cours...';
            textInput.parentNode.after(recordingStatus);
        }
        
        let errorMessage = document.getElementById('errorMessage');
        if (!errorMessage) {
            errorMessage = document.createElement('div');
            errorMessage.id = 'errorMessage';
            errorMessage.className = 'alert alert-danger py-1 mt-2';
            errorMessage.style.display = 'none';
            recordingStatus.after(errorMessage);
        }
        
        // Show error for 3 seconds
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = "block";
            setTimeout(() => {
                errorMessage.style.display = "none";
            }, 3000);
        }
        
       // Initialize speech recognition
       function initSpeechRecognition() {
            try {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                recognition = new SpeechRecognition();
                recognition.lang = 'fr-FR';
                recognition.continuous = false;
                recognition.interimResults = false;
                
                recognition.onstart = function() {
                    isRecording = true;
                    errorMessage.style.display = 'none';
                    recordingStatus.style.display = 'block';
                    voiceInputBtn.classList.add('btn-danger');
                    voiceInputBtn.classList.remove('btn-outline-secondary');
                };
                
                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript;
                    textInput.value = transcript;
                };
                
                recognition.onerror = function(event) {
                    if (event.error === 'not-allowed') {
                        showError("Accès au microphone refusé. Veuillez l'autoriser dans les paramètres du navigateur.");
                    } else {
                        showError(`Erreur: ${event.error}`);
                    }
                    stopRecording();
                };
                
                recognition.onend = function() {
                    stopRecording();
                };
                
                return true;
            } catch (error) {
                showError("Votre navigateur ne prend pas en charge la reconnaissance vocale.");
                return false;
            }
        }
        
        function stopRecording() {
            if (recognition && isRecording) {
                try {
                    recognition.stop();
                } catch (e) {
                    // Ignore errors during stop
                }
            }
            isRecording = false;
            recordingStatus.style.display = 'none';
            voiceInputBtn.classList.remove('btn-danger');
            voiceInputBtn.classList.add('btn-outline-secondary');
        }
        
        voiceInputBtn.addEventListener('click', function() {
            errorMessage.style.display = 'none';
            
            if (!recognition) {
                if (!initSpeechRecognition()) {
                    return;
                }
            }
            
            if (isRecording) {
                stopRecording();
            } else {
                try {
                    recognition.start();
                } catch (error) {
                    // If there's an error starting, try to reinitialize
                    recognition = null;
                    if (initSpeechRecognition()) {
                        try {
                            recognition.start();
                        } catch (e) {
                            showError("Impossible de démarrer la reconnaissance vocale.");
                        }
                    }
                }
            }
        });
    }
    
    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('dragover');
    }
    
    function unhighlight() {
        dropArea.classList.remove('dragover');
    }
    
    // Handle dropped files
    dropArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        // Check file limit for drag and drop
        if (files.length > MAX_FILES) {
            alert(`You can only select up to ${MAX_FILES} images.`);
            return;
        }
        
        handleFiles(files);
    });
    
    // Handle selected files
    fileInput.addEventListener('change', function() {
        // Check file limit for file input
        if (this.files.length > MAX_FILES) {
            alert(`You can only select up to ${MAX_FILES} images.`);
            this.value = '';
            return;
        }
        
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        preview.innerHTML = '';
        selectedFiles = Array.from(files);
        
        selectedFiles.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = file.name;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-btn';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', function() {
                        selectedFiles = selectedFiles.filter((_, i) => i !== index);
                        div.remove();
                        updateFileInput();
                    });
                    
                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    function updateFileInput() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        fileInput.files = dataTransfer.files;
    }
    
    // Handle form submission
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (fileInput.files.length === 0) {
            alert('Please select at least one image');
            return;
        }
        
        // Show loading indicator
        loadingIndicator.style.display = 'block';
        
        const formData = new FormData(this);
        formData.append('speciality', 'Gynécologue');

        // Submit form data via AJAX
        fetch('https://wicdialer.com/speciality/upload', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayResults(data);
            } else {
                alert(data.error || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        })
        .finally(() => {
            loadingIndicator.style.display = 'none';
        });
    });
    
    // Display results
    function displayResults(data) {
        // Update images
        const imageContainer = document.querySelector('.col-md-5');
        
        // Remove previous image if it exists
        const existingImg = document.getElementById('result-image');
        if (existingImg) {
            existingImg.remove();
        }
        
        // Create and append all images
        if (data.file_paths && data.file_paths.length > 0) {
            data.file_paths.forEach((path, index) => {
                const img = document.createElement('img');
                img.src = `${path}`;
                img.alt = `Radiographic image ${index + 1}`;
                img.className = "result-image mb-3";
                img.style.maxWidth = "350px";  // Set maximum width
                img.style.height = "400px"; 
                // Give the first image the ID 'result-image' to maintain compatibility
                if (index === 0) {
                    img.id = "result-image";
                }
                
                // Insert before the clinical query paragraph
                const clinicalQuery = document.getElementById('clinical-query');
                imageContainer.insertBefore(img, clinicalQuery);
            });
        }
        
        // Update clinical query
        const clinicalQuery = document.getElementById('clinical-query');
        if (data.text_input) {
            clinicalQuery.textContent = `Query: ${data.text_input}`;
        } else {
            clinicalQuery.textContent = "Pas d'informations cliniques additionnels";
        }
        
        // Update impression
        const impression = document.getElementById('impression');
        impression.textContent = data.analysis_result.summary || 'Analysis complete';
        
        // Get the raw data if available
        const rawData = data.analysis_result.raw_data || {};
        
        // Update findings
        const findingsList = document.getElementById('findings');
        findingsList.innerHTML = '';
        if (rawData.findings) {
            const findings = Array.isArray(rawData.findings) ? rawData.findings : [rawData.findings];
            findings.forEach(finding => {
                const li = document.createElement('li');
                li.textContent = finding;
                findingsList.appendChild(li);
            });
        } else {
            // Use details as fallback
            data.analysis_result.details.forEach(detail => {
                if (detail.startsWith('Finding:')) {
                    const li = document.createElement('li');
                    li.textContent = detail.replace('Finding:', '').trim();
                    findingsList.appendChild(li);
                }
            });
            if (findingsList.children.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'No specific findings noted';
                findingsList.appendChild(li);
            }
        }
        
        // Update diagnosis
        const diagnosisList = document.getElementById('diagnosis');
        diagnosisList.innerHTML = '';
        if (rawData.diagnosis) {
            const diagnoses = Array.isArray(rawData.diagnosis) ? rawData.diagnosis : [rawData.diagnosis];
            diagnoses.forEach(diagnosis => {
                const li = document.createElement('li');
                li.textContent = diagnosis;
                diagnosisList.appendChild(li);
            });
        } else {
            // Use details as fallback
            data.analysis_result.details.forEach(detail => {
                if (detail.startsWith('Diagnosis:')) {
                    const li = document.createElement('li');
                    li.textContent = detail.replace('Diagnosis:', '').trim();
                    diagnosisList.appendChild(li);
                }
            });
            if (diagnosisList.children.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'No diagnosis provided';
                diagnosisList.appendChild(li);
            }
        }
        
        // Update recommendations
        const recommendationsList = document.getElementById('recommendations');
        recommendationsList.innerHTML = '';
        if (rawData.recommendations) {
            const recommendations = Array.isArray(rawData.recommendations) ? rawData.recommendations : [rawData.recommendations];
            recommendations.forEach(recommendation => {
                const li = document.createElement('li');
                li.textContent = recommendation;
                recommendationsList.appendChild(li);
            });
        } else {
            // Use details as fallback
            data.analysis_result.details.forEach(detail => {
                if (detail.startsWith('Recommendation:')) {
                    const li = document.createElement('li');
                    li.textContent = detail.replace('Recommendation:', '').trim();
                    recommendationsList.appendChild(li);
                }
            });
            if (recommendationsList.children.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'No specific recommendations provided';
                recommendationsList.appendChild(li);
            }
        }
        
        // Hide upload section, show results section
        uploadSection.style.display = 'none';
        resultsSection.style.display = 'block';
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
});

document.getElementById('new-analysis-btn').addEventListener('click', function() {
    location.reload();
});
document.addEventListener('DOMContentLoaded', function() {
            const editModeToggle = document.getElementById('edit-mode-toggle');
            const saveAnalysisBtn = document.getElementById('save-analysis-btn');
            
            // Additional buttons for adding items
            const addFindingBtn = document.getElementById('add-finding');
            const addDiagnosisBtn = document.getElementById('add-diagnosis');
            const addRecommendationBtn = document.getElementById('add-recommendation');
            
            // Sections to make editable
            const editableSections = [
                document.getElementById('clinical-query'),
                document.getElementById('impression'),
                document.getElementById('findings'),
                document.getElementById('diagnosis'),
                document.getElementById('recommendations')
            ];
            
            let isEditMode = false;
            
            // Toggle edit mode
            editModeToggle.addEventListener('click', function() {
                isEditMode = !isEditMode;
                
                // Toggle contenteditable and visual cues
                editableSections.forEach(section => {
                    section.contentEditable = isEditMode;
                    if (isEditMode) {
                        section.classList.add('border', 'border-primary');
                    } else {
                        section.classList.remove('border', 'border-primary');
                    }
                });
                
                // Show/hide additional buttons in edit mode
                [addFindingBtn, addDiagnosisBtn, addRecommendationBtn, saveAnalysisBtn].forEach(btn => {
                    btn.style.display = isEditMode ? 'block' : 'none';
                });
                
                // Update toggle button
                editModeToggle.innerHTML = isEditMode 
                    ? '<i class="fas fa-lock me-1"></i>Verrouiller' 
                    : '<i class="fas fa-edit me-1"></i>Mode Édition';
            });
            
            // Function to add new list item
            function createAddItemHandler(listId) {
                return function() {
                    const list = document.getElementById(listId);
                    const newItem = document.createElement('li');
                    newItem.className = 'list-group-item';
                    newItem.contentEditable = true;
                    newItem.textContent = 'Nouvel élément';
                    list.appendChild(newItem);
                };
            }
            
            // Add event listeners for adding items
            addFindingBtn.addEventListener('click', createAddItemHandler('findings'));
            addDiagnosisBtn.addEventListener('click', createAddItemHandler('diagnosis'));
            addRecommendationBtn.addEventListener('click', createAddItemHandler('recommendations'));
            
            // Save analysis functionality
            saveAnalysisBtn.addEventListener('click', function() {
                const analysisData = {
                    clinicalQuery: document.getElementById('clinical-query').innerText,
                    impression: document.getElementById('impression').innerText,
                    findings: Array.from(document.getElementById('findings').children).map(li => li.innerText),
                    diagnosis: Array.from(document.getElementById('diagnosis').children).map(li => li.innerText),
                    recommendations: Array.from(document.getElementById('recommendations').children).map(li => li.innerText)
                };
                
                // In a real application, you would send this data to a backend
                console.log('Analyse sauvegardée:', analysisData);
                alert('Analyse médicale enregistrée avec succès !');
                
                // Exit edit mode
                editModeToggle.click();
            });
        });
// Function to send HTML content to Flask backend with detailed error handling
function sendHtmlToFlask(htmlContent, endpoint = 'https://wicdialer.com/speciality/submit-html') {
  console.log(`Sending request to ${endpoint} with content length: ${htmlContent.length}`);
  
  // Create a FormData object to send the HTML content
  const formData = new FormData();
  formData.append('html_content', htmlContent);
  
  // Configure the fetch request
  const requestOptions = {
    method: 'POST',
    body: formData,
  };
  
  // Send the request to the Flask backend with detailed error handling
  return fetch(endpoint, requestOptions)
    .then(response => {
      console.log(`Received response with status: ${response.status}`);
      
      // Check if the response is a PDF file
      const contentType = response.headers.get('content-type');
      console.log(`Content-Type: ${contentType}`);
      
      if (contentType && contentType.includes('application/pdf')) {
        // It's a PDF, handle it directly
        console.log('PDF received directly, creating download');
        return response.blob().then(blob => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = url;
          a.download = 'rapport_medical.pdf';
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          return { success: true, message: 'PDF downloaded successfully' };
        });
      } else if (!response.ok) {
        // It's not a PDF and there's an error
        return response.text().then(text => {
          console.error('Error response:', text);
          throw new Error(`Server error: ${response.status} - ${text}`);
        });
      } else {
        // It's not a PDF but seems to be a successful JSON response
        return response.json();
      }
    })
    .catch(error => {
      console.error('Request failed:', error);
      throw error;
    });
}

// Function to get simplified HTML content from the content div
function getContentDivHtml() {
  console.log('Getting content div HTML');
  
  const contentDiv = document.getElementById('content');
  if (!contentDiv) {
    console.error('Content div not found!');
    throw new Error('Content div with ID "content" not found on page');
  }
  
  // Make a simplified version of the content
  console.log('Creating simplified version of content');
  const simplifiedContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Rapport Médical</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        img { max-width: 100%; }
        .list-group { list-style-type: disc; padding-left: 20px; }
        .list-group-item { margin-bottom: 8px; }
      </style>
    </head>
    <body>
      <h1>Rapport d'Analyse Médicale</h1>
      <div>${contentDiv.innerHTML}</div>
    </body>
    </html>
  `;
  
  console.log('Content prepared successfully');
  return simplifiedContent;
}

// Improved downloadPDF function with better error handling
function downloadPDF() {
  console.log('Download PDF function called');
  
  // Show loading indicator
  const loadingElement = document.getElementById('loading');
  if (loadingElement) {
    loadingElement.style.display = 'block';
  }
  
  // Create a status message
  let statusMessage = document.getElementById('pdf-status');
  if (!statusMessage) {
    statusMessage = document.createElement('div');
    statusMessage.id = 'pdf-status';
    statusMessage.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #2a2a2a; color: white; padding: 10px 15px; border-radius: 5px; z-index: 9999; box-shadow: 0 2px 10px rgba(0,0,0,0.2);';
    document.body.appendChild(statusMessage);
  }
  statusMessage.textContent = 'Préparation du PDF en cours...';
  
  try {
    console.log('Getting content HTML');
    const contentHtml = getContentDivHtml();
    
    console.log('Sending content to server');
    statusMessage.textContent = 'Envoi au serveur...';
    
    sendHtmlToFlask(contentHtml)
      .then(response => {
        console.log('Server response received:', response);
        
        // Update status message based on response
        if (response.success) {
          statusMessage.textContent = 'PDF généré avec succès!';
          statusMessage.style.background = '#28a745';
          
          // Remove the status message after 3 seconds
          setTimeout(() => {
            statusMessage.remove();
          }, 3000);
        } else if (response.error) {
          statusMessage.textContent = `Erreur: ${response.message || response.error}`;
          statusMessage.style.background = '#dc3545';
          
          // Keep error message visible for 5 seconds
          setTimeout(() => {
            statusMessage.remove();
          }, 5000);
        }
        
        // Hide loading indicator
        if (loadingElement) {
          loadingElement.style.display = 'none';
        }
      })
      .catch(error => {
        console.error('Error in PDF generation:', error);
        
        // Update status with error
        statusMessage.textContent = `Erreur: ${error.message || 'Problème de communication avec le serveur'}`;
        statusMessage.style.background = '#dc3545';
        
        // Keep error message visible for 5 seconds
        setTimeout(() => {
          statusMessage.remove();
        }, 5000);
        
        // Hide loading indicator
        if (loadingElement) {
          loadingElement.style.display = 'none';
        }
      });
  } catch (error) {
    console.error('Error preparing content:', error);
    
    // Update status with error
    statusMessage.textContent = `Erreur de préparation: ${error.message}`;
    statusMessage.style.background = '#dc3545';
    
    // Keep error message visible for 5 seconds
    setTimeout(() => {
      statusMessage.remove();
    }, 5000);
    
    // Hide loading indicator
    if (loadingElement) {
      loadingElement.style.display = 'none';
    }
  }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('PDF download functionality initialized');
});
    </script>
</body>
</html>

@endsection