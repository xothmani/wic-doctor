@extends('layouts.app')

@section('content')
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
      <h1 class="m-0 text-bold">{{ trans('lang.audit_logs_plural') }} <small
        class="mx-3">|</small><small>{{ trans('lang.audit_logs_desc') }}</small></h1>
      </div>
      <div class="col-md-6">
      <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt mx-1"></i>
          {{ trans('lang.dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ trans('lang.audit_logs_plural') }}</li>
      </ol>
      </div>
    </div>
    </div>
  </div>

  <!-- Main content -->
  <div class="content">
    <div class="clearfix"></div>
    @include('flash::message')

    <!-- Filter Card -->
    <div class="card shadow-sm">
    <div class="card-header">
      <h3 class="card-title">{{ trans('lang.filter_logs') }}</h3>
    </div>
    <div class="card-body">
      <form id="filterForm" action="{{ route('audit-logs.index') }}" method="GET" class="row">
      <div class="col-md-3">
        <div class="form-group">
        <label for="action">{{ trans('lang.action_type') }}</label>
        <select name="action" id="action" class="form-control">
          <option value="">{{ trans('lang.all_actions') }}</option>
          @foreach($actions as $action)
        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
        {{ ucfirst(str_replace('_', ' ', $action)) }}
        </option>
      @endforeach
        </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
        <label for="start_date">{{ trans('lang.start_date') }}</label>
        <input type="date" name="start_date" id="start_date" class="form-control"
          value="{{ request('start_date') }}">
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
        <label for="end_date">{{ trans('lang.end_date') }}</label>
        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-group">
        <button type="submit" class="btn btn-primary">{{ trans('lang.apply_filters') }}</button>
        <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary ml-2">{{ trans('lang.reset') }}</a>
        </div>
      </div>
      </form>
    </div>
    </div>

    <!-- Logs Table Card -->
    <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
      <div class="d-flex flex-row">
        <li class="nav-item">
        <a class="nav-link active" href="{{ url()->current() }}"><i
          class="fa fa-list mr-2"></i>{{ trans('lang.audit_logs_table') }}</a>
        </li>
      </div>
      @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      {!! $dataTable->table(['width' => '100%', 'class' => 'table table-bordered table-striped']) !!}
    </div>
    </div>
  </div>

  <!-- Changes Modal -->
  <div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
      <h5 class="modal-title" id="changesModalLabel">{{ trans('audit.changes_details') }}</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="row mb-4">
        <div class="col-md-6">
        <h6 class="text-muted mb-3">{{ trans('audit.old_values') }}</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" id="oldValuesTable">
          <thead class="table-light">
            <tr>
            <th>{{ trans('audit.field') }}</th>
            <th>{{ trans('audit.value') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
          </table>
        </div>
        </div>
        <div class="col-md-6">
        <h6 class="text-muted mb-3">{{ trans('audit.new_values') }}</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" id="newValuesTable">
          <thead class="table-light">
            <tr>
            <th>{{ trans('audit.field') }}</th>
            <th>{{ trans('audit.value') }}</th>
            </tr>
          </thead>
          <tbody></tbody>
          </table>
        </div>
        </div>
      </div>
      <div class="details-section">
        <p class="mb-2"><span class="label">{{ trans('audit.action') }}:</span> <span id="modalAction"
          class="value"></span></p>
        <p class="mb-2"><span class="label">{{ trans('audit.entity_type') }}:</span> <span id="modalEntityType"
          class="value"></span></p>
        <p class="mb-2"><span class="label">{{ trans('audit.description') }}:</span> <span id="modalDescription"
          class="value"></span></p>
        <p class="mb-2"><span class="label">{{ trans('audit.by') }}:</span> <span id="modalUser" class="value"></span>
        </p>
        <p class="mb-2"><span class="label">{{ trans('audit.date') }}:</span> <span id="modalDate"
          class="value"></span></p>
      </div>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('audit.close') }}</button>
      </div>
    </div>
    </div>
  </div>
  <!-- Inline CSS for Modal Styling -->
  <style>
    /* Custom button size (smaller than btn-sm) */
    .btn-xs {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    line-height: 1.2;
    }

    .btn-xs i {
    font-size: 0.75rem;
    }

    #changesModal .modal-content {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    #changesModal .modal-header {
    border-bottom: none;
    padding: 1.5rem 2rem;
    }

    #changesModal .modal-body {
    padding: 2rem;
    }

    #changesModal .modal-footer {
    border-top: none;
    padding: 1rem 2rem;
    }

    #changesModal h6 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
    }

    #changesModal .table {
    margin-bottom: 0;
    font-size: 0.9rem;
    }

    #changesModal .table thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    }

    #changesModal .table td {
    padding: 0.75rem;
    vertical-align: middle;
    color: #333;
    }

    #changesModal .table tbody tr:nth-child(odd) {
    background-color: #f8f9fa;
    }

    #changesModal .table tbody tr:hover {
    background-color: #e9ecef;
    }

    #changesModal .table td:first-child {
    font-weight: 500;
    color: #1a73e8;
    }

    #changesModal .details-section {
    background-color: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    }

    #changesModal .details-section p {
    margin: 0;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    }

    #changesModal .details-section .label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
    display: inline-block;
    }

    #changesModal .details-section .value {
    color: #212529;
    font-weight: 400;
    }

    #changesModal .btn-close-white {
    filter: invert(1);
    }

    @media (max-width: 767px) {
    #changesModal .modal-body {
      padding: 1rem;
    }

    #changesModal .details-section .label {
      min-width: 100px;
    }

    #changesModal .table td {
      font-size: 0.85rem;
      padding: 0.5rem;
    }
    }
  </style>

