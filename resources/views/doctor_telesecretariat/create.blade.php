@extends('layouts.app')

@push('css_lib')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
@endpush

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">{{ trans('lang.doctor_telesecretariat') }}
                        <small class="mx-3">|</small><small>{{ trans('lang.doctor_telesecretariat_add') }}</small>
                    </h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                {{ trans('lang.dashboard') }}</a></li>
                        <li class="breadcrumb-item">
                            <a href="{!! route('doctor_requests.index') !!}">{{ trans('lang.doctor_telesecretariat') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('lang.doctor_telesecretariat_add') }}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    @if(session('success'))
        <div id="success-alert" class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div id="error-alert" class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        @include('adminlte-templates::common.errors')
        <div class="clearfix"></div>

        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i
                                class="fa fa-plus mr-2"></i>{{ trans('lang.doctor_telesecretariat_add') }}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'doctor_telesecretariat.store']) !!}
                <div class="row">
                    @include('doctor_telesecretariat.fields')
                </div>
                {!! Form::close() !!}
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    <!-- Add this modal markup -->
    <div class="modal fade" id="userSetupModal" tabindex="-1" role="dialog" aria-labelledby="userSetupModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userSetupModalLabel">
                        <i class="fas fa-user-plus mr-2"></i>{{ trans('lang.setup_user_account') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="userSetupForm">
                        <!-- Email from previous form (readonly) -->
                        <div class="form-group">
                            <label>{{ trans('lang.user_email') }}</label>
                            <input type="email" class="form-control" id="setupEmail" readonly>
                        </div>

                        <!-- Name Field -->
                        <div class="form-group">
                            <label>{{ trans('lang.user_name') }} *</label>
                            <input type="text" class="form-control" id="setupName" required>
                        </div>

                        <!-- Password Field -->
                        <div class="form-group">
                            <label>{{ trans('lang.user_password') }} *</label>
                            <input type="password" class="form-control" id="setupPassword" required>
                        </div>

                        <!-- Phone Field -->
                        <div class="form-group">
                            <label>{{ trans('lang.phone_number') }}</label>
                            <input type="tel" class="form-control" id="setupPhone">
                        </div>

                        <!-- Role Selection -->
                        <div class="form-group">
                            <label>{{ trans('lang.user_role') }} *</label>
                            <select class="form-control select2" id="setupRole" required>
                                <option value="telesecretariat">{{ trans('lang.telesecretariat') }}</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>{{ trans('lang.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="saveUserSetup">
                        <i class="fas fa-save mr-2"></i>{{ trans('lang.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.media_modal')
@endsection

@push('scripts_lib')
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

            $('form[route="doctor_telesecretariat.store"]').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let formData = new FormData(form[0]);

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            // Pre-fill email in modal
                            $('#setupEmail').val(response.email);

                            // Show the modal
                            $('#userSetupModal').modal('show');
                        }
                    },
                    error: function (xhr) {
                        // Show error message from server
                        Swal.fire({
                            icon: 'error',
                            title: '{{ trans("lang.error") }}',
                            text: xhr.responseJSON.message || '{{ trans("lang.error_saving") }}'
                        });
                    }
                });
            });
            // Handle user setup form submission
            $('#saveUserSetup').click(function () {
                let userData = {
                    email: $('#setupEmail').val(),
                    name: $('#setupName').val(),
                    password: $('#setupPassword').val(),
                    phone_number: $('#setupPhone').val(),
                    role: $('#setupRole').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: '{{ route("Doctors_users.store") }}',
                    method: 'POST',
                    data: userData,
                    success: function (response) {
                        if (response.success) {
                            $('#userSetupModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: '{{ trans("lang.user_created_successfully") }}',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = '{{ route("doctor_telesecretariat.index") }}';
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ trans("lang.error") }}',
                            text: xhr.responseJSON.message || '{{ trans("lang.error_creating_user") }}'
                        });
                    }
                });
            });
        });
    </script>
@endpush