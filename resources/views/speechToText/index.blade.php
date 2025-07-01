@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WIC Dr. - Génération de Rapports</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

      body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        

       


        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1e3a8a;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            color: #64748b;
            font-size: 16px;
        }

        .report-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .input-section {
            margin-bottom: 30px;
        }

        .input-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            color: #1e3a8a;
            font-weight: 600;
        }

        .voice-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .voice-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 2px;
        }

        .voice-btn.record {
            background: #fd8e26;
            color: white;
        }

        .voice-btn.record:hover {
            background: #fd8e26;
            transform: translateY(-2px);
        }

        .voice-btn.record.recording {
            animation: pulse 1.5s infinite;
        }

        .voice-btn.stop {
            background: #64748b;
            color: white;
        }

        .voice-btn.stop:hover {
            background: #475569;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .voice-status {
            padding: 8px 16px;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 14px;
            color: #64748b;
        }

        .text-area-container {
            position: relative;
            margin-bottom: 30px;
        }

        .report-textarea {
            width: 100%;
            min-height: 300px;
            padding: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            line-height: 1.6;
            resize: vertical;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        .report-textarea:focus {
            outline: none;
            border-color: #fd8e26;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .char-counter {
            position: absolute;
            bottom: 15px;
            right: 15px;
            font-size: 12px;
            color: #94a3b8;
        }

        .report-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .option-group {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .option-group h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #fd8e26;
        }

        .generate-section {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .generate-btn {
            background: linear-gradient(135deg, #fd8e26,rgb(255, 164, 79));
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .generate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(16, 185, 129, 0.4);
        }

        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .download-links {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .download-btn {
            padding: 10px 20px;
            border: 2px solid #fd8e26;
            background: white;
            color: #fd8e26;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .download-btn:hover {
            background: #fd8e26;
            color: white;
            transform: translateY(-2px);
        }

        .templates {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .template-btn {
            padding: 8px 16px;
            background: #e2e8f0;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .template-btn:hover {
            background: #fd8e26;
            color: white;
        }

        .ai-assist {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .ai-assist h3 {
            margin-bottom: 10px;
        }

        .ai-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .ai-btn:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>


    <div class="main-content">
       


        <div class="report-container">
       <div class="container text-center custom-top-spacing" >
                 <h1
                    style="text-align: center; color: #001f3f;font-weight: bold; font-size: 2.5em; background: #001f3f; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                    Génération de Rapports Médicaux
</h1>
    
    <p class="lead text-muted mb-4">Créez des rapports professionnels avec l'assistance vocale et l'intelligence artificielle</p>
</div>  

            <div class="input-section mt-4">
                <div class="input-header">
                    <h2 class="section-title">Contenu du Rapport</h2>
                    <div class="voice-controls">
                        <button class="voice-btn record ml-2" id="recordBtn" onclick="toggleRecording()">
                          <i                             class="fas fa-microphone "
>
                          </i> Enregistrer
                        </button>
                        <button class="voice-btn stop ml-2" id="stopBtn" onclick="stopRecording()" style="display: none;">
                            ⏹️ Arrêter
                        </button>
                        <div class="voice-status" id="voiceStatus">Prêt à enregistrer</div>
                    </div>
                </div>

                <div class="templates">
                    <button class="template-btn" onclick="loadTemplate('consultation')">Consultation générale</button>
                    <button class="template-btn" onclick="loadTemplate('urgence')">Rapport d'urgence</button>
                    <button class="template-btn" onclick="loadTemplate('suivi')">Suivi patient</button>
                    <button class="template-btn" onclick="loadTemplate('prescription')">Prescription</button>
                </div>

                <div class="text-area-container">
                    <textarea class="report-textarea" id="reportText" placeholder="Saisissez le contenu de votre rapport médical ici... 

Vous pouvez également utiliser la reconnaissance vocale en cliquant sur le bouton microphone.

Exemple de structure :
- Motif de consultation
- Anamnèse
- Examen clinique
- Diagnostic
- Traitement prescrit
- Recommandations"></textarea>
                    <div class="char-counter" id="charCounter">0 caractères</div>
                </div>
            </div>

            <div class="report-options">
                <div class="option-group">
                    <h3>Type de Document</h3>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" id="pdfReport" checked>
                            <span>Rapport PDF</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="docxReport">
                            <span>Document Word</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="htmlReport">
                            <span>Page Web</span>
                        </label>
                    </div>
                </div>

                <div class="option-group">
                    <h3>Informations à inclure</h3>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" id="patientInfo" checked>
                            <span>Infos patient</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="doctorInfo" checked>
                            <span>Infos médecin</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="timestamp" checked>
                            <span>Horodatage</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" id="signature">
                            <span>Signature électronique</span>
                        </label>
                    </div>
                </div>

                <div class="option-group">
                    <h3>Format de sortie</h3>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="radio" name="format" value="professional" checked>
                            <span>Format professionnel</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="format" value="simple">
                            <span>Format simplifié</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="format" value="detailed">
                            <span>Format détaillé</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="generate-section">
                <button class="generate-btn" onclick="generateReport()">
                    ✨ Générer le Rapport
                </button>
                
                <div class="download-links" id="downloadLinks" style="display: none;">
                    <a href="#" class="download-btn" onclick="downloadPDF()">
                        📄 Télécharger PDF
                    </a>
                    <a href="#" class="download-btn" onclick="downloadWord()">
                        📝 Télécharger DOCX
                    </a>
                    <a href="#" class="download-btn" onclick="downloadHTML()">
                        🌐 Voir HTML
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isRecording = false;
        let recognition = null;
        let reportData = '';

        // Initialisation de la reconnaissance vocale
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.lang = 'fr-FR';

            recognition.onresult = function(event) {
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    }
                }
                if (finalTranscript) {
                    const textarea = document.getElementById('reportText');
                    textarea.value += finalTranscript + ' ';
                    updateCharCounter();
                }
            };

            recognition.onerror = function(event) {
                console.error('Erreur de reconnaissance vocale:', event.error);
                document.getElementById('voiceStatus').textContent = 'Erreur: ' + event.error;
            };
        }

        function toggleRecording() {
            if (!recognition) {
                alert('La reconnaissance vocale n\'est pas supportée par votre navigateur.');
                return;
            }

            if (isRecording) {
                stopRecording();
            } else {
                startRecording();
            }
        }

        function startRecording() {
            isRecording = true;
            recognition.start();
            document.getElementById('recordBtn').classList.add('recording');
            document.getElementById('recordBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'flex';
            document.getElementById('voiceStatus').textContent = 'Enregistrement en cours...';
        }

        function stopRecording() {
            isRecording = false;
            recognition.stop();
            document.getElementById('recordBtn').classList.remove('recording');
            document.getElementById('recordBtn').style.display = 'flex';
            document.getElementById('stopBtn').style.display = 'none';
            document.getElementById('voiceStatus').textContent = 'Enregistrement arrêté';
        }

        function updateCharCounter() {
            const textarea = document.getElementById('reportText');
            const counter = document.getElementById('charCounter');
            counter.textContent = textarea.value.length + ' caractères';
        }

        function loadTemplate(type) {
            const templates = {
                consultation: `RAPPORT DE CONSULTATION MÉDICALE

Date: ${new Date().toLocaleDateString('fr-FR')}
Patient: [Nom du patient]
Âge: [Âge]

MOTIF DE CONSULTATION:
[Décrire le motif de la consultation]

ANAMNÈSE:
[Historique médical et symptômes rapportés]

EXAMEN CLINIQUE:
[Résultats de l'examen physique]

DIAGNOSTIC:
[Diagnostic posé]

TRAITEMENT PRESCRIT:
[Médicaments et posologie]

RECOMMANDATIONS:
[Conseils et suivi]`,

                urgence: `RAPPORT D'URGENCE MÉDICALE

Date et heure: ${new Date().toLocaleString('fr-FR')}
Patient: [Nom du patient]
Âge: [Âge]

CIRCONSTANCES DE L'URGENCE:
[Description de la situation d'urgence]

ÉTAT À L'ARRIVÉE:
[État clinique initial]

EXAMENS RÉALISÉS:
[Examens complémentaires effectués]

DIAGNOSTIC D'URGENCE:
[Diagnostic posé]

TRAITEMENT D'URGENCE:
[Traitements administrés]

ÉVOLUTION:
[Évolution de l'état du patient]

ORIENTATION:
[Hospitalisation, retour à domicile, etc.]`,

                suivi: `RAPPORT DE SUIVI MÉDICAL

Date: ${new Date().toLocaleDateString('fr-FR')}
Patient: [Nom du patient]

ANTÉCÉDENTS:
[Rappel des antécédents pertinents]

ÉVOLUTION DEPUIS LA DERNIÈRE CONSULTATION:
[Changements observés]

OBSERVANCE DU TRAITEMENT:
[Respect du traitement prescrit]

NOUVEL EXAMEN CLINIQUE:
[Nouveaux findings]

ADAPTATION DU TRAITEMENT:
[Modifications thérapeutiques]

PROCHAINE CONSULTATION:
[Date du prochain rendez-vous]`,

                prescription: `PRESCRIPTION MÉDICALE

Date: ${new Date().toLocaleDateString('fr-FR')}
Patient: [Nom du patient]
Âge: [Âge]

DIAGNOSTIC:
[Diagnostic justifiant la prescription]

PRESCRIPTIONS:

1. [Nom du médicament 1]
   - Posologie: [Dosage]
   - Durée: [Durée du traitement]
   - Mode d'administration: [Instructions]

2. [Nom du médicament 2]
   - Posologie: [Dosage]
   - Durée: [Durée du traitement]
   - Mode d'administration: [Instructions]

RECOMMANDATIONS:
[Conseils particuliers]

EFFETS SECONDAIRES À SURVEILLER:
[Effets indésirables possibles]`
            };

            document.getElementById('reportText').value = templates[type] || '';
            updateCharCounter();
        }


        function generateReport() {
            const reportText = document.getElementById('reportText').value;
            if (!reportText.trim()) {
                alert('Veuillez saisir le contenu du rapport avant de le générer.');
                return;
            }

            // Simulation de génération
            const generateBtn = document.querySelector('.generate-btn');
            generateBtn.textContent = '⏳ Génération en cours...';
            generateBtn.disabled = true;

            setTimeout(() => {
                generateBtn.textContent = '✅ Rapport Généré!';
                document.getElementById('downloadLinks').style.display = 'flex';
                
                setTimeout(() => {
                    generateBtn.textContent = '✨ Générer le Rapport';
                    generateBtn.disabled = false;
                }, 2000);
            }, 2000);

            reportData = reportText;
        }

        function downloadPDF() {
            const reportContent = document.getElementById('reportText').value;
            const blob = new Blob([
                `RAPPORT MÉDICAL
================

${reportContent}

================
Généré le ${new Date().toLocaleString('fr-FR')}
WIC Dr. - Système de Gestion Médicale`
            ], { type: 'text/plain' });
            
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `rapport_medical_${Date.now()}.txt`;
            a.click();
            URL.revokeObjectURL(url);
        }

        function downloadWord() {
            downloadPDF(); // Simulation - dans un vrai système, cela générerait un fichier Word
        }

        function downloadHTML() {
            const reportContent = document.getElementById('reportText').value;
            const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <title>Rapport Médical</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .content { white-space: pre-wrap; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccc; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport Médical</h1>
        <p>Généré le ${new Date().toLocaleString('fr-FR')}</p>
    </div>
    <div class="content">${reportContent}</div>
    <div class="footer">
        WIC Dr. - Système de Gestion Médicale
    </div>
</body>
</html>`;
            
            const blob = new Blob([htmlContent], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            window.open(url, '_blank');
        }

        // Mise à jour du compteur de caractères
        document.getElementById('reportText').addEventListener('input', updateCharCounter);
        
        // Initialisation
        updateCharCounter();
    </script>
</body>
</html>
@endsection