@extends('layouts.app')

@push('css_lib')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .required-field::after {
            content: ' *';
            color: red;
            font-weight: bold;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .validation-message {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .card {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .btn {
            margin-right: 10px;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(92, 107, 192, 0.1);
        }

        .card-body {
            padding: 2.5rem;
        }

        .section-title {
            color: #001f3f;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #001f3f;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 0.2rem rgba(92, 107, 192, 0.15);
        }

        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #424242;
            font-size: 0.95rem;
        }

        .required-field::after {
            content: "*";
            color: #001f3f;
            margin-left: 4px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #001f3f;
        }

        .input-icon input {
            padding-left: 2.5rem;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #001f3f;
            border-color: #001f3f;
        }

        .btn-primary:hover {
            background-color: #3f51b5;
            border-color: #3f51b5;
            transform: translateY(-1px);
        }

        .form-row {
            display: flex;
            margin: -10px;
            flex-wrap: wrap;
        }

        .form-col {
            flex: 1;
            padding: 10px;
            min-width: 250px;
        }

        .price-fields {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .validation-message {
            font-size: 0.85rem;
            margin-top: 0.25rem;
            color: #dc3545;
            display: none;
        }

        .btn.bg-{{setting('theme_color')}} {
            padding: 0.75rem 2rem;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn.bg-{{setting('theme_color')}}:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(92, 107, 192, 0.2);
            opacity: 0.9;
        }

        .btn.bg-{{setting('theme_color')}}:active {
            transform: translateY(0);
        }

        .btn.bg-{{setting('theme_color')}} i {
            font-size: 0.9em;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <br><br>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h3 class="mb-4" style="color: #001f3f;">
                    <i class="fas fa-video me-2"></i>&nbsp;&nbsp;{{ trans('lang.new_teleconsultation') }}
                </h3>

                <form id="sendMeetingForm" action="{{ route('send.meeting.info') }}" method="POST" class="needs-validation"
                    novalidate>
                    @csrf

                    <!-- Patient Information Section -->
                    <div class="section-title">
                        <i class="fas fa-user-circle me-2"></i>&nbsp;&nbsp;{{ trans('lang.patient_information') }}
                    </div>
                    <input type="hidden" name="patient_id" value="{{ $data['patient_id'] }}">
                    <input type="hidden" name="appointment_id" value="{{ $data['appointment_id'] }}">

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="patient_first_name" class="required-field">Prénom</label>
                                <div class="input-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="patient_first_name" class="form-control"
                                        name="patient_first_name" value="{{ $data['patient_first_name'] }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="patient_last_name" class="required-field">Nom</label>
                                <div class="input-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="patient_last_name" class="form-control" name="patient_last_name"
                                        value="{{ $data['patient_last_name'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="section-title mt-4">
                        <i class="fas fa-address-card me-2"></i>&nbsp;&nbsp;{{ trans('lang.contact_information') }}
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="patientPhone" class="required-field">Téléphone</label>
                                <div class="input-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="patientPhone" class="form-control" name="phone"
                                        value="{{ $data['phone'] }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="patientEmail" class="required-field">Email</label>
                                <div class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="patientEmail" class="form-control" name="email"
                                        value="{{ $data['patient_Email'] }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Section -->
                    <div class="section-title mt-4">
                        <i class="fas fa-calendar-alt me-2"></i>&nbsp;&nbsp;{{ trans('lang.appointment_information') }}
                    </div>

                    <div class="form-group">
                        <label for="startAt" class="required-field">Date et heure</label>
                        <div class="input-icon">
                            <i class="fas fa-clock"></i>
                            <input type="datetime-local" id="startAt" class="form-control" name="start_at"
                                value="{{ \Carbon\Carbon::parse($data['start_at'])->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="section-title mt-4">
                        <i class="fas fa-money-bill me-2"></i>&nbsp;&nbsp;{{ trans('lang.payment_information') }}
                    </div>

                    <div class="form-group">
                        <label class="required-field">Mode de paiement</label>
                        <select class="form-control" id="paymentMode" name="payment_mode" required>
                            <option value="">Sélectionner le mode de paiement</option>
                            <option value="TND">Dinar Tunisien (TND)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="USD">Dollar (USD)</option> <!-- New -->
                            <option value="SPLIT">Paiement mixte (TND + EUR + USD)</option>
                        </select>
                    </div>

                    <div id="priceFields" class="price-fields">
                        <div id="tndPriceField" class="form-group" style="display: none;">
                            <label for="tele_price_tnd" class="required-field">Prix (TND)</label>
                            <div class="input-icon">
                                <i class="fas fa-money-bill"></i>
                                <input type="number" id="tele_price_tnd" class="form-control" name="tele_price_tnd"
                                    step="0.01" min="0.01" placeholder="Montant en dinars"
                                    data-validation-message="Le prix doit être supérieur à 0">
                            </div>
                        </div>

                        <div id="eurPriceField" class="form-group" style="display: none;">
                            <label for="tele_price_eur" class="required-field">Prix (EUR)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                                <input type="number" id="tele_price_eur" class="form-control" name="tele_price_eur"
                                    step="0.01" min="0.01" placeholder="Montant en euros"
                                    data-validation-message="Le prix doit être supérieur à 0">
                            </div>
                        </div>
                        <div id="usdPriceField" class="form-group" style="display: none;">
                            <label for="tele_price_usd" class="required-field">Prix (USD)</label>
                            <div class="input-icon">
                                <i class="fas fa-dollar-sign"></i>
                                <input type="number" id="tele_price_usd" class="form-control" name="tele_price_usd"
                                    step="0.01" min="0.01" placeholder="Montant en dollars"
                                    data-validation-message="Le prix doit être supérieur à 0">
                            </div>
                        </div>

                    </div>

                    <!-- Form Actions -->
                    <div class="form-group mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn bg-{{setting('theme_color')}} text-white">
                            <i class="fas fa-paper-plane me-2"></i>&nbsp;&nbsp;{{ trans('lang.send') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts_lib')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        // Replace your existing payment mode change listener with this:
        document.addEventListener('DOMContentLoaded', function () {
            const paymentMode = document.getElementById('paymentMode');
            const tndField = document.getElementById('tndPriceField');
            const eurField = document.getElementById('eurPriceField');
            const usdField = document.getElementById('usdPriceField'); // <-- you missed this line
            const tndInput = document.getElementById('tele_price_tnd');
            const eurInput = document.getElementById('tele_price_eur');
            const usdInput = document.getElementById('tele_price_usd');


            paymentMode.addEventListener('change', function () {
                switch (this.value) {
                    case 'TND':
                        tndField.style.display = 'block';
                        eurField.style.display = 'none';
                        tndInput.required = true;
                        eurInput.required = false;
                        eurInput.value = '';
                        break;

                    case 'EUR':
                        tndField.style.display = 'none';
                        eurField.style.display = 'block';
                        tndInput.required = false;
                        eurInput.required = true;
                        tndInput.value = '';
                        break;
                    case 'USD':
                        tndField.style.display = 'none';
                        eurField.style.display = 'none';
                        usdField.style.display = 'block';
                        tndInput.required = false;
                        eurInput.required = false;
                        usdInput.required = true;
                        tndInput.value = '';
                        eurInput.value = '';
                        break;

                    case 'SPLIT':
                        tndField.style.display = 'block';
                        eurField.style.display = 'block';
                        usdField.style.display = 'block';
                        tndInput.required = true;
                        eurInput.required = true;
                        usdInput.required = true;
                        break;

                    default:
                        tndField.style.display = 'none';
                        eurField.style.display = 'none';
                        usdField.style.display = 'none';
                        tndInput.required = false;
                        eurInput.required = false;
                        usdInput.required = false;
                        tndInput.value = '';
                        eurInput.value = '';
                        usdInput.value = '';
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('sendMeetingForm');
            const inputs = form.querySelectorAll('input[required]');


            // Real-time validation
            inputs.forEach(input => {
                ['blur', 'input'].forEach(eventType => {
                    input.addEventListener(eventType, () => validateField(input));
                });
            });

            function validateField(field) {
                const validationMessage = field.nextElementSibling;
                const isValid = field.checkValidity();

                field.classList.toggle('is-invalid', !isValid);
                if (validationMessage) {
                    validationMessage.style.display = isValid ? 'none' : 'block';
                    validationMessage.textContent = field.dataset.validationMessage;
                }
            }
            function validatePrices() {
                const paymentMode = document.getElementById('paymentMode').value;
                const tndPrice = parseFloat(document.getElementById('tele_price_tnd').value) || 0;
                const eurPrice = parseFloat(document.getElementById('tele_price_eur').value) || 0;
                const usdPrice = parseFloat(document.getElementById('tele_price_usd').value) || 0;

                // First check if payment mode is selected
                if (!paymentMode) {
                    Swal.fire({
                        title: 'Erreur de validation',
                        text: 'Veuillez sélectionner un mode de paiement',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                switch (paymentMode) {
                    case 'TND':
                        if (!document.getElementById('tele_price_tnd').value || tndPrice <= 0) {
                            Swal.fire({
                                title: 'Erreur de validation',
                                text: 'Le montant en TND doit être supérieur à 0',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }
                        break;

                    case 'EUR':
                        if (!document.getElementById('tele_price_eur').value || eurPrice <= 0) {
                            Swal.fire({
                                title: 'Erreur de validation',
                                text: 'Le montant en EUR doit être supérieur à 0',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }
                        break;
                    case 'USD':
                        if (!document.getElementById('tele_price_usd').value || usdPrice <= 0) {
                            Swal.fire({
                                title: 'Erreur de validation',
                                text: 'Le montant en USD doit être supérieur à 0',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }
                        break;

                    case 'SPLIT':
                        if (!document.getElementById('tele_price_tnd').value ||
                            !document.getElementById('tele_price_eur').value ||
                            !document.getElementById('tele_price_usd').value ||
                            tndPrice <= 0 ||
                            eurPrice <= 0 ||
                            usdPrice <= 0) {
                            Swal.fire({
                                title: 'Erreur de validation',
                                text: 'Pour un paiement mixte, les deux montants doivent être supérieurs à 0',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }
                        break;
                }
                return true;
            }
            // Form submission
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                let isValid = true;
                const validationErrors = [];

                // Validate all required fields
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        isValid = false;
                        validationErrors.push(`${input.labels[0].textContent.replace('*', '')} est requis`);
                        validateField(input);
                    }
                });

                // Price validation
                const telePriceTnd = parseFloat(document.getElementById('tele_price_tnd').value) || 0;
                const telePriceEur = parseFloat(document.getElementById('tele_price_eur').value) || 0;
                const telePriceUsd = parseFloat(document.getElementById('tele_price_usd').value) || 0;

                if (!validatePrices()) {
                    return;
                }
                if (!isValid) {
                    Swal.fire({
                        title: 'Erreur de validation',
                        html: validationErrors.join('<br>'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Confirmation dialog
                Swal.fire({
                    title: 'Vérification des informations',
                    html: `

                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="text-left">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card mb-3" style="border-color: #001f3f">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-header text-white" style="background-color: #001f3f">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-user-circle"></i> Informations
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-body p-3">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="row">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-12 mb-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <strong>Patient :</strong> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${document.getElementById('patient_first_name').value} 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${document.getElementById('patient_last_name').value}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <strong>Contact :</strong><br>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    📧 ${document.getElementById('patientEmail').value}<br>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    📞 ${document.getElementById('patientPhone').value}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-md-6">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <strong>Rendez-vous :</strong><br>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    📅 ${new Date(document.getElementById('startAt').value).toLocaleString('fr-FR')}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="card mb-3" style="border-color: #001f3f">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-header text-white" style="background-color: #001f3f">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-money-bill"></i> Prix & Lien
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="card-body p-3">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="mb-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <strong>Prix :</strong><br>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                🇹🇳 ${document.getElementById('tele_price_tnd').value} TND
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                🇪🇺 ${document.getElementById('tele_price_eur').value} EUR
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                🇺🇸 ${document.getElementById('tele_price_usd').value} USD
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="alert alert-info border-left border-primary py-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-info-circle"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        Un email sera envoyé au patient avec ces informations.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                            `,

                    width: '600px',
                    padding: '1rem',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Modifier',
                    confirmButtonColor: '#001f3f',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Envoi en cours...',
                            text: 'Veuillez patienter...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit the form
                        form.submit();
                    }
                });
            });
        });


        function copyToClipboard() {
            const meetLink = document.getElementById('meetLink');
            navigator.clipboard.writeText(meetLink.value).then(() => {
                Swal.fire({
                    title: 'Succès!',
                    text: 'Le lien de la réunion a été copié dans le presse-papiers!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(err => {
                Swal.fire({
                    title: 'Erreur',
                    text: 'Impossible de copier le lien de la réunion.',
                    icon: 'error'
                });
            });
        }

    </script>
@endpush