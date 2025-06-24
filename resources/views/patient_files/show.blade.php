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
        <header class="content-header py-4 bg-light border-bottom">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h1 class="m-0 d-flex align-items-center flex-wrap">
                            <span class="font-weight-bold mr-2">{{ trans('lang.patient_file_details') }}</span>
                            <span class="text-muted mx-2">|</span>
                            <span class="badge badge-primary px-3 py-2">
                                <i class="fas fa-user-injured mr-1"></i>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </span>
                        </h1>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-white rounded-pill px-4 py-2 shadow-sm float-md-right">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/dashboard') }}">{{ trans('lang.dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('patients.index') }}">{{ trans('lang.patients_plural') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('patient_files.index', $patient) }}">{{ trans('lang.patient_files_plural') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ trans('lang.file_details') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <main class="content py-5">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row g-4">
                    <div class="col-lg-4 col-md-12 order-lg-2 order-1">
                        <section class="card sidebar-card mb-4 shadow-lg border-0" role="region" aria-label="{{ trans('lang.uploaded_by') }}">
                            <div class="card-header bg-gradient-primary text-white">
                                <h2 class="card-title h5 mb-0">
                                    <i class="fas fa-user-md mr-2"></i>{{ trans('lang.uploaded_by') }}
                                </h2>
                            </div>
                            <div class="card-body p-4">
                                <div class="uploader-profile d-flex align-items-start">
                                    <div class="uploader-avatar mr-3">
                                        @if($file->uploader && $file->uploader->media->isNotEmpty())
                                            <img src="{{ $file->uploader->media->first()->getUrl() }}"
                                                alt="{{ $file->uploader->name }}" class="avatar-img rounded">
                                        @else
                                            <div class="avatar-placeholder rounded">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="uploader-info flex-grow-1">
                                        <h3 class="uploader-name h6 mb-1">{{ $file->uploader->name ?? trans('lang.unknown_uploader') }}</h3>
                                        @if($file->uploader && $file->uploader->doctor && $file->uploader->doctor->specialities && $file->uploader->doctor->specialities->isNotEmpty())
                                            <div class="specialities mb-2">
                                                @foreach($file->uploader->doctor->specialities->take(2) as $speciality)
                                                    <span class="badge badge-light mr-1">{{ $speciality->name }}</span>
                                                @endforeach
                                                @if($file->uploader->doctor->specialities->count() > 2)
                                                    <span class="badge badge-secondary">+{{ $file->uploader->doctor->specialities->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="contact-info">
                                            @if($file->uploader && $file->uploader->email)
                                                <div class="contact-item mb-1">
                                                    <i class="fas fa-envelope mr-2"></i>
                                                    <span>{{ $file->uploader->email }}</span>
                                                </div>
                                            @endif
                                            @if($file->uploader && $file->uploader->phone)
                                                <div class="contact-item">
                                                    <i class="fas fa-phone mr-2"></i>
                                                    <span>{{ $file->uploader->phone }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="action-buttons d-flex flex-column gap-3">
                            @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                <a href="{{ route('patient_files.create', $patient) }}" class="action-btn upload-btn">
                                    <i class="fas fa-plus mr-2"></i>{{ trans('lang.upload_new') }}
                                </a>
                            @endif
                            @if(auth()->user()->hasPermissionInContext('patient_files.destroy', $doctorId) && $file->uploader && $file->uploader->id === auth()->user()->id)
                                <button onclick="fileManager.confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')" class="action-btn delete-btn">
                                    <i class="fas fa-trash mr-2"></i>{{ trans('lang.delete_file') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-12 order-lg-1 order-2">
                        <div class="card file-details-card shadow-lg border-0" role="region" aria-label="{{ trans('lang.file_details') }}">
                            <div class="card-header bg-gradient-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="card-info">
                                        <h2 class="card-title h3 mb-1 d-flex align-items-center">
                                            <i class="fas fa-file-medical mr-2"></i>{{ trans('lang.file_details') }}
                                        </h2>
                                        <p class="card-subtitle mb-0 text-light" style="font-size: 0.8rem">
                                            {{ trans('lang.uploaded') }} {{ $file->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="header-actions d-flex gap-2">
                                        @if(auth()->user()->hasPermissionInContext('patient_files.download', $doctorId))
                                            <button class="action-btn download-btn" onclick="fileManager.downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')" data-toggle="tooltip" title="{{ trans('lang.download') }}">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.edit', $doctorId))
                                            <button class="action-btn edit-btn" onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'" data-toggle="tooltip" title="{{ trans('lang.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button class="action-btn assign-btn" onclick="fileManager.openAssignModal('{{ $file->id }}', '{{ $file->file_name }}')" data-toggle="tooltip" title="{{ trans('lang.assign_access') }}">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <section class="file-preview-section p-4 border-bottom" role="region" aria-label="{{ trans('lang.file_preview') }}">
                                    <div class="d-flex align-items-center gap-4 flex-wrap">
                                        <div class="file-icon-container flex-shrink-0">
                                            @php
                                                $extension = pathinfo($file->file_name, PATHINFO_EXTENSION);
                                                $iconClass = match (strtolower($extension)) {
                                                    'pdf' => 'fas fa-file-pdf text-danger',
                                                    'doc', 'docx' => 'fas fa-file-word text-primary',
                                                    'xls', 'xlsx' => 'fas fa-file-excel text-success',
                                                    'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
                                                    'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'fas fa-file-image text-info',
                                                    'zip', 'rar', '7z' => 'fas fa-file-archive text-secondary',
                                                    'txt' => 'fas fa-file-alt text-muted',
                                                    default => 'fas fa-file text-primary'
                                                };
                                            @endphp
                                            <i class="{{ $iconClass }} file-icon"></i>
                                            <div class="file-type-badge">{{ strtoupper($extension ?? 'FILE') }}</div>
                                        </div>
                                        <div class="file-basic-info flex-grow-1">
                                            <h3 class="file-title h5 mb-3">{{ $file->file_name }}</h3>
                                            <div class="file-stats d-flex flex-column gap-2">
                                                <div class="stat-item">
                                                    <i class="fas fa-hdd text-info mr-2"></i>
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
                                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                                    <span class="stat-label">{{ trans('lang.type') }}:</span>
                                                    <span class="stat-value">{{ strtoupper($extension ?? 'Unknown') }}</span>
                                                </div>
                                                <div class="stat-item">
                                                    <i class="fas fa-calendar text-success mr-2"></i>
                                                    <span class="stat-label">{{ trans('lang.uploaded') }}:</span>
                                                    <span class="stat-value">{{ $file->created_at->format('M d, Y \a\t g:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="file-description-section p-4 border-bottom" role="region" aria-label="{{ trans('lang.description') }}">
                                    <h2 class="section-title h5 mb-3">
                                        <i class="fas fa-comment-medical text-primary mr-2"></i>{{ trans('lang.description') }}
                                    </h2>
                                    <div class="description-content">
                                        @if($file->description)
                                            <p class="description-text">{{ $file->description }}</p>
                                        @else
                                            <div class="no-description d-flex align-items-center text-muted">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <span>{{ trans('lang.no_description_provided') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </section>

                                <section class="assigned-users-section p-4" role="region" aria-label="{{ trans('lang.users_with_access') }}">
                                    <div class="section-header d-flex justify-content-between align-items-center mb-3">
                                        <h2 class="section-title h5 mb-0">
                                            <i class="fas fa-users text-info mr-2"></i>{{ trans('lang.users_with_access') }}
                                        </h2>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button class="action-btn assign-btn" onclick="fileManager.openAssignModal('{{ $file->id }}', '{{ $file->file_name }}')">
                                                <i class="fas fa-user-plus mr-1"></i>{{ trans('lang.assign_access') }}
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
                                                    <i class="fas fa-users text-muted fa-2x"></i>
                                                </div>
                                                <p class="text-muted mb-0">{{ trans('lang.no_users_with_access') }}</p>
                                            </div>
                                        @else
                                            @foreach($fileUsers as $fileUser)
                                                @if($fileUser->user)
                                                    <div class="user-item d-flex align-items-center p-3 mb-2 rounded bg-white shadow-sm" style="animation-delay: {{ $loop->index * 0.1 }}s">
                                                        <div class="user-avatar mr-3">
                                                            @if($fileUser->user->media->isNotEmpty())
                                                                <img src="{{ $fileUser->user->media->first()->getUrl() }}"
                                                                    alt="{{ $fileUser->user->name }}" class="avatar-img rounded">
                                                            @else
                                                                <div class="avatar-placeholder rounded">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="user-info flex-grow-1">
                                                            <h4 class="user-name h6 mb-1">{{ $fileUser->user->name }}</h4>
                                                            <small class="text-muted d-block">{{ $fileUser->user->email }}</small>
                                                            @if($fileUser->expiration_date)
                                                                <div class="expiration-info mt-1">
                                                                    <span class="badge badge-warning">
                                                                        {{ trans('lang.expires') }} {{ $fileUser->expiration_date->diffForHumans() }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
            <div class="modal fade" id="assignAccessModal" tabindex="-1" aria-labelledby="assignAccessModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
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
                            <div class="selected-file-info p-3 bg-light border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file text-primary mr-2"></i>
                                    <span class="font-weight-bold">{{ trans('lang.file') }}:</span>
                                    <span class="ml-2" id="selectedFileName">-</span>
                                </div>
                            </div>

                            <div class="search-section p-4 bg-light">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="userSearch" placeholder="{{ trans('lang.enter_user_email') }}" autocomplete="off">
                                </div>
                                <small class="text-muted mt-2 d-block">{{ trans('lang.enter_exact_email_address') }}</small>
                            </div>

                            <form id="assignAccessForm" method="POST" action="{{ route('patient_files.assign_access', [$patient, $file]) }}">
                                @csrf
                                <div class="users-modal-list" id="userList">
                                    <div id="noResultsMessage" class="text-center p-4 text-muted">
                                        <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                        <p>{{ trans('lang.enter_email_to_search') }}</p>
                                    </div>

                                    @if(isset($allUsers))
                                        @foreach($allUsers as $user)
                                            @if($user->name && $user->email)
                                                <div class="modal-user-item" data-email="{{ $user->email }}"
                                                    data-name="{{ $user->name }}" data-user-id="{{ $user->id }}">
                                                    <div class="modal-user-avatar mr-3">
                                                        @if($user->media->isNotEmpty())
                                                            <img src="{{ $user->media->first()->getUrl() }}" alt="{{ $user->name }}"
                                                                class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white"
                                                                style="width: 40px; height: 40px;">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-user-info flex-grow-1">
                                                        <h6 class="user-name mb-1">{{ $user->name }}</h6>
                                                        <small class="text-muted d-block">{{ $user->email }}</small>
                                                        @if($user->doctor && $user->doctor->specialities->isNotEmpty())
                                                            <div class="specialities mt-1">
                                                                @foreach($user->doctor->specialities->take(3) as $speciality)
                                                                    <span class="badge badge-light badge-sm mr-1">{{ $speciality->name }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-user-status">
                                                        <div class="assigned-indicator text-success" style="display: none;">
                                                            <span class="badge badge-success">{{ trans('lang.already_assigned') }}</span>
                                                        </div>
                                                        <div class="select-indicator">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>

                                <div class="form-group p-4 border-top">
                                    <label for="expiration_date">{{ trans('lang.expiration_date') }} ({{ trans('lang.optional') }})</label>
                                    <input type="date" name="expiration_date" id="expiration_date" class="form-control">
                                    <small class="form-text text-muted">{{ trans('lang.leave_empty_for_permanent_access') }}</small>
                                </div>

                                <input type="hidden" name="user_id" id="selectedUserId" required>
                                <input type="hidden" name="patient_file_id" id="selectedFileId" value="{{ $file->id }}">
                            </form>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-light" data-dismiss="modal">{{ trans('lang.close') }}</button>
                            <button type="submit" class="btn btn-primary" form="assignAccessForm" id="assignButton" disabled>
                                <i class="fas fa-user-plus mr-1"></i>{{ trans('lang.assign') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="content py-5">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card permission-denied-card shadow-lg border-0 text-center p-5">
                            <div class="permission-icon mb-4">
                                <i class="fas fa-lock text-warning fa-4x"></i>
                            </div>
                            <h2 class="h4 text-dark mb-3">{{ trans('lang.access_denied') }}</h2>
                            <p class="text-muted mb-4">{{ trans('lang.no_permission_message') }}</p>
                            <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i>{{ trans('lang.go_back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('styles')
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a67d8;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #48bb78;
            --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            --danger-color: #f56565;
            --danger-gradient: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            --warning-color: #ed8936;
            --warning-gradient: linear-gradient(135deg, #fdcb6e 0%, #f39c12 100%);
            --info-color: #4299e1;
            --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            --secondary-color: #a0aec0;
            --muted-color: #718096;
            --border-color: #e2e8f0;
            --shadow-light: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-heavy: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gap-1 {
            gap: 0.25rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 1rem;
        }

        .gap-4 {
            gap: 1.5rem;
        }

        .gap-5 {
            gap: 3rem;
        }

        .content-header {
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
        }

        .breadcrumb {
            box-shadow: var(--shadow-light);
        }

        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--primary-dark);
        }

        .card {
            border-radius: var(--border-radius);
            transition: var(--transition);
            animation: slideInUp 0.6s ease forwards;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-heavy);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: none;
        }

        .file-icon-container {
            position: relative;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }

        .file-icon {
            font-size: 3rem;
            transition: var(--transition);
        }

        .file-type-badge {
            position: absolute;
            bottom: -6px;
            right: -6px;
            background: white;
            color: var(--primary-color);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            box-shadow: var(--shadow-light);
            border: 2px solid #f8f9fa;
        }

        .file-title {
            font-weight: 600;
            color: #2d3748;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow-light);
        }

        .stat-label {
            color: var(--muted-color);
            font-weight: 500;
        }

        .stat-value {
            font-weight: 600;
            color: #2d3748;
        }

        .description-content {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .description-text {
            color: #2d3748;
            line-height: 1.6;
        }

        .no-description {
            font-style: italic;
            color: var(--muted-color);
        }

        .user-item {
            animation: slideInUp 0.6s ease forwards;
            transition: var(--transition);
        }

        .user-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .avatar-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow-light);
        }

        .avatar-placeholder {
            width: 40px;
            height: 40px;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            box-shadow: var(--shadow-light);
        }

        .user-name, .uploader-name {
            font-weight: 600;
            color: #2d3748;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--muted-color);
        }

        .action-btn {
            width: 100%;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 0.875rem;
            cursor: pointer;
            color: white;
            font-weight: 600;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .upload-btn {
            background: var(--success-gradient);
        }

        .delete-btn {
            background: var(--danger-gradient);
        }

        .download-btn {
            background: var(--success-gradient);
        }

        .edit-btn {
            background: var(--warning-gradient);
        }

        .assign-btn {
            background: var(--primary-gradient);
        }

        .empty-state {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .modal-content {
            border-radius: var(--border-radius);
        }

        .users-modal-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .modal-user-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
            cursor: pointer;
            border-left: 3px solid transparent;
            opacity: 0;
            visibility: hidden;
            max-height: 0;
            overflow: hidden;
        }

        .modal-user-item.visible {
            opacity: 1;
            visibility: visible;
            max-height: 100px;
        }

        .modal-user-item:hover:not(.already-assigned) {
            background-color: #f7fafc;
        }

        .modal-user-item.selected {
            background-color: rgba(102, 126, 234, 0.05);
            border-left-color: var(--primary-color);
        }

        .modal-user-item.already-assigned {
            background-color: #f7fafc;
            opacity: 0.7;
            cursor: not-allowed;
        }

        .select-indicator {
            width: 24px;
            height: 24px;
            background: var(--border-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
            font-size: 0.75rem;
            opacity: 0.3;
        }

        .modal-user-item.visible .select-indicator {
            opacity: 1;
        }

        .modal-user-item.selected .select-indicator {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .modal-user-item.already-assigned .select-indicator {
            display: none;
        }

        #noResultsMessage {
            text-align: center;
            padding: 2rem;
            color: var(--muted-color);
            transition: var(--transition);
        }

        #noResultsMessage.hidden {
            display: none;
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

        @media (max-width: 768px) {
            .file-preview-section {
                flex-direction: column;
                text-align: center;
            }

            .file-icon-container {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }

            .action-btn {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .header-actions .action-btn {
                width: 36px;
                height: 36px;
                font-size: 0.8rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script>
        class FileManager {
            constructor() {
                this.currentFileId = null;
                this.selectedUserId = null;
                this.assignedUsers = new Set();
                this.searchTimeout = null;
                this.assignRoute = "{{ route('patient_files.assign_access', [$patient, $file]) }}";

                this.initializeElements();
                this.bindEvents();
                this.initializeTooltips();

                window.fileManager = this;
            }

            initializeElements() {
                this.modal = document.getElementById('assignAccessModal');
                this.assignForm = document.getElementById('assignAccessForm');
                this.userSearch = document.getElementById('userSearch');
                this.userList = document.getElementById('userList');
                this.noResultsMessage = document.getElementById('noResultsMessage');
                this.selectedFileName = document.getElementById('selectedFileName');
                this.selectedUserIdInput = document.getElementById('selectedUserId');
                this.selectedFileIdInput = document.getElementById('selectedFileId');
                this.assignButton = document.getElementById('assignButton');
                this.expirationDate = document.getElementById('expiration_date');

                const requiredElements = [
                    'modal', 'assignForm', 'userSearch', 'userList',
                    'noResultsMessage', 'selectedFileName', 'selectedUserIdInput',
                    'selectedFileIdInput', 'assignButton'
                ];

                const missingElements = requiredElements.filter(element => !this[element]);
                if (missingElements.length > 0) {
                    console.error('FileManager: Missing required elements:', missingElements);
                    return false;
                }

                return true;
            }

            initializeTooltips() {
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }

            bindEvents() {
                if (this.userSearch) {
                    this.userSearch.addEventListener('input', (e) => {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.handleSearch(e);
                        }, 300);
                    });
                }

                if (this.modal) {
                    $(this.modal).on('hidden.bs.modal', () => this.resetModal());
                    $(this.modal).on('shown.bs.modal', () => {
                        if (this.userSearch) this.userSearch.focus();
                    });
                }

                if (this.assignForm) {
                    this.assignForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
                }

                if (this.userList) {
                    this.userList.addEventListener('click', (e) => {
                        const userItem = e.target.closest('.modal-user-item');
                        if (userItem && !userItem.classList.contains('already-assigned')) {
                            this.selectUser(userItem);
                        }
                    });
                }
            }

            handleSearch(event) {
                const searchEmail = event.target.value.trim().toLowerCase();
                this.filterUsers(searchEmail);
            }

            filterUsers(searchEmail) {
                if (!this.userList) return;

                // If searchEmail is empty, don't show any users
                if (searchEmail === '') {
                    this.userList.querySelectorAll('.modal-user-item').forEach(item => {
                        this.hideUserItem(item);
                    });
                    this.updateNoResultsMessage('', false);
                    return;
                }

                const userItems = this.userList.querySelectorAll('.modal-user-item');
                let hasVisibleUsers = false;

                userItems.forEach(item => {
                    const userEmail = (item.dataset.email || '').trim().toLowerCase();
                    const userName = (item.dataset.name || '').trim().toLowerCase();

                    const shouldShow = !searchEmail ||
                        userEmail.includes(searchEmail) ||
                        userName.includes(searchEmail);

                    if (shouldShow) {
                        this.showUserItem(item);
                        hasVisibleUsers = true;
                    } else {
                        this.hideUserItem(item);
                        if (item.dataset.userId === this.selectedUserId) {
                            this.clearSelection();
                        }
                    }
                });

                this.updateNoResultsMessage(searchEmail, hasVisibleUsers);
            }

            showUserItem(item) {
                item.classList.add('visible');
                item.style.display = 'flex';
                item.style.maxHeight = '100px';
                item.style.opacity = '1';

                if (!item.classList.contains('already-assigned')) {
                    const selectIndicator = item.querySelector('.select-indicator');
                    if (selectIndicator) {
                        selectIndicator.style.display = 'flex';
                    }
                }
            }

            hideUserItem(item) {
                item.classList.remove('visible', 'selected');
                item.style.display = 'none';
                item.style.maxHeight = '0';
                item.style.opacity = '0';

                const selectIndicator = item.querySelector('.select-indicator');
                if (selectIndicator) {
                    selectIndicator.style.display = 'none';
                }
            }

            updateNoResultsMessage(searchEmail, hasVisibleUsers) {
                if (!this.noResultsMessage) return;

                if (hasVisibleUsers) {
                    this.noResultsMessage.style.display = 'none';
                } else {
                    this.noResultsMessage.style.display = 'block';
                    this.noResultsMessage.innerHTML = searchEmail ? `
                        <i class="fas fa-user-slash mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p>{{ trans('lang.no_user_found') }}: <strong>${this.escapeContent(searchEmail)}</strong></p>
                    ` : `
                        <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p>{{ trans('lang.enter_email_to_search') }}</p>
                    `;
                }
            }

            selectUser(userItem) {
                if (!userItem || userItem.classList.contains('already-assigned')) {
                    console.log('Cannot select user: item is null or already assigned');
                    return;
                }

                this.clearSelection();

                userItem.classList.add('selected');
                this.selectedUserId = userItem.dataset.userId;

                if (this.selectedUserIdInput) {
                    this.selectedUserIdInput.value = this.selectedUserId;
                }

                if (this.assignButton) {
                    this.assignButton.disabled = false;
                }

                const selectIndicator = userItem.querySelector('.select-indicator');
                if (selectIndicator) {
                    selectIndicator.style.background = 'var(--primary-color)';
                    selectIndicator.style.color = 'white';
                    selectIndicator.style.transform = 'scale(1.1)';
                }

                console.log('User selected:', this.selectedUserId);
            }

            clearSelection() {
                if (this.userList) {
                    this.userList.querySelectorAll('.modal-user-item').forEach(item => {
                        item.classList.remove('selected');
                        const selectIndicator = item.querySelector('.select-indicator');
                        if (selectIndicator) {
                            selectIndicator.style.background = 'var(--border-color)';
                            selectIndicator.style.color = 'var(--primary-color)';
                            selectIndicator.style.transform = 'scale(1)';
                        }
                    });
                }

                this.selectedUserId = null;
                if (this.selectedUserIdInput) {
                    this.selectedUserIdInput.value = '';
                }
                if (this.assignButton) {
                    this.assignButton.disabled = true;
                }
            }

            openAssignModal(fileId, fileName) {
                this.currentFileId = fileId;

                if (this.selectedFileName) {
                    this.selectedFileName.textContent = fileName;
                }
                if (this.selectedFileIdInput) {
                    this.selectedFileIdInput.value = fileId;
                }
                if (this.assignForm) {
                    this.assignForm.action = this.assignRoute;
                }

                this.resetModal();
                this.loadAssignedUsers();
                if (typeof $ !== 'undefined') {
                    $('#assignAccessModal').modal('show');
                }
            }

            resetModal() {
                if (this.userSearch) {
                    this.userSearch.value = '';
                }
                if (this.expirationDate) {
                    this.expirationDate.value = '';
                }

                this.clearSelection();

                if (this.userList) {
                    this.userList.querySelectorAll('.modal-user-item').forEach(item => {
                        this.hideUserItem(item);
                        item.classList.remove('already-assigned');
                        const assignedIndicator = item.querySelector('.assigned-indicator');
                        if (assignedIndicator) {
                            assignedIndicator.style.display = 'none';
                        }
                    });
                }

                this.updateNoResultsMessage(null, false);
            }

            loadAssignedUsers() {
                if (!this.currentFileId || !this.userList) return;

                @php
                    $assignedUserIds = \App\Models\PatientFileUser::where('patient_file_id', $file->id)
                        ->where('patient_id', $patient->id)
                        ->where(function ($query) {
                            $query->whereNull('expiration_date')
                                ->orWhere('expiration_date', '>', now());
                        })
                        ->pluck('user_id')
                        ->toArray();
                @endphp
                const assignedUsers = @json($assignedUserIds);
                assignedUsers.forEach(userId => {
                    const userItem = this.userList.querySelector(`[data-user-id="${userId}"]`);
                    if (userItem) {
                        userItem.classList.add('already-assigned');
                        const assignedIndicator = userItem.querySelector('.assigned-indicator');
                        if (assignedIndicator) {
                            assignedIndicator.style.display = 'block';
                        }
                    }
                });
            }

            handleFormSubmit(event) {
                event.preventDefault();

                if (!this.selectedUserId) {
                    alert('{{ trans('lang.select_user_to_assign') }}');
                    return;
                }

                const formData = new FormData(this.assignForm);
                const data = {
                    user_id: formData.get('user_id'),
                    patient_file_id: formData.get('patient_file_id'),
                    expiration_date: formData.get('expiration_date'),
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: this.assignForm.action,
                    method: 'POST',
                    data: data,
                    success: (response) => {
                        this.showSuccessMessage();
                        $(this.modal).modal('hide');
                        location.reload();
                    },
                    error: (xhr) => {
                        console.error('Error assigning access:', xhr.responseText);
                        alert('{{ trans('lang.error_assigning_access') }}');
                    }
                });
            }

            showSuccessMessage() {
                alert('{{ trans('lang.access_assigned_successfully') }}');
            }

            escapeContent(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            downloadFile(url) {
                window.open(url, '_blank');
            }

            confirmDelete(url) {
                if (confirm('{{ trans('lang.confirm_delete_file') }}')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const fileManager = new FileManager();
            window.openAssignModal = (fileId, fileName) => fileManager.openAssignModal(fileId, fileName);
            window.downloadFile = (url) => fileManager.downloadFile(url);
            window.confirmDelete = (url) => fileManager.confirmDelete(url);
        });
    </script>
@endsection