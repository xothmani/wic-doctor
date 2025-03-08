@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-dark">{{ trans('lang.teleconsultation_plural') }}
                        <small class="mx-3">|</small>
                        <small>{{ trans('lang.teleconsultation_table') }}</small>
                    </h1>
                </div>
                <div class="col-md-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="fa fa-dashboard"></i> {{ trans('lang.dashboard') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a
                                href="{!! route('teleconsultations.index') !!}">{{ trans('lang.teleconsultation_plural') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('lang.teleconsultation_table') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <head>
        <!-- ...existing head content... -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <!-- Main Content -->
    <div class="content">
        <!-- Flash Messages -->
        @include('flash::message')

        <!-- Teleconsultation Card -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title">{{ trans('lang.teleconsultation_table') }}</h5>
            </div>
            <!-- <div class="card-header">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <h5 class="card-title">{{ trans('lang.teleconsultation_table') }}</h5>
                                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeetModal">
                                                                        <i class="fas fa-plus-circle"></i> Nouvelle téléconsultation
                                                                    </button>
                                                                </div>
                                                            </div> -->
            <div class="card-body">
                <!-- Table Responsive -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ trans('lang.patient_name') }}</th>
                                <th>{{ trans('lang.patient_phone') }}</th>
                                <th>{{ trans('lang.teleconsultation_date') }}</th>
                                <th>{{ trans('lang.teleconsultation_time') }}</th>
                                <th>{{ trans('lang.teleconsultation_status') }}</th>
                                <th>{{ trans('lang.teleconsultation_link') }}</th>
                                <th>{{ trans('lang.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                                                    @php
                                                        $patient = $patients->get($room->patient_id); // Fetch patient data
                                                        $paymentStatus = $room->appointment->appointment_status_id ?? null; // Fetch payment status
                                                        $statusLabel = $paymentStatus == 9
                                                            ? ['text' => 'Payé', 'badge' => 'success']
                                                            : ['text' => 'En attente de paiement', 'badge' => 'warning'];
                                                            $isFinished = in_array($room->status, ['completed', 'failed']);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $patient ? $patient->first_name . ' ' . $patient->last_name : trans('lang.unknown') }}
                                                        </td>
                                                        <td>{{ $patient ? $patient->phone_number : trans('lang.not_available') }}</td>
                                                        <td>{{ $room->date ?: trans('lang.not_specified') }}</td>
                                                        <td>{{ $room->time ?: trans('lang.not_specified') }}</td>
                                                        <td>
                                                            <span class="badge badge-{{ $statusLabel['badge'] }}">
                                                                {{ $statusLabel['text'] }}
                                                            </span>
                                                        </td>
                                                        <td>
        @if(!$isFinished)
            <a href="{{ $room->meet_link }}" target="_blank" class="btn bg-{{setting('theme_color')}} btn-sm">
                {{ trans('lang.open_link') }}
            </a>
        @else
            <span class="text-muted">
                <i class="fas fa-lock"></i> {{ trans('lang.consultation_finished') }}
            </span>
        @endif
    </td>
    <td>
        @if(!$isFinished)
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success btn-sm update-status"
                    data-room-id="{{ $room->id }}" data-status="completed">
                    <i class="fas fa-check"></i> Terminer
                </button>
                <button type="button" class="btn btn-danger btn-sm update-status"
                    data-room-id="{{ $room->id }}" data-status="failed">
                    <i class="fas fa-times"></i> Échoué
                </button>
            </div>
        @else
            <span class="badge badge-{{ $room->status === 'completed' ? 'success' : 'danger' }}">
                {{ $room->status === 'completed' ? 'Terminée' : 'Échouée' }}
            </span>
        @endif
    </td>
                                                    </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <!-- Render Pagination Links -->
                <div class="d-flex justify-content-end">
                    {{ $rooms->links() }}
                </div>


            </div>
        </div>
    </div>
    <!-- Create Meeting Modal -->
    <div class="modal fade" id="createMeetModal" tabindex="-1" aria-labelledby="createMeetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #5c6bc0; color: white;">
                    <h5 class="modal-title" id="createMeetModalLabel">
                        <i class="fas fa-video"></i> Nouvelle consultation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createMeetingForm" action="{{ route('create.specific.meeting') }}" method="POST"
                        class="needs-validation" novalidate>
                        @csrf
                        <div class="row g-3">
                            <!-- Patient Information -->
                            <div class="col-md-6">
                                <label for="patient_first_name" class="required-field">Prénom</label>
                                <input type="text" id="patient_first_name" class="form-control" name="patient_first_name"
                                    required minlength="2" placeholder="Prénom du patient"
                                    data-validation-message="Le prénom doit contenir au moins 2 caractères">
                                <div class="validation-message"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="patient_last_name" class="required-field">Nom</label>
                                <input type="text" id="patient_last_name" class="form-control" name="patient_last_name"
                                    required minlength="2" placeholder="Nom du patient"
                                    data-validation-message="Le nom doit contenir au moins 2 caractères">
                                <div class="validation-message"></div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <label for="phone" class="required-field">Téléphone</label>
                                <input type="tel" id="phone" class="form-control" name="phone" required
                                    pattern="^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$"
                                    placeholder="+216 XX XXX XXX" data-validation-message="Numéro de téléphone invalide">
                                <div class="validation-message"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="patient_Email" class="required-field">Email</label>
                                <input type="email" id="patient_Email" class="form-control" name="patient_Email" required
                                    placeholder="patient@example.com" data-validation-message="Email invalide">
                                <div class="validation-message"></div>
                            </div>

                            <!-- Date and Time -->
                            <div class="col-12">
                                <label for="start_at" class="required-field">Date et heure</label>
                                <input type="datetime-local" id="start_at" class="form-control" name="start_at" required
                                    data-validation-message="Date et heure requises">
                                <div class="validation-message"></div>
                            </div>

                            <!-- Payment Information -->
                            <div class="col-12">
                                <label class="required-field">Mode de paiement</label>
                                <select class="form-control" id="paymentMode" name="payment_mode" required>
                                    <option value="">Choisir le mode de paiement</option>
                                    <option value="TND">Dinar Tunisien (TND)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                    <option value="SPLIT">Paiement mixte (TND + EUR)</option>
                                </select>
                            </div>

                            <!-- Dynamic Price Fields -->
                            <div class="col-md-6" id="tndPriceField" style="display: none;">
                                <label for="tele_price_tnd" class="required-field">Prix (TND)</label>
                                <input type="number" id="tele_price_tnd" class="form-control" name="tele_price_tnd"
                                    placeholder="Montant en dinars" step="0.01" min="0.01"
                                    data-validation-message="Prix invalide">
                                <div class="validation-message"></div>
                            </div>

                            <div class="col-md-6" id="eurPriceField" style="display: none;">
                                <label for="tele_price_eur" class="required-field">Prix (EUR)</label>
                                <input type="number" id="tele_price_eur" class="form-control" name="tele_price_eur"
                                    placeholder="Montant en euros" step="0.01" min="0.01"
                                    data-validation-message="Prix invalide">
                                <div class="validation-message"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="createMeetingForm" class="btn"
                        style="background-color: #5c6bc0; color: white;">
                        <i class="fas fa-check"></i> Créer
                    </button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-content {
            border-radius: 8px;
            border: none;
        }

        .form-control:focus {
            border-color: #5c6bc0;
            box-shadow: 0 0 0 0.2rem rgba(92, 107, 192, 0.25);
        }

        .required-field::after {
            content: "*";
            color: #5c6bc0;
            margin-left: 4px;
        }

        .validation-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn-group {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
        }

        .btn-group .btn {
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
        }

        .btn-group .btn i {
            margin-right: 0.5rem;
        }

        .table td {
            vertical-align: middle;
        }

        /* Hover effects */
        .btn:hover {
            transform: translateY(-1px);
            transition: transform 0.2s;
        }

        /* Custom colors for status buttons */
        .btn-success {
            background-color: #4caf50;
            border-color: #4caf50;
        }

        .btn-danger {
            background-color: #f44336;
            border-color: #f44336;
        }

        .bg-{{setting('theme_color')}} {
            background-color: #5c6bc0;
            color: white;
        }
    </style>
@endsection
@push('scripts_lib')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Payment mode handling
            document.getElementById('paymentMode').addEventListener('change', function () {
                const tndField = document.getElementById('tndPriceField');
                const eurField = document.getElementById('eurPriceField');
                const tndInput = document.getElementById('tele_price_tnd');
                const eurInput = document.getElementById('tele_price_eur');

                switch (this.value) {
                    case 'TND':
                        tndField.style.display = 'block';
                        eurField.style.display = 'none';
                        tndInput.required = true;
                        eurInput.required = false;
                        break;
                    case 'EUR':
                        tndField.style.display = 'none';
                        eurField.style.display = 'block';
                        tndInput.required = false;
                        eurInput.required = true;
                        break;
                    case 'SPLIT':
                        tndField.style.display = 'block';
                        eurField.style.display = 'block';
                        tndInput.required = true;
                        eurInput.required = true;
                        break;
                    default:
                        tndField.style.display = 'none';
                        eurField.style.display = 'none';
                        tndInput.required = false;
                        eurInput.required = false;
                }
            });

            // Form validation
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                // Check form validity
                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                // Validate prices
                if (!validatePrices()) {
                    return;
                }

                // Show confirmation dialog
                Swal.fire({
                    title: 'Confirmer la création',
                    text: 'Voulez-vous créer cette téléconsultation ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Créer',
                    cancelButtonText: 'Annuler',
                    confirmButtonColor: '#5c6bc0',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Création en cours...',
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
        document.addEventListener('DOMContentLoaded', function () {
            // Handle status update buttons
            document.querySelectorAll('.update-status').forEach(button => {
                button.addEventListener('click', function () {
                    const roomId = this.dataset.roomId;
                    const status = this.dataset.status;

                    Swal.fire({
                        title: 'Confirmer le changement',
                        text: `Voulez-vous marquer cette consultation comme ${status === 'completed' ? 'terminée' : 'échouée'} ?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#5c6bc0',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Oui, confirmer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send AJAX request to update status
                            fetch(`/meet/${roomId}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ status: status })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Succès!',
                                            text: data.message,
                                            icon: 'success',
                                            timer: 1500
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire('Erreur', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                                });
                        }
                    });
                });
            });
        });
    </script>
@endpush
<!-- Add Bootstrap CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>