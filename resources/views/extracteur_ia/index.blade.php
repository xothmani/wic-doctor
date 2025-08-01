@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediSmart AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7f5 100%);
            min-height: 100vh;
            padding: 20px;
            color: #001f3f;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 20px;
        }

        .header {
            background: #515250;
            color: white;
            padding: 15px 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            padding: 40px 30px;
        }

        .method-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 31, 63, 0.1);
            transition: all 0.3s ease;
            border: 3px solid transparent;
            text-align: center;
        }

        .method-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 31, 63, 0.15);
            border-color: #11b8aa;
        }

        .method-icon {
            width: 70px;
            height: 70px;
            background: #001f3f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.5rem;
        }

        .method-title {
            font-size: 1.5rem;
            color: #001f3f;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .method-description {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .upload-area {
            border: 3px dashed #001f3f;
            border-radius: 15px;
            padding: 30px;
            background: #ebf5ffff;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            background: #ebf5ffff;
            border-color: #001f3f;
        }

        .upload-area.dragover {
            background: #ebf5ffff;
            border-color: #001f3f;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3rem;
            color: #001f3f;
            margin-bottom: 15px;
        }

        .upload-area p {
            margin: 5px 0;
            color: #2c3e50;
            font-size: 1rem;
        }

        .upload-area small {
            color: #4a5568;
            font-size: 0.8rem;
        }

        .file-input {
            display: none;
        }

        .audio-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .record-btn {
            background: #e53e3e;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .record-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.4);
        }

        .record-btn.recording {
            background: #fc8181;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .time-display {
            font-size: 0.9rem;
            color: #2c3e50;
            margin-top: 5px;
        }

        .btn {
            background: #001f3f;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 5px;
            text-decoration: none;

        }

        .btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-success {
            background: #ffffffff;
            color: #001f3f;
            border: 1px solid #001f3f;
        }

        .btn-success:hover {
            background: #001f3f;
            transform: translateY(-2px);
                        color: white;

        }

        .btn-primary {
            background: #001f3f;
            color: white;
        }

        .btn-primary:hover {
            background: #003366;
            transform: translateY(-2px);
                        color: white;

        }

        .btn-warning {
            background: #ffffffff;
            color: #11b8aa;
            border: 1px solid rgb(17, 184, 170);
        }

        .btn-warning:hover {
            background: #11b8aa;
            transform: translateY(-2px);
            color: white;
        }

        .results-section {
            margin-bottom: 40px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #f8fffe;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            border: 1px solid #11b8aa;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #001f3f;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #556677;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 1.8rem;
            color: #001f3f;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .patient-card {
            background: #fff;
            border: 1px solid #e6f4f3;
            border-radius: 15px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 31, 63, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .patient-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 31, 63, 0.12);
        }

        .patient-header {
            background: #001f3f;
            color: white;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .patient-name {
            font-size: 1.3rem;
            font-weight: 600;
        }
        .patient-info {
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .info-section {
            flex: 1;
            min-width: 250px;
            background: #f8fffe;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #001f3f;
        }
        .section-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: #001f3f;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field-group {
            margin-bottom: 15px;
        }

        .field-label {
            font-size: 0.85rem;
            color: #556677;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
            display: block;
        }

        .field-value {
            font-size: 1rem;
            color: #001f3f;
            font-weight: 500;
            min-height: 24px;
            padding: 8px 0;
        }

        .field-input {
            width: 100%;
            border: 2px solid #e6f4f3;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            background: white;
        }

        .field-input:focus {
            outline: none;
            border-color: #001f3f;
            box-shadow: 0 0 0 3px rgba(17, 184, 170, 0.1);
        }

        .field-input.select {
            cursor: pointer;
        }

        .field-input.textarea {
            resize: vertical;
            min-height: 80px;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #556677;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ccdddd;
        }

        .no-data h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e6f4f3;
        }

        .processing-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 31, 63, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .processing-overlay.show {
            display: flex;
        }

        .processing-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 31, 63, 0.2);
        }

        .processing-content i {
            font-size: 2rem;
            color: #001f3f;
            margin-bottom: 15px;
        }

        .status-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .status-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .status-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }

        .save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #001f3f;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .save-indicator.show {
            opacity: 1;
        }

        .edit-mode {
            background: #fff8e1 !important;
            border-left-color: #11b8aa !important;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .content {
                padding: 30px 20px;
            }

            .methods-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .method-card {
                padding: 20px;
            }

            .patient-info {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-notes-medical"></i> Extracteur IA</h1>
            <p>Système Intelligent d'Extraction d'Informations Patients</p>
        </div>

        <div class="methods-grid">
            <!-- Méthode 1: Upload d'Image avec OCR -->
            <div class="method-card">
                <div class="method-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3 class="method-title">Upload d'Image & OCR</h3>
                <p class="method-description">
                    Téléchargez des photos de documents médicaux, formulaires ou notes. Notre IA extraira et numérisera toutes les informations patient automatiquement.
                </p>
                <div style="height: 25px;"></div>

                <div class="upload-area" onclick="document.getElementById('imageInput').click()">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <p>Cliquez pour télécharger des images ou glissez-déposez</p>
                    <small>Supporte les formats JPG, PNG, HEIC</small>
                </div>
                <input type="file" id="imageInput" class="file-input" accept="image/*" multiple>

                <div id="imagePreviewContainer" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
                <div style="height: 5px;"></div>

                <div class="audio-controls">
                    <button class="btn" onclick="processImages()" id="processImagesBtn" disabled>
                        <i class="fas fa-magic"></i> Extraire les Informations
                    </button>
                </div>
            </div>

            <!-- Méthode 2: Upload de Document -->
            <div class="method-card">
                <div class="method-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="method-title">Traitement de Documents</h3>
                <p class="method-description">
                    Téléchargez des fichiers PDF ou Word contenant des informations patient. L'analyse avancée extrait instantanément les données structurées.
                </p>
                <br>
                <div style="height: 25px;"></div>

                <div class="upload-area" onclick="document.getElementById('docInput').click()">
                    <div class="upload-icon">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <p>Télécharger des documents PDF ou Word</p>
                    <small>Supporte les formats PDF, DOC, DOCX</small>
                </div>
                <div style="height: 5px;"></div>

                <input type="file" id="docInput" name="documents" class="file-input" accept=".pdf,.doc,.docx" multiple>
                <div class="audio-controls">
                    <button class="btn" onclick="processDocuments()" id="processDocsBtn" disabled>
                        <i class="fas fa-cogs"></i> Traiter les Documents
                    </button>
                </div>
            </div>

            <!-- Méthode 3: Enregistrement Vocal -->
            <div class="method-card">
                <div class="method-icon">
                    <i class="fas fa-microphone"></i>
                </div>
                <h3 class="method-title">Enregistrement Vocal</h3>
                <p class="method-description">
                    Enregistrez les informations patient verbalement. Notre IA transcrit et structure automatiquement les données en format numérique organisé.
                </p>
                <div style="height: 25px;"></div>

                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-waveform-lines" id="audioIcon"></i>
                    </div>
                    <p id="recordingStatus">Enregistrer les informations des patients</p>
                    <div class="time-display" id="timeDisplay" style="display: none;">00:00</div>
                    <small>Voix claire, résultat précis</small>
                </div>
                <div style="height: 4px;"></div>
                <div class="audio-controls">
                    <button class="record-btn" onclick="toggleRecording()" id="recordBtn">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <button class="btn" onclick="processAudio()" id="processAudioBtn" style="display: none;">
                        <i class="fas fa-brain"></i> Traiter l'Audio
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="container" id="resultsSection" style="display: none;">
        

            <div class="content">
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-number" id="processedCount">0</div>
                        <div class="stat-label">Fichiers traités</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="patientsCount">0</div>
                        <div class="stat-label">Patients détectés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="processedTime">-</div>
                        <div class="stat-label">Durée du traitement</div>
                    </div>
                </div>

                <div class="results-section">
                    <h2 class="section-title">
                        <i class="fas fa-users"></i> Données des patients
                    </h2>
                    <div id="resultsContainer">
                        <div class="no-data">
                            <i class="fas fa-spinner fa-spin"></i>
                            <h3>Chargement des résultats...</h3>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button onclick="toggleEditAll()" class="btn btn-warning" id="editAllBtn">
                        <i class="fas fa-edit"></i> Modifier tous les patients
                    </button>
                    <button onclick="confirmAction()" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Ajouter les patients
                    </button>
                    <a href="#" onclick="location.reload();" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Traiter d'autres fichiers
                    </a>
                </div>
            </div>
        </div>

        <!-- Processing Overlay -->
        <div class="processing-overlay" id="processingOverlay">
            <div class="processing-content">
                <i class="fas fa-brain fa-spin"></i>
                <h3>Traitement en cours...</h3>
                <p>Extraction et analyse des données médicales</p>
            </div>
        </div>

        <!-- Save Indicator -->
        <div class="save-indicator" id="saveIndicator">
            <i class="fas fa-check"></i> Modifications enregistrées automatiquement
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        let isRecording = false;
        let mediaRecorder;
        let audioChunks = [];
        let extractedData = null;
        let isEditMode = false;

        // Patient field definitions
        const patientFields = {
            personal: {
                title: 'Infos Personnelles',
                icon: 'fas fa-user',
                fields: {
                    prenom: { label: 'Prénom', type: 'text' },
                    nom_de_famille: { label: 'Nom de famille', type: 'text' },
                    sexe: { label: 'Sexe', type: 'select', options: ['Homme', 'Femme'] },
                    date_de_naissance: { label: 'Date de naissance', type: 'date' }
                }
            },
            contact: {
                title: 'Contact',
                icon: 'fas fa-phone',
                fields: {
                    email: { label: 'Email', type: 'email' },
                    telephone: { label: 'Téléphone', type: 'tel' },
                    fixe: { label: 'Téléphone fixe', type: 'tel' }
                }
            },
            medical: {
                title: 'Informations Médicales',
                icon: 'fas fa-heartbeat',
                fields: {
                    poids: { label: 'Poids (kg)', type: 'number' },
                    taille: { label: 'Taille (cm)', type: 'number' },
                    groupe_sanguin: { label: 'Groupe sanguin', type: 'select', options: ['','A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] },
                    cnam_assurance: { label: 'Couverture médicale', type: 'select', options: ['','CNAM', 'Assurance'] }
                }
            },
            history: {
                title: 'Historique Médical',
                icon: 'fas fa-history',
                fields: {
                    allergie: { label: 'Allergies', type: 'textarea' },
                    antecedent: { label: 'Antécédents médicaux', type: 'textarea' }
                }
            }
        };

        function showProcessingOverlay() {
            document.getElementById('processingOverlay').classList.add('show');
        }

        function hideProcessingOverlay() {
            document.getElementById('processingOverlay').classList.remove('show');
        }

        function showResults() {
            document.querySelector('.methods-grid').style.display = 'none';
            document.getElementById('resultsSection').style.display = 'block';
            document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        }

        function resetToUpload() {
            isEditMode = false;
            extractedData = null;
            window.location.href = '/';
        }

        function toggleEditAll() {
            isEditMode = !isEditMode;
            const editAllBtn = document.getElementById('editAllBtn');
            editAllBtn.innerHTML = isEditMode
                ? '<i class="fas fa-save"></i> Enregistrer'
                : '<i class="fas fa-edit"></i> Modifier tous les patients';

            if (!isEditMode && extractedData) {
                // Save changes to extractedData
                const patientCards = document.querySelectorAll('.patient-card');
                patientCards.forEach((card, index) => {
                    const patient = extractedData[index] || {};
                    Object.keys(patientFields).forEach(section => {
                        Object.keys(patientFields[section].fields).forEach(field => {
                            const input = card.querySelector(`[data-field="${field}"]`);
                            if (input) {
                                patient[field] = input.value || '';
                            }
                        });
                    });
                    extractedData[index] = patient;
                });

                // Show save indicator and status
                const saveIndicator = document.getElementById('saveIndicator');
                saveIndicator.classList.add('show');
                showStatus('Modifications enregistrées avec succès', 'success');
                setTimeout(() => {
                    saveIndicator.classList.remove('show');
                }, 3000);
            }

            // Re-render patient cards
            if (extractedData) {
                const container = document.getElementById('resultsContainer');
                container.innerHTML = '';
                const dataArray = Array.isArray(extractedData) ? extractedData : [extractedData];
                dataArray.forEach((patient, index) => {
                    const patientCard = createPatientCard(patient, index);
                    container.appendChild(patientCard);
                });
            } else {
                showStatus('Aucune donnée à modifier', 'error');
            }
        }

        async function confirmAction() {
            if (!extractedData) {
                showStatus('Aucune donnée à ajouter', 'error');
                return;
            }

            showProcessingOverlay();
            try {
                const response = await fetch('https://wicdialer.com/extract_ai/api/add_patients', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(extractedData)
                });

                const result = await response.json();
                if (result.success) {
                    showStatus('Patients ajoutés avec succès !', 'success');
                } else {
                    throw new Error(result.error || 'Erreur lors de l\'ajout des patients');
                }
            } catch (error) {
                console.error('Add patients error:', error);
                showStatus(`Échec de l'ajout des patients : ${error.message}`, 'error');
            } finally {
                hideProcessingOverlay();
            }
        }

        async function processImages() {
            console.log('processImages is called');
            const fileInput = document.getElementById('imageInput');
            const files = fileInput.files;

            if (files.length === 0) {
                showStatus('Veuillez sélectionner des images à traiter', 'error');
                return;
            }

            showProcessingOverlay();
            const startTime = Date.now();
            const processImagesBtn = document.getElementById('processImagesBtn');
            const originalText = processImagesBtn.innerHTML;
            processImagesBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            processImagesBtn.disabled = true;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('images', files[i]);
                console.log('Added file:', files[i].name);
            }

            try {
                const response = await fetch('https://wicdialer.com/extract_ai/api/process/images', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    throw new Error(`Invalid JSON response: ${responseText.substring(0, 50)}...`);
                }

                if (result.success) {
                    displayResults(result.data, result.processed_count, Date.now() - startTime);
                    showStatus('Images traitées avec succès !', 'success');
                } else {
                    throw new Error(result.error || 'Erreur lors du traitement');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showStatus(`Échec du traitement des images : ${error.message}`, 'error');
            } finally {
                hideProcessingOverlay();
                processImagesBtn.innerHTML = originalText;
                processImagesBtn.disabled = false;
            }
        }

        async function processDocuments() {
            console.log('Process Documents');
            const fileInput = document.getElementById('docInput');
            const files = fileInput.files;

            if (files.length === 0) {
                showStatus('Veuillez sélectionner des documents à traiter', 'error');
                return;
            }

            showProcessingOverlay();
            const startTime = Date.now();
            const processDocsBtn = document.getElementById('processDocsBtn');
            const originalText = processDocsBtn.innerHTML;
            processDocsBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            processDocsBtn.disabled = true;

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('documents', files[i]);
                console.log(`Document ${i + 1}:`, files[i].name, files[i].size, 'bytes');
            }

            try {
                const response = await fetch('https://wicdialer.com/extract_ai/api/process/documents', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    throw new Error(`Invalid JSON response: ${responseText.substring(0, 50)}...`);
                }

                if (result.success) {
                    displayResults(result.data, files.length, Date.now() - startTime);
                    showStatus('Documents traités avec succès !', 'success');
                } else {
                    throw new Error(result.error || 'Erreur lors du traitement');
                }
            } catch (error) {
                console.error('Document processing error:', error);
                showStatus(`Échec du traitement des documents : ${error.message}`, 'error');
            } finally {
                hideProcessingOverlay();
                processDocsBtn.innerHTML = originalText;
                processDocsBtn.disabled = false;
            }
        }

        async function toggleRecording() {
            console.log('🎯 toggleRecording called, isRecording:', isRecording);
            
            const recordBtn = document.getElementById('recordBtn');
            const processAudioBtn = document.getElementById('processAudioBtn');
            const recordingStatus = document.getElementById('recordingStatus');
            const audioIcon = document.getElementById('audioIcon');

            if (!isRecording) {
                console.log('▶️ Starting recording...');
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });
                    audioChunks = [];
                    mediaRecorder = new MediaRecorder(stream);
                    
                    console.log('🎙️ MediaRecorder created, state:', mediaRecorder.state);

                    mediaRecorder.ondataavailable = (event) => {
                        console.log('📦 Data available, size:', event.data.size);
                        if (event.data.size > 0) {
                            audioChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = () => {
                        console.log('📁 MediaRecorder onstop event fired');
                        
                        window.recordedBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        console.log('📊 Recorded blob size:', window.recordedBlob.size);

                        if (window.recordedBlob && window.recordedBlob.size > 0) {
                            console.log('✅ Recording complete. Ready to process...');
                            setTimeout(() => {
                                updateUIAfterRecording();
                            }, 100);
                        } else {
                            console.error('❌ Recording is empty.');
                            showStatus('Enregistrement vide ou échoué', 'error');
                            setTimeout(() => {
                                resetUIToInitialState();
                            }, 100);
                        }
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    console.log('🎙️ Recording started, state:', mediaRecorder.state);
                    
                    updateUIDuringRecording();
                    startTimer();
                } catch (err) {
                    console.error('Microphone access denied:', err);
                    showStatus('Accès au microphone refusé', 'error');
                }
            } else {
                console.log('🛑 Stopping recording...');
                
                recordBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                recordBtn.disabled = true;
                recordingStatus.textContent = 'Arrêt de l\'enregistrement...';
                
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    console.log('🛑 Stopping mediaRecorder, current state:', mediaRecorder.state);
                    mediaRecorder.stop();
                }
                
                if (mediaRecorder && mediaRecorder.stream) {
                    console.log('🛑 Stopping all tracks...');
                    mediaRecorder.stream.getTracks().forEach(track => track.stop());
                }
                
                isRecording = false;
                console.log('🛑 Recording stopped, isRecording set to false');
                clearInterval(timerInterval);
            }
        }

        function startTimer() {
            recordingStartTime = Date.now();
            document.getElementById('timeDisplay').style.display = 'block';
            timerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            document.getElementById('timeDisplay').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function updateUIAfterRecording() {
            console.log('🎨 Updating UI after recording...');
            
            const recordBtn = document.getElementById('recordBtn');
            const recordingStatus = document.getElementById('recordingStatus');
            const audioIcon = document.getElementById('audioIcon');
            const processAudioBtn = document.getElementById('processAudioBtn');

            recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            recordBtn.classList.remove('recording');
            recordBtn.disabled = false;
            recordingStatus.textContent = 'Enregistrement terminé - Prêt à traiter';
            audioIcon.className = 'fas fa-waveform-lines';
            audioIcon.style.color = '#28a745';
            audioIcon.style.animation = 'none';
            processAudioBtn.style.display = 'inline-block';
            console.log('🎨 UI update complete');
        }

        function resetUIToInitialState() {
            const recordBtn = document.getElementById('recordBtn');
            const recordingStatus = document.getElementById('recordingStatus');
            const audioIcon = document.getElementById('audioIcon');
            const processAudioBtn = document.getElementById('processAudioBtn');

            recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
            recordBtn.classList.remove('recording');
            recordingStatus.textContent = 'Enregistrer les informations des patients';
            audioIcon.className = 'fas fa-waveform-lines';
            audioIcon.style.color = '';
            audioIcon.style.animation = 'none';
            processAudioBtn.style.display = 'none';
        }

        async function processAudio() {
            console.log('Process audio function');
            if (!window.recordedBlob || window.recordedBlob.size === 0) {
                showStatus('Aucun audio enregistré', 'error');
                return;
            }

            showProcessingOverlay();
            const startTime = Date.now();
            const processAudioBtn = document.getElementById('processAudioBtn');
            const originalText = processAudioBtn.innerHTML;
            processAudioBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            processAudioBtn.disabled = true;

            const formData = new FormData();
            formData.append('audio', window.recordedBlob, 'recording.wav');

            try {
                const response = await fetch('https://wicdialer.com/extract_ai/api/process/audio', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    throw new Error(`Invalid JSON response: ${responseText.substring(0, 50)}...`);
                }

                if (result.success) {
                    displayResults(result.data, 1, Date.now() - startTime);
                    showStatus('Audio traité avec succès !', 'success');
                    resetUIToInitialState();
                } else {
                    throw new Error(result.error || 'Erreur lors du traitement');
                }
            } catch (error) {
                console.error('Audio processing error:', error);
                showStatus(`Échec de l'envoi de l'audio au backend : ${error.message}`, 'error');
            } finally {
                processAudioBtn.innerHTML = originalText;
                processAudioBtn.disabled = false;
                hideProcessingOverlay();
            }
        }

        function displayResults(data, filesCount, processingTime) {
            extractedData = data;
            document.getElementById('processedCount').textContent = filesCount;
            document.getElementById('patientsCount').textContent = Array.isArray(data) ? data.length : 1;
            document.getElementById('processedTime').textContent = `${Math.round(processingTime / 1000)}s`;

            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';

            if (!data || (Array.isArray(data) && data.length === 0)) {
                container.innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-info-circle"></i>
                        <h3>Aucun patient détecté</h3>
                        <p>Aucune information patient n'a été trouvée dans les fichiers traités.</p>
                    </div>
                `;
            } else {
                const dataArray = Array.isArray(data) ? data : [data];
                dataArray.forEach((patient, index) => {
                    const patientCard = createPatientCard(patient, index);
                    container.appendChild(patientCard);
                });
            }

            showResults();
        }

        function createPatientCard(patient, index) {
            const card = document.createElement('div');
            card.className = 'patient-card';
            const patientName = patient.prenom || patient.nom_de_famille ? `${patient.prenom || ''} ${patient.nom_de_famille || ''}`.trim() : `Patient ${index + 1}`;

            let patientInfoHTML = '';
            Object.keys(patientFields).forEach(section => {
                const { title, icon, fields } = patientFields[section];
                let sectionHTML = `<div class="info-section ${isEditMode ? 'edit-mode' : ''}"><div class="section-header"><i class="${icon}"></i> ${title}</div>`;
                Object.keys(fields).forEach(field => {
                    const fieldDef = fields[field];
                    const value = patient[field] || '';
                    let fieldHTML = `<div class="field-group"><span class="field-label">${fieldDef.label}</span>`;

                    if (isEditMode) {
                        if (fieldDef.type === 'select') {
                            let optionsHTML = fieldDef.options.map(option => 
                                `<option value="${option}" ${value === option ? 'selected' : ''}>${option}</option>`
                            ).join('');
                            fieldHTML += `<select class="field-input select" data-field="${field}">${optionsHTML}</select>`;
                        } else if (fieldDef.type === 'textarea') {
                            fieldHTML += `<textarea class="field-input textarea" data-field="${field}">${value}</textarea>`;
                        } else {
                            fieldHTML += `<input type="${fieldDef.type}" class="field-input" data-field="${field}" value="${value}">`;
                        }
                    } else {
                        fieldHTML += `<div class="field-value">${value || 'Non renseigné'}</div>`;
                    }
                    fieldHTML += `</div>`;
                    sectionHTML += fieldHTML;
                });
                sectionHTML += `</div>`;
                patientInfoHTML += sectionHTML;
            });

            card.innerHTML = `
                <div class="patient-header">
                    <div class="patient-name">${patientName}</div>
                </div>
                <div class="patient-info">
                    ${patientInfoHTML}
                </div>
            `;

            return card;
        }

        
        function updatePatientField(patientIndex, fieldKey, value) {
            // Update the data
            extractedData[patientIndex][fieldKey] = value || 'pas specifie';
            
            // Update the display
            const displayElement = document.getElementById(`display_${patientIndex}_${fieldKey}`);
            const isEmpty = !value || value === 'pas specifie';
            displayElement.innerHTML = createFieldDisplay(value, isEmpty);
            
            // Update patient name in header if name fields changed
            if (fieldKey === 'prenom' || fieldKey === 'nom_de_famille') {
                const patientName = getPatientDisplayName(extractedData[patientIndex]);
                const card = document.querySelector(`[data-patient-id="${patientIndex}"]`);
                const nameElement = card.querySelector('.patient-header h3');
                nameElement.innerHTML = `<i class="fas fa-user"></i> ${patientName}`;
            }
            
            // Show save indicator
            showSaveIndicator();
        }

        function formatFieldName(fieldName) {
            return fieldName.charAt(0).toUpperCase() + fieldName.slice(1).replace(/_/g, ' ');
        }

        function showStatus(message, type) {
            const existingStatus = document.querySelector('.status-message');
            if (existingStatus) {
                existingStatus.remove();
            }

            const statusDiv = document.createElement('div');
            statusDiv.className = `status-message status-${type}`;
            statusDiv.textContent = message;

            document.body.appendChild(statusDiv);

            setTimeout(() => {
                statusDiv.remove();
            }, 5000);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            // Ensure results section is hidden on load
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('resultsContainer').innerHTML = `
                <div class="no-data">
                    <i class="fas fa-spinner fa-spin"></i>
                    <h3>Chargement des résultats...</h3>
                </div>
            `;

            const imageInput = document.getElementById('imageInput');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const docInput = document.getElementById('docInput');

            if (imageInput && previewContainer) {
                imageInput.addEventListener('change', () => {
                    previewContainer.innerHTML = '';
                    const files = imageInput.files;
                    document.getElementById('processImagesBtn').disabled = files.length === 0;

                    if (files.length === 0) {
                        previewContainer.innerHTML = '<p>Aucune image sélectionnée</p>';
                        return;
                    }

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        if (!file.type.startsWith('image/')) continue;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = file.name;
                            img.title = file.name;
                            img.style.width = '150px';
                            img.style.height = 'auto';
                            img.style.border = '1px solid #ddd';
                            img.style.borderRadius = '4px';
                            img.style.padding = '3px';
                            previewContainer.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            if (docInput) {
                docInput.addEventListener('change', () => {
                    document.getElementById('processDocsBtn').disabled = docInput.files.length === 0;
                });
            }

            // Drag and Drop functionality
            document.querySelectorAll('.upload-area').forEach(area => {
                area.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    area.classList.add('dragover');
                });

                area.addEventListener('dragleave', () => {
                    area.classList.remove('dragover');
                });

                area.addEventListener('drop', (e) => {
                    e.preventDefault();
                    area.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    const inputId = area.querySelector('input[type="file"]').id;
                    document.getElementById(inputId).files = files;
                    if (inputId === 'imageInput') {
                        imageInput.dispatchEvent(new Event('change'));
                    } else if (inputId === 'docInput') {
                        docInput.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
        async function confirmAction() {
            try {
                // Check if data exists
                if (!extractedData || extractedData.length === 0) {
                    alert('Aucune donnée à confirmer');
                    return;
                }

                // Validate required fields
                const requiredFields = ['prenom', 'nom_de_famille', 'date_de_naissance', 'Téléphone'];
                const patientFields = {
                    personal: {
                        fields: {
                            prenom: { label: 'Prénom' },
                            nom_de_famille: { label: 'Nom de famille' },
                            date_de_naissance: { label: 'Date de naissance' }
                        }
                    },
                    contact: {
                        fields: {
                            telephone: { label: 'Téléphone' }
                        }
                    }
                };
                let validationErrors = [];
                extractedData.forEach((patient, index) => {
                    requiredFields.forEach(field => {
                        if (!patient[field] || patient[field].trim() === '') {
                            const fieldLabel = patientFields.personal.fields[field]?.label || 
                                            patientFields.contact.fields[field]?.label || field;
                            validationErrors.push(`Patient ${index + 1}: ${fieldLabel} est requis`);
                        }
                    });
                });

                if (validationErrors.length > 0) {
                    alert(validationErrors.join('; '));
                    return;
                }

                // Show loading state
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                button.disabled = true;
                const formData = new FormData();
                userID=420
                const DoctorID = {{ $doctorId }};
                // Make API call to Flask backend with extracted data
                const response = await fetch('https://wicdialer.com/extract_ai/api/confirm', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        formdata: {
                            userID: userID,
                            DoctorID: DoctorID
                        },
                        data: extractedData,
                        timestamp: new Date().toISOString(),
                        filename: `patients_data_${new Date().toISOString().split('T')[0]}.json`
                    })
                });

                if (response.ok) {
                    const result = await response.json();

                    // Handle successful response - show French success message
                    button.innerHTML = '<i class="fas fa-check"></i> Confirmé!';
                    console.log('Success:', result);
                    showStatus('Patients ajoutés avec succès !', 'success');

                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);

                    return; // Exit early on success
                } else {
                    // Handle error response
                    const error = await response.json();
                    alert('Erreur: ' + (error.message || 'Quelque chose s\'est mal passé'));
                    console.error('Error:', error);
                }
            } catch (error) {
                // Handle network or other errors
                console.error('Erreur réseau:', error);
                alert('Erreur réseau: Impossible de se connecter au serveur');
            } finally {
                // Reset button state only if not already handled in success
                const button = event.target.closest('button');
                if (!button.innerHTML.includes('Confirmé!')) {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            }
        }
    </script>
</body>
</html>

@endsection