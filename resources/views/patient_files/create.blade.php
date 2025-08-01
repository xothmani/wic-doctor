<!-- resources/views/patient_files/create.blade.php -->
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
                            {{trans('lang.patient_files_create') }}
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
                            <li class="breadcrumb-item active">{{trans('lang.patient_files_create')}}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        

        <div class="content">
            
            <div class="clearfix"></div>
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                        <div class="d-flex flex-row">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('patient_files.index', $patient) }}"><i
                                        class="fa fa-list mr-2"></i>{{trans('lang.patient_files_table')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                        class="fa fa-plus mr-2"></i>{{trans('lang.patient_files_create')}}
                                </a>
                            </li>
                        </div>
                    </ul>
                </div>

                <div class="card-body p-5">
                    <!-- Upload Icon and Title -->
                    <div class="text-center mb-5">
                        <h3 class="text-primary mb-2">{{trans('lang.upload_patient_file')}}</h3>
                        <p class="text-muted">{{trans('lang.upload_file_description')}}</p>
                    </div>

                    <form action="{{ route('patient_files.store', $patient) }}" method="POST" enctype="multipart/form-data"
                        id="uploadForm">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <!-- Modern File Upload Area -->
                                <div class="upload-area mb-4" id="uploadArea">
                                    <div class="upload-content">
                                        <i class="fas fa-file-upload text-primary mb-3" style="font-size: 3rem;"></i>
                                        <h5 class="mb-2">{{trans('lang.drag_drop_files')}}</h5>
                                        <p class="text-muted mb-3">{{trans('lang.or_click_to_select')}}</p>
                                        <input type="file" name="file" id="fileInput" class="file-input" required>
                                        <button type="button" class="btn btn-outline-primary" id="browseBtn">
                                            <i class="fas fa-folder-open mr-2"></i>{{trans('lang.browse_files')}}
                                        </button>
                                    </div>
                                    <div class="upload-overlay">
                                        <i class="fas fa-download text-white mb-2" style="font-size: 2rem;"></i>
                                        <p class="text-white mb-0">{{trans('lang.drop_files_here')}}</p>
                                    </div>
                                </div>

                                @error('file')
                                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        {{ $message }}
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @enderror

                                <!-- File Preview Area -->
                                <div class="file-preview-container mb-4" id="filePreview" style="display: none;">
                                    <div class="file-preview-item">
                                        <div class="d-flex align-items-center">
                                            <div class="file-icon mr-3">
                                                <i class="fas fa-file text-primary" style="font-size: 2rem;"></i>
                                            </div>
                                            <div class="file-info flex-grow-1">
                                                <h6 class="mb-1" id="fileName"></h6>
                                                <small class="text-muted" id="fileSize"></small>
                                            </div>
                           <!--                  <div>
                                            <p>téléchargé avec succées</p>
                                            
                                        </div> -->
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                                <i class="fas fa-times"></i>
                                            </button>

                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: 100%;"></div>
                                            
                                        </div>
                                           
                                    </div>
                                </div>

                                <!-- Supported File Types -->
                                <div class="alert alert-info border-0 bg-light-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info mr-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <h6 class="alert-heading mb-2 badge-title" style="font-size: 1rem; color: #007BFF;">
                                                {{trans('lang.supported_file_types')}}
                                            </h6>
                                            <div class="file-types">
                                                <span class="badge badge-soft-primary mr-1 mb-1">PDF</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">DOC/DOCX</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">Images</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">Excel</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">PowerPoint</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">Archives</span>
                                                <span class="badge badge-soft-primary mr-1 mb-1">Medical</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description Field -->
                                <div class="form-group">
                                    <label for="description" class="form-label text-dark font-weight-bold">
                                        <i class="fas fa-comment-medical mr-2 text-primary"></i>
                                        {{trans('lang.description')}}
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text bg-light border-right-0">
                                                <i class="fas fa-edit text-primary"></i>
                                            </div>
                                        </div>
                                        <textarea name="description" class="form-control border-left-0 shadow-sm" rows="4"
                                            placeholder="{{trans('lang.patient_file_description_placeholder')}}"
                                            style="resize: vertical;">{{ old('description') }}</textarea>
                                    </div>
                                    @error('description')
                                        <div class="text-danger mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-8">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-light btn-lg px-4">
                                        {{trans('lang.cancel')}}
                                    </a>
                                    <button type="submit"
                                        class="btn bg-{{setting('theme_color')}} text-white btn-lg px-5 shadow-sm upload-btn">
                                        <span class="btn-text">{{trans('lang.upload_file')}}</span>
                                        <span class="btn-loading" style="display: none;">
                                            <i class="fas fa-spinner fa-spin mr-2"></i>{{trans('lang.uploading')}}...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
@endsection

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary, #007bff) 0%, var(--info, #17a2b8) 100%);
    }

    .upload-icon-container {
        position: relative;
        display: inline-block;
    }

    .upload-icon-wrapper {
        position: relative;
        display: inline-block;
    }

    @keyframes pulse {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0.8;
        }

        50% {
            transform: translate(-50%, -50%) scale(1.2);
            opacity: 0.2;
        }

        100% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0.8;
        }
    }

    .upload-area {
        position: relative;
        border: 3px dashed #dee2e6;
        border-radius: 15px;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa),
            linear-gradient(45deg, #f8f9fa 25%, transparent 25%, transparent 75%, #f8f9fa 75%, #f8f9fa);
        background-size: 20px 20px;
        background-position: 0 0, 10px 10px;
        cursor: pointer;
        z-index: 1;
    }

    .upload-area .upload-content {
        position: relative;
        z-index: 2;
        pointer-events: auto;
    }

    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 1;
        pointer-events: none;
    }

    .upload-area .upload-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 123, 255, 0.9);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 3;
        pointer-events: none;
    }

    .upload-area.dragover .upload-overlay {
        opacity: 1;
        pointer-events: auto;
    }

    #browseBtn {
        position: relative;
        z-index: 4;
        pointer-events: auto;
    }

    .upload-area:hover,
    .upload-area.dragover {
        border-color: var(--primary, #007bff);
        background-color: rgba(0, 123, 255, 0.05);
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 123, 255, 0.2);
    }

    .file-preview-container {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
    }

    .file-preview-item {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .bg-light-info {
        background-color: rgba(23, 162, 184, 0.1) !important;
    }

    .badge-soft-primary {
        color:  #11b8aa;
        background-color: rgba(0, 123, 255, 0.1);
        border: 1px solid rgba(0, 123, 255, 0.2);
    }

    .badge-soft-info {
        color:  #11b8aa;
        background-color: rgba(23, 162, 184, 0.1);
        border: 1px solid rgba(23, 162, 184, 0.2);
    }

    .badge-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color:  #11b8aa;
    }

    .upload-btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
    }

    .form-label {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }

    .input-group-lg .form-control {
        font-size: 1rem;
    }

    .shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.querySelector('.upload-btn');
        const browseBtn = document.getElementById('browseBtn');

        browseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.click();
        });

        uploadArea.addEventListener('click', function (e) {
            if (!uploadArea.classList.contains('dragover') && e.target !== browseBtn) {
                fileInput.click();
            }
        });

        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (!uploadArea.contains(e.relatedTarget)) {
                uploadArea.classList.remove('dragover');
            }
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                fileInput.files = dt.files;
                handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', function (e) {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.style.display = 'block';
            uploadArea.style.display = 'none';
        }

        window.removeFile = function () {
            fileInput.value = '';
            filePreview.style.display = 'none';
            uploadArea.style.display = 'block';
        };

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        uploadForm.addEventListener('submit', function (e) {
            const btnText = uploadBtn.querySelector('.btn-text');
            const btnLoading = uploadBtn.querySelector('.btn-loading');

            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            uploadBtn.disabled = true;

            setTimeout(() => {
                if (document.querySelector('.alert-danger')) {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    uploadBtn.disabled = false;
                }
            }, 1000);
        });
    });
</script>