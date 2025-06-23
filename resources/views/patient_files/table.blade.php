<!-- resources/views/patient_files/table.blade.php -->

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
                            <span class="size-badge unknown">{{trans('lang.unknown')}}</span>
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
                        data-toggle="tooltip" title="{{trans('lang.view_details')}}">
                        <i class="fas fa-eye"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.download', auth()->user()->getDoctorId()))
                    <button class="action-btn download-btn" data-url="{{ route('patient_files.download', [$patient, $file]) }}"
                        data-toggle="tooltip" title="{{trans('lang.download')}}" type="button">
                        <i class="fas fa-download"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.edit', auth()->user()->getDoctorId()))
                    <button class="action-btn edit-btn"
                        onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'"
                        data-toggle="tooltip" title="{{trans('lang.edit')}}">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
                    <button class="action-btn assign-btn" data-toggle="modal" data-target="#assignAccessModal"
                        data-file-id="{{ $file->id }}" data-file-name="{{ $file->file_name }}"
                        data-toggle="tooltip" title="{{trans('lang.assign_access')}}">
                        <i class="fas fa-user-plus"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()) && $file->uploader && $file->uploader->id === auth()->user()->id)
                    <button class="action-btn delete-btn" data-url="{{ route('patient_files.destroy', [$patient, $file]) }}"
                        data-toggle="tooltip" title="{{trans('lang.delete')}}" type="button">
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
            <h5 class="empty-title">{{trans('lang.no_files_found')}}</h5>
            <p class="empty-description">{{trans('lang.no_files_description')}}</p>
            @if(auth()->user()->hasPermissionInContext('patient_files.create', auth()->user()->getDoctorId()))
                <a href="{{ route('patient_files.create', $patient) }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>{{trans('lang.upload_first_file')}}
                </a>
            @endif
        </div>
    @endforelse
</div>

<!-- Single Assign Access Modal -->
@if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
    <div class="modal fade" id="assignAccessModal" tabindex="-1" role="dialog"
        aria-labelledby="assignAccessModalLabel" aria-hidden="true">
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
                    <div class="selected-file-info p-3 bg-light border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file text-primary mr-2"></i>
                            <span class="font-weight-bold">{{ trans('lang.file') }}:</span>
                            <span class="ml-2" id="selectedFileName">-</span>
                        </div>
                    </div>
                    
                    <div class="search-section p-4 bg-light">
                        <div class="search-input-group position-relative">
                            <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 10;"></i>
                            <input type="text" class="form-control pl-5" id="userSearch"
                                placeholder="{{ trans('lang.enter_user_email') }}...">
                        </div>
                    </div>
                    
                    <form id="assignAccessForm" method="POST">
                        @csrf
                        <div class="users-modal-list" id="userList" style="max-height: 400px; overflow-y: auto;">
                            @if(isset($allUsers))
                                @foreach($allUsers as $user)
                                    @if($user->name && $user->email)
                                        <div class="modal-user-item user-item-modal d-flex align-items-center p-3 border-bottom" 
                                             data-value="{{ $user->id }}" data-email="{{ $user->email }}" 
                                             data-name="{{ $user->name }}" style="display: none; cursor: pointer;"
                                             onclick="selectUser(this)">
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
                                                <div class="select-indicator text-primary" style="display: none;">
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
/* Custom styles for the assign user functionality */
.search-input-group {
    position: relative;
}

.modal-user-item:hover {
    background-color: #f8f9fa;
}

.modal-user-item.selected {
    background-color: #e3f2fd;
    border-left: 3px solid #007bff;
}

.modal-user-item.already-assigned {
    background-color: #f8f9fa;
    opacity: 0.7;
    cursor: not-allowed;
}

.selected-file-info {
    font-size: 0.9rem;
}

