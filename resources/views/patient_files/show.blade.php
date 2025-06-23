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
        <!-- Content Header -->
        <header class="content-header py-4">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h1 class="m-0 d-flex align-items-center flex-wrap">
                            <span class="text-bold mr-2">{{ trans('lang.patient_file_details') }}</span>
                            <span class="text-muted mx-2">|</span>
                            <span class="badge badge-info px-3 py-2">
                                <i class="fas fa-user-injured mr-1"></i>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </span>
                        </h1>
                    </div>
                    <div class="col-md-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-white rounded-pill px-4 py-2 shadow-sm float-md-right d-none d-md-flex">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/dashboard') }}" aria-label="{{ trans('lang.dashboard') }}">
                                        <i class="fas fa-tachometer-alt"></i> {{ trans('lang.dashboard') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('patients.index') }}">{{ trans('lang.patients_plural') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ route('patient_files.index', $patient) }}">{{ trans('lang.patient_files_plural') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ trans('lang.file_details') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content py-5">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row g-4">
                    <!-- Sidebar (Top on Mobile) -->
                    <div class="col-lg-4 col-md-12 order-lg-2 order-1">
                        <!-- Uploader Information -->
                        <section class="card sidebar-card mb-4" role="region" aria-label="{{ trans('lang.uploaded_by') }}">
                            <div class="card-header bg-gradient-info text-white">
                                <h2 class="card-title h5 mb-0">
                                    <i class="fas fa-user-md mr-2"></i>{{ trans('lang.uploaded_by') }}
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="uploader-profile d-flex align-items-start">
                                    <div class="uploader-avatar mr-3">
                                        @if($file->uploader && $file->uploader->media->isNotEmpty())
                                            <img src="{{ $file->uploader->media->first()->getUrl() }}"
                                                alt="{{ $file->uploader->name }}" class="avatar-img rounded-circle">
                                        @else
                                            <div class="avatar-placeholder rounded-circle">
                                                <i class="fas fa-user-md"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="uploader-info flex-grow-1">
                                        <h3 class="uploader-name h6 mb-1">
                                            {{ $file->uploader->name ?? trans('lang.unknown_uploader') }}
                                        </h3>
                                        @if($file->uploader && $file->uploader->doctor && $file->uploader->doctor->specialities && $file->uploader->doctor->specialities->isNotEmpty())
                                            <div class="specialities mb-2">
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

                        <!-- Action Buttons -->
                        <div class="action-buttons d-flex flex-column gap-3">
                            @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                <a href="{{ route('patient_files.create', $patient) }}" class="action-button upload-btn"
                                    aria-label="{{ trans('lang.upload_new') }}">
                                    <i class="fas fa-plus mr-2"></i>
                                    <span>{{ trans('lang.upload_new') }}</span>
                                </a>
                            @endif
                            @if(auth()->user()->hasPermissionInContext('patient_files.destroy', $doctorId) && $file->uploader && $file->uploader->id === auth()->user()->id)
                                <a onclick="confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')"
                                    class="action-button delete-btn" aria-label="{{ trans('lang.delete_file') }}"
                                    data-toggle="tooltip" title="{{ trans('lang.delete_file') }}">
                                    <i class="fas fa-trash mr-2"></i>
                                    <span>{{ trans('lang.delete_file') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-lg-8 col-md-12 order-lg-1 order-2">
                        <div class="card file-details-card shadow-lg border-0" role="region"
                            aria-label="{{ trans('lang.file_details') }}">
                            <div class="card-header bg-gradient-info text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="card-info">
                                        <h2 class="card-title h3 mb-1 d-flex align-items-center">
                                            <i class="fas fa-file-medical mr-2"></i>
                                            {{ trans('lang.file_details') }}
                                        </h2>
                                        <p class="card-subtitle mb-0 text-light" style="font-size: 0.8rem">
                                            {{ trans('lang.uploaded') }} {{ $file->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="header-actions d-flex gap-2">
                                        @if(auth()->user()->hasPermissionInContext('patient_files.download', $doctorId))
                                            <button class="header-button header-download-btn"
                                                onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')"
                                                data-toggle="tooltip" title="{{ trans('lang.download') }}"
                                                aria-label="{{ trans('lang.download') }}">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.edit', $doctorId))
                                            <button class="header-button header-edit-btn"
                                                onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'"
                                                data-toggle="tooltip" title="{{ trans('lang.edit') }}"
                                                aria-label="{{ trans('lang.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button class="header-button header-assign-btn" data-toggle="modal"
                                                data-target="#assignAccessModal" data-toggle="tooltip"
                                                title="{{ trans('lang.assign_access') }}"
                                                aria-label="{{ trans('lang.assign_access') }}">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <!-- File Preview -->
                                <section class="file-preview-section p-4" role="region"
                                    aria-label="{{ trans('lang.file_preview') }}">
                                    <div class="d-flex align-items-center gap-4 flex-wrap">
                                        <div class="file-icon-display flex-shrink-0">
                                            @php
                                                $extension = pathinfo($file->file_name, PATHINFO_EXTENSION);
                                                $iconData = match (strtolower($extension)) {
                                                    'pdf' => ['icon' => 'fas fa-file-pdf', 'color' => 'danger', 'bg' => 'rgba(220, 53, 69, 0.15)'],
                                                    'doc', 'docx' => ['icon' => 'fas fa-file-word', 'color' => 'primary', 'bg' => 'rgba(0, 123, 255, 0.15)'],
                                                    'xls', 'xlsx' => ['icon' => 'fas fa-file-excel', 'color' => 'success', 'bg' => 'rgba(40, 167, 69, 0.15)'],
                                                    'ppt', 'pptx' => ['icon' => 'fas fa-file-powerpoint', 'color' => 'warning', 'bg' => 'rgba(255, 193, 7, 0.15)'],
                                                    'jpg', 'jpeg', 'png', 'gif', 'bmp' => ['icon' => 'fas fa-file-image', 'color' => 'info', 'bg' => 'rgba(23, 162, 184, 0.15)'],
                                                    'zip', 'rar', '7z' => ['icon' => 'fas fa-file-archive', 'color' => 'secondary', 'bg' => 'rgba(108, 117, 125, 0.15)'],
                                                    'txt' => ['icon' => 'fas fa-file-alt', 'color' => 'dark', 'bg' => 'rgba(52, 58, 64, 0.15)'],
                                                    default => ['icon' => 'fas fa-file', 'color' => 'primary', 'bg' => 'rgba(0, 123, 255, 0.15)']
                                                };
                                            @endphp
                                            <div class="file-icon-wrapper" style="background: {{ $iconData['bg'] }}">
                                                <i class="{{ $iconData['icon'] }} text-{{ $iconData['color'] }} fa-3x"></i>
                                                <div class="file-extension-badge">{{ strtoupper($extension ?? 'FILE') }}</div>
                                            </div>
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
                                                    <span
                                                        class="stat-value">{{ $file->created_at->format('M d, Y \a\t g:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- File Description -->
                                <section class="file-description-section p-4" role="region"
                                    aria-label="{{ trans('lang.description') }}">
                                    <div class="section-header d-flex justify-content-between align-items-center">
                                        <h2 class="section-title h5 mb-0">
                                            <i class="fas fa-comment-medical text-primary mr-2"></i>
                                            {{ trans('lang.description') }}
                                        </h2>
                                    </div>
                                    <div class="description-content mt-3">
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

                                <!-- Assigned Users -->
                                <section class="assigned-users-section p-4" role="region"
                                    aria-label="{{ trans('lang.users_with_access') }}">
                                    <div class="section-header d-flex justify-content-between align-items-center">
                                        <h2 class="section-title h5 mb-0">
                                            <i class="fas fa-users text-info mr-2"></i>
                                            {{ trans('lang.users_with_access') }}
                                        </h2>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
                                            <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                                data-target="#assignAccessModal" aria-label="{{ trans('lang.assign_access') }}">
                                                <i class="fas fa-user-plus mr-1"></i> {{ trans('lang.assign_access') }}
                                            </button>
                                        @endif
                                    </div>
                                    <div class="users-list mt-3">
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
                                            <div class="empty-state text-center py-4 bg-light rounded">
                                                <div class="empty-icon mb-3">
                                                    <i class="fas fa-users text-muted fa-2x"></i>
                                                </div>
                                                <p class="text-muted mb-0">{{ trans('lang.no_users_with_access') }}</p>
                                            </div>
                                        @else
                                            @foreach($fileUsers as $fileUser)
                                                @if($fileUser->user)
                                                    <div class="user-item d-flex align-items-center p-3 mb-2 rounded bg-white shadow-sm"
                                                        style="animation-delay: {{ $loop->index * 0.1 }}s">
                                                        <div class="user-avatar mr-3">
                                                            @if($fileUser->user->media->isNotEmpty())
                                                                <img src="{{ $fileUser->user->media->first()->getUrl() }}"
                                                                    alt="{{ $fileUser->user->name }}" class="avatar-img rounded-circle">
                                                            @else
                                                                <div class="avatar-placeholder rounded-circle">
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
                                                                        {{ trans('lang.expires') }}
                                                                        {{ $fileUser->expiration_date->diffForHumans() }}
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

        <!-- Assign Access Modal -->
        @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctorId))
            <div class="modal fade" id="assignAccessModal" tabindex="-1" role="dialog" aria-labelledby="assignAccessModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-primary text-white border-0">
                            <h2 class="modal-title h5" id="assignAccessModalLabel">
                                <i class="fas fa-user-plus mr-2"></i>{{ trans('lang.assign_access') }}
                            </h2>
                            <button type="button" class="btn-close text-white" data-dismiss="modal"
                                aria-label="{{ trans('lang.close') }}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <!-- Search Input -->
                            <div class="search-section p-4 bg-light">
                                <div class="search-input-group position-relative">
                                    <i class="fas fa-search search-icon position-absolute"></i>
                                    <input type="text" class="form-control search-input" id="userSearch"
                                        placeholder="{{ trans('lang.enter_user_email') }}"
                                        aria-label="{{ trans('lang.enter_user_email') }}">
                                </div>
                            </div>

                            <!-- Users List -->
                            <form action="{{ route('patient_files.assign_access', [$patient, $file]) }}" method="POST"
                                id="assignAccessForm">
                                @csrf
                                <div class="users-modal-list p-4" id="userList">
                                    @foreach($allUsers as $user)
                                        @if($user->name && $user->email)
                                            <div class="modal-user-item d-flex align-items-center p-3 mb-2 rounded bg-white shadow-sm"
                                                data-value="{{ $user->id }}" data-email="{{ $user->email }}" style="display: none;"
                                                onclick="selectUser(this)" role="button" aria-label="Select {{ $user->name }}">
                                                <div class="modal-user-avatar mr-3">
                                                    @if($user->media->isNotEmpty())
                                                        <img src="{{ $user->media->first()->getUrl() }}" alt="{{ $user->name }}"
                                                            class="avatar-img rounded-circle">
                                                    @else
                                                        <div class="avatar-placeholder rounded-circle">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-user-info flex-grow-1">
                                                    <h4 class="user-name h6 mb-1">{{ $user->name }}</h4>
                                                    <small class="text-muted d-block">{{ $user->email }}</small>
                                                    @if($user->doctor && $user->doctor->specialities->isNotEmpty())
                                                        <div class="specialities mt-1">
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
                                                        <div class="select-indicator rounded-circle">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="form-group p-4">
                                    <label for="expiration_date" class="form-label">{{ trans('lang.expiration_date') }}</label>
                                    <input type="date" name="expiration_date" id="expiration_date" class="form-control"
                                        aria-label="{{ trans('lang.expiration_date') }}">
                                </div>
                                <input type="hidden" name="user_id" id="selectedUserId" required>
                                <input type="hidden" name="patient_file_id" value="{{ $file->id }}">
                            </form>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"
                                aria-label="{{ trans('lang.close') }}">
                                {{ trans('lang.close') }}
                            </button>
                            <button type="submit" class="btn btn-primary" form="assignAccessForm"
                                aria-label="{{ trans('lang.assign') }}">
                                <i class="fas fa-user-plus mr-1"></i>{{ trans('lang.assign') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Access Denied -->
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
                            <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-primary"
                                aria-label="{{ trans('lang.go_back') }}">
                                <i class="fas fa-arrow-left mr-2"></i>{{ trans('lang.go_back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        function downloadFile(url) {
            window.open(url, '_blank');
        }

        function confirmDelete(url) {
            if (confirm('{{ trans('lang.confirm_delete_file') }}')) {
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
@endsection

@section('styles')
    <style>
        :root {
            --primary: #5a67d8;
            --primary-gradient: linear-gradient(135deg, #5a67d8 0%, #7f9cf5 100%);
            --info: #38b2ac;
            --info-gradient: linear-gradient(135deg, #38b2ac 0%, #81e6d9 100%);
            --success: #48bb78;
            --success-gradient: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
            --danger: #f56565;
            --danger-gradient: linear-gradient(135deg, #f56565 0%, #feb2b2 100%);
            --warning: #ed8936;
            --warning-gradient: linear-gradient(135deg, #ed8936 0%, #f6e05e 100%);
            --secondary: #718096;
            --secondary-gradient: linear-gradient(135deg, #718096 0%, #a0aec0 100%);
            --dark: #2d3748;
            --dark-gradient: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
            --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.16);
            --border-radius: 1rem;
            --transition: all 0.3s ease;
        }

        button {
            cursor: pointer;
            border: none;
            outline: none;
            background: none;
            transition: var(--transition);
        }

        /* Bootstrap 5-like gap utilities */
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

        /* Global Card Styles */
        .card {
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            animation: fadeInUp 0.5s ease forwards;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 1.25rem;
            border-bottom: none;
        }

        .card-header.bg-gradient-primary {
            background: var(--primary-gradient);
        }

        .card-header.bg-gradient-info {
            background: var(--info-gradient);
        }

        /* Content Header */
        .content-header {
            background: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .breadcrumb {
            background: white;
            box-shadow: var(--shadow-soft);
        }

        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--dark);
        }

        .badge-info {
            background: var(--info);
        }

        /* File Preview Section */
        .file-preview-section {
            background: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .file-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
        }

        .file-icon-wrapper:hover {
            transform: scale(1.05);
        }

        .file-extension-badge {
            position: absolute;
            bottom: -8px;
            right: -8px;
            background: white;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-soft);
            border: 2px solid #f7fafc;
        }

        .file-title {
            color: var(--dark);
            font-weight: 600;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
        }

        .stat-item:hover {
            transform: translateX(5px);
        }

        .stat-label {
            font-weight: 500;
            color: var(--secondary);
        }

        .stat-value {
            font-weight: 600;
            color: var(--dark);
        }

        /* Description Section */
        .file-description-section {
            background: white;
        }

        .description-content {
            background: #f7fafc;
            border-radius: 0.75rem;
            padding: 1.25rem;
            border-left: 4px solid var(--primary);
        }

        .description-text {
            color: var(--dark);
            line-height: 1.6;
        }

        .no-description {
            font-style: italic;
        }

        /* Assigned Users Section */
        .assigned-users-section {
            background: white;
        }

        .user-item {
            transition: var(--transition);
            animation: fadeInUp 0.5s ease forwards;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .user-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .user-avatar .avatar-img,
        .modal-user-avatar .avatar-img,
        .uploader-avatar .avatar-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow-soft);
        }

        .user-avatar .avatar-placeholder,
        .modal-user-avatar .avatar-placeholder,
        .uploader-avatar .avatar-placeholder {
            width: 40px;
            height: 40px;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            box-shadow: var(--shadow-soft);
        }

        .user-name {
            color: var(--dark);
            font-weight: 600;
        }

        .badge-warning {
            background: var(--warning-gradient);
            color: white;
        }

        .empty-state {
            background: #f7fafc;
            border-radius: 0.75rem;
        }

        /* Uploader Section */
        .uploader-name {
            color: var(--dark);
            font-weight: 600;
        }

        .speciality-badge {
            background: rgba(90, 103, 216, 0.1);
            color: var(--primary);
            border: 1px solid rgba(90, 103, 216, 0.2);
            padding: 4px 8px;
            border-radius: 0.5rem;
            font-size: 0.75rem;
        }

        .speciality-badge.more {
            background: #e2e8f0;
            color: var(--secondary);
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
        }

        /* Action Buttons */
        .action-button,
        .header-button {
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow-soft);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .header-button {
            width: 40px;
            height: 40px;
            padding: 0;
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .header-download-btn {
            background: var(--info-gradient);
        }

        .header-edit-btn {
            background: var(--success-gradient);
        }

        .header-assign-btn {
            background: var(--primary-gradient);
        }

        .upload-btn {
            background: var(--success-gradient);
            color: white;
        }

        .delete-btn {
            background: var(--danger-gradient);
            color: white;
        }

        .action-button:hover,
        .header-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
        }

        .modal-header {
            padding: 1.25rem;
            border-bottom: none;
        }

        .search-section {
            background: #f7fafc;
        }

        .search-input-group {
            position: relative;
        }

        .search-icon {
            top: 50%;
            transform: translateY(-50%);
            left: 12px;
            color: var(--secondary);
        }

        .search-input {
            padding-left: 2.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: var(--shadow-soft);
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
        }

        .users-modal-list {
            max-height: 250px;
            overflow-y: auto;
        }

        .modal-user-item {
            transition: var(--transition);
            cursor: pointer;
        }

        .modal-user-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .modal-user-item.active {
            background: rgba(90, 103, 216, 0.1);
            border-left: 4px solid var(--primary);
        }

        .modal-user-status .badge-success {
            background: var(--success-gradient);
            color: white;
        }

        .select-indicator {
            width: 24px;
            height: 24px;
            background: #e2e8f0;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .modal-user-item.active .select-indicator {
            background: var(--success-gradient);
            color: white;
        }

        .form-group .form-control {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: var(--shadow-soft);
        }

        .form-group .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
        }

        .modal-footer {
            padding: 1.25rem;
            background: #f7fafc;
        }

        .modal-footer .btn {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
        }

        .btn-outline-secondary {
            border-color: #e2e8f0;
            background: white;
            box-shadow: var(--shadow-soft);
        }

        .btn-outline-secondary:hover {
            background: #f7fafc;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-soft);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content-header {
                text-align: center;
            }

            .breadcrumb {
                float: none !important;
                justify-content: center;
            }

            .file-preview-section {
                flex-direction: column;
                text-align: center;
            }

            .file-icon-display {
                margin-bottom: 1.5rem;
            }

            .user-item,
            .modal-user-item {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .user-avatar,
            .modal-user-avatar {
                margin-right: 0;
            }

            .modal-user-status {
                justify-content: center;
            }

            .action-buttons {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .action-button {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 576px) {
            .header-actions {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .header-button {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }
        }
    </style>
@endsection