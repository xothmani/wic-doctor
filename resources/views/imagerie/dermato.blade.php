@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse d'Images Dermatologiques</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #ce7e52;
            --secondary-color: #e8aa89;
            --accent-color: #f9e1d3;
            --light-color: #fff7f3;
            --dark-color: #8a5033;
        }

        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fbff; /* Light blue tint */
            color: #333;
        }
        
    
        
        .container {
            max-width: 1200px;
            padding: 5px;
        }
        .custom-top-spacing {
    margin-top: 20px;
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
            border: 2px dashed #cde8f8;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: var(--light-gray);
        }
        
        .upload-area:hover, .upload-area.dragover {
            border-color: var(--primary-color);
            background-color: rgba(62, 140, 232, 0.05);
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
        
        /* Dermatology-specific elements */
        .body-map {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        
        .lesion-characteristic {
            margin-bottom: 10px;
        }
        
        .color-select {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }
        
        .color-option {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .color-option:hover {
            transform: scale(1.1);
        }
        
        .color-option.selected {
            border-color: black;
        }
        
        .skin-type-selector {
            margin-bottom: 20px;
        }
        
        .lesion-location-map {
            background-image: url('/api/placeholder/400/320');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            height: 300px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .body-region-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .body-region-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .body-region-btn:hover {
            background-color: var(--accent-color);
        }
        
        .body-region-btn.selected {
            background-color: var(--primary-color);
            color: white;
        }
        
        .lesion-size-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ai-analysis-panel {
            background-color: #f0f8ff;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-top: 15px;
            border-radius: 0 4px 4px 0;
        }
        
        .ai-confidence {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .ai-confidence-bar {
            flex-grow: 1;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 0 10px;
            overflow: hidden;
        }
        
        .ai-confidence-fill {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 5px;
        }
        
        .dermoscopy-features {
            margin-top: 15px;
        }
        
        .feature-tag {
            display: inline-block;
            padding: 2px 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.85rem;
        }
        
        .feature-tag.present {
            background-color: #d1e7ff;
            border-left: 3px solid var(--primary-color);
        }
        
        .biopsy-recommendation {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .biopsy-high {
            background-color: #ffebeb;
            border-left: 4px solid #dc3545;
            color: #dc3545;
        }
        
        .biopsy-medium {
            background-color: #fff7e6;
            border-left: 4px solid #fd7e14;
            color: #fd7e14;
        }
        
        .biopsy-low {
            background-color: #e6fff2;
            border-left: 4px solid #198754;
            color: #198754;
        }
        
        .differential-diagnosis {
            margin-top: 15px;
        }
        
        .differential-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
        <div class="container">
         <div class="container text-center custom-top-spacing" >
    <h3 class="fw-bold" style="color: var(--primary-color);">
        <i class="fas fa-microscope me-2"></i>
        Analyseur d'Images Dermatologiques
    </h3>
    <p class="lead text-muted">Analyse intelligente des images cutanées...</p>
</div>
        </div>

    <div class="container main-content">
        <div class="row">
            <div class="col-md-12">
                <div id="upload-section">
                    <form id="upload-form" enctype="multipart/form-data">
                        <div class="card">
                            <div class="card-header">
                                Type d'Examen Dermatologique
                            </div>
                            <div class="card-body">
                                <div class="study-type-selector">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeClinical" value="clinical" checked>
                                        <label class="form-check-label" for="studyTypeClinical">Photographie Clinique</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeDermoscopy" value="dermoscopy">
                                        <label class="form-check-label" for="studyTypeDermoscopy">Dermoscopie</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeWoodLamp" value="woodlamp">
                                        <label class="form-check-label" for="studyTypeWoodLamp">Lampe de Wood</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="studyType" id="studyTypeConfocal" value="confocal">
                                        <label class="form-check-label" for="studyTypeConfocal">Microscopie Confocale</label>
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
                                            <label for="patientGender" class="form-label">Genre</label>
                                            <select class="form-select" id="patientGender" name="patient_gender">
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
                                            <label for="skinType" class="form-label">Type de Peau (Fitzpatrick)</label>
                                            <select class="form-select" id="skinType" name="skin_type">
                                                <option value="" selected>Sélectionner</option>
                                                <option value="type1">Type I - Blanche, très claire</option>
                                                <option value="type2">Type II - Blanche, claire</option>
                                                <option value="type3">Type III - Beige</option>
                                                <option value="type4">Type IV - Olive, modérément brune</option>
                                                <option value="type5">Type V - Brune</option>
                                                <option value="type6">Type VI - Noire</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="lesionDuration" class="form-label">Durée de la Lésion</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="lesionDuration" name="lesion_duration" min="0">
                                                <select class="form-select" id="durationUnit" name="duration_unit">
                                                    <option value="days">Jours</option>
                                                    <option value="weeks">Semaines</option>
                                                    <option value="months">Mois</option>
                                                    <option value="years">Années</option>
                                                </select>
                                            </div>
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
                                    <h5>Glissez-déposez vos images dermatologiques ici</h5>
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
                                        <option value="new_lesion">Nouvelle lésion</option>
                                        <option value="changing_lesion">Lésion qui évolue</option>
                                        <option value="mole_check">Contrôle de nævus</option>
                                        <option value="skin_cancer_screening">Dépistage de cancer cutané</option>
                                        <option value="rash">Éruption cutanée</option>
                                        <option value="acne">Acné</option>
                                        <option value="psoriasis">Psoriasis</option>
                                        <option value="eczema">Eczéma</option>
                                        <option value="other">Autre</option>
                                    </select>
                                    
                                    <div class="mb-3">
                                        <label for="textInput" class="form-label">Informations cliniques supplémentaires :</label>
                                        <div class="input-group">
                                            <textarea class="form-control" id="textInput" name="text_input" rows="3" placeholder="Exemple : Patient de 45 ans avec apparition d'une lésion pigmentée sur le dos depuis 3 mois. Antécédents familiaux de mélanome..."></textarea>
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
                    
                    <div class="loading text-center mt-4" id="loading" style="display: none;">
                        <div class="spinner"></div>
                        <h5>Analyse des images dermatologiques</h5>
                        <p class="text-muted">Cela peut prendre quelques instants...</p>
                    </div>
                </div>
                
                <div id="results-section" class="mt-4" style="display: none;">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            Résultats de l'Analyse Médicale
                            <button id="edit-mode-toggle" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Mode Édition
                            </button>
                        </div>
                        <div class="card-body" id="content">
                            <div class="row">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Cache frequently used elements
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');
    const uploadForm = document.getElementById('upload-form');
    const loadingIndicator = document.getElementById('loading');
    const uploadSection = document.getElementById('upload-section');
    const resultsSection = document.getElementById('results-section');
    const newAnalysisBtn = document.getElementById('new-analysis-btn');
    const patientStatus = document.getElementById('patientStatus');
    const studyTypeRadios = document.querySelectorAll('input[name="studyType"]');
    const MAX_FILES = 6; // Set maximum files limit
    
    // Show pregnancy info section when patient status is set to pregnant (if element exists)
    if (patientStatus) {
        const pregnancyInfo = document.querySelector('.pregnancy-info');
        if (pregnancyInfo) {
            patientStatus.addEventListener('change', function() {
                if (this.value === 'pregnant') {
                    pregnancyInfo.style.display = 'block';
                } else {
                    pregnancyInfo.style.display = 'none';
                }
            });
        }
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
        if (studyType === 'clinical') {
            addOption(clinicalIndication, 'routine_checkup', 'Examen de routine');
            addOption(clinicalIndication, 'new_lesion', 'Nouvelle lésion');
            addOption(clinicalIndication, 'changing_lesion', 'Lésion qui évolue');
            addOption(clinicalIndication, 'mole_check', 'Contrôle de nævus');
            addOption(clinicalIndication, 'skin_cancer_screening', 'Dépistage de cancer cutané');
            addOption(clinicalIndication, 'rash', 'Éruption cutanée');
        } else if (studyType === 'dermoscopy') {
            addOption(clinicalIndication, 'mole_check', 'Contrôle de nævus');
            addOption(clinicalIndication, 'melanoma_screening', 'Dépistage de mélanome');
            addOption(clinicalIndication, 'pigmented_lesion', 'Lésion pigmentée');
            addOption(clinicalIndication, 'vascular_lesion', 'Lésion vasculaire');
        } else if (studyType === 'woodlamp') {
            addOption(clinicalIndication, 'fungal_infection', 'Infection fongique');
            addOption(clinicalIndication, 'vitiligo', 'Vitiligo');
            addOption(clinicalIndication, 'pigmentation_disorder', 'Trouble de la pigmentation');
        } else if (studyType === 'confocal') {
            addOption(clinicalIndication, 'melanoma_assessment', 'Évaluation de mélanome');
            addOption(clinicalIndication, 'basal_cell_carcinoma', 'Carcinome basocellulaire');
            addOption(clinicalIndication, 'squamous_cell_carcinoma', 'Carcinome épidermoïde');
        }
        
        // Always add common options
        addOption(clinicalIndication, 'acne', 'Acné');
        addOption(clinicalIndication, 'psoriasis', 'Psoriasis');
        addOption(clinicalIndication, 'eczema', 'Eczéma');
        addOption(clinicalIndication, 'other', 'Autre');
    }
    
    function addOption(selectElement, value, text) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        selectElement.add(option);
    }
    
    // Set up body region selector
    const bodyRegionBtns = document.querySelectorAll('.body-region-btn');
    const selectedRegionInput = document.getElementById('selectedRegion');
    
    bodyRegionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            bodyRegionBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedRegionInput.value = this.textContent.trim();
        });
    });
    
    // Set up color selector
    const colorOptions = document.querySelectorAll('.color-option');
    const selectedColorsInput = document.getElementById('selectedColors');
    let selectedColorsList = [];
    
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            
            if (this.classList.contains('selected')) {
                this.classList.remove('selected');
                selectedColorsList = selectedColorsList.filter(c => c !== color);
            } else {
                this.classList.add('selected');
                selectedColorsList.push(color);
            }
            
            selectedColorsInput.value = selectedColorsList.join(',');
        });
    });
    
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
        formData.append('speciality', 'Dermatologue');

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