.speciality-badge {
    background-color: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.75rem;
    margin-right: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('userSearch');
    const userList = document.getElementById('userList');
    const assignButton = document.getElementById('assignButton');
    const assignForm = document.getElementById('assignAccessForm');
    const selectedFileNameSpan = document.getElementById('selectedFileName');
    const selectedUserIdInput = document.getElementById('selectedUserId');
    const selectedFileIdInput = document.getElementById('selectedFileId');
    let currentSelectedUser = null;
    let currentFileId = null;

    // Handle assign button clicks
    document.querySelectorAll('.assign-btn').forEach(button => {
        button.addEventListener('click', function() {
            currentFileId = this.dataset.fileId;
            const fileName = this.dataset.fileName;
            
            // Update modal content
            selectedFileNameSpan.textContent = fileName;
            selectedFileIdInput.value = currentFileId;
            
            // Update form action URL
            assignForm.action = `{{ route('patient_files.assign_access', [$patient, ':fileId']) }}`.replace(':fileId', currentFileId);
            
            // Reset modal state
            resetModal();
            
            // Load and show users with assignment status
            loadUsersWithAssignmentStatus();
        });
    });

    // Search functionality
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userItems = userList.querySelectorAll('.modal-user-item');
            
            userItems.forEach(item => {
                const email = item.dataset.email.toLowerCase();
                const name = item.dataset.name.toLowerCase();
                
                if (email.includes(searchTerm) || name.includes(searchTerm)) {
                    item.style.display = 'flex !important';
                } else {
                    item.style.display = 'none !important';
                }
            });
        });
    }

    // Global function for user selection
    window.selectUser = function(element) {
        // Don't allow selection of already assigned users
        if (element.classList.contains('already-assigned')) {
            return;
        }
        
        // Remove previous selection
        document.querySelectorAll('.modal-user-item').forEach(item => {
            item.classList.remove('selected');
            item.querySelector('.select-indicator').style.display = 'none';
        });
        
        // Select current user
        element.classList.add('selected');
        element.querySelector('.select-indicator').style.display = 'block';
        
        currentSelectedUser = {
            id: element.dataset.value,
            name: element.dataset.name,
            email: element.dataset.email
        };
        
        selectedUserIdInput.value = currentSelectedUser.id;
        assignButton.disabled = false;
    };

    // Load users with assignment status
    function loadUsersWithAssignmentStatus() {
        if (!currentFileId) return;
        
        // Reset all users to normal state
        document.querySelectorAll('.modal-user-item').forEach(item => {
            item.classList.remove('already-assigned');
            item.querySelector('.assigned-indicator').style.display = 'none';
        });
        
        // Here you would typically make an AJAX call to check which users are already assigned
        // For now, we'll use the existing data structure if available
        @if(isset($files))
            @foreach($files as $file)
                if (currentFileId == '{{ $file->id }}') {
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
                        const userItem = document.querySelector(`[data-value="${userId}"]`);
                        if (userItem) {
                            userItem.classList.add('already-assigned');
                            userItem.querySelector('.assigned-indicator').style.display = 'block';
                        }
                    });
                }
            @endforeach
        @endif
    }

    // Reset modal state
    function resetModal() {
        userSearch.value = '';
        document.getElementById('expiration_date').value = '';
        document.querySelectorAll('.modal-user-item').forEach(item => {
            item.classList.remove('selected');
            item.querySelector('.select-indicator').style.display = 'none';
            item.style.display = 'none';
        });
        currentSelectedUser = null;
        selectedUserIdInput.value = '';
        assignButton.disabled = true;
    }

    // Reset modal when closed
    $('#assignAccessModal').on('hidden.bs.modal', function() {
        resetModal();
        currentFileId = null;
    });
});
</script>

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            --danger-gradient: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
            --warning-gradient: linear-gradient(135deg, #fdcb6e 0%, #f39c12 100%);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .modern-table-container {
            padding: 0;
        }

        .search-input-group {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            /* top: 50%; */
            /* transform: translateY(-50%); */
            color: #74b9ff;
            z-index: 2;
        }

        .file-card {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
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
            height: 4px;
            background: var(--info-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .file-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .file-card:hover::before {
            opacity: 1;
        }

        .file-icon-container {
            position: relative;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .file-icon {
            font-size: 3rem;
            transition: all 0.3s ease;
        }

        .file-type-badge {
            position: absolute;
            bottom: -8px;
            right: -8px;
            background: white;
            color: #667eea;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border: 2px solid #f8f9fa;
        }

        .file-content {
            flex-grow: 1;
            min-width: 0;
            margin-right: 20px;
        }

        .file-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .file-name {
            font-weight: 600;
            color: #2d3436;
            margin: 0;
            font-size: 1.1rem;
            word-break: break-word;
            line-height: 1.3;
            max-width: 70%;
        }

        .file-size {
            flex-shrink: 0;
        }

        .size-badge {
            background: linear-gradient(135deg, rgba(116, 185, 255, 0.1) 0%, rgba(162, 155, 254, 0.1) 100%);
            color: #667eea;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(116, 185, 255, 0.2);
        }

        .size-badge.unknown {
            background: #f1f3f4;
            color: #636e72;
            border-color: #ddd;
        }

        .file-description {
            margin-bottom: 15px;
        }

        .description-text {
            color: #636e72;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .file-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .uploader-info {
            display: flex;
            align-items: center;
        }

        .uploader-avatar {
            margin-right: 10px;
            position: relative;
            flex-shrink: 0;
            height: 35px;
            width: 35px;
            border-radius: 10px;
            overflow: hidden;
        }

        .uploader-avatar .avatar-img {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .uploader-avatar .avatar-placeholder {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: var(--info-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .uploader-details {
            display: flex;
            flex-direction: column;
        }

        .uploader-name {
            font-weight: 600;
            color: #2d3436;
            font-size: 0.9rem;
            line-height: 1.2;
        }

        .upload-date {
            color: #636e72;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            margin-top: 2px;
        }

        .file-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transition: all 0.3s ease;
            transform: translate(-50%, -50%);
        }

        .action-btn:hover::before {
            width: 100%;
            height: 100%;
        }

        .view-btn {
            background: var(--info-gradient);
            color: white;
        }

        .download-btn {
            background: var(--success-gradient);
            color: white;
        }

        .edit-btn {
            background: var(--warning-gradient);
            color: white;
        }

        .delete-btn {
            background: var(--danger-gradient);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
        }

        .empty-icon i {
            font-size: 5rem;
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .empty-title {
            color: #2d3436;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-description {
            color: #636e72;
            margin-bottom: 30px;
            font-size: 1rem;
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
                padding: 20px 15px;
            }

            .file-icon-container {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .file-content {
                margin-right: 0;
                margin-bottom: 15px;
                width: 100%;
            }

            .file-header {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .file-name {
                max-width: 100%;
                text-align: center;
            }

            .file-meta {
                justify-content: center;
            }

            .file-actions {
                justify-content: center;
            }

            .action-btn {
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .file-actions {
                flex-wrap: wrap;
                gap: 6px;
            }

            .action-btn {
                width: 40px;
                height: 40px;
                font-size: 0.8rem;
            }
        }

        .modal-user-status .badge-success {
            background: var(--success-gradient);
            color: white;
        }

        .select-indicator {
            width: 24px;
            height: 24px;
            background: #e2e8f0;
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-user-item.active .select-indicator {
            background: var(--success-gradient);
            color: white;
        }

        .modal-user-item {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-user-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .modal-user-item.active {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
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

        /* Custom styles for the assign user functionality */
        .search-input-group {
            position: relative;
        }

        .modal-user-item:hover {
            background-color: #f8f9fa;
        }

        .modal-user-item.selected {
            background-color: #e3f2fd;
            border-left: 3px solid #007bff;
        }

        .selected-user-item {
            animation: fadeInUp 0.3s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-remove-btn {
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .user-remove-btn:hover {
            opacity: 1;
            color: #dc3545;
        }

        .speciality-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            margin-right: 4px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        console.log('Table script loaded');
        // console.log('jQuery loaded:', typeof $ !== 'undefined' ? $.fn.jquery : 'Not loaded');
        // console.log('allUsers:', {{ json_encode($allUsers) }});

        function tableDownloadFile(url) {
            console.log('Downloading file from:', url);
            window.open(url, '_blank');
        }

        function tableConfirmDelete(url) {
            if (confirm('{{ trans("lang.confirm_delete_file") }}')) {
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
            console.log('Initializing table view');

            $('[data-toggle="tooltip"]').tooltip();

            $('.download-btn').on('click', function () {
                console.log('Download button clicked');
                const url = $(this).data('url');
                tableDownloadFile(url);
            });

            $('.assign-btn').on('click', function () {
                const modalTarget = $(this).data('target');
                $(modalTarget).modal('show');
            });

            $('.delete-btn').on('click', function () {
                const url = $(this).data('url');
                tableConfirmDelete(url);
            });

            @foreach($files as $file)
                $('#assignAccessModal_{{ $file->id }}').on('show.bs.modal', function () {
                    console.log('Opening modal for file ID: {{ $file->id }}');
                    const searchField = document.getElementById('userSearch_{{ $file->id }}');
                    if (searchField) {
                        searchField.value = '';
                        const newSearchField = searchField.cloneNode(true);
                        searchField.parentNode.replaceChild(newSearchField, searchField);
                        newSearchField.addEventListener('input', function () {
                            filterUserList(this, '{{ $file->id }}');
                        });
                        setTimeout(() => newSearchField.focus(), 300);
                        filterUserList(newSearchField, '{{ $file->id }}');
                    }
                });

                $('#assignAccessModal_{{ $file->id }}').on('hidden.bs.modal', function () {
                    console.log('Closing modal for file ID: {{ $file->id }}');
                    document.getElementById('selectedUserId_{{ $file->id }}').value = '';
                    document.getElementById('expiration_date_{{ $file->id }}').value = '';
                    document.querySelectorAll('#userList_{{ $file->id }} .modal-user-item').forEach(item => {
                        item.classList.remove('active');
                        item.style.display = 'none';
                    });
                });
            @endforeach
                            });

        function filterUserList(input, fileId) {
            const searchTerm = input.value.toLowerCase().trim();
            console.log('Filtering users for file ID:', fileId, 'with term:', searchTerm);
            document.querySelectorAll(`#userList_${fileId} .modal-user-item`).forEach(item => {
                const email = item.getAttribute('data-email').toLowerCase();
                item.style.display = (searchTerm === '' || email.includes(searchTerm)) ? '' : 'none';
            });
        }

        function selectUser(element, fileId) {
            const userId = element.getAttribute('data-value');
            console.log('Selected user ID:', userId, 'for file ID:', fileId);
            document.getElementById(`selectedUserId_${fileId}`).value = userId;
            document.querySelectorAll(`#userList_${fileId} .modal-user-item`).forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            let selectedUsers = [];
            const userSearch = document.getElementById('userSearch');
            const userList = document.getElementById('userList');
            const selectedUsersList = document.getElementById('selectedUsersList');
            const emptyState = document.getElementById('emptyState');
            const addButton = document.getElementById('addSelectedUser');
            const assignedUsersInput = document.getElementById('assignedUsersData');
            let currentSelectedUser = null;

            // Search functionality
            if (userSearch) {
                userSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const userItems = userList.querySelectorAll('.modal-user-item');
                    
                    userItems.forEach(item => {
                        const email = item.dataset.email.toLowerCase();
                        const name = item.dataset.name.toLowerCase();
                        
                        if (email.includes(searchTerm) || name.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Update the assigned users input
            function updateAssignedUsersInput() {
                const usersData = selectedUsers.map(user => ({
                    user_id: user.id,
                    expiration_date: user.expiration_date
                }));
                assignedUsersInput.value = JSON.stringify(usersData);
            }

            // Render selected users
            function renderSelectedUsers() {
                if (selectedUsers.length === 0) {
                    emptyState.style.display = 'block';
                    return;
                }
                
                emptyState.style.display = 'none';
                
                const usersHtml = selectedUsers.map(user => `
                    <div class="user-item selected-user-item d-flex align-items-center p-3 mb-2 rounded bg-white shadow-sm">
                        <div class="user-avatar mr-3">
                            ${user.avatar ? 
                                `<img src="${user.avatar}" alt="${user.name}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">` :
                                `<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>`
                            }
                        </div>
                        <div class="user-info flex-grow-1">
                            <h4 class="user-name h6 mb-1">${user.name}</h4>
                            <small class="text-muted d-block">${user.email}</small>
                            ${user.expiration_date ? 
                                `<div class="expiration-info mt-1">
                                    <span class="badge badge-warning">
                                        {{ trans('lang.expires') }} ${new Date(user.expiration_date).toLocaleDateString()}
                                    </span>
                                </div>` : ''
                            }
                        </div>
                        <div class="user-actions">
                            <button type="button" class="btn btn-sm btn-outline-danger user-remove-btn" onclick="removeUser(${user.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
                
                selectedUsersList.innerHTML = usersHtml;
            }

            // Global functions for the onclick handlers
            window.selectUser = function(element) {
                // Remove previous selection
                document.querySelectorAll('.modal-user-item').forEach(item => {
                    item.classList.remove('selected');
                    item.querySelector('.select-indicator').style.display = 'none';
                });
                
                // Select current user
                element.classList.add('selected');
                element.querySelector('.select-indicator').style.display = 'block';
                
                currentSelectedUser = {
                    id: element.dataset.value,
                    name: element.dataset.name,
                    email: element.dataset.email,
                    avatar: element.querySelector('img') ? element.querySelector('img').src : null
                };
                
                addButton.disabled = false;
            };

            window.removeUser = function(userId) {
                selectedUsers = selectedUsers.filter(user => user.id != userId);
                renderSelectedUsers();
                updateAssignedUsersInput();
            };

            // Add selected user
            if (addButton) {
                addButton.addEventListener('click', function() {
                    if (currentSelectedUser) {
                        // Check if user is already selected
                        if (selectedUsers.find(user => user.id === currentSelectedUser.id)) {
                            alert('{{ trans("lang.user_already_selected") }}');
                            return;
                        }
                        
                        const expirationDate = document.getElementById('expiration_date').value;
                        currentSelectedUser.expiration_date = expirationDate;
                        
                        selectedUsers.push(currentSelectedUser);
                        renderSelectedUsers();
                        updateAssignedUsersInput();
                        
                        // Reset modal
                        document.getElementById('userSearch').value = '';
                        document.getElementById('expiration_date').value = '';
                        document.querySelectorAll('.modal-user-item').forEach(item => {
                            item.classList.remove('selected');
                            item.querySelector('.select-indicator').style.display = 'none';
                            item.style.display = 'none';
                        });
                        currentSelectedUser = null;
                        addButton.disabled = true;
                        
                        // Close modal
                        $('#assignAccessModal').modal('hide');
                    }
                });
            }

            // Reset modal when closed
            $('#assignAccessModal').on('hidden.bs.modal', function() {
                document.getElementById('userSearch').value = '';
                document.getElementById('expiration_date').value = '';
                document.querySelectorAll('.modal-user-item').forEach(item => {
                    item.classList.remove('selected');
                    item.querySelector('.select-indicator').style.display = 'none';
                    item.style.display = 'none';
                });
                currentSelectedUser = null;
                if (addButton) addButton.disabled = true;
            });
        });
    </script>
@endsection

<!-- resources/views/patient_files/table.blade.php -->

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
                                <img src="{{ $file->uploader->media->first()->getUrl() }}" alt="{{ $file->uploader->name }}" class="avatar-img">
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
                    <button class="action-btn view-btn" onclick="window.location.href='{{ route('patient_files.show', [$patient, $file]) }}'" data-toggle="tooltip" title="{{ trans('lang.view_details') }}">
                        <i class="fas fa-eye"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.download', auth()->user()->getDoctorId()))
                    <button class="action-btn download-btn" onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')" data-toggle="tooltip" title="{{ trans('lang.download') }}">
                        <i class="fas fa-download"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.edit', auth()->user()->getDoctorId()))
                    <button class="action-btn edit-btn" onclick="window.location.href='{{ route('patient_files.edit', [$patient, $file]) }}'" data-toggle="tooltip" title="{{ trans('lang.edit') }}">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
                    <button class="action-btn assign-btn" onclick="openAssignModal('{{ $file->id }}', '{{ $file->file_name }}')" data-toggle="tooltip" title="{{ trans('lang.assign_access') }}">
                        <i class="fas fa-user-plus"></i>
                    </button>
                @endif

                @if(auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()) && $file->uploader && $file->uploader->id === auth()->user()->id)
                    <button class="action-btn delete-btn" onclick="confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')" data-toggle="tooltip" title="{{ trans('lang.delete') }}">
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

<!-- Assign Access Modal -->
@if(auth()->user()->hasPermissionInContext('patient_files.assign_access', auth()->user()->getDoctorId()))
    <div class="modal fade" id="assignAccessModal" tabindex="-1" role="dialog" aria-labelledby="assignAccessModalLabel" aria-hidden="true">
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
                    <div class="selected-file-info p-3 bg-light border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file text-primary mr-2"></i>
                            <span class="font-weight-bold">{{ trans('lang.file') }}:</span>
                            <span class="ml-2" id="selectedFileName">-</span>
                        </div>
                    </div>
                    
                    <div class="search-section p-4 bg-light">
                        <div class="search-input-group position-relative">
                            <i class="fas fa-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 10;"></i>
                            <input type="text" class="form-control pl-5" id="userSearch" placeholder="{{ trans('lang.enter_user_email') }}...">
                        </div>
                        <small class="text-muted mt-2 d-block">{{ trans('lang.enter_exact_email_address') }}</small>
                    </div>
                    
                    <form id="assignAccessForm" method="POST">
                        @csrf
                        <div class="users-modal-list" id="userList" style="max-height: 400px; overflow-y: auto;">
                            <div id="noResultsMessage" class="text-center p-4 text-muted">
                                <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p>{{ trans('lang.enter_email_to_search') }}</p>
                            </div>
                            
                            @if(isset($allUsers))
                                @foreach($allUsers as $user)
                                    @if($user->name && $user->email)
                                        <div class="modal-user-item user-item-modal d-flex align-items-center p-3 border-bottom" 
                                             data-email="{{ $user->email }}" 
                                             data-name="{{ $user->name }}" 
                                             data-user-id="{{ $user->id }}"
                                             style="display: none; cursor: pointer;"
                                             onclick="selectUser(this)">
                                            <div class="modal-user-avatar mr-3">
                                                @if($user->media->isNotEmpty())
                                                    <img src="{{ $user->media->first()->getUrl() }}" alt="{{ $user->name }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
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
                                                <div class="select-indicator text-primary" style="display: none;">
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
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
    --danger-gradient: linear-gradient(135deg, #ff7675 0%, #d63031 100%);
    --warning-gradient: linear-gradient(135deg, #fdcb6e 0%, #f39c12 100%);
    --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
}

.modern-table-container {
    padding: 0;
}

.file-card {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: var(--shadow-soft);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
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
    height: 4px;
    background: var(--info-gradient);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.file-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
}

.file-card:hover::before {
    opacity: 1;
}

.file-icon-container {
    position: relative;
    margin-right: 20px;
    flex-shrink: 0;
}

.file-icon {
    font-size: 3rem;
    transition: all 0.3s ease;
}

.file-type-badge {
    position: absolute;
    bottom: -8px;
    right: -8px;
    background: white;
    color: #667eea;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border: 2px solid #f8f9fa;
}

.file-content {
    flex-grow: 1;
    min-width: 0;
    margin-right: 20px;
}

.file-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.file-name {
    font-weight: 600;
    color: #2d3436;
    margin: 0;
    font-size: 1.1rem;
    word-break: break-word;
    line-height: 1.3;
    max-width: 70%;
}

.size-badge {
    background: linear-gradient(135deg, rgba(116, 185, 255, 0.1) 0%, rgba(162, 155, 254, 0.1) 100%);
    color: #667eea;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid rgba(116, 185, 255, 0.2);
}

.size-badge.unknown {
    background: #f1f3f4;
    color: #636e72;
    border-color: #ddd;
}

.description-text {
    color: #636e72;
    font-size: 0.95rem;
    margin: 0;
    line-height: 1.4;
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
    margin-right: 10px;
    position: relative;
    flex-shrink: 0;
    height: 35px;
    width: 35px;
    border-radius: 10px;
    overflow: hidden;
}

.uploader-avatar .avatar-img {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.uploader-avatar .avatar-placeholder {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    background: var(--info-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.uploader-details {
    display: flex;
    flex-direction: column;
}

.uploader-name {
    font-weight: 600;
    color: #2d3436;
    font-size: 0.9rem;
    line-height: 1.2;
}

.upload-date {
    color: #636e72;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    margin-top: 2px;
}

.file-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.action-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transition: all 0.3s ease;
    transform: translate(-50%, -50%);
}

.action-btn:hover::before {
    width: 100%;
    height: 100%;
}

.view-btn { background: var(--info-gradient); color: white; }
.download-btn { background: var(--success-gradient); color: white; }
.edit-btn { background: var(--warning-gradient); color: white; }
.delete-btn { background: var(--danger-gradient); color: white; }
.assign-btn { background: var(--primary-gradient); color: white; }

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow-soft);
}

.empty-icon i {
    font-size: 5rem;
    opacity: 0.3;
    margin-bottom: 20px;
}

.empty-title {
    color: #2d3436;
    margin-bottom: 10px;
    font-weight: 600;
}

.empty-description {
    color: #636e72;
    margin-bottom: 30px;
    font-size: 1rem;
}

/* Modal Styles */
.modal-user-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.modal-user-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.modal-user-item.selected {
    background-color: #e3f2fd;
    border-left-color: #007bff;
}

.modal-user-item.already-assigned {
    background-color: #f8f9fa;
    opacity: 0.7;
    cursor: not-allowed;
}

.select-indicator {
    width: 24px;
    height: 24px;
    background: #e2e8f0;
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.modal-user-item.selected .select-indicator {
    background: var(--success-gradient);
    color: white;
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
        padding: 20px 15px;
    }
    
    .file-icon-container {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .file-content {
        margin-right: 0;
        margin-bottom: 15px;
        width: 100%;
    }
    
    .file-header {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .file-name {
        max-width: 100%;
        text-align: center;
    }
    
    .file-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Global variables
    let currentFileId = null;
    let currentSelectedUser = null;
    
    // Modal elements
    const assignForm = document.getElementById('assignAccessForm');
    const selectedFileNameSpan = document.getElementById('selectedFileName');
    const selectedUserIdInput = document.getElementById('selectedUserId');
    const selectedFileIdInput = document.getElementById('selectedFileId');
    const assignButton = document.getElementById('assignButton');
    const userSearch = document.getElementById('userSearch');
    const userList = document.getElementById('userList');
    const noResultsMessage = document.getElementById('noResultsMessage');
    
    // Search functionality - exact email match only
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchEmail = this.value.trim();
            const userItems = userList.querySelectorAll('.modal-user-item');
            let hasResults = false;
            
            // Hide all users initially
            userItems.forEach(item => {
                item.style.display = 'none';
            });
            
            // Show exact matches only
            if (searchEmail) {
                userItems.forEach(item => {
                    const userEmail = item.dataset.email;
                    if (userEmail === searchEmail) {
                        item.style.display = 'flex';
                        hasResults = true;
                    }
                });
            }
            
            // Show/hide no results message
            noResultsMessage.style.display = hasResults ? 'none' : 'block';
            noResultsMessage.innerHTML = searchEmail ? 
                `<i class="fas fa-user-slash mb-2" style="font-size: 2rem; opacity: 0.5;"></i><p>{{ trans('lang.no_user_found_with_email') }}: <strong>${searchEmail}</strong></p>` :
                `<i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i><p>{{ trans('lang.enter_email_to_search') }}</p>`;
        });
    }
    
    // Reset modal state
    function resetModal() {
        if (userSearch) userSearch.value = '';
        document.getElementById('expiration_date').value = '';
        
        // Hide all user items and reset selection
        document.querySelectorAll('.modal-user-item').forEach(item => {
            item.classList.remove('selected', 'already-assigned');
            item.querySelector('.select-indicator').style.display = 'none';
            item.querySelector('.assigned-indicator').style.display = 'none';
            item.style.display = 'none';
        });
        
        // Show no results message
        if (noResultsMessage) {
            noResultsMessage.style.display = 'block';
            noResultsMessage.innerHTML = `<i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i><p>{{ trans('lang.enter_email_to_search') }}</p>`;
        }
        
        currentSelectedUser = null;
        if (selectedUserIdInput) selectedUserIdInput.value = '';
        if (assignButton) assignButton.disabled = true;
    }
    
    // Load assigned users for current file
    function loadAssignedUsers() {
        if (!currentFileId) return;
        
        @if(isset($files))
            @foreach($files as $file)
                if (currentFileId === '{{ $file->id }}') {
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
                        const userItem = document.querySelector(`[data-user-id="${userId}"]`);
                        if (userItem) {
                            userItem.classList.add('already-assigned');
                            userItem.querySelector('.assigned-indicator').style.display = 'block';
                        }
                    });
                }
            @endforeach
        @endif
    }
    
    // Global functions for onclick handlers
    window.openAssignModal = function(fileId, fileName) {
        currentFileId = fileId;
        
        // Update modal content
        if (selectedFileNameSpan) selectedFileNameSpan.textContent = fileName;
        if (selectedFileIdInput) selectedFileIdInput.value = fileId;
        
        // Update form action
        if (assignForm) {
            assignForm.action = `{{ route('patient_files.assign_access', [$patient, ':fileId']) }}`.replace(':fileId', fileId);
        }
        
        // Reset modal and load assigned users
        resetModal();
        loadAssignedUsers();
        
        // Show modal
        $('#assignAccessModal').modal('show');
    };
    
    window.selectUser = function(element) {
        // Don't allow selection of already assigned users
        if (element.classList.contains('already-assigned')) {
            return;
        }
        
        // Remove previous selection
        document.querySelectorAll('.modal-user-item').forEach(item => {
            item.classList.remove('selected');
            item.querySelector('.select-indicator').style.display = 'none';
        });
        
        // Select current user
        element.classList.add('selected');
        element.querySelector('.select-indicator').style.display = 'block';
        
        currentSelectedUser = {
            id: element.dataset.userId,
            name: element.dataset.name,
            email: element.dataset.email
        };
        
        if (selectedUserIdInput) selectedUserIdInput.value = currentSelectedUser.id;
        if (assignButton) assignButton.disabled = false;
    };
    
    window.downloadFile = function(url) {
        window.open(url, '_blank');
    };
    
    window.confirmDelete = function(url) {
        if (confirm('{{ trans("lang.confirm_delete_file") }}')) {
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
    };
    
    // Reset modal when closed
    $('#assignAccessModal').on('hidden.bs.modal', function() {
        resetModal();
        currentFileId = null;
    });
});
</script>

@section('styles')
    <!-- CSS is already included in the style tag above -->
@endsection

@section('scripts')
    <!-- JavaScript is already included in the script tag above -->
@endsection