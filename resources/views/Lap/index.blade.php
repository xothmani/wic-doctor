@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')


    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nouvelle Prescription Médicale</title>
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
                max-width: 1000px;
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
                background: #A458E3;
                color: white;
                padding: 30px;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .header::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>') repeat;
                animation: float 20s linear infinite;
            }

            @keyframes float {
                0% {
                    transform: translate(-50%, -50%) rotate(0deg);
                }

                100% {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }

            .header h1 {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 10px;
                position: relative;
                z-index: 2;
            }

            .header p {
                font-size: 1.1rem;
                opacity: 0.9;
                position: relative;
                z-index: 2;
            }

            .form-container {
                padding: 40px;
            }

            .form-section {
                margin-bottom: 35px;
                background: white;
                border-radius: 15px;
                padding: 25px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                border-left: 4px solid #A458E3;
                transition: all 0.3s ease;
            }

            .form-section:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            }

            .section-title {
                font-size: 1.3rem;
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
                background: linear-gradient(to right, #A458E3, #c08ff0);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 12px;
            }

            .form-row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
            }

            .form-group label {
                font-weight: 600;
                color: #34495e;
                margin-bottom: 8px;
                font-size: 0.95rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px 16px;
                border: 2px solid #e1e8ed;
                border-radius: 10px;
                font-size: 1rem;
                transition: all 0.3s ease;
                background: #fafbfc;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                outline: none;
                border-color: #A458E3;
                background: white;
                box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
                transform: translateY(-1px);
            }

            .medication-item {
                background: #f8f9fa;
                border: 2px dashed #dee2e6;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 15px;
                transition: all 0.3s ease;
                position: relative;
            }

            .medication-item:hover {
                border-color: #A458E3;
                background: white;
            }

            .medication-header {
                display: flex;
                justify-content: between;
                align-items: center;
                margin-bottom: 15px;
            }

            .medication-number {
                background: linear-gradient(to right, #A458E3, #c08ff0);
                color: white;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 0.9rem;
            }

            .remove-btn {
                background: #ff6b6b;
                color: white;
                border: none;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 18px;
                transition: all 0.3s ease;
                position: absolute;
                top: 15px;
                right: 15px;
            }

            .remove-btn:hover {
                background: #ff5252;
                transform: scale(1.1);
            }

            .add-medication {
                background: linear-gradient(135deg, #11b8aa 0%, #0d9a8e 100%);
                color: white;
                border: none;
                padding: 12px 25px;
                border-radius: 25px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 20px 0;
            }

            .add-medication:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(17, 184, 170, 0.4);

            }

            .form-actions {
                display: flex;
                gap: 15px;
                justify-content: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 2px solid #f1f3f4;
            }

            .btn {
                padding: 15px 30px;
                border: none;
                border-radius: 25px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .btn-primary {
                background: #A458E3;
                color: white;

            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(164, 88, 227, 0.4);
                background: #A458E3;

            }

            .btn-secondary {
                background: #6c757d;
                color: white;
            }

            .btn-secondary:hover {
                background: #5a6268;
                transform: translateY(-2px);
            }

            .required {
                color: #e74c3c;
            }

            @media (max-width: 768px) {
                .header h1 {
                    font-size: 2rem;
                }

                .form-container {
                    padding: 20px;
                }

                .form-row {
                    grid-template-columns: 1fr;
                }

                .form-actions {
                    flex-direction: column;
                }
            }

            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }

            .toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked+.slider {
                background-color: #A458E3;
            }

            input:focus+.slider {
                box-shadow: 0 0 1px #A458E3;
            }

            input:checked+.slider:before {
                transform: translateX(26px);
            }

            .switch-label {
                margin-left: 10px;
                font-weight: 500;
            }
        </style>
    </head>

    <body>
        <div class="container-form">
            <div class="header">
                <h1>Nouvelle Prescription</h1>
                <p>Rédigez vos ordonnances en toute sécurité <br>grâce à notre système intelligent d’aide à la prescription.
                </p>
            </div>

            <div class="form-container">
                <form id="prescriptionForm">
                    <!-- Informations Médecin -->
                    <!--                 <div class="form-section">
                                <h2 class="section-title">
                                    <span class="section-icon">👨‍⚕️</span>
                                    Informations Médecin
                                </h2>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Nom complet <span class="required">*</span></label>
                                        <input type="text" name="doctorName" placeholder="Dr. Jean Dupont" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Numéro d'ordre <span class="required">*</span></label>
                                        <input type="text" name="doctorId" placeholder="123456" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Spécialité</label>
                                        <select name="specialty">
                                            <option value="">Sélectionner...</option>
                                            <option value="medecine-generale">Médecine Générale</option>
                                            <option value="cardiologie">Cardiologie</option>
                                            <option value="dermatologie">Dermatologie</option>
                                            <option value="pediatrie">Pédiatrie</option>
                                            <option value="psychiatrie">Psychiatrie</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Cabinet/Hôpital</label>
                                        <input type="text" name="clinic" placeholder="Cabinet médical Central">
                                    </div>
                                </div>
                            </div> -->
            <div class="result-content" style="background-color: rgba(40, 167, 69, 0.05); border: 1px solid rgba(40, 167, 69, 0.3); border-left: 5px solid #28a745; padding: 10px;border-radius: 15px;margin: 10px;">
            
            <div class="result-text">
                <div class="result-title-container" style="color: #28a745">
                    <div class="result-icon" style="color: #28a745">
                        <i class="fas fa-check-circle"></i>    <span style="color: #28a745; font-weight: bold"  >Bonne compatibilité</span> 
                    </div>
                  
                </div>
                <div class="result-message">Compatibilité et appropriativité de la dose</div>
                <div class="result-details">Le paracétamol est prescrit à une dose de 1 g une fois par jour pendant 5 jours. Cette posologie est appropriée pour un adulte et ne présente pas de risques majeurs de toxicité hépatique chez un patient de 29 ans sans antécédents médicaux spécifiques mentionnés. Il n'y a pas de interactions connues avec d'autres médicaments non mentionnés dans cette évaluation.</div>
            </div>
        </div>
                    <div class="form-section">
                        <h2 class="section-title">
                            <span class="section-icon"><i class="fas fa-robot"></i></span>
                            Aide à la Prescription
                        </h2>

                        <div class="form-group">
                            <p>
                                Ces médicaments sont analysés par notre intelligence artificielle, offrant une restitution
                                précise et détaillée de la compatibilité des médicaments avec le patient.
                                <b>Pour activer ou désactiver l'aide à la prescription, cliquez sur le bouton
                                    ci-dessous</b>.
                            </p>
                        </div>

                        <div class="form-group">
                            <div class="d-flex align-items-center">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="aiHelpToggle">
                                    <span class="slider"></span>
                                </label>
                                <span class="switch-label ms-2">Activer l'aide à la prescription</span>
                            </div>
                        </div>
                    </div>

                    <!-- Informations Patient -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <span class="section-icon"><i class="fas fa-user"></i></span>
                            Sélection du Patient
                        </h2>

                        <div class="form-group" style="position: relative;">
                            <label>Nom complet <span class="required">*</span></label>
                            <input type="text" id="patientSearch" name="patientName" placeholder="Marie Martin"
                                autocomplete="off" required>

                            <ul id="autocompleteList" style="position: absolute;
                        top: 100%;
                        left: 0;
                        right: 0;
                        background: white;
                        border: 1px solid #e1e8ed;
                        border-radius: 10px;
                        max-height: 50vh;
                        overflow-y: auto;
                        z-index: 1000;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                        display: none;">
                                @foreach($patients as $dp)
                                    @php
                                        $patient = $dp->patient;
                                        $fullName = $patient->first_name . ' ' . $patient->last_name;
                                    @endphp
                                    <li class="autocomplete-item" style="padding: 8px; cursor: pointer;">{{ $fullName }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <script>
                        const input = document.getElementById('patientSearch');
                        const list = document.getElementById('autocompleteList');
                        const items = document.querySelectorAll('.autocomplete-item');

                        input.addEventListener('input', () => {
                            const query = input.value.toLowerCase();

                            let hasResults = false;
                            items.forEach(item => {
                                if (item.textContent.toLowerCase().includes(query)) {
                                    item.style.display = 'block';
                                    hasResults = true;
                                } else {
                                    item.style.display = 'none';
                                }
                            });

                            list.style.display = (query && hasResults) ? 'block' : 'none';
                        });

                        items.forEach(item => {
                            item.addEventListener('click', () => {
                                input.value = item.textContent;
                                list.style.display = 'none';
                            });
                        });

                        // Fermer la liste si on clique ailleurs
                        document.addEventListener('click', (e) => {
                            if (!e.target.closest('.form-group')) {
                                list.style.display = 'none';
                            }
                        });
                    </script>


                    <!-- Médicaments -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <span class="section-icon"><i class="fas fa-pills"></i></span>
                            Médicaments Prescrits
                        </h2>


                        <div id="medicationsContainer">
                            <div class="medication-item">
                                <div class="medication-header">
                                    <span class="medication-number">1</span>
                                </div>
                                <div class="form-group">
                                    <label>Nom du médicament <span class="required">*</span></label>
                                    <input type="text" name="medicationName[]" placeholder="Paracétamol 500mg" required>
                                </div>

                         <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div class="form-group" style="flex: 1;">
        <label>Posologie <span class="text-danger">*</span></label>
        <input type="text" name="medicaments[${medicationCount - 1}][posologie]" placeholder="1 cp" required >
    </div>

    <div class="form-group" style="flex: 1;">
        <label>Nombre de fois <span class="text-danger">*</span></label>
        <div class="input-group" style="display: flex;">
            <input type="number" min="1" max="10"  placeholder="Nombre" required style="flex: 1;" />
            <select required style="flex: 1;">
                <option value="fois par jour">Fois / jour</option>
                <option value="fois par semaine">Fois / semaine</option>
                <option value="fois par mois">Fois / mois</option>
                <option value="fois par an">Fois / an</option>
            </select>
            <input type="hidden" name="medicaments[${medicationCount - 1}][frequence_complete]" class="frequence-complete" />
        </div>
    </div>
</div>
 <div class="form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
   

    <div class="form-group" style="flex: 1;">
        <label>Nombre de fois <span class="text-danger">*</span></label>
        <div class="input-group" style="display: flex; ">
            <input type="number" min="1" max="10"  placeholder="Nombre" required style="flex: 1;" />
            <select required style="flex: 1;">
                <option value="fois par jour">Fois / jour</option>
                <option value="fois par semaine">Fois / semaine</option>
                <option value="fois par mois">Fois / mois</option>
                <option value="fois par an">Fois / an</option>
            </select>
            <input type="hidden" name="medicaments[${medicationCount - 1}][frequence_complete]" class="frequence-complete" />
        </div>
    </div>
    <div class="form-group" style="flex: 1;">
        <label>Horaire <span class="text-danger">*</span></label>
        <select name="medicaments[${medicationCount - 1}][horaire]" required >
            <option value="" disabled selected>Choisir un horaire</option>
            <option value="avant repas">Avant repas</option>
            <option value="après repas">Après repas</option>
        </select>
    </div>
</div>




                            </div>
                        </div>

                        <button type="button" class="add-medication" onclick="addMedication()">
                            + Ajouter un médicament
                        </button>
                    </div>

                    <!-- Informations Complémentaires -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <span class="section-icon"><i class="fas fa-file-alt"></i></span>
                            Informations Complémentaires
                        </h2>

                        <div class="form-group">
                            <label>Date de prescription</label>
                            <input type="date" name="prescriptionDate" value="2025-06-21">
                        </div>

                        <div class="form-group">
                            <label>Notes médicales</label>
                            <textarea name="notes" rows="3"
                                placeholder="Observations, allergies, contre-indications..."></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Créer la Prescription</button>
                        <button type="button" class="btn btn-secondary">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let medicationCount = 1;

            function addMedication() {
                medicationCount++;
                const container = document.getElementById('medicationsContainer');
                const medicationDiv = document.createElement('div');
                medicationDiv.className = 'medication-item';
                medicationDiv.innerHTML = `
                <div class="medication-header">
                    <span class="medication-number">${medicationCount}</span>
                    <button type="button" class="remove-btn" onclick="removeMedication(this)" style="float:right; background:#ff4444; color:white; border:none; border-radius:50%; width:24px; height:24px; cursor:pointer;">×</button>
                </div>

                <div class="form-group">
                    <label>Nom du médicament <span class="required">*</span></label>
                    <input type="text" name="medicaments[${medicationCount - 1}][name]" placeholder="Paracétamol 500mg" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Posologie <span class="required">*</span></label>
                        <input type="text" name="medicaments[${medicationCount - 1}][posology]" placeholder="1 cp" required>
                    </div>

                    <div class="form-group">
                <label>Nombre de fois <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" min="1" max="10"
                        placeholder="Nombre" required
                        class="nb-de-fois" style="flex: 1;" />
                    <select class="frequency-unit" required style="flex: 1;">
                        <option value="fois par jour">Fois / jour</option>
                        <option value="fois par semaine">Fois / semaine</option>
                        <option value="fois par mois">Fois / mois</option>
                        <option value="fois par an">Fois / an</option>
                    </select>
                    <input type="hidden" name="medicaments[${medicationCount - 1}][frequence_complete]" class="frequence-complete" />
                </div>
            </div>

                </div>

                <div class="form-row">
          <div class="form-group">
    <label>Durée du traitement <span class="text-danger">*</span></label>
    <div class="input-group" style="display: flex; gap: 10px;">
        <input type="number" min="1" required
            placeholder="Nombre" class="nb-de-jours" style="flex: 0 0 50%;" />
        <select class="duration-unit" required style="flex: 0 0 50%;">
            <option value="jours">Jours</option>
            <option value="semaines">Semaines</option>
            <option value="mois">Mois</option>
        </select>
        <input type="hidden" name="medicaments[${medicationCount - 1}][duree_complete]" class="duree-complete" />
    </div>
</div>



                    <div class="form-group">
    <label>Horaire</label>
    <select name="medicaments[${medicationCount - 1}][horaire]" required>
        <option value="" disabled selected>Choisir un horaire</option>
        <option value="avant repas">Avant repas</option>
        <option value="après repas">Après repas</option>
    </select>
</div>

                </div>
            `;

                container.appendChild(medicationDiv);

                // Animation d'apparition
                medicationDiv.style.opacity = '0';
                medicationDiv.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    medicationDiv.style.transition = 'all 0.5s ease';
                    medicationDiv.style.opacity = '1';
                    medicationDiv.style.transform = 'translateY(0)';
                }, 10);
            }

            function removeMedication(button) {
                const medicationItem = button.parentNode;
                medicationItem.style.transition = 'all 0.3s ease';
                medicationItem.style.opacity = '0';
                medicationItem.style.transform = 'translateX(-100%)';
                setTimeout(() => {
                    medicationItem.remove();
                    updateMedicationNumbers();
                }, 300);
            }

            function updateMedicationNumbers() {
                const medications = document.querySelectorAll('.medication-number');
                medications.forEach((num, index) => {
                    num.textContent = index + 1;
                });
                medicationCount = medications.length;
            }

            document.getElementById('prescriptionForm').addEventListener('submit', function (e) {
                e.preventDefault();

                // Animation de soumission
                const submitBtn = document.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Création en cours...';
                submitBtn.style.opacity = '0.7';

                setTimeout(() => {
                    alert('Prescription créée avec succès !');
                    submitBtn.textContent = originalText;
                    submitBtn.style.opacity = '1';
                }, 2000);
            });

            // Auto-resize textarea
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        </script>
    </body>

    </html>

@endsection