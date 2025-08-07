@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyseur d'Images Gastroentérologiques</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #27ae60;     
            --secondary-color:rgb(52, 187, 108);   
            --accent-color:rgb(94, 212, 144);      
            --light-color:rgb(255, 255, 255);    
            --dark-color:rgb(8, 122, 56);      
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
     
        .container {
            max-width: 1200px;
            padding: 10px;
        }
     
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--light-color);
            color: var(--dark-color);
            font-weight: 600;
            padding: 12px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .upload-area {
            border: 2px dashed var(--accent-color);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .upload-area.dragover {
            background-color: var(--light-color);
            border-color: var(--primary-color);
        }
        
        .upload-icon {
            font-size: 48px;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .file-input-label {
            background-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .file-input-label:hover {
            background-color: var(--primary-color);
        }
        
        .custom-file-input {
            display: none;
        }
        
        .preview-area {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
            gap: 10px;
        }
        
        .preview-item {
            position: relative;
            width: 150px;
            height: 150px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            overflow: hidden;
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
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .loading {
            display: none;
            padding: 40px 0;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--accent-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
        
        #results-section {
            display: none;
        }
        
        .result-image {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        
        .analysis-section {
            padding: 15px;
        }
        
        .editable-section {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        #clinical-query {
            font-style: italic;
            color: #555;
        }
        
        #impression {
            font-weight: 500;
            font-size: 1.1em;
            line-height: 1.6;
        }
        
        .findings-list, .diagnosis-list, .recommendations-list {
            margin-top: 25px;
        }
        
        .findings-list h5, .diagnosis-list h5, .recommendations-list h5 {
            color: var(--dark-color);
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .list-group {
            border-radius: 5px;
            overflow: hidden;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 12px 15px;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
        
        [contenteditable="true"] {
            outline: none;
        }
        
        .study-type-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
        <div class="container">


        <div class="container text-center custom-top-spacing" >
    <h3 class="fw-bold" style="color: var(--primary-color);">
                <i class="fas fa-diagnoses me-2"></i>
                Analyseur d'Images Gastroentérologiques
    </h3>
    <p class="lead text-muted">Détection assistée des anomalies digestives à partir d’images endoscopiques et médicales.</p>
</div>



           

    <div class="container main-content">
        <div class="row">
            <div class="col-md-12">
                <div id="upload-section">
                    <form id="upload-form" enctype="multipart/form-data">
                        <div class="card">
                            <div class="card-header">
                                Type d'Examen Gastroentérologique
                            </div>
                            <div class="card-body">
                                <div class="study-type-selector">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeEndoscopy" value="endoscopy" checked>
                                        <label class="form-check-label" for="studyTypeEndoscopy">Endoscopie Digestive Haute</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeColonoscopy" value="colonoscopy">
                                        <label class="form-check-label" for="studyTypeColonoscopy">Coloscopie</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeCapsule" value="capsule">
                                        <label class="form-check-label" for="studyTypeCapsule">Capsule Endoscopique</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeERCP" value="ercp">
                                        <label class="form-check-label" for="studyTypeERCP">CPRE</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeEUS" value="eus">
                                        <label class="form-check-label" for="studyTypeEUS">Écho-endoscopie</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                Informations sur le Patient
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
                                            <label for="patientSex" class="form-label">Sexe</label>
                                            <select class="form-select" id="patientSex" name="patient_sex">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="male">Homme</option>
                                                <option value="female">Femme</option>
                                                <option value="other">Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patientBMI" class="form-label">Indice de Masse Corporelle (IMC)</label>
                                            <input type="number" step="0.1" class="form-control" id="patientBMI" name="patient_bmi" min="10" max="60">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patientStatus" class="form-label">Antécédents digestifs</label>
                                            <select class="form-select" id="patientStatus" name="patient_status">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="no_history">Aucun</option>
                                                <option value="ibd">MICI</option>
                                                <option value="polyps">Polypes</option>
                                                <option value="ulcer">Ulcère</option>
                                                <option value="cancer">Cancer digestif</option>
                                                <option value="other">Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card" id="colon-prep-section" style="display: none;">
                            <div class="card-header">
                                Préparation Colique
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="prepQuality" class="form-label">Qualité de la préparation</label>
                                            <select class="form-select" id="prepQuality" name="prep_quality">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="excellent">Excellente</option>
                                                <option value="good">Bonne</option>
                                                <option value="fair">Moyenne</option>
                                                <option value="poor">Médiocre</option>
                                                <option value="inadequate">Insuffisante</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="prepProduct" class="form-label">Produit utilisé</label>
                                            <select class="form-select" id="prepProduct" name="prep_product">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="peg">PEG (Polyéthylène glycol)</option>
                                                <option value="moviprep">MoviPrep</option>
                                                <option value="plenvu">Plenvu</option>
                                                <option value="picoprep">Picoprep</option>
                                                <option value="other">Autre</option>
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
                                    <h5>Glissez-déposez vos images gastroentérologiques ici</h5>
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
                                        <option value="screening">Dépistage</option>
                                        <option value="abdominal_pain">Douleurs abdominales</option>
                                        <option value="bleeding">Saignement digestif</option>
                                        <option value="anemia">Anémie</option>
                                        <option value="weight_loss">Perte de poids</option>
                                        <option value="dysphagia">Dysphagie</option>
                                        <option value="reflux">RGO</option>
                                        <option value="other">Autre</option>
                                    </select>
                                    
                                    <div class="mb-3">
                                        <label for="textInput" class="form-label">Informations cliniques supplémentaires :</label>
                                        <div class="input-group">
                                            <textarea class="form-control" id="textInput" name="text_input" rows="3" placeholder="Exemple : Patient de 65 ans présentant des douleurs épigastriques depuis 3 semaines avec une perte de poids de 5kg..."></textarea>
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
                        <h5>Analyse des images gastroentérologiques</h5>
                        <p class="text-muted">Cela peut prendre quelques instants...</p>
                    </div>
                </div>
                
                <div id="results-section" class="mt-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            Résultats de l'Analyse Gastroentérologique
                            <button id="edit-mode-toggle" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Mode Édition
                            </button>
                        </div>
                        <div class="card-body" id="content">
                            <div class="row">
                                <div class="col-md-5">
                                    <img id="result-image" src="" alt="Image endoscopique" class="result-image">
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
    const colonPrepSection = document.getElementById('colon-prep-section');
    const studyTypeRadios = document.querySelectorAll('input[name="studyType"]');
    const MAX_FILES = 6; // Set maximum files limit
    
    // Show colon prep section when study type is colonoscopy
    for (const radio of studyTypeRadios) {
        radio.addEventListener('change', function() {
            if (this.value === 'colonoscopy') {
                colonPrepSection.style.display = 'block';
            } else {
                colonPrepSection.style.display = 'none';
            }
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
        if (studyType === 'endoscopy') {
            addOption(clinicalIndication, 'dysphagia', 'Dysphagie');
            addOption(clinicalIndication, 'reflux', 'Reflux gastro-œsophagien');
            addOption(clinicalIndication, 'epigastric_pain', 'Douleur épigastrique');
            addOption(clinicalIndication, 'anemia', 'Anémie');
            addOption(clinicalIndication, 'weight_loss', 'Perte de poids');
            addOption(clinicalIndication, 'dyspepsia', 'Dyspepsie');
            addOption(clinicalIndication, 'upper_bleeding', 'Hémorragie digestive haute');
        } else if (studyType === 'colonoscopy') {
            addOption(clinicalIndication, 'screening', 'Dépistage');
            addOption(clinicalIndication, 'lower_bleeding', 'Rectorragie');
            addOption(clinicalIndication, 'abdominal_pain', 'Douleur abdominale');
            addOption(clinicalIndication, 'change_bowel', 'Trouble du transit');
            addOption(clinicalIndication, 'anemia', 'Anémie');
            addOption(clinicalIndication, 'ibd_monitoring', 'Suivi de MICI');
            addOption(clinicalIndication, 'polyp_surveillance', 'Surveillance de polypes');
        } else if (studyType === 'capsule') {
            addOption(clinicalIndication, 'obscure_bleeding', 'Saignement obscur');
            addOption(clinicalIndication, 'suspected_crohn', 'Suspicion de maladie de Crohn');
            addOption(clinicalIndication, 'small_bowel_lesion', 'Lésion du grêle suspecte');
            addOption(clinicalIndication, 'anemia', 'Anémie inexpliquée');
            addOption(clinicalIndication, 'malabsorption', 'Syndrome de malabsorption');
        } else if (studyType === 'ercp') {
            addOption(clinicalIndication, 'jaundice', 'Ictère');
            addOption(clinicalIndication, 'biliary_stones', 'Lithiase biliaire');
            addOption(clinicalIndication, 'cholangitis', 'Angiocholite');
            addOption(clinicalIndication, 'pancreatic_disease', 'Pathologie pancréatique');
            addOption(clinicalIndication, 'biliary_stricture', 'Sténose biliaire');
        } else if (studyType === 'eus') {
            addOption(clinicalIndication, 'pancreatic_mass', 'Masse pancréatique');
            addOption(clinicalIndication, 'submucosal_lesion', 'Lésion sous-muqueuse');
            addOption(clinicalIndication, 'lymph_nodes', 'Évaluation ganglionnaire');
            addOption(clinicalIndication, 'biliary_stones', 'Lithiase biliaire');
            addOption(clinicalIndication, 'staging', 'Staging tumoral');
        }
        
        addOption(clinicalIndication, 'other', 'Autre');
    }
    
    function addOption(selectElement, value, text) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        selectElement.add(option);
    }
    
    // Set initial indications for default study type
    updateUIForStudyType('endoscopy');
    
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
        formData.append('speciality', 'Gastro-Entérologue');

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
                img.alt = `medical image ${index + 1}`;
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