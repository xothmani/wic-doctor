<!-- resources/views/patient_files/show.blade.php -->
@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.show';
    $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
        ->with('readable')
        ->first();

    $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@section('content')
    @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-bold">
                            {{trans('lang.patient_file_details')}}
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
                            <li class="breadcrumb-item active">{{trans('lang.file_details')}}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row">
                    <!-- Main File Details Card -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card shadow-lg border-0 file-details-card">
                            <div class="card-header bg-gradient-primary text-white position-relative">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="header-info d-flex flex-column" style="margin: 0 1vw;">
                                        <h3 class="card-title mb-1 d-flex align-items-center">
                                            <i class="fas fa-file-medical mr-2"></i>
                                            {{ trans('lang.file_details') }}
                                        </h3>
                                        <p class="card-subtitle mb-0">
                                            {{ trans('lang.uploaded') }} {{ $file->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="header-actions">
                                        @if(auth()->user()->hasPermissionInContext('patient_files.download', $doctorId))
                                            <button class="header-button header-download-btn"
                                                onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')"
                                                data-toggle="tooltip" title="{{trans('lang.download')}}">
                                                <i class="fas fa-download mr-1"></i> {{ trans('lang.download') }}
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.edit', $doctorId))
                                            <button class="header-button header-edit-btn"
                                                onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'"
                                                data-toggle="tooltip" title="{{trans('lang.edit')}}">
                                                <i class="fas fa-edit mr-1"></i> {{ trans('lang.edit') }}
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button class="header-button header-assign-btn" data-toggle="modal"
                                                data-target="#assignAccessModal" data-toggle="tooltip"
                                                title="{{trans('lang.assign_access')}}">
                                                <i class="fas fa-user-plus mr-1"></i> {{ trans('lang.assign_access') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <!-- File Preview Section -->
                                <div class="file-preview-section">
                                    <div class="file-icon-display">
                                        @php
                                            $extension = pathinfo($file->file_name, PATHINFO_EXTENSION);
                                            $iconData = match (strtolower($extension)) {
                                                'pdf' => ['icon' => 'fas fa-file-pdf', 'color' => 'danger', 'bg' => 'rgba(220, 53, 69, 0.1)'],
                                                'doc', 'docx' => ['icon' => 'fas fa-file-word', 'color' => 'primary', 'bg' => 'rgba(0, 123, 255, 0.1)'],
                                                'xls', 'xlsx' => ['icon' => 'fas fa-file-excel', 'color' => 'success', 'bg' => 'rgba(40, 167, 69, 0.1)'],
                                                'ppt', 'pptx' => ['icon' => 'fas fa-file-powerpoint', 'color' => 'warning', 'bg' => 'rgba(255, 193, 7, 0.1)'],
                                                'jpg', 'jpeg', 'png', 'gif', 'bmp' => ['icon' => 'fas fa-file-image', 'color' => 'info', 'bg' => 'rgba(23, 162, 184, 0.1)'],
                                                'zip', 'rar', '7z' => ['icon' => 'fas fa-file-archive', 'color' => 'secondary', 'bg' => 'rgba(108, 117, 125, 0.1)'],
                                                'txt' => ['icon' => 'fas fa-file-alt', 'color' => 'dark', 'bg' => 'rgba(52, 58, 64, 0.1)'],
                                                default => ['icon' => 'fas fa-file', 'color' => 'primary', 'bg' => 'rgba(0, 123, 255, 0.1)']
                                            };
                                        @endphp
                                        <div class="file-icon-wrapper" style="background: {{ $iconData['bg'] }}">
                                            <i class="{{ $iconData['icon'] }} text-{{ $iconData['color'] }}"></i>
                                            <div class="file-extension-badge">{{ strtoupper($extension ?? 'FILE') }}</div>
                                        </div>
                                    </div>

                                    <div class="file-basic-info">
                                        <h4 class="file-title">{{ $file->file_name }}</h4>
                                        <div class="file-stats">
                                            <div class="stat-item">
                                                <i class="fas fa-hdd text-info"></i>
                                                <span class="stat-label">{{ trans('lang.size') }}:</span>
                                                <span class="stat-value">
                                                    @if(isset($file->file_size) && $file->file_size)
                                                        {{ number_format($file->file_size / 1024 / 1024, 2) }} MB
                                                    @else
                                                        {{ trans('lang.unknown') }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="stat-item">
                                                <i class="fas fa-file-alt text-primary"></i>
                                                <span class="stat-label">{{ trans('lang.type') }}:</span>
                                                <span class="stat-value">{{ strtoupper($extension ?? 'Unknown') }}</span>
                                            </div>
                                            <div class="stat-item">
                                                <i class="fas fa-calendar text-success"></i>
                                                <span class="stat-label">{{ trans('lang.uploaded') }}:</span>
                                                <span
                                                    class="stat-value">{{ $file->created_at->format('M d, Y \a\t g:i A') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- File Description -->
                                <div class="file-description-section">
                                    <div class="section-header">
                                        <h5 class="section-title">
                                            <i class="fas fa-comment-medical text-primary mr-2"></i>
                                            {{ trans('lang.description') }}
                                        </h5>
                                    </div>
                                    <div class="description-content">
                                        @if($file->description)
                                            <p class="description-text">{{ $file->description }}</p>
                                        @else
                                            <div class="no-description">
                                                <i class="fas fa-info-circle text-muted mr-2"></i>
                                                <span class="text-muted">{{ trans('lang.no_description_provided') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Assigned Users -->
                                <div class="assigned-users-section">
                                    <div class="section-header">
                                        <h5 class="section-title">
                                            <i class="fas fa-users text-info mr-2"></i>
                                            {{ trans('lang.users_with_access') }}
                                        </h5>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button type="button" class="btn btn-light btn-sm" data-toggle="modal"
                                                data-target="#assignAccessModal">
                                                <i class="fas fa-user-plus mr-1"></i> {{ trans('lang.assign_access') }}
                                            </button>
                                        @endif
                                    </div>
                                    <div class="users-list">
                                        @php
                                            $fileUsers = \App\Models\PatientFileUser::where('patient_file_id', $file->id)
                                                ->where('patient_id', $patient->id)
                                                ->where(function ($query) {
                                                    $query->whereNull('expiration_date')
                                                          ->orWhere('expiration_date', '>', now());
                                                })
                                                ->with('user')
                                                ->get();
                                        @endphp
                                        @if($fileUsers->isEmpty())
                                            <div class="empty-state text-center py-4">
                                                <div class="empty-icon mb-3">
                                                    <i class="fas fa-users text-muted"></i>
                                                </div>
                                                <p class="text-muted mb-0">{{ trans('lang.no_users_with_access') }}</p>
                                            </div>
                                        @else
                                            @foreach($fileUsers as $fileUser)
                                                @if($fileUser->user)
                                                    <div class="user-item">
                                                        <div class="user-avatar">
                                                            @if($fileUser->user->media->isNotEmpty())
                                                                <img src="{{ $fileUser->user->media->first()->getUrl() }}"
                                                                    alt="{{ $fileUser->user->name }}" class="avatar-img">
                                                            @else
                                                                <div class="avatar-placeholder">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="user-info">
                                                            <h6 class="user-name">{{ $fileUser->user->name }}</h6>
                                                            <small class="text-muted">{{ $fileUser->user->email }}</small>
                                                            @if($fileUser->expiration_date)
                                                                <div class="expiration-info">
                                                                    <span
                                                                        class="badge badge-warning">{{ trans('lang.expires') }} {{ $fileUser->expiration_date->diffForHumans() }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Uploader Information -->
                        <div class="card sidebar-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-user-md"></i>
                                    {{ trans('lang.uploaded_by') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="uploader-profile">
                                    <div class="uploader-avatar">
                                        @if($file->uploader && $file->uploader->media->isNotEmpty())
                                            <img src="{{ $file->uploader->media->first()->getUrl() }}"
                                                alt="{{ $file->uploader->name }}" class="avatar-img">
                                        @else
                                            <div class="avatar-placeholder">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="uploader-info">
                                        <h6 class="uploader-name">
                                            {{ $file->uploader->name ?? trans('lang.unknown_uploader') }}
                                        </h6>
                                        @if($file->uploader && $file->uploader->doctor && $file->uploader->doctor->specialities && $file->uploader->doctor->specialities->isNotEmpty())
                                            <div class="specialities">
                                                @foreach($file->uploader->doctor->specialities->take(2) as $speciality)
                                                    <span class="speciality-badge">{{ $speciality->name }}</span>
                                                @endforeach
                                                @if($file->uploader->doctor->specialities->count() > 2)
                                                    <span class="speciality-badge more">
                                                        +{{ $file->uploader->doctor->specialities->count() - 2 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="contact-info">
                                            @if($file->uploader && $file->uploader->email)
                                                <div class="contact-item">
                                                    <i class="fas fa-envelope"></i>
                                                    <span>{{ $file->uploader->email }}</span>
                                                </div>
                                            @endif
                                            @if($file->uploader && $file->uploader->phone)
                                                <div class="contact-item">
                                                    <i class="fas fa-phone"></i>
                                                    <span>{{ $file->uploader->phone }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                <a href="{{ route('patient_files.create', $patient) }}" class="action-button upload-btn">
                                    <i class="fas fa-plus"></i>
                                    <span>{{ trans('lang.upload_new') }}</span>
                                </a>
                            @endif
                            @if(
                                    auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()) &&
                                    $file->uploader && $file->uploader->id === auth()->user()->id
                                )
                                <a onclick="confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')"
                                    data-toggle="tooltip" class="action-button delete-btn">
                                    <i class="fas fa-trash"></i>
                                    <span>{{ trans(key: 'lang.delete_file') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
            <div class="modal fade" id="assignAccessModal" tabindex="-1" role="dialog" aria-labelledby="assignAccessModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-primary text-white border-0">
                            <h5 class="modal-title" id="assignAccessModalLabel">
                                <i class="fas fa-user-plus mr-2"></i>{{ trans('lang.assign_access') }}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <!-- Search Input -->
                            <div class="search-section p-4 bg-light">
                                <div class="search-input-group">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" id="userSearch"
                                        placeholder="{{ trans('lang.enter_user_email') }}...">
                                </div>
                            </div>

                            <!-- Users List -->
                            <form action="{{ route('patient_files.assign_access', $patient) }}" method="POST" id="assignAccessForm">
                                @csrf
                                <div class="users-modal-list" id="userList">
                                    @foreach($allUsers as $user)
                                        @if($user->name && $user->email)
                                            <div class="modal-user-item user-item-modal" data-value="{{ $user->id }}"
                                                data-email="{{ $user->email }}" style="display: none;"
                                                onclick="selectUser(this)">
                                                <div class="modal-user-avatar">
                                                    @if($user->media->isNotEmpty())
                                                        <img src="{{ $user->media->first()->getUrl() }}" alt="{{ $user->name }}"
                                                            class="avatar-img">
                                                    @else
                                                        <div class="avatar-placeholder">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-user-info">
                                                    <h6 class="user-name">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                    @if($user->doctor && $user->doctor->specialities->isNotEmpty())
                                                        <div class="specialities">
                                                            @foreach($user->doctor->specialities->take(3) as $speciality)
                                                                <span class="speciality-badge">{{ $speciality->name }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-user-status">
                                                    @if($fileUsers->pluck('user_id')->contains($user->id))
                                                        <span class="badge badge-success">{{ trans('lang.already_assigned') }}</span>
                                                    @else
                                                        <div class="select-indicator">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="form-group p-4">
                                    <label for="expiration_date">{{ trans('lang.expiration_date') }}</label>
                                    <input type="date" name="expiration_date" id="expiration_date" class="form-control">
                                </div>
                                <input type="hidden" name="user_id" id="selectedUserId" required>
                                <input type="hidden" name="patient_file_id" value="{{ $file->id }}">
                            </form>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ trans('lang.close') }}</button>
                            <button type="submit" class="btn btn-primary" form="assignAccessForm">
                                <i class="fas fa-user-plus mr-1"></i>{{ trans('lang.assign') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <script>
            function downloadFile(url) {
                window.open(url, '_blank');
            }

            function confirmDelete(url) {
                if (confirm('{{trans("lang.confirm_delete_file")}}')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();

                $('#assignAccessModal').on('show.bs.modal', function () {
                    const searchField = document.getElementById('userSearch');
                    if (searchField) {
                        searchField.value = '';
                        const newSearchField = searchField.cloneNode(true);
                        searchField.parentNode.replaceChild(newSearchField, searchField);
                        newSearchField.addEventListener('input', function () {
                            filterUserList(this);
                        });
                        setTimeout(() => newSearchField.focus(), 300);
                        document.querySelectorAll('.modal-user-item').forEach(item => {
                            item.style.display = 'none';
                        });
                    }
                });

                $('#assignAccessModal').on('hidden.bs.modal', function () {
                    document.getElementById('selectedUserId').value = '';
                    document.getElementById('expiration_date').value = '';
                    document.querySelectorAll('.modal-user-item').forEach(item => {
                        item.classList.remove('active');
                        item.style.display = 'none';
                    });
                });
            });

            function filterUserList(input) {
                const searchTerm = input.value.toLowerCase().trim();
                document.querySelectorAll('.modal-user-item').forEach(item => {
                    const email = item.getAttribute('data-email').toLowerCase();
                    item.style.display = (searchTerm && email.includes(searchTerm)) ? '' : 'none';
                });
            }

            function selectUser(element) {
                const userId = element.getAttribute('data-value');
                document.getElementById('selectedUserId').value = userId;
                document.querySelectorAll('.modal-user-item').forEach(item => {
                    item.classList.remove('active');
                });
                element.classList.add('active');
            }
        </script>
    @else
        <div class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-lg border-0 permission-denied-card">
                            <div class="card-body text-center p-5">
                                <div class="permission-icon mb-4">
                                    <i class="fas fa-lock text-warning" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="text-dark mb-3">{{ trans('lang.access_denied') }}</h4>
                                <p class="text-muted mb-4">{{ trans('lang.no_permission_message') }}</p>
                                <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left mr-2"></i>{{ trans('lang.go_back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        --danger-gradient: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
        --warning-gradient: linear-gradient(135deg, #fdcb6e 0%, #f39c12 100%);
        --secondary-gradient: linear-gradient(135deg, #a4a4a4 0%, #6c757d 100%);
        --dark-gradient: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
        --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
        --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .file-details-card,
    .uploader-card,
    .metadata-card,
    .actions-card,
    .permission-denied-card {
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        animation: slideInUp 0.6s ease forwards;
    }

    .file-details-card:hover,
    .uploader-card:hover,
    .metadata-card:hover,
    .actions-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .card-header.bg-gradient-primary {
        background: var(--primary-gradient) !important;
    }

    .card-header.bg-gradient-info {
        background: var(--info-gradient) !important;
    }

    .card-header.bg-gradient-secondary {
        background: var(--secondary-gradient) !important;
    }

    .card-header.bg-gradient-dark {
        background: var(--dark-gradient) !important;
    }

    .file-preview-section {
        padding: 40px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        gap: 30px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .file-icon-display {
        flex-shrink: 0;
    }

    .file-icon-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: var(--shadow-soft);
        transition: all 0.3s ease;
    }

    .file-icon-wrapper:hover {
        transform: scale(1.05);
        box-shadow: var(--shadow-medium);
    }

    .file-icon-wrapper i {
        font-size: 4rem;
        transition: all 0.3s ease;
    }

    .file-extension-badge {
        position: absolute;
        bottom: -10px;
        right: -10px;
        background: white;
        color: #667eea;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 12px;
        box-shadow: var(--shadow-soft);
        border: 3px solid #f8f9fa;
    }

    .file-basic-info {
        flex-grow: 1;
    }

    .file-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 10px;
        line-height: 1;
        word-break: break-word;
    }

    .file-stats {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .stat-item:hover {
        transform: translateX(10px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .stat-item i {
        font-size: 1rem;
        width: 15px;
        text-align: center;
    }

    .stat-label {
        font-weight: 600;
        color: #636e72;
        margin-right: 5px;
    }

    .stat-value {
        font-weight: 700;
        color: #2d3436;
    }

    /* Section Styles */
    .file-description-section,
    .file-navigation-section {
        padding: 30px 40px;
    }

    .section-header {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f3f4;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #2d3436;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .description-content {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 15px;
        border-left: 4px solid #667eea;
    }

    .description-text {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #495057;
        margin: 0;
    }

    .no-description {
        display: flex;
        align-items: center;
        font-style: italic;
    }

    .action-button {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        margin: 20px 20 0 20;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: var(--shadow-soft);
    }

    .header-button {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        box-shadow: var(--shadow-soft);
    }

    .back-btn {
        background: var(--secondary-gradient);
        color: white;
    }

    .upload-btn {
        background: var(--success-gradient);
        color: white;
    }

    .download-btn {
        background: var(--info-gradient);
        color: white;
    }

    .delete-btn {
        background: var(--danger-gradient);
        color: white;
    }

    .header-download-btn {
        background: rgba(0, 0, 0, 0.2);
        color: white;
    }

    .header-edit-btn {
        background: var(--success-color);
        color: white;
    }

    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
        text-decoration: none;
        color: white;
    }

    .uploader-avatar-large {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }

    .uploader-avatar-large .avatar-img {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: var(--shadow-soft);
        overflow: hidden;
    }

    .uploader-avatar-large .avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: var(--info-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: var(--shadow-soft);
    }

    .status-indicator {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid white;
        background: #2d3436;
    }

    .uploader-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3436;
        margin: 0;
    }

    .uploader-title {
        font-size: 0.9rem;
        color: #636e72;
        margin: 0;
    }

    .uploader-bio {
        font-size: 0.9rem;
        color: #495057;
        margin: 0;
    }

    .specialities {
        margin: 10px 0;
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

    .card-subtitle {
        font-size: 0.9rem;
        color: rgb(212, 222, 226);
        margin: 0;
    }
</style>

<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
        --success-color: #198754;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #0dcaf0;
        --light-color: #f8f9fa;
        --dark-color: #212529;

        --border-radius: 0.5rem;
        --border-radius-lg: 1rem;
        --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);

        --spacing-xs: 0.25rem;
        --spacing-sm: 0.5rem;
        --spacing-md: 1rem;
        --spacing-lg: 1.5rem;
        --spacing-xl: 3rem;

        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.25rem;
        --font-size-xl: 1.5rem;

        --transition: all 0.3s ease;
    }

    .badge-soft-info {
        color: var(--info, #17a2b8);
        background-color: rgba(23, 162, 184, 0.1);
        border: 1px solid rgba(23, 162, 184, 0.2);
    }

    .content-header {
        margin-bottom: var(--spacing-lg);
    }

    .page-title {
        font-size: var(--font-size-xl);
        font-weight: 700;
        color: var(--dark-color);
        margin: 0;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--spacing-sm);
    }

    .page-separator {
        color: var(--secondary-color);
        margin: 0 var(--spacing-sm);
    }

    .patient-badge {
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        color: white;
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: var(--border-radius);
        font-size: var(--font-size-sm);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
    }

    .breadcrumb-nav {
        display: flex;
        justify-content: flex-end;
    }

    .uploader-contact {
        margin-top: 15px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm) 0;
        font-size: var(--font-size-sm);
        color: var(--dark-color);
    }

    .metadata-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .metadata-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        background: var(--light-color);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .metadata-item:hover {
        background: white;
        box-shadow: var(--shadow-sm);
        transform: translateX(5px);
    }

    .metadata-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        box-shadow: var(--shadow-sm);
        flex-shrink: 0;
    }

    .metadata-icon i {
        font-size: var(--font-size-lg);
    }

    .metadata-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .metadata-label {
        font-size: var(--font-size-sm);
        font-weight: 600;
        color: var(--secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metadata-value {
        font-size: var(--font-size-base);
        font-weight: 700;
        color: var(--dark-color);
        word-break: break-word;
    }

    .file-icon-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
        border: 2px solid rgba(220, 53, 69, 0.2);
    }

    .file-icon-primary {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
        border: 2px solid rgba(13, 110, 253, 0.2);
    }

    .file-icon-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(25, 135, 84, 0.05));
        border: 2px solid rgba(25, 135, 84, 0.2);
    }

    .file-icon-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
        border: 2px solid rgba(255, 193, 7, 0.2);
    }

    .file-icon-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.1), rgba(13, 202, 240, 0.05));
        border: 2px solid rgba(13, 202, 240, 0.2);
    }

    .file-icon-secondary {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.1), rgba(108, 117, 125, 0.05));
        border: 2px solid rgba(108, 117, 125, 0.2);
    }

    .file-icon-dark {
        background: linear-gradient(135deg, rgba(33, 37, 41, 0.1), rgba(33, 37, 41, 0.05));
        border: 2px solid rgba(33, 37, 41, 0.2);
    }

    .file-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: var(--shadow);
        transition: var(--transition);
        margin: 0 auto var(--spacing-lg);
    }

    .file-icon-wrapper:hover {
        transform: scale(1.05);
        box-shadow: var(--shadow-lg);
    }

    .file-icon-wrapper i {
        font-size: 3rem;
        transition: var(--transition);
    }

    .file-extension-badge {
        position: absolute;
        bottom: -10px;
        right: -10px;
        background: white;
        color: var(--primary-color);
        font-size: 0.75rem;
        font-weight: 700;
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        border: 2px solid var(--light-color);
    }

    .file-preview-section {
        padding: var(--spacing-xl);
        background: linear-gradient(135deg, var(--light-color) 0%, #e9ecef 100%);
        display: flex;
        align-items: center;
        gap: var(--spacing-xl);
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .file-icon-display {
        flex-shrink: 0;
    }

    .file-basic-info {
        flex-grow: 1;
    }

    .file-title {
        font-size: var(--font-size-xl);
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: var(--spacing-lg);
        line-height: 1.3;
        word-break: break-word;
    }

    .file-stats {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .stat-item:hover {
        transform: translateX(10px);
        box-shadow: var(--shadow);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--light-color);
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: var(--font-size-lg);
    }

    .stat-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .stat-label {
        font-size: var(--font-size-sm);
        font-weight: 600;
        color: var(--secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: var(--font-size-base);
        font-weight: 700;
        color: var(--dark-color);
    }

    .section-container {
        padding: var(--spacing-xl);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .section-container:last-child {
        border-bottom: none;
    }

    .section-header {
        margin-bottom: var(--spacing-lg);
        padding-bottom: var(--spacing-md);
        border-bottom: 2px solid var(--light-color);
    }

    .section-title {
        font-size: var(--font-size-lg);
        font-weight: 700;
        color: var(--dark-color);
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .section-content {
        padding: var(--spacing-lg);
        background: var(--light-color);
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary-color);
    }

    .description-text {
        font-size: var(--font-size-base);
        line-height: 1.6;
        color: var(--dark-color);
        margin: 0;
    }

    .empty-state {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        font-style: italic;
        color: var(--secondary-color);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm) var(--spacing-lg);
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        box-shadow: var(--shadow-sm);
        font-size: var(--font-size-sm);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), #0056b3);
        color: white;
    }

    .btn-secondary {
        background: linear-gradient(135deg, var(--secondary-color), #495057);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #146c43);
        color: white;
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--warning-color), #e0a800);
        color: var(--dark-color);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #b02a37);
        color: white;
    }

    .btn-outline-secondary {
        background: transparent;
        color: var(--secondary-color);
        border: 2px solid var(--secondary-color);
    }

    .btn-outline-secondary:hover {
        background: var(--secondary-color);
        color: white;
    }

    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-sm);
        font-size: 0.8rem;
    }

    .btn-text {
        display: inline;
    }

    @media (max-width: 768px) {
        .btn-text {
            display: none;
        }
    }

    .file-details-card,
    .sidebar-card,
    .permission-denied-card {
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        transition: var(--transition);
        box-shadow: var(--shadow);
        border: none;
        background: white;
    }

    .file-details-card:hover,
    .sidebar-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-color), #0056b3);
        color: white;
        border: none;
        padding: var(--spacing-lg);
    }

    .header-content {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        gap: var(--spacing-lg);
    }

    .header-info {
        flex: 1;
    }

    .header-actions {
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
    }

    .card-title {
        font-size: var(--font-size-lg);
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .card-subtitle {
        font-size: var(--font-size-sm);
        color: rgba(255, 255, 255, 0.8);
        margin: var(--spacing-xs) 0 0 0;
    }

    .card-body {
        padding: 0;
    }

    .uploader-profile {
        text-align: center;
    }

    .uploader-avatar {
        position: relative;
        display: inline-block;
        margin-bottom: var(--spacing-lg);
    }

    .avatar-img {
        width: 80px;
        height: 80px;
        border-radius: var(--border-radius-lg);
        object-fit: cover;
        border: 4px solid white;
        box-shadow: var(--shadow-sm);
    }

    .avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: var(--border-radius-lg);
        background: linear-gradient(135deg, var(--info-color), var(--primary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: var(--shadow-sm);
    }

    .uploader-info {
        text-align: left;
    }

    .uploader-name {
        font-size: var(--font-size-lg);
        font-weight: 700;
        color: var(--dark-color);
        margin: 0 0 var(--spacing-sm) 0;
    }

    .specialities {
        margin: var(--spacing-sm) 0;
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-xs);
    }

    .speciality-badge {
        display: inline-block;
        padding: var(--spacing-xs) var(--spacing-sm);
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
        color: var(--primary-color);
        border-radius: var(--border-radius);
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }

    .speciality-badge.more {
        background: var(--light-color);
        color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .contact-info {
        margin-top: var(--spacing-md);
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .permission-denied-card {
        max-width: 500px;
        margin: 0 auto;
    }

    .permission-icon {
        font-size: 4rem;
        color: var(--warning-color);
        margin-bottom: var(--spacing-lg);
    }

    .permission-title {
        font-size: var(--font-size-xl);
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: var(--spacing-md);
    }

    .permission-message {
        font-size: var(--font-size-base);
        color: var(--secondary-color);
        margin-bottom: var(--spacing-lg);
    }

    @media (max-width: 1200px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-md);
        }

        .header-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .file-preview-section {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-lg);
            padding: var(--spacing-lg);
        }

        .file-icon-wrapper {
            width: 70px;
            height: 70px;
        }

        .file-icon-wrapper i {
            font-size: 2rem;
        }

        .file-title {
            font-size: var(--font-size-lg);
        }

        .section-container {
            padding: var(--spacing-lg);
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            justify-content: center;
        }

        .metadata-item {
            flex-direction: column;
            text-align: center;
            gap: var(--spacing-sm);
        }

        .custom-breadcrumb {
            flex-direction: column;
            gap: var(--spacing-xs);
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: var(--font-size-lg);
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }

        .patient-badge {
            align-self: flex-start;
        }

        .header-actions {
            flex-direction: column;
            width: 100%;
        }

        .header-actions .btn {
            justify-content: center;
        }
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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .file-details-card,
    .sidebar-card {
        animation: slideInUp 0.6s ease forwards;
    }

    .permission-denied-card {
        animation: fadeIn 0.8s ease forwards;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .btn:focus {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
    }

    .modal .btn:focus {
        outline-color: white;
    }

    .modal-content {
        border-radius: var(--border-radius-lg);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: var(--spacing-lg);
    }

    .modal-body {
        padding: var(--spacing-lg);
    }

    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        padding: var(--spacing-lg);
        gap: var(--spacing-sm);
    }

    .alert {
        border-radius: var(--border-radius);
        border: none;
        padding: var(--spacing-md);
        margin-bottom: var(--spacing-md);
    }

    .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
        color: #856404;
        border-left: 4px solid var(--warning-color);
    }

    @media print {

        .header-actions,
        .action-buttons,
        .modal {
            display: none !important;
        }

        .file-details-card,
        .sidebar-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
</style>