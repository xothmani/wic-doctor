@extends('layouts.app')

@push('css_lib')
    <!-- ...existing css imports... -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

    <style>
        .form-section {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }

        .intro-card {
            background: linear-gradient(135deg, #001f3f, #3F51B5);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 12px 15px;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #001f3f;
            box-shadow: 0 0 0 0.2rem rgba(92, 107, 192, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .btn-primary-custom {
            background: #001f3f;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background: #3F51B5;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(92, 107, 192, 0.2);
        }

        .custom-switch .custom-control-label::before {
            width: 45px;
            height: 24px;
        }

        .custom-switch .custom-control-label::after {
            width: 18px;
            height: 18px;
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success fade show">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div id="error-alert" class="alert alert-danger fade show">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-section">
                <!-- Introduction Card -->
                <div class="intro-card mb-5">
                    <h3 class="mb-3">Télésécrétariat</h3>
                    <p class="mb-2">
                        Le télésécrétariat vous permet de déléguer la gestion de votre agenda à un professionnel de
                        manière sécurisée et personnalisée.
                    </p>
                    <p class="mb-0">
                        Le télésecrétaire aura un accès direct à votre agenda avec des permissions personnalisées
                        pour gérer vos rendez-vous tout en respectant vos préférences.
                    </p>
                </div>

                {!! Form::open(['route' => 'doctor_telesecretariat.store_profile_management', 'method' => 'POST']) !!}
                <!-- Email Field -->
                <div class="mb-4">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="Entrez l'email du télésecrétaire" required>
                </div>

                <div class="row">
                    <!-- Start Date -->
                    <div class="col-md-4 mb-4">
                        <label for="start_date" class="form-label">Date de début</label>
                        <input type="date" id="start_date" name="start_date" class="form-control">
                    </div>

                    <!-- End Date -->
                    <div class="col-md-4 mb-4">
                        <label for="end_date" class="form-label">Date de fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control">
                    </div>

                    <!-- Active Switch -->
                    <div class="col-md-4 mb-4">
                        <label class="form-label d-block">Statut</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1">
                            <label class="custom-control-label" for="is_active">Actif</label>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('doctor_telesecretariat.create') }}" class="btn btn-light border me-2">
                        <i class="fa fa-undo me-1"></i> {{ trans('lang.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fa fa-save me-1"></i> {{ trans('lang.save') }}
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts_lib')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/summernote/summernote.min.js') }}"></script>
    <script src="{{ asset('vendor/dropzone/min/dropzone.min.js') }}"></script>
    <script src="{{ asset('vendor/moment/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
    <script>
        $(document).ready(function () {
            // Alert auto-dismiss script
            document.addEventListener("DOMContentLoaded", function () {
                setTimeout(function () {
                    const errorAlert = document.getElementById('error-alert');
                    const successAlert = document.getElementById('success-alert');

                    if (errorAlert) {
                        errorAlert.style.transition = "opacity 1s";
                        errorAlert.style.opacity = "0";
                        setTimeout(() => errorAlert.remove(), 1000);
                    }

                    if (successAlert) {
                        successAlert.style.transition = "opacity 1s";
                        successAlert.style.opacity = "0";
                        setTimeout(() => successAlert.remove(), 1000);
                    }
                }, 5000);
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            const today = new Date().toISOString().split('T')[0];
            $('#start_date').attr('min', today);

            // Date validation handlers
            $('#start_date').on('change', function () {
                const startDate = $(this).val();
                $('#end_date').attr('min', startDate);

                // If end date is before start date, clear it
                if ($('#end_date').val() && $('#end_date').val() < startDate) {
                    $('#end_date').val('');
                }
            });

            // Form submission validation
            $('form').on('submit', function (e) {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Erreur de validation',
                        text: 'La date de début est obligatoire',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#001f3f'
                    });
                    return false;
                }

                if (endDate && endDate < startDate) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Erreur de validation',
                        text: 'La date de fin doit être postérieure à la date de début',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#001f3f'
                    });
                    return false;
                }
            });

        });
    </script>
@endpush