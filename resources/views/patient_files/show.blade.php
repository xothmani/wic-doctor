<!-- resources/views/patient_files/show.blade.php -->
@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.show';
    // Retrieve the permission with its related readable record
    $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
        ->with('readable')
        ->first();

    // Use the dynamic attribute for the display name; fall back to the key if not found
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
                                    <div class="header-info d-flex flex-column"  style="margin: 0 1vw;">
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
                                            <button class="action-button download-btn"
                                                onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')"
                                                data-toggle="tooltip" title="{{trans('lang.download')}}">
                                                <i class="fas fa-download mr-1"></i> {{ trans('lang.download') }}
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.edit', $doctorId))
                                            <button class="action-button download-btn"
                                                onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'"
                                                data-toggle="tooltip" title="{{trans('lang.edit')}}">
                                                <i class="fas fa-edit mr-1"></i> {{ trans('lang.edit') }}
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

                                <!-- File Navigation -->
                                <div class="file-navigation-section">
                                    <div class="section-header">
                                        <h5 class="section-title">
                                            <i class="fas fa-exchange-alt text-info mr-2"></i>
                                            {{ trans('lang.quick_actions') }}
                                        </h5>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="{{ route('patient_files.index', $patient) }}" class="action-button back-btn">
                                            <i class="fas fa-arrow-left"></i>
                                            <span>{{ trans('lang.back_to_files') }}</span>
                                        </a>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                            <a href="{{ route('patient_files.create', $patient) }}"
                                                class="action-button upload-btn">
                                                <i class="fas fa-plus"></i>
                                                <span>{{ trans('lang.upload_new') }}</span>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.download', $doctorId))
                                            <button
                                                onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')"
                                                class="action-button download-btn">
                                                <i class="fas fa-download"></i>
                                                <span>{{ trans('lang.download_file') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Metadata & Related Info -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Uploader Information -->
                        <div class="card shadow-lg border-0 uploader-card mb-4">
                            <div class="card-header bg-gradient-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-md mr-2"></i>
                                    {{ trans('lang.uploaded_by') }}
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="uploader-profile">
                                    <div class="uploader-avatar-large">
                                        @if($file->uploader && $file->uploader->media->isNotEmpty())
                                            <img src="{{ $file->uploader->media->first()->getUrl() }}" alt="{{ $file->uploader->name }}"
                                                class="avatar-img">
                                        @else
                                            <div class="avatar-placeholder">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="uploader-info">
                                        <h6 class="uploader-name">{{ $file->uploader->name ?? trans('lang.unknown_uploader') }}
                                        </h6>
                                        @if($file->uploader->doctor->specialities && $file->uploader->doctor->specialities->isNotEmpty())
                                            <div class="specialities">
                                                @foreach($file->uploader->doctor->specialities->take(2) as $speciality)
                                                    <span class="speciality-badge">{{ $speciality->name }}</span>
                                                @endforeach
                                                @if($file->uploader->doctor->specialities->count() > 2)
                                                    <span
                                                        class="speciality-badge more">+{{ $file->uploader->doctor->specialities->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="uploader-contact">
                                            @if($file->uploader && $file->uploader->email)
                                                <div class="contact-item">
                                                    <i class="fas fa-envelope text-primary mr-2"></i>
                                                    <span>{{ $file->uploader->email }}</span>
                                                </div>
                                            @endif
                                            @if($file->uploader && $file->uploader->phone)
                                                <div class="contact-item">
                                                    <i class="fas fa-phone text-success mr-2"></i>
                                                    <span>{{ $file->uploader->phone }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- File Metadata -->
                        <div class="card shadow-lg border-0 metadata-card mb-4">
                            <div class="card-header bg-gradient-secondary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    {{ trans('lang.file_information') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="metadata-list">
                                    <div class="metadata-item">
                                        <div class="metadata-icon">
                                            <i class="fas fa-file-signature text-primary"></i>
                                        </div>
                                        <div class="metadata-content">
                                            <span class="metadata-label">{{ trans('lang.file_name') }}</span>
                                            <span class="metadata-value">{{ $file->file_name }}</span>
                                        </div>
                                    </div>

                                    <div class="metadata-item">
                                        <div class="metadata-icon">
                                            <i class="fas fa-weight text-info"></i>
                                        </div>
                                        <div class="metadata-content">
                                            <span class="metadata-label">{{ trans('lang.file_size') }}</span>
                                            <span class="metadata-value">
                                                @if(isset($file->file_size) && $file->file_size)
                                                    {{ number_format($file->file_size / 1024, 1) }} KB
                                                @else
                                                    {{ trans('lang.unknown') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="metadata-item">
                                        <div class="metadata-icon">
                                            <i class="fas fa-file-code text-warning"></i>
                                        </div>
                                        <div class="metadata-content">
                                            <span class="metadata-label">{{ trans('lang.file_type') }}</span>
                                            <span class="metadata-value">{{ strtoupper($extension ?? 'Unknown') }}</span>
                                        </div>
                                    </div>

                                    <div class="metadata-item">
                                        <div class="metadata-icon">
                                            <i class="fas fa-calendar-plus text-success"></i>
                                        </div>
                                        <div class="metadata-content">
                                            <span class="metadata-label">{{ trans('lang.upload_date') }}</span>
                                            <span class="metadata-value">{{ $file->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>

                                    <div class="metadata-item">
                                        <div class="metadata-icon">
                                            <i class="fas fa-clock text-secondary"></i>
                                        </div>
                                        <div class="metadata-content">
                                            <span class="metadata-label">{{ trans('lang.upload_time') }}</span>
                                            <span class="metadata-value">{{ $file->created_at->format('g:i A') }}</span>
                                        </div>
                                    </div>

                                    @if($file->updated_at != $file->created_at)
                                        <div class="metadata-item">
                                            <div class="metadata-icon">
                                                <i class="fas fa-edit text-warning"></i>
                                            </div>
                                            <div class="metadata-content">
                                                <span class="metadata-label">{{ trans('lang.last_modified') }}</span>
                                                <span class="metadata-value">{{ $file->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
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

    /* Card Styles */
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

    /* Card Headers */
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

    /* File Preview Section */
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
        font-size: 1.8rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 20px;
        line-height: 1.3;
        word-break: break-word;
    }

    .file-stats {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
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
        font-size: 1.2rem;
        width: 20px;
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
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f1f3f4;
    }

    .section-title {
        font-size: 1.3rem;
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

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .action-button {
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

    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
        text-decoration: none;
        color: white;
    }

    /* Uploader Profile */
    .uploader-profile {
        text-align: center;
        padding: 20px;
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
        color:rgb(212, 222, 226);
        margin: 0;
    }
</style>