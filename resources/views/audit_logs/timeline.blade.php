<!-- resources/views/audit_logs/timeline.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ __('Audit Logs') }}</h1>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>{{ __('Filter logs') }}</h5>
                <form method="GET" action="{{ route('audit.timeline') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="action" class="form-label">{{ __('Type d\'action') }}</label>
                        <select class="form-select" name="action" id="action">
                            <option value="">{{ __('Toutes les actions') }}</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ __('audit.actions.' . $action) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">{{ __('Date de début') }}</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">{{ __('Date de fin') }}</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">{{ __('Appliquer les filtres') }}</button>
                        <a href="{{ route('audit.timeline') }}" class="btn btn-secondary">{{ __('Réinitialiser') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-container">
            @forelse($logsByDate as $date => $logs)
                <div class="date-header">
                    {{ \Carbon\Carbon::parse($date)->format('M d') }}
                </div>
                <div class="timeline">
                    @foreach($logs as $log)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-card">
                                    <div class="user-avatar">
                                        @if($log->user)
                                            <div class="avatar-circle">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                        @else
                                            <div class="avatar-circle system">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-content">
                                        <div class="card-header">
                                            <strong>{{ $log->user ? $log->user->name : __('System') }}</strong>
                                            @if($log->doctor)
                                                <span class="doctor-badge">{{ $log->doctor->name }}</span>
                                            @endif
                                            <span class="action-type">{{ __('audit.actions.' . $log->action) }}</span>
                                            <span class="timestamp">{{ $log->created_at->format('g:i a') }}</span>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $log->description }}</p>

                                            @if(!$log->read_at)
                                                <button type="button" class="btn btn-sm mark-read" data-id="{{ $log->id }}">
                                                    <i class="fas fa-check"></i> {{ __('Mark as read') }}
                                                </button>
                                            @endif

                                            @if($log->changes)
                                                <button type="button" class="btn btn-sm view-changes" data-id="{{ $log->id }}">
                                                    <i class="fas fa-eye"></i> {{ __('View changes') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="empty-state">
                    <p>{{ __('No audit logs found for the selected filters.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal for viewing changes -->
    <div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changesModalLabel">{{ __('Changes') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="changesModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .timeline-container {
            position: relative;
            padding: 20px 0;
        }

        .date-header {
            font-weight: bold;
            margin: 20px 0 10px;
            padding-left: 40px;
            color: #666;
        }

        .timeline {
            position: relative;
            margin-left: 20px;
            border-left: 2px dashed #e0e0e0;
            padding-bottom: 20px;
        }

        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 15px;
        }

        .timeline-dot {
            position: absolute;
            left: -6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #007bff;
            z-index: 1;
        }

        .timeline-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            width: 100%;
        }

        .timeline-card:hover {
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        .user-avatar {
            margin-right: 15px;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .avatar-circle.system {
            background: #6c757d;
        }

        .card-content {
            flex: 1;
        }

        .card-header {
            margin-bottom: 10px;
        }

        .doctor-badge {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-left: 8px;
        }

        .action-type {
            color: #6c757d;
            font-size: 0.9em;
            margin-left: 8px;
        }

        .timestamp {
            float: right;
            color: #6c757d;
            font-size: 0.85em;
        }

        .btn.mark-read {
            background-color: #17a2b8;
            color: white;
            margin-right: 5px;
        }

        .btn.view-changes {
            background-color: #007bff;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Handle mark as read button
            $(document).on('click', '.mark-read', function () {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ route('audit.markAsRead') }}",
                    type: 'POST',
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $(this).closest('.timeline-card').removeClass('unread');
                            $(this).remove();
                        }
                    }.bind(this)
                });
            });

            // Handle view changes button
            $(document).on('click', '.view-changes', function () {
                const id = $(this).data('id');
                $.ajax({
                    url: "{{ route('audit.getChanges') }}",
                    type: 'GET',
                    data: {
                        id: id
                    },
                    success: function (response) {
                        $('#changesModalBody').html(response.html);
                        $('#changesModal').modal('show');
                    }
                });
            });
        });
    </script>
@endpush