dropArea.addEventListener('dragover', e => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('dragover');
});

dropArea.addEventListener('drop', e => {
    e.preventDefault();
    dropArea.classList.remove('dragover');
    
    if (e.dataTransfer.files.length > 0) {
        handleFiles(e.dataTransfer.files);
    }
});

uploadForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate form
    if (!validateForm()) {
        return false;
    }
    
    // Show loading indicator
    loadingIndicator.style.display = 'block';
    uploadSection.style.display = 'none';
    
    // Create FormData and submit
    const formData = new FormData(this);
    
    fetch('/submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading
        loadingIndicator.style.display = 'none';
        
        if (data.success) {
            // Show results section
            resultsSection.style.display = 'block';
            
            // Fill in the results data
            document.getElementById('result-image').src = '/' + data.analysis.image_path;
            document.getElementById('clinical-query').textContent = data.analysis.clinical_query;
            document.getElementById('impression').textContent = data.analysis.impression;
            
            // Populate findings list
            const findingsList = document.getElementById('findings');
            findingsList.innerHTML = '';
            data.analysis.findings.forEach(finding => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = finding;
                findingsList.appendChild(li);
            });
            
            // Populate diagnosis list
            const diagnosisList = document.getElementById('diagnosis');
            diagnosisList.innerHTML = '';
            data.analysis.diagnosis.forEach(diagnosis => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = diagnosis;
                diagnosisList.appendChild(li);
            });
            
            // Populate recommendations list
            const recommendationsList = document.getElementById('recommendations');
            recommendationsList.innerHTML = '';
            data.analysis.recommendations.forEach(recommendation => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = recommendation;
                recommendationsList.appendChild(li);
            });
        } else {
            // Show error
            alert("Erreur lors de l'analyse: " + data.error);
            uploadSection.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingIndicator.style.display = 'none';
        uploadSection.style.display = 'block';
        alert("Une erreur s'est produite lors de l'analyse. Veuillez réessayer.");
    });
});

