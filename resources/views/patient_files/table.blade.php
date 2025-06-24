<!-- New File Manager Component -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager Component</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- File Cards Container -->
    <div class="modern-table-container">
        <!-- Sample file cards for demonstration -->
        <div class="file-card" style="animation-delay: 0s">
            <div class="file-icon-container">
                <i class="fas fa-file-pdf text-danger file-icon"></i>
                <div class="file-type-badge">PDF</div>
            </div>

            <div class="file-content">
                <div class="file-header">
                    <h6 class="file-name">Medical Report.pdf</h6>
                    <div class="file-size">
                        <span class="size-badge">2.5 MB</span>
                    </div>
                </div>

                <div class="file-description">
                    <p class="description-text">
                        Patient's latest medical examination report including blood work and X-ray results.
                    </p>
                </div>

                <div class="file-meta">
                    <div class="uploader-info">
                        <div class="uploader-avatar">
                            <div class="avatar-placeholder">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="uploader-details">
                            <span class="uploader-name">Dr. Smith</span>
                            <span class="upload-date">
                                <i class="fas fa-clock mr-1"></i>
                                2 hours ago
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="file-actions">
                <button class="action-btn view-btn" data-toggle="tooltip" title="View details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn download-btn" data-toggle="tooltip" title="Download">
                    <i class="fas fa-download"></i>
                </button>
                <button class="action-btn assign-btn" onclick="fileManager.openAssignModal('1', 'Medical Report.pdf')"
                    data-toggle="tooltip" title="Assign access">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button class="action-btn delete-btn" data-toggle="tooltip" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>

        <!-- Another sample file -->
        <div class="file-card" style="animation-delay: 0.1s">
            <div class="file-icon-container">
                <i class="fas fa-file-image text-info file-icon"></i>
                <div class="file-type-badge">JPG</div>
            </div>

            <div class="file-content">
                <div class="file-header">
                    <h6 class="file-name">X-Ray Results.jpg</h6>
                    <div class="file-size">
                        <span class="size-badge">1.2 MB</span>
                    </div>
                </div>

                <div class="file-description">
                    <p class="description-text">
                        Chest X-ray showing clear lung fields with no abnormalities detected.
                    </p>
                </div>

                <div class="file-meta">
                    <div class="uploader-info">
                        <div class="uploader-avatar">
                            <div class="avatar-placeholder">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="uploader-details">
                            <span class="uploader-name">Dr. Johnson</span>
                            <span class="upload-date">
                                <i class="fas fa-clock mr-1"></i>
                                1 day ago
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="file-actions">
                <button class="action-btn view-btn" data-toggle="tooltip" title="View details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="action-btn download-btn" data-toggle="tooltip" title="Download">
                    <i class="fas fa-download"></i>
                </button>
                <button class="action-btn assign-btn" onclick="fileManager.openAssignModal('2', 'X-Ray Results.jpg')"
                    data-toggle="tooltip" title="Assign access">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
        </div>

        <!-- Empty State (hidden by default, shown when no files) -->
        <div class="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h5 class="empty-title">No Files Found</h5>
            <p class="empty-description">Upload your first file to get started.</p>
        </div>
    </div>

    <!-- Assign Access Modal -->
    <div class="modal fade" id="assignAccessModal" tabindex="-1" aria-labelledby="assignAccessModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary text-white border-0">
                    <h5 class="modal-title" id="assignAccessModalLabel">
                        <i class="fas fa-user-plus mr-2"></i>Assign Access
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body p-0">
                    <div class="selected-file-info p-3 bg-light border-bottom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file text-primary mr-2"></i>
                            <span class="font-weight-bold">File:</span>
                            <span class="ml-2" id="selectedFileName">-</span>
                        </div>
                    </div>

                    <div class="search-section p-4 bg-light">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="email" class="form-control" id="userSearch"
                                placeholder="Enter user email address" autocomplete="off">
                        </div>
                        <small class="text-muted mt-2 d-block">Enter the exact email address to search for users</small>
                    </div>

                    <form id="assignAccessForm" method="POST" action="#">
                        <div class="users-modal-list" id="userList">
                            <div id="noResultsMessage" class="text-center p-4 text-muted">
                                <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p>Enter an email address to search for users</p>
                            </div>

                            <!-- Sample users for demonstration -->
                            <div class="modal-user-item" data-email="john.doe@example.com" data-name="John Doe"
                                data-user-id="1">
                                <div class="modal-user-avatar mr-3">
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="modal-user-info flex-grow-1">
                                    <h6 class="user-name mb-1">John Doe</h6>
                                    <small class="text-muted d-block">john.doe@example.com</small>
                                    <div class="specialities mt-1">
                                        <span class="badge badge-light badge-sm mr-1">Cardiology</span>
                                        <span class="badge badge-light badge-sm mr-1">Internal Medicine</span>
                                    </div>
                                </div>
                                <div class="modal-user-status">
                                    <div class="assigned-indicator text-success" style="display: none;">
                                        <span class="badge badge-success">Already assigned</span>
                                    </div>
                                    <div class="select-indicator">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-user-item" data-email="jane.smith@example.com" data-name="Jane Smith"
                                data-user-id="2">
                                <div class="modal-user-avatar mr-3">
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="modal-user-info flex-grow-1">
                                    <h6 class="user-name mb-1">Jane Smith</h6>
                                    <small class="text-muted d-block">jane.smith@example.com</small>
                                    <div class="specialities mt-1">
                                        <span class="badge badge-light badge-sm mr-1">Neurology</span>
                                    </div>
                                </div>
                                <div class="modal-user-status">
                                    <div class="assigned-indicator text-success" style="display: none;">
                                        <span class="badge badge-success">Already assigned</span>
                                    </div>
                                    <div class="select-indicator">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-user-item already-assigned" data-email="assigned.user@example.com"
                                data-name="Already Assigned User" data-user-id="3">
                                <div class="modal-user-avatar mr-3">
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="modal-user-info flex-grow-1">
                                    <h6 class="user-name mb-1">Already Assigned User</h6>
                                    <small class="text-muted d-block">assigned.user@example.com</small>
                                </div>
                                <div class="modal-user-status">
                                    <div class="assigned-indicator text-success" style="display: block;">
                                        <span class="badge badge-success">Already assigned</span>
                                    </div>
                                    <div class="select-indicator" style="display: none;">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group p-4 border-top">
                            <label for="expiration_date">Expiration Date (Optional)</label>
                            <input type="date" name="expiration_date" id="expiration_date" class="form-control">
                            <small class="form-text text-muted">Leave empty for permanent access</small>
                        </div>

                        <input type="hidden" name="user_id" id="selectedUserId" required>
                        <input type="hidden" name="patient_file_id" id="selectedFileId">
                    </form>
                </div>

                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="assignAccessForm" id="assignButton" disabled>
                        <i class="fas fa-user-plus mr-1"></i>Assign Access
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* CSS Variables for consistent theming */
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

        /* File Card Styles */
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

        /* File Icon Styles */
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

        /* File Content Styles */
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

        /* Uploader Info Styles */
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

        /* Action Button Styles */
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

        /* Empty State Styles */
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

        /* Modal Styles */
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
            opacity: 0.7 !important;
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

        /* Animations */
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

        /* Responsive Design */
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

    <script>
        class FileManager {
            constructor() {
                this.currentFileId = null;
                this.selectedUserId = null;
                this.assignedUsers = new Set();
                this.searchTimeout = null;

                this.initializeElements();
                this.bindEvents();
                this.initializeTooltips();

                // Make instance globally available
                window.fileManager = this;

                console.log('FileManager initialized successfully');
            }

            initializeElements() {
                // Modal elements
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

                // Validate required elements
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
                // Initialize Bootstrap tooltips
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }

            bindEvents() {
                // Search input event with debouncing
                if (this.userSearch) {
                    this.userSearch.addEventListener('input', (e) => {
                        clearTimeout(this.searchTimeout);
                        this.searchTimeout = setTimeout(() => {
                            this.handleSearch(e);
                        }, 300);
                    });
                }

                // Modal events
                if (this.modal) {
                    $(this.modal).on('hidden.bs.modal', () => this.resetModal());
                    $(this.modal).on('shown.bs.modal', () => {
                        if (this.userSearch) {
                            this.userSearch.focus();
                        }
                    });
                }

                // Form submission
                if (this.assignForm) {
                    this.assignForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
                }

                // User item clicks - using event delegation
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
                console.log('Searching for:', searchEmail);
                this.filterUsers(searchEmail);
            }

            filterUsers(searchEmail) {
                if (!this.userList) return;

                const userItems = this.userList.querySelectorAll('.modal-user-item');
                let hasVisibleUsers = false;

                userItems.forEach(item => {
                    const userEmail = (item.dataset.email || '').trim().toLowerCase();
                    const userName = (item.dataset.name || '').trim().toLowerCase();

                    // Match by email or name
                    const shouldShow = !searchEmail ||
                        userEmail.includes(searchEmail) ||
                        userName.includes(searchEmail);

                    if (shouldShow) {
                        this.showUserItem(item);
                        hasVisibleUsers = true;
                    } else {
                        this.hideUserItem(item);

                        // Clear selection if hidden user was selected
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

                // Show select indicator for non-assigned users
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

                    if (searchEmail) {
                        this.noResultsMessage.innerHTML = `
                    <i class="fas fa-user-slash mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p>No user found with email: <strong>${this.escapeHtml(searchEmail)}</strong></p>
                `;
                    } else {
                        this.noResultsMessage.innerHTML = `
                    <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p>Enter an email address to search for users</p>
                `;
                    }
                }
            }

            selectUser(userItem) {
                if (!userItem || userItem.classList.contains('already-assigned')) {
                    console.log('Cannot select user: item is null or already assigned');
                    return;
                }

                // Clear previous selection
                this.clearSelection();

                // Select new user
                userItem.classList.add('selected');
                this.selectedUserId = userItem.dataset.userId;

                // Update form inputs
                if (this.selectedUserIdInput) {
                    this.selectedUserIdInput.value = this.selectedUserId;
                }

                // Enable assign button
                if (this.assignButton) {
                    this.assignButton.disabled = false;
                }

                // Update visual indicators
                const selectIndicator = userItem.querySelector('.select-indicator');
                if (selectIndicator) {
                    selectIndicator.style.background = 'var(--primary-color)';
                    selectIndicator.style.color = 'white';
                    selectIndicator.style.transform = 'scale(1.1)';
                }

                console.log('User selected:', this.selectedUserId);
            }

            clearSelection() {
                // Remove selection from all user items
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

                // Reset form state
                this.selectedUserId = null;
                if (this.selectedUserIdInput) {
                    this.selectedUserIdInput.value = '';
                }
                if (this.assignButton) {
                    this.assignButton.disabled = true;
                }
            }

            openAssignModal(fileId, fileName) {
                console.log('Opening assign modal for file:', fileId, fileName);

                this.currentFileId = fileId;

                // Update modal content
                if (this.selectedFileName) {
                    this.selectedFileName.textContent = fileName;
                }
                if (this.selectedFileIdInput) {
                    this.selectedFileIdInput.value = fileId;
                }

                // Reset modal state
                this.resetModal();

                // Load assigned users for this file
                this.loadAssignedUsers();

                // Show modal
                if (typeof $ !== 'undefined') {
                    $('#assignAccessModal').modal('show');
                }
            }

            resetModal() {
                // Clear form inputs
                if (this.userSearch) {
                    this.userSearch.value = '';
                }
                if (this.expirationDate) {
                    this.expirationDate.value = '';
                }

                // Clear selection
                this.clearSelection();

                // Hide all user items initially
                if (this.userList) {
                    this.userList.querySelectorAll('.modal-user-item').forEach(item => {
                        this.hideUserItem(item);
                        item.classList.remove('already-assigned');

                        // Reset assigned indicator
                        const assignedIndicator = item.querySelector('.assigned-indicator');
                        if (assignedIndicator) {
                            assignedIndicator.style.display = 'none';
                        }
                    });
                }

                // Show default no results message
                this.updateNoResultsMessage('', false);
            }

            loadAssignedUsers() {
                // This would typically load from server
                // For demo purposes, we'll use the sample data
                if (this.currentFileId && this.userList) {
                    const assignedUserItem = this.userList.querySelector('[data-user-id="3"]');
                    if (assignedUserItem) {
                        assignedUserItem.classList.add('already-assigned');
                        const assignedIndicator = assignedUserItem.querySelector('.assigned-indicator');
                        if (assignedIndicator) {
                            assignedIndicator.style.display = 'block';
                        }
                    }
                }
            }

            handleFormSubmit(event) {
                event.preventDefault();

                if (!this.selectedUserId) {
                    alert('Please select a user to assign access to.');
                    return;
                }

                // Get form data
                const formData = new FormData(this.assignForm);

                console.log('Form submission:', {
                    fileId: this.currentFileId,
                    userId: this.selectedUserId,
                    expirationDate: formData.get('expiration_date')
                });

                // Here you would typically make an AJAX request to your server
                // For demo purposes, we'll just show a success message
                this.showSuccessMessage();

                // Close modal
                if (typeof $ !== 'undefined') {
                    $('#assignAccessModal').modal('hide');
                }
            }

            showSuccessMessage() {
                // You can implement your preferred notification system here
                alert('Access assigned successfully!');
            }

            // Utility methods
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Global methods for backward compatibility
            downloadFile(url) {
                if (url) {
                    window.open(url, '_blank');
                }
            }

            confirmDelete(url) {
                if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    form.appendChild(csrfInput);

                    // Add method spoofing for DELETE
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

        // Initialize FileManager when DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            const fileManager = new FileManager();

            // Make methods globally available for onclick handlers
            window.openAssignModal = (fileId, fileName) => fileManager.openAssignModal(fileId, fileName);
            window.downloadFile = (url) => fileManager.downloadFile(url);
            window.confirmDelete = (url) => fileManager.confirmDelete(url);
            window.selectUser = (element) => fileManager.selectUser(element);
        });
    </script>