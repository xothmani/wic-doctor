@extends('layouts.app') {{-- Si vous utilisez un layout --}}
@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Médicale - Sélection Patient</title>
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
   
  body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                min-height: 100vh;
                padding: 10px;
            }

        .container-form {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: rgb(0, 31, 63);
            color: white;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/><circle cx="20" cy="20" r="1.5" fill="white" opacity="0.08"/><circle cx="80" cy="30" r="1" fill="white" opacity="0.06"/></svg>') repeat;
            animation: float 20s linear infinite;
            pointer-events: none;
        }

        @keyframes float {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        .header-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .header-title {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }

        .consultation-timer {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #11b8aa 0%, #0d9a8e 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .timer-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: blink 1s infinite;
            flex-shrink: 0;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        .timer-text {
            white-space: nowrap;
            font-family: 'Courier New', monospace;
        }

        .content {
            padding: 30px;
        }

        .patient-selection {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 1000; /* Z-index élevé pour la section de sélection */
        }

        .patient-info-display {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            color: black;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .patient-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #11b8aa 0%, #0d9a8e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            font-weight: bold;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .patient-details h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .patient-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(6, 56, 219, 0.1);
        }

        .form-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            position: relative;
            z-index: 1; /* Z-index bas pour les sections du formulaire */
        }

        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #053178;
            color: white;
            font-size: 12px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #fd8e26;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 10000; /* Z-index très élevé pour la liste d'autocomplétion */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            display: none;
        }

        .autocomplete-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s ease;
        }

        .autocomplete-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .history-section {
            grid-column: 1 / -1;
        }

        .consultation-section {
            grid-column: 1 / -1;
        }

        .consultation-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .field-with-voice {
            position: relative;
        }

        .voice-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #001f3f;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .voice-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .ai-section {
            background: rgba(255, 255, 255, 0.95);
            color: black;
            grid-column: 1 / -1;
            margin-top: 20px;
        }

        .ai-section .section-title {
            color: black;
        }

        .ai-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
        }

        .ai-controls {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(45deg, #2c3e50, #34495e);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .history-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #fd8e26;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .history-date {
            background: #fd8e26;
            color: white;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .floating-action:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(255, 107, 107, 0.5);
        }

        .no-history {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-sections {
                grid-template-columns: 1fr;
            }
            
            .consultation-fields {
                grid-template-columns: 1fr;
            }
            
            .patient-info {
                flex-direction: column;
                text-align: center;
            }
            
            .patient-meta {
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .header {
                padding: 15px 10px;
                min-height: 120px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .header-content {
                order: 2;
                margin-top: 10px;
            }
            
            .header-title {
                font-size: 18px;
                margin-bottom: 6px;
                padding-right: 0;
                line-height: 1.2;
            }
            
            .header-subtitle {
                font-size: 12px;
                padding-right: 0;
                line-height: 1.3;
            }
            
            .consultation-timer {
                position: static;
                order: 1;
                align-self: flex-end;
                margin: 0 0 10px 0;
                padding: 5px 10px;
                font-size: 11px;
                gap: 5px;
                border-radius: 20px;
                min-width: 90px;
            }
            
            .timer-dot {
                width: 5px;
                height: 5px;
            }
        }
          .history-timeline {
            position: relative;
            padding-left: 0;
        }

        .history-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            border-left-color: #5a67d8;
        }

        .history-card:last-child {
            margin-bottom: 0;
        }

        .history-date {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            color: #667eea;
            font-weight: 600;
            font-size: 14px;
        }

        .history-date i {
            margin-right: 8px;
            font-size: 16px;
        }

        .history-content {
            color: #4a5568;
            line-height: 1.6;
            font-size: 15px;
        }

        .history-content p {
            margin: 0 0 12px 0;
        }

        .history-content p:last-child {
            margin-bottom: 0;
        }

        .no-history {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #e2e8f0;
        }

        .no-history i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
        }

        /* Toutes les cartes ont la même couleur */
        .history-card,
        .history-card.recent,
        .history-card.important,
        .history-card.urgent {
            border-left-color: #667eea;
        }

        .history-card:hover,
        .history-card.recent:hover,
        .history-card.important:hover,
        .history-card.urgent:hover {
            border-left-color: #5a67d8;
        }

        /* Animation d'apparition */
        .history-card {
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }
        
    </style>
</head>
<body>
    <div class="container-form">
        <div class="header">
        <div class="consultation-timer">
            <div class="timer-dot"></div>
            <span class="timer-text">Durée: <span id="timer">00:04:37</span></span>
        </div>
        <div class="header-content">
            <div class="header-title">Génerer un rapport médical basée sur une consultation</div>
            <div class="header-subtitle">Interface de création de rapport médical</div>
        </div>
    </div>

        <div class="content">
            <!-- Sélection du Patient -->
            <div class="patient-selection">
                <div class="section-title">
                    <div class="section-icon"><i class="fas fa-user"></i></div>

                    Sélection du Patient
                </div>
        <div class="form-group">
    <label class="form-label">Nom complet <span class="required">*</span></label>
    <input type="text" id="patientSearch" class="form-input" placeholder="Rechercher un patient..." autocomplete="off" required>
    <ul id="autocompleteList" class="autocomplete-list"></ul>
</div>
            </div>

            <!-- Informations Patient (affiché après sélection) -->

<div class="patient-info-display" id="patientInfoDisplay" style="display: none;">
    <div class="patient-info">
        <div class="patient-avatar" id="patientAvatar">MM</div>
        <div class="patient-details">
            <h2 id="patientName">Nom</h2>
            <div class="patient-meta">
                <div class="meta-item" id="patientBirthdate"><i class="fas fa-calendar-alt"></i> </div>
                <div class="meta-item" id="patientAge"><i class="fas fa-user"></i></div>
               <div class="meta-item" id="patientGender">
    <!-- le genre sera injecté ici -->
</div>

            </div>
        </div>
    </div>
</div>

            <div class="form-sections">
                <!-- Informations Physiques -->
                <div class="section">
                    <div class="section-title">
                        <div class="section-icon"><i class="fas fa-balance-scale"></i></div>

                        Informations Physiques
                    </div>
                    <div class="form-group">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" id="weight" class="form-input" placeholder="Insérer le poids">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Taille (cm)</label>
                        <input type="number" id="height" class="form-input" placeholder="Insérer la taille">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Groupe sanguin</label>
                        <select id="bloodType" class="form-select">
                            <option>Sélectionner un groupe sanguin</option>
                            <option>A+</option>
                            <option>A-</option>
                            <option>B+</option>
                            <option>B-</option>
                            <option>AB+</option>
                            <option>AB-</option>
                            <option>O+</option>
                            <option>O-</option>
                        </select>
                    </div>
                </div>

                <!-- Informations Médicales -->
                <div class="section">
                   <div class="section-title">
    <div class="section-icon"><i class="fas fa-hospital-alt"></i></div>
    Informations Médicales
</div>

                    <div class="form-group">
                        <label class="form-label">Allergies</label>
                        <textarea id="allergies" class="form-textarea" placeholder="Indiquer les allergies du patient"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Antécédents</label>
                        <textarea id="history" class="form-textarea" placeholder="Indiquer les antécédents médicaux du patient"></textarea>
                    </div>
                </div>

                <!-- Historique Médical -->
<div class="section history-section">
    <div class="section-title">
        <div class="section-icon"><i class="fas fa-notes-medical"></i></div>
        Historique Médical
    </div>

    <div id="medicalHistory">
        <div class="no-history">Aucun historique médical.</div>
    </div>
</div>


                <!-- Consultation Actuelle -->
                <div class="section consultation-section">
                 <div class="section-title">
    <div class="section-icon"><i class="fas fa-search"></i></div>
    Consultation du <span class="required" id="consultationDate">03/07/2025</span>
</div>

                    <div class="consultation-fields">
                        <div class="form-group">
                            <label class="form-label">Raison <span class="required">*</span></label>
                            <div class="field-with-voice">
                                <textarea class="form-textarea" placeholder="Insérer une raison"></textarea>
                                <button class="voice-btn">🎤</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Observation <span class="required">*</span></label>
                            <div class="field-with-voice">
                                <textarea class="form-textarea" placeholder="Insérer une observation"></textarea>
                                <button class="voice-btn">🎤</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section IA -->
                <div class="section ai-section">
                   <div class="section-title">
    <div class="section-icon"><i class="fas fa-robot"></i></div>
    Intelligence Artificielle - Innover Ensemble
</div>

                    <div class="ai-info">
                        <p>Cet enregistrement audio est analysé par notre intelligence artificielle pour générer automatiquement un rapport médical structuré au format PDF, offrant une restitution précise et détaillée de la consultation.</p>
                        <p><strong>Cliquez sur le bouton micro pour commencer à enregistrer.</strong></p>
                    </div>
                    <div class="ai-controls">
                        <button class="btn btn-primary">
                            🎤 Micro
                        </button>
                       <button class="btn btn-secondary">
    <i class="fas fa-play"></i> Écouter
</button>

                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="floating-action" title="Sauvegarder la consultation">
    <i class="fas fa-save"></i>
</div>
<script>
    const patientsData = @json($patientsData);
</script>


<script>
    const input = document.getElementById('patientSearch');
    const list = document.getElementById('autocompleteList');
    const patientInfoDisplay = document.getElementById('patientInfoDisplay');

    // Générer dynamiquement les items
    Object.entries(patientsData).forEach(([id, data]) => {
        const li = document.createElement('li');
        li.classList.add('autocomplete-item');
        li.setAttribute('data-patient-id', id);
        li.textContent = data.name;

        li.addEventListener('click', () => {
            input.value = data.name;
            list.style.display = 'none';
            fillPatientData(data);
            patientInfoDisplay.style.display = 'block';
        });

        list.appendChild(li);
    });

    // Filtrage en fonction de la saisie
    input.addEventListener('input', () => {
        const query = input.value.toLowerCase();
        let hasResults = false;

        document.querySelectorAll('.autocomplete-item').forEach(item => {
            if (item.textContent.toLowerCase().includes(query)) {
                item.style.display = 'block';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        list.style.display = (query && hasResults) ? 'block' : 'none';
    });

function fillPatientData(data) {
    console.log(patientsData);

    // Avatar, nom, date de naissance et âge
    document.getElementById('patientAvatar').textContent = data.avatar;
    document.getElementById('patientName').textContent = data.name;
    document.getElementById('patientBirthdate').innerHTML = `<i class="fas fa-calendar-alt"></i> ${data.birthdate}`;
    document.getElementById('patientAge').innerHTML = `<i class="fas fa-user"></i> ${data.age}`;

    // Genre
    const genderLabel = data.gender === 'femme' ? 'femme' : 'homme';
    document.getElementById('patientGender').innerHTML =
        `<i class="fas fa-${data.gender === 'femme' ? 'venus' : 'mars'}"></i> ${genderLabel}`;

    // Poids (si défini)
    if (data.weight !== null && data.weight !== undefined && data.weight !== '') {
        document.getElementById('weight').value = data.weight;
    }

    // Taille (si définie)
    if (data.height !== null && data.height !== undefined && data.height !== '') {
        document.getElementById('height').value = data.height;
    }

    // Groupe sanguin (si défini)
    if (data.groupe_sanguin !== null && data.groupe_sanguin !== undefined && data.groupe_sanguin !== '') {
        document.getElementById('bloodType').value = data.groupe_sanguin;
    }

    // Allergies (si définies)
    if (data.allergie !== null && data.allergie !== undefined && data.allergie !== '') {
        document.getElementById('allergies').value = data.allergie;
    }

    // Antécédents (si définis)
    if (data.antecedent !== null && data.antecedent !== undefined && data.antecedent !== '') {
        document.getElementById('history').value = data.antecedent;
    }
    // Historique médical
if (data.historique) {
    document.getElementById('medicalHistory').innerHTML = data.historique;
} else {
    document.getElementById('medicalHistory').innerHTML = '<div class="no-history">Aucun historique médical.</div>';
}

}



</script>


</body>
</html>
@endsection
