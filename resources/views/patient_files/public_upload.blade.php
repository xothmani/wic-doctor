@extends('layouts.auth.default')

<body class="bg-light">
    <div class="container mx-auto p-4">
        <div class="card shadow-sm">
            <div class="alert alert-info border-0 bg-dark-info shadow-sm"
                style="border-radius: 10px 10px 0 0 !important;">
                <i class="fas fa-user-md mr-2"></i>
                {{ trans('lang.upload_requested_by') }} <strong>{{ $user->name }} {{ $user->lastname }}</strong><br>
                <i class="fas fa-user-injured mr-2"></i>
                {{ trans('lang.upload_for_patient') }} <strong>{{ $patient->first_name }}
                    {{ $patient->last_name }}</strong>
            </div>
            <div class="card-body p-5">
                <!-- Upload Icon and Title -->
                <div class="text-center mb-5">
                    <h3 class="text-primary mb-2">{{ trans('lang.upload_patient_file') }}</h3>
                    <p class="text-muted">{{ trans('lang.upload_file_description') }}</p>
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                @endif
                <div class="text-center mb-4">
                    <div id="countdown-timer" class="alert alert-info border-0 bg-light-info shadow-sm">
                        <i class="fas fa-clock mr-2 text-info"></i>
                        <span id="timer-text">{{ trans('lang.link_expires_in') }} <span
                                id="timer-value">Loading...</span></span>
                        <span id="timer-expired" style="display: none;">
                            <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                            {{ trans('lang.link_expired') }}
                        </span>
                    </div>
                </div>
                <form
                    action="{{ route('patient_files.public_upload', ['patient' => $patient->id, 'user' => $user->id, 'expires' => request()->query('expires'), 'signature' => request()->query('signature')]) }}"
                    method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <!-- Modern File Upload Area -->
                            <div class="upload-area mb-4" id="uploadArea">
                                <div class="upload-content">
                                    <i class="fas fa-file-upload text-primary mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="mb-2">{{ trans('lang.drag_drop_files') }}</h5>
                                    <p class="text-muted mb-3">{{ trans('lang.or_click_to_select') }}</p>
                                    <input type="file" name="file" id="fileInput" class="file-input" required>
                                    <button type="button" class="btn btn-outline-primary" id="browseBtn">
                                        <i class="fas fa-folder-open mr-2"></i>{{ trans('lang.browse_files') }}
                                    </button>
                                </div>
                                <div class="upload-overlay">
                                    <i class="fas fa-download text-white mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-white mb-0">{{ trans('lang.drop_files_here') }}</p>
                                </div>
                            </div>

                            @error('file')
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    {{ $message }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
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
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="removeFile()">
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
                                        <h6 class="alert-heading mb-2 badge-title"
                                            style="font-size: 1rem; color: #007BFF;">
                                            {{ trans('lang.supported_file_types') }}
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
                                    {{ trans('lang.description') }}
                                </label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-light border-right-0">
                                            <i class="fas fa-edit text-primary"></i>
                                        </div>
                                    </div>
                                    <textarea name="description" class="form-control border-left-0 shadow-sm" rows="4"
                                        placeholder="{{ trans('lang.patient_file_description_placeholder') }}"
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
                            <div class="d-flex justify-content-end">
                                <button type="submit"
                                    class="btn bg-info }} text-white btn-lg px-5 shadow-sm upload-btn">
                                    <span class="btn-text">{{ trans('lang.upload_file') }}</span>
                                    <span class="btn-loading" style="display: none;">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>{{ trans('lang.uploading') }}...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary, #007bff) 0%, var(--info, #17a2b8) 100%);
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
            color: var(--primary, #007bff);
            background-color: rgba(0, 123, 255, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.2);
        }

        .badge-soft-info {
            color: var(--info, #17a2b8);
            background-color: rgba(23, 162, 184, 0.1);
            border: 1px solid rgba(23, 162, 184, 0.2);
        }

        .badge-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--info, #17a2b8);
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

        #countdown-timer {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-size: 1rem;
            border-radius: 10px;
        }

        #timer-text,
        #timer-expired {
            font-weight: 500;
        }

        #timer-text {
            color: black;
        }

        #timer-value {
            font-weight: 700;
            color: var(--info, #17a2b8);
        }

        #timer-expired {
            color: var(--danger, #dc3545);
        }


        .upload-btn.btn-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
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
            const expiresTimestamp = parseInt('{{ request()->query('expires') }}') * 1000; // Convert seconds to milliseconds
            const timerValue = document.getElementById('timer-value');
            const timerText = document.getElementById('timer-text');
            const timerExpired = document.getElementById('timer-expired');

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

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = expiresTimestamp - now;

                if (timeLeft <= 0) {
                    timerText.style.display = 'none';
                    timerExpired.style.display = 'inline';
                    uploadBtn.disabled = true;
                    uploadBtn.classList.add('btn-disabled');
                    clearInterval(countdownInterval);
                    return;
                }

                const hours = Math.floor(timeLeft / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                timerValue.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }

            if (!isNaN(expiresTimestamp) && expiresTimestamp > 0) {
                updateCountdown();
                const countdownInterval = setInterval(updateCountdown, 1000);
            } else {
                timerText.style.display = 'none';
                timerExpired.style.display = 'inline';
                uploadBtn.disabled = true;
                uploadBtn.classList.add('btn-disabled');
            }
        });
    </script>
</body>