@extends('layouts.app')

@section('content')
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-md-6">
          <h1 class="m-0 text-bold">{{trans('lang.audit_logs_plural')}} <small class="mx-3">|</small><small>{{trans('lang.audit_logs_desc')}}</small></h1>
        </div>
        <div class="col-md-6">
          <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt mx-1"></i> {{trans('lang.dashboard')}}</a></li>
            <li class="breadcrumb-item active">{{trans('lang.audit_logs_plural')}}</li>
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
        <h3 class="card-title">{{trans('lang.filter_logs')}}</h3>
      </div>
      <div class="card-body">
        <form id="filterForm" action="{{ route('audit-logs.index') }}" method="GET" class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="action">{{trans('lang.action_type')}}</label>
              <select name="action" id="action" class="form-control">
                <option value="">{{trans('lang.all_actions')}}</option>
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
              <label for="start_date">{{trans('lang.start_date')}}</label>
              <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="end_date">{{trans('lang.end_date')}}</label>
              <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-group">
              <button type="submit" class="btn btn-primary">{{trans('lang.apply_filters')}}</button>
              <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary ml-2">{{trans('lang.reset')}}</a>
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
              <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-list mr-2"></i>{{trans('lang.audit_logs_table')}}</a>
            </li>
          </div>
          @include('layouts.right_toolbar', compact('dataTable'))
        </ul>
      </div>
      <div class="card-body">
        {!! $dataTable->table(['width' => '100%']) !!}
      </div>
    </div>
  </div>

  <!-- Modal -->
  <!-- Modal -->
<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="valuesModal" tabindex="-1" role="dialog" aria-labelledby="valuesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="valuesModalLabel">{{trans('lang.details')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Display User and Doctor Names instead of IDs -->
                <ul id="valuesContent"></ul> <!-- Values will be populated here -->
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>            </div>
        </div>
    </div>
</div>




@endsection

@push('css_lib')
  @include('layouts.datatables_css')
@endpush

@push('scripts_lib')
  @include('layouts.datatables_js')
  {!! $dataTable->scripts() !!}
  <script>
    // Pass the translations for fields based on the current language
    window.translations = @json(__('audit.fields', [], app()->getLocale()));
</script>
<script>
    // Pass all necessary translations
    window.translations = {
        fields: @json(__('audit.fields')),
        status: @json(__('audit.status')),
        modal: @json(__('audit.modal'))
    };

    $(document).on('click', '.view-values', function() {
        var values = $(this).data('values');
        var title = $(this).data('title');

        // Translate modal title
        var translatedTitle = title === 'Old Values' ? 
            "{{trans('lang.old_values')}}" : 
            "{{trans('lang.new_values')}}";

        var formattedContent = '<ul>';
        
        for (var key in values) {
            if (values.hasOwnProperty(key)) {
                var label = window.translations[key] || key.charAt(0).toUpperCase() + key.slice(1).replace('_', ' '); 
                
                if (key === 'user_id') {
                    formattedContent += '<li><strong>' + label + ':</strong> ' + values[key] + '</li>';
                } else if (key === 'doctor_id') {
                    formattedContent += '<li><strong>' + label + ':</strong> ' + values[key] + '</li>';
                } else {
                    formattedContent += '<li><strong>' + label + ':</strong> ' + (values[key] ? values[key] : 'N/A') + '</li>';
                }
            }
        }
        formattedContent += '</ul>';

        $('#valuesContent').html(formattedContent);
        $('#valuesModalLabel').text(translatedTitle);
    });
</script>



@endpush
