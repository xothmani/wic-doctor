<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Component</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="modern-table-container">
        @forelse ($files as $file)
            <div class="file-card" style="animation-delay: {{ $loop->index * 0.1 }}s">
                <div class="file-icon-container">
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

                <div class="file-content">
                    <div class="file-header">
                        <h6 class="file-name">{{ $file->file_name }}</h6>
                        <div class="file-size">
                            @if(isset($file->file_size) && $file->file_size)
                                <span class="size-badge">{{ number_format($file->file_size / 1024, 1) }} KB</span>
                            @else
                                <span class="size-badge unknown">{{ trans('lang.unknown') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="file-description">
                        <p class="description-text">
                            {{ $file->description ?? trans('lang.no_description') }}
                        </p>
                    </div>

                    <div class="file-meta">
                        <div class="uploader-info">
                            <div class="uploader-avatar">
                                @if($file->uploader && $file->uploader->media->isNotEmpty())
                                    <img src="{{ $file->uploader->media->first()->getUrl() }}" alt="{{ $file->uploader->name }}"
                                        class="avatar-img">
                                @else
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="uploader-details">
                                <span class="uploader-name">{{ $file->uploader->name ?? trans('lang.unknown_uploader') }}</span>
                                <span class="upload-date">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $file->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="file-actions">
                    @if(auth()->user()->hasPermissionInContext('patient_files.show', auth()->user()->getDoctorId()))
                        <button class="action-btn view-btn"
                            onclick="window.location.href='{{ route('patient_files.show', [$patient, $file]) }}'"
                            data-toggle="tooltip" title="{{ trans('lang.view_details') }}">
                            <i class="fas fa-eye"></i>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermissionInContext('patient_files.download', auth()->user()->getDoctorId()))
                        <button class="action-btn download-btn"
                            onclick="fileManager.downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')"
                            data-toggle="tooltip" title="{{ trans('lang.download') }}">
                            <i class="fas fa-download"></i>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermissionInContext('patient_files.edit', auth()->user()->getDoctorId()))
                        <button class="action-btn edit-btn"
                            onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'"
                            data-toggle="tooltip" title="{{ trans('lang.edit') }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
                        <button class="action-btn assign-btn" onclick="fileManager.openAssignModal('{{ $file->id }}', '{{ addslashes($file->file_name) }}')" data-toggle="tooltip" title="{{ trans('lang.assign_access') }}"
                            data-toggle="tooltip" title="{{ trans('lang.assign_access') }}">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()) && $file->uploader && $file->uploader->id === auth()->user()->id)
                        <button class="action-btn delete-btn"
                            onclick="fileManager.confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')"
                            data-toggle="tooltip" title="{{ trans('lang.delete') }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-file-medical-alt text-muted"></i>
                </div>
                <h5 class="empty-title">{{ trans('lang.no_files_found') }}</h5>
                <p class="empty-description">{{ trans('lang.no_files_description') }}</p>
                @if(auth()->user()->hasPermissionInContext('patient_files.create', auth()->user()->getDoctorId()))
                    <a href="{{ route('patient_files.create', $patient) }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{ trans('lang.upload_first_file') }}
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
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
                                <input type="email" class="form-control" id="userSearch"
                                    placeholder="{{ trans('lang.enter_user_email') }}" autocomplete="off">
                            </div>
                            <small class="text-muted mt-2 d-block">{{ trans('lang.enter_exact_email_address') }}</small>
                        </div>

                        <form id="assignAccessForm" method="POST" action="{{ route('patient_files.assign_access', [$patient, ':id']) }}">
                            @csrf
                            <div class="users-modal-list" id="userList">
                                <div id="noResultsMessage" class="text-center p-4 text-muted" style="display: none;">
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
                            <input type="hidden" name="patient_file_id" id="selectedFileId">
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

        .modern-table-container {
            padding: 0;
        }

        .file-card {
            display: flex;
            align-items: center;
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            opacity: 0;
            animation: slideInUp 0.6s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .file-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--info-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .file-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-heavy);
        }

        .file-card:hover::before {
            opacity: 1;
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

        .file-content {
            flex-grow: 1;
            min-width: 0;
            margin-right: 1.5rem;
        }

        .file-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            gap: 1rem;
        }

        .file-name {
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            font-size: 1.125rem;
            word-break: break-word;
            line-height: 1.2;
            flex: 1;
        }

        .size-badge {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
            white-space: nowrap;
        }

        .size-badge.unknown {
            background: #f7fafc;
            color: var(--muted-color);
            border-color: var(--border-color);
        }

        .description-text {
            color: var(--muted-color);
            font-size: 0.875rem;
            margin: 0 0 0.75rem 0;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .uploader-info {
            display: flex;
            align-items: center;
        }

        .uploader-avatar {
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .uploader-avatar .avatar-img {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: var(--shadow-light);
        }

        .uploader-avatar .avatar-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
            box-shadow: var(--shadow-light);
        }

        .uploader-details {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
        }

        .uploader-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.875rem;
            line-height: 1.2;
        }

        .upload-date {
            color: var(--muted-color);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .file-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 0.875rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .action-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .view-btn {
            background: var(--info-gradient);
        }

        .download-btn {
            background: var(--success-gradient);
        }

        .edit-btn {
            background: var(--warning-gradient);
        }

        .delete-btn {
            background: var(--danger-gradient);
        }

        .assign-btn {
            background: var(--primary-gradient);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .empty-icon i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 1.5rem;
        }

        .empty-title {
            color: #2d3748;
            margin-bottom: 0.75rem;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .empty-description {
            color: var(--muted-color);
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .bg-gradient-primary {
            background: var(--primary-gradient);
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

        .modal-user-info .user-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .modal-user-info .text-muted {
            font-size: 0.75rem;
        }

        .specialities .badge {
            font-size: 0.65rem;
            padding: 0.125rem 0.5rem;
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
            .file-card {
                flex-direction: column;
                text-align: center;
                padding: 1.25rem;
                gap: 1rem;
            }

            .file-icon-container {
                margin-right: 0;
            }

            .file-content {
                margin-right: 0;
                width: 100%;
            }

            .file-header {
                flex-direction: column;
                align-items: center;
                gap: 0.75rem;
            }

            .file-name {
                text-align: center;
            }

            .file-actions {
                justify-content: center;
                flex-wrap: wrap;
            }

            .modal-dialog {
                margin: 1rem;
            }
        }

        @media (max-width: 576px) {
            .file-card {
                padding: 1rem;
            }

            .file-icon {
                font-size: 2.5rem;
            }

            .action-btn {
                width: 36px;
                height: 36px;
            }
        }
    </style>

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
                this.assignRouteTemplate = "{{ route('patient_files.assign_access', [$patient, ':file_id']) }}";

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
                        userEmail === searchEmail;

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
                    if (searchEmail && searchEmail!=='') {
                        this.noResultsMessage.style.display = 'block';
                        this.noResultsMessage.innerHTML = searchEmail ? `
                            <i class="fas fa-user-slash mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p>{{ trans('lang.no_user_found') }}: <strong>${this.escapeHtml(searchEmail)}</strong></p>
                        ` : `
                            <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                            <p>{{ trans('lang.enter_email_to_search') }}</p>
                        `;
                    }else {
                        this.noResultsMessage.style.display = 'none';
                    }
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
                    try {
                        this.selectedFileName.textContent = decodeURIComponent(escape(fileName));
                    } catch (e) {
                        this.selectedFileName.textContent = fileName;
                    }
                }
                if (this.selectedFileIdInput) {
                    this.selectedFileIdInput.value = fileId;
                }
                if (this.assignForm) {
                    this.assignForm.action = this.assignRouteTemplate.replace(':file_id', fileId);
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

                this.updateNoResultsMessage('', false);
            }

            loadAssignedUsers() {
                if (!this.currentFileId || !this.userList) return;

                const url = "/patient_files/patient_files/{{ $patient->id }}/assigned_users/" + this.currentFileId;

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: (response) => {
                        const assignedUsers = response.user_ids || [];
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
                    },
                    error: (xhr) => {
                        console.error('Error loading assigned users:', xhr.responseText);
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
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            downloadFile(url) {
                window.location.href = url;
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
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
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
        }

        document.addEventListener('DOMContentLoaded', function () {
            const fileManager = new FileManager();
            window.openAssignModal = (fileId, fileName) => fileManager.openAssignModal(fileId, fileName);
            window.downloadFile = (url) => fileManager.downloadFile(url);
            window.confirmDelete = (url) => fileManager.confirmDelete(url);
        });
    </script>
@endsection