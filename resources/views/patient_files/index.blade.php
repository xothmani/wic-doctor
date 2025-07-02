<!-- resources/views/patient_files/index.blade.php -->
@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.create';
    $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
        ->with('readable')
        ->first();

    $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@section('content')
    @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-bold">
                            {{trans('lang.patient_files_plural')}}
                            <small class="mx-3 text-muted">|</small>
                            <small class="badge badge-soft-info px-3 py-1">
                                <i class="fas fa-user-injured mr-1"></i>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex shadow-sm">
                            <li class="breadcrumb-item">
                                <a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i>
                                    {{trans('lang.dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{trans('lang.patients_plural')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a
                                    href="{{ route('patient_files.index', $patient) }}">{{trans('lang.patient_files_plural')}}</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row">
                    <!-- Files Table (Main Content) -->
                    <div class="col-lg-12 col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <ul
                                    class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                                    <div class="d-flex flex-row">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="{{ url()->current() }}"><i
                                                    class="fa fa-list mr-2"></i>{{ trans('lang.patient_files_table') }}
                                            </a>
                                        </li>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('patient_files.create', $patient) }}"><i
                                                        class="fa fa-plus mr-2"></i>{{ trans('lang.patient_files_create') }}
                                                </a>
                                            </li>
                                        @endif
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" id="generatePublicUploadLinkTab" data-toggle="modal"
                                                data-target="#generatePublicUploadLinkModal">
                                                <i class="fa fa-link mr-2"></i>{{ trans('lang.generate_public_upload_link') }}
                                            </a>
                                        </li>
                                    </div>
                                    @if(isset($dataTable))
                                        @include('layouts.right_toolbar', compact('dataTable'))
                                    @endif
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="files-container">
                                    @include('patient_files.table')
                                </div>
                            </div>
                            <div class="modal fade" id="generatePublicUploadLinkModal" tabindex="-1" role="dialog"
                                aria-labelledby="generatePublicUploadLinkModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content rounded-20">
                                        <div class="modal-header bg-gradient-info text-white">
                                            <h5 class="modal-title" id="generatePublicUploadLinkModalLabel">
                                                <i class="fas fa-link mr-2"></i>{{ trans('lang.generate_public_upload_link') }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="public-upload-result" class="text-center">
                                                <p class="text-muted mb-4">
                                                    {{ trans('lang.click_to_generate_public_upload_link') }}
                                                </p>
                                                <form id="generatePublicUploadLinkForm"
                                                    action="{{ route('patient_files.generate_public_upload_link', $patient) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn bg-gradient-info text-white px-5 shadow-sm">
                                                        <span class="btn-text">{{ trans('lang.generate_link') }}</span>
                                                        <span class="btn-loading" style="display: none;">
                                                            <i
                                                                class="fas fa-spinner fa-spin mr-2"></i>{{ trans('lang.generating') }}...
                                                        </span>
                                                    </button>
                                                </form>
                                                <div id="public-upload-link-container" class="mt-4" style="display: none;">
                                                    <h6 class="mb-3 text-dark font-weight-bold">
                                                        {{ trans('lang.public_upload_link') }}
                                                    </h6>
                                                    <div class="input-group mb-3">
                                                        <input type="text" id="public-upload-link"
                                                            class="form-control border-right-0" readonly>
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-info" type="button" id="copyLinkBtn">
                                                                <i class="fas fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <h6 class="mb-3 text-dark font-weight-bold">{{ trans('lang.qr_code') }}</h6>
                                                    <img id="public-upload-qr" src="" alt="QR Code"
                                                        class="img-fluid rounded shadow-sm" style="max-width: 300px;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light"
                                                data-dismiss="modal">{{ trans('lang.close') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    {{ __('Vous n\'avez pas la permission (:permission) d\'accéder à cette page.', ['permission' => $readablePermission]) }}
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
        <script>

            $(document).ready(function () {
                $('#assignDoctorModal').on('show.bs.modal', function () {
                    const searchField = document.getElementById('doctorSearch');
                    if (searchField) {
                        searchField.value = '';
                        const newSearchField = searchField.cloneNode(true);
                        searchField.parentNode.replaceChild(newSearchField, searchField);
                        newSearchField.addEventListener('input', function () {
                            filterDoctorList(this);
                        });
                        setTimeout(() => newSearchField.focus(), 300);
                        document.querySelectorAll('.modal-doctor-item').forEach(item => {
                            item.style.display = 'none';
                        });
                    }
                });

                $('#assignDoctorModal').on('hidden.bs.modal', function () {
                    document.getElementById('selectedDoctorId').value = '';
                    document.querySelectorAll('.modal-doctor-item').forEach(item => {
                        item.classList.remove('active');
                        item.style.display = 'none';
                    });
                });
            });

            function filterDoctorList(input) {
                const searchTerm = input.value.toLowerCase().trim();
                document.querySelectorAll('.modal-doctor-item').forEach(item => {
                    const email = item.getAttribute('data-email').toLowerCase();
                    item.style.display = (searchTerm && email === searchTerm) ? '' : 'none';
                });
            }

            function selectDoctor(element) {
                const doctorId = element.getAttribute('data-value');
                document.getElementById('selectedDoctorId').value = doctorId;
                document.querySelectorAll('.modal-doctor-item').forEach(item => {
                    item.classList.remove('active');
                });
                element.classList.add('active');
            }

            document.getElementById('generatePublicUploadLinkForm')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const form = this;
                const btn = form.querySelector('button[type="submit"]');
                const btnText = btn.querySelector('.btn-text');
                const btnLoading = btn.querySelector('.btn-loading');

                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                btn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                        btn.disabled = false;

                        if (data.upload_url && data.qr_code) {
                            const linkContainer = document.getElementById('public-upload-link-container');
                            const linkInput = document.getElementById('public-upload-link');
                            const qrImage = document.getElementById('public-upload-qr');
                            linkInput.value = data.upload_url;
                            qrImage.src = data.qr_code;
                            linkContainer.style.display = 'block';
                        } else {
                            alert('{{ trans('lang.error_generating_link') }}');
                        }
                    })
                    .catch(error => {
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                        btn.disabled = false;
                        console.error('Error:', error);
                        alert('{{ trans('lang.error_generating_link') }}');
                    });
            });

            document.getElementById('copyLinkBtn')?.addEventListener('click', function () {
                const linkInput = document.getElementById('public-upload-link');
                linkInput.select();
                document.execCommand('copy');
                alert('{{ trans('lang.link_copied') }}');
            });

            $('#generatePublicUploadLinkModal').on('hidden.bs.modal', function () {
                const linkContainer = document.getElementById('public-upload-link-container');
                const linkInput = document.getElementById('public-upload-link');
                const qrImage = document.getElementById('public-upload-qr');
                linkInput.value = '';
                qrImage.src = '';
                linkContainer.style.display = 'none';
            });
        </script>
    @endpush