function validateForm() {
    // Check if at least one file is selected
    if (fileInput.files.length === 0) {
        alert("Veuillez sélectionner au moins une image à analyser.");
        return false;
    }
    
    // Check if lesion location is selected
    if (!selectedRegionInput.value) {
        alert("Veuillez sélectionner la localisation de la lésion.");
        return false;
    }
    
    // Check if lesion type is selected
    const lesionTypeSelected = document.querySelector('input[name="lesionType"]:checked');
    if (!lesionTypeSelected) {
        alert("Veuillez sélectionner le type de lésion.");
        return false;
    }
    
    return true;
}

function handleFiles(files) {
    if (files.length > MAX_FILES) {
        alert(`Vous ne pouvez télécharger que ${MAX_FILES} images à la fois.`);
        return;
    }
    
    preview.innerHTML = ''; // Clear preview area
    
    // Process each file
    Array.from(files).forEach(file => {
        if (!file.type.match('image.*')) {
            alert(`Le fichier "${file.name}" n'est pas une image.`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = document.createElement('div');
            previewContainer.className = 'preview-item';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.title = file.name;
            
            const nameLabel = document.createElement('div');
            nameLabel.className = 'filename';
            nameLabel.textContent = file.name;
            
            previewContainer.appendChild(img);
            previewContainer.appendChild(nameLabel);
            preview.appendChild(previewContainer);
        };
        
        reader.readAsDataURL(file);
    });
    
    // Update file input
    fileInput.files = files;
}

fileInput.addEventListener('change', function() {
    handleFiles(this.files);
});

newAnalysisBtn.addEventListener('click', function() {
    // Reset the form and show upload section again
    uploadForm.reset();
    resultsSection.style.display = 'none';
    uploadSection.style.display = 'block';
    preview.innerHTML = '';
    
    // Clear active states
    bodyRegionBtns.forEach(btn => btn.classList.remove('active'));
    colorOptions.forEach(option => option.classList.remove('selected'));
    selectedColorsList = [];
});

const editModeToggle = document.getElementById('edit-mode-toggle');
const editableSections = document.querySelectorAll('.editable-section');
const saveAnalysisBtn = document.getElementById('save-analysis-btn');
const addButtons = document.querySelectorAll('#add-finding, #add-diagnosis, #add-recommendation');

editModeToggle.addEventListener('click', function() {
    const isEditMode = this.classList.contains('active');
    
    if (isEditMode) {
        // Disable edit mode
        this.classList.remove('active');
        this.innerHTML = '<i class="fas fa-edit me-1"></i>Mode Édition';
        editableSections.forEach(section => {
            section.setAttribute('contenteditable', 'false');
        });
        saveAnalysisBtn.style.display = 'none';
        addButtons.forEach(btn => btn.style.display = 'none');
    } else {
        // Enable edit mode
        this.classList.add('active');
        this.innerHTML = '<i class="fas fa-times me-1"></i>Quitter l\'édition';
        editableSections.forEach(section => {
            section.setAttribute('contenteditable', 'true');
        });
        saveAnalysisBtn.style.display = 'block';
        addButtons.forEach(btn => btn.style.display = 'inline-block');
    }
});

document.getElementById('add-finding').addEventListener('click', function() {
    addListItem('findings');
});

document.getElementById('add-diagnosis').addEventListener('click', function() {
    addListItem('diagnosis');
});

document.getElementById('add-recommendation').addEventListener('click', function() {
    addListItem('recommendations');
});

function addListItem(listId) {
    const list = document.getElementById(listId);
    const li = document.createElement('li');
    li.className = 'list-group-item';
    li.setAttribute('contenteditable', 'true');
    li.textContent = 'Nouveau...';
    list.appendChild(li);
    li.focus();
}

    </script>
</body>
</html>
                 




                
@endsection