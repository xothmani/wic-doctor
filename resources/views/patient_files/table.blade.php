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
                    <button class="action-btn download-btn"
                        onclick="downloadFile('{{ route('patient_files.download', [$patient, $file]) }}')" data-toggle="tooltip"
                        title="{{trans('lang.download')}}">
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

                @if(
                        auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()) &&
                        $file->uploader && $file->uploader->id === auth()->user()->id
                    )
                    <button class="action-btn delete-btn"
                        onclick="confirmDelete('{{ route('patient_files.destroy', [$patient, $file]) }}')" data-toggle="tooltip"
                        title="{{trans('lang.delete')}}">
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
    });
</script>

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
</style>