@endsection

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
    }

    .bg-gradient-primary {
        background: var(--primary-gradient);
    }

    .bg-gradient-info {
        background: var(--info-gradient);
    }

    .files-card,
    .doctors-card {
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .files-card:hover,
    .doctors-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .nav-link.hover-glow:hover {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .floating-btn {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        border-radius: 20px;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .floating-btn:hover {
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .doctors-list {
        padding: 0;
    }

    .doctor-item {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
        opacity: 0;
        animation: slideInUp 0.6s ease forwards;
        position: relative;
    }

    .doctor-item:hover {
        background: linear-gradient(90deg, rgba(116, 185, 255, 0.05) 0%, rgba(162, 155, 254, 0.05) 100%);
        transform: translateX(5px);
    }

    .doctor-item:last-child {
        border-bottom: none;
    }

    .doctor-avatar {
        position: relative;
        margin-right: 15px;
        flex-shrink: 0;
        height: 50px;
        width: 50px;
        overflow: hidden;
    }

    .avatar-img {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        background: var(--info-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .avatar-placeholder.error {
        background: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
    }

    .status-indicator {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid white;
    }

    .status-indicator.online {
        background: #00b894;
        box-shadow: 0 0 10px rgba(0, 184, 148, 0.5);
    }

    .doctor-info {
        flex-grow: 1;
        min-width: 0;
    }

    .doctor-name {
        margin: 0 0 5px 0;
        font-weight: 600;
        color: #2d3436;
        font-size: 1rem;
    }

    .specialities {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .speciality-badge {
        display: inline-block;
        padding: 3px 8px;
        background: linear-gradient(135deg, rgba(116, 185, 255, 0.1) 0%, rgba(162, 155, 254, 0.1) 100%);
        color: #5a67d8;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid rgba(116, 185, 255, 0.2);
    }

    .speciality-badge.more {
        background: #f1f3f4;
        color: #636e72;
    }

    .doctor-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .email-btn {
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        color: white;
    }

    .info-btn {
        background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%);
        color: white;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        color: white;
        text-decoration: none;
    }

    .empty-state {
        margin: 40px 0;
    }

    .empty-icon i {
        font-size: 4rem;
        opacity: 0.3;
    }

    .modal-content {
        border-radius: 20px;
        overflow: hidden;
    }

    .search-section {
        border-bottom: 1px solid #e9ecef;
    }

    .search-input-group {
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #74b9ff;
        z-index: 2;
    }

    .search-input {
        padding-left: 45px;
        border: 2px solid #e9ecef;
        border-radius: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        border-color: #74b9ff;
        box-shadow: 0 0 20px rgba(116, 185, 255, 0.2);
    }

    .doctors-modal-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .modal-doctor-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #f1f3f4;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .modal-doctor-item:hover {
        background: rgba(116, 185, 255, 0.05);
    }

    .modal-doctor-item.active {
        background: linear-gradient(90deg, rgba(116, 185, 255, 0.1) 0%, rgba(162, 155, 254, 0.1) 100%);
        border-left: 4px solid #74b9ff;
    }

    .modal-doctor-avatar {
        margin-right: 15px;
        height: 45px;
        width: 45px;
        border-radius: 15px;
        overflow: hidden;
        flex-shrink: 0
    }

    .modal-doctor-avatar .avatar-img,
    .modal-doctor-avatar .avatar-placeholder {
        width: 45px;
        height: 45px;
    }

    .modal-doctor-info {
        flex-grow: 1;
    }

    .modal-doctor-status {
        flex-shrink: 0;
    }

    .select-indicator {
        width: 25px;
        height: 25px;
        border: 2px solid #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .modal-doctor-item.active .select-indicator {
        background: var(--info-gradient);
        border-color: #74b9ff;
        color: white;
    }

    .modal-content.rounded-20 {
        border-radius: 20px;
    }

    .bg-gradient-info {
        background: var(--info-gradient);
    }

    .btn.bg-gradient-info {
        background: var(--info-gradient);
        border: none;
        transition: all 0.3s ease;
    }

    .btn.bg-gradient-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
    }

    #public-upload-link-container .input-group {
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    #public-upload-qr {
        border: 1px solid #e9ecef;
        padding: 10px;
        background: white;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge-soft-info {
        color: var(--info, #17a2b8);
        background-color: rgba(23, 162, 184, 0.1);
        border: 1px solid rgba(23, 162, 184, 0.2);
    }

    @media (max-width: 768px) {
        .floating-btn {
            position: static;
            transform: none;
            margin-top: 10px;
        }

        .doctor-item {
            padding: 15px;
        }

        .action-btn {
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }
    }
</style>