@endsection

@push('css_lib')
  @include('layouts.datatables_css')
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@push('scripts_lib')
  @include('layouts.datatables_js')
  {!! $dataTable->scripts() !!}
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    // Initialize Bootstrap modal
    const changesModal = new bootstrap.Modal(document.getElementById('changesModal'), {
      backdrop: 'static',
      keyboard: false
    });

    // Handle View Changes button
    document.addEventListener('click', function (e) {
      if (e.target.closest('.view-changes')) {
      const button = e.target.closest('.view-changes');
      const auditLogId = button.dataset.id;

      console.log('Fetching details for ID:', auditLogId);

      fetch(`/audit-logs/details/${auditLogId}`, {
        headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
        }
      })
        .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          return response.json().then(err => {
          throw new Error(err.error || `HTTP error! Status: ${response.status}`);
          });
        }
        return response.json();
        })
        .then(data => {
        console.log('Received data:', data);

        // Populate modal
        document.getElementById('modalAction').textContent = data.action || '{{ trans("lang.na") }}';
        document.getElementById('modalEntityType').textContent = data.entity_type || '{{ trans("lang.na") }}';
        document.getElementById('modalDescription').textContent = data.description || '{{ trans("lang.na") }}';
        document.getElementById('modalUser').textContent = data.user_name || '{{ trans("lang.system") }}';
        document.getElementById('modalDate').textContent = data.created_at || '{{ trans("lang.na") }}';

        // Clear tables
        const oldValuesTable = document.querySelector('#oldValuesTable tbody');
        const newValuesTable = document.querySelector('#newValuesTable tbody');
        oldValuesTable.innerHTML = '';
        newValuesTable.innerHTML = '';

        // Populate old values
        if (data.old_values && Object.keys(data.old_values).length > 0) {
          for (const [key, value] of Object.entries(data.old_values)) {
          oldValuesTable.innerHTML += `
      <tr>
      <td>${key}</td>
      <td>${value || '{{ trans("lang.na") }}'}</td>
      </tr>
      `;
          console.log('Old value:', key, value);
          }
        } else {
          oldValuesTable.innerHTML = '<tr><td colspan="2">{{ trans("lang.no_old_values") }}</td></tr>';
          console.log('No old values found');
        }

        // Populate new values
        if (data.new_values && Object.keys(data.new_values).length > 0) {
          for (const [key, value] of Object.entries(data.new_values)) {
          newValuesTable.innerHTML += `
      <tr>
      <td>${key}</td>
      <td>${value || '{{ trans("lang.na") }}'}</td>
      </tr>
      `;
          console.log('New value:', key, value);
          }
        } else {
          newValuesTable.innerHTML = '<tr><td colspan="2">{{ trans("lang.no_new_values") }}</td></tr>';
          console.log('No new values found');
        }

        // Show the modal
        console.log('Showing modal');
        changesModal.show();
        })
        .catch(error => {
        console.error('Error fetching details:', error.message);
        alert('{{ trans("lang.failed_to_load_changes") }}: ' + error.message);
        });
      }
    });

    // Handle Mark as Read button
    document.addEventListener('click', function (e) {
      if (e.target.closest('.mark-read')) {
      const button = e.target.closest('.mark-read');
      const auditLogId = button.dataset.id;

      console.log('Marking as read for ID:', auditLogId);

      fetch(`/audit-logs/${auditLogId}/mark-read`, {
        method: 'POST',
        headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
        }
      })
        .then(response => {
        console.log('Mark as read response status:', response.status);
        if (!response.ok) {
          return response.json().then(err => {
          throw new Error(err.error || 'Failed to mark as read');
          });
        }
        return response.json();
        })
        .then(data => {
        console.log('Mark as read response:', data);
        if (data.success) {
          const row = button.closest('tr');
          row.classList.remove('fw-bold');
          button.outerHTML = '<span class="badge bg-success">{{ trans("lang.read") }}</span>';
          row.dataset.readAt = new Date().toISOString();
        } else {
          throw new Error('Mark as read failed');
        }
        })
        .catch(error => {
        console.error('Error marking as read:', error.message);
        alert('{{ trans("lang.failed_to_mark_read") }}: ' + error.message);
        });
      }
    });

    // Highlight specific log if ID is provided
    const urlParams = new URLSearchParams(window.location.search);
    const auditLogId = urlParams.get('id');
    if (auditLogId) {
      setTimeout(() => {
      const row = document.querySelector(`#audit-logs-table tr[id="${auditLogId}"]`);
      if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        row.classList.add('table-primary');
      }
      }, 1000);
    }
    });
  </script>
@endpush