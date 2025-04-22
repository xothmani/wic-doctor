@extends('layouts.app')

@php
  $doctorId = auth()->user()->getDoctorId();
@endphp

@section('content')
  @if(auth()->user()->hasPermissionInContext('patients.edit', $doctorId))
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-md-6">
            <h1 class="m-0 text-bold">{{trans('lang.patient_plural')}} <small class="mx-3">|</small><small>{{trans('lang.patient_desc')}}</small></h1>
          </div>
          <div class="col-md-6">
            <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
              <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt mx-1"></i> {{trans('lang.dashboard')}}</a></li>
              <li class="breadcrumb-item"><a href="{!! route('patients.index') !!}">{{trans('lang.patient_plural')}}</a></li>
              <li class="breadcrumb-item active">{{trans('lang.patient_edit')}}</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->
    
    <div class="content">
      <div class="clearfix"></div>
      @include('flash::message')
      @include('adminlte-templates::common.errors')
      <div class="clearfix"></div>
      
      <div class="card shadow-sm">
        <div class="card-header">
          <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
            @can('patients.index')
              <li class="nav-item">
                <a class="nav-link" href="{!! route('patients.index') !!}"><i class="fas fa-list mr-2"></i>{{trans('lang.patient_table')}}</a>
              </li>
            @endcan
            @can('patients.create')
              <li class="nav-item">
                <a class="nav-link" href="{!! route('patients.create') !!}"><i class="fas fa-plus mr-2"></i>{{trans('lang.patient_create')}}</a>
              </li>
            @endcan
            <li class="nav-item">
              <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-edit mr-2"></i>{{trans('lang.patient_edit')}}</a>
            </li>
          </ul>
        </div>
        
        <div class="card-body">
          {!! Form::model($patient, ['route' => ['patients.update', $patient->id], 'method' => 'patch']) !!}
            <div class="row">
  @if($patient->assignedUser)
    <div class="col-12">
        <div class="card card-light">
            <div class="card-header" style="background-color: #ffffff; border-bottom: 1px solid rgba(0,0,0,.125);">
                <div class="d-flex justify-content-center mb-3">
                    <h5 class="d-flex align-items-center">
                        <i class="fas fa-user-plus mr-2" style="color: #0d6efd;"></i>
                        Utilisateur assigné à ce patient :
                    </h5>
                </div>
            </div>
            
            <div class="card-body" style="background-color: #ffffff;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prénom</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="color: #0d6efd;">
                                    👨‍⚕️                                    </span>
                                </div>
                                <input type="text" 
                                       name="assigned_user[first_name]" 
                                       class="form-control" 
                                       value="{{ json_decode($patient->assignedUser->name)->fr ?? $patient->assignedUser->name }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nom</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span backgroundcolor="white", class="input-group-text" >
                                    👨‍⚕️                                    </span>
                                </div>
                                <input type="text" 
                                       name="assigned_user[last_name]" 
                                       class="form-control" 
                                       value="{{ json_decode($patient->assignedUser->lastname)->fr ?? $patient->assignedUser->lastname }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Type de relation</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="color: #0d6efd;">
                                    🌐                                     </span>
                                </div>
                                <select name="assigned_user[relationship]" class="form-control">
                                    <option value="{{ $patient->relationship_type }}" selected>
                                        {{ __('Type de relation_'.$patient->relationship_type) }}
                                    </option>
                                    <option value="pere">{{ __('Père') }}</option>
                                    <option value="mere">{{ __('Mère') }}</option>
                                    <option value="conjoint">{{ __('Conjoint(e)') }}</option>
                                    <option value="grand_pere">{{ __('Grand-père') }}</option>
                                    <option value="grand_mere">{{ __('Grand-mère') }}</option>
                                    <option value="enfant">{{ __('Enfant') }}</option>
                                    <option value="aide_soignante">{{ __('Aide-soignante') }}</option>
                                    <option value="autre">{{ __('Autre') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="color: #0d6efd;">
                                    📱                                    </span>
                                </div>
                                <input type="text" 
                                       name="assigned_user[phone]" 
                                       class="form-control" 
                                       value="{{ $patient->assignedUser->phone_number }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="color: #0d6efd;">
                                    
🌐                                    </span>
                                </div>
                                <input type="email" 
                                       name="assigned_user[email]" 
                                       class="form-control" 
                                       value="{{ $patient->assignedUser->email ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="assigned_user[id]" value="{{ $patient->assignedUser->id }}">
            </div>
        </div>
    </div>
@endif

            
              @include('patients.fields')
              
          </div>
          {!! Form::close() !!}
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
    
    @include('layouts.media_modal')
  @else
    <div class="content-header">
      <div class="container-fluid">
        <div class="alert alert-danger">
          {{ __('Vous navez pas la permission d accéder à cette page.') }}
        </div>
      </div>
    </div>
  @endif
@endsection

@push('css_lib')
  <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
  <style>
    /* Style personnalisé pour harmoniser avec AdminLTE */
    .card-secondary:not(.card-outline) > .card-header {
      background-color: #6c757d;
      color: #fff;
    }
    
    .card-header .card-title {
      font-weight: 600;
    }
    .input-group-text {
    background-color: white !important;
    color: #0d6efd;
}

    .input-group-text {
      min-width: 40px;
      justify-content: center;
    }
    
    .form-group label {
      font-weight: 600;
      color: #495057;
    }
    .custom-icon-margin {
  margin-right: 0.5rem; /* ou la valeur que tu souhaites, ex. 8px */
}

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .col-md-6 {
        margin-bottom: 15px;
      }
    }
  </style>
@endpush

@push('scripts_lib')
  <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
  <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
  <script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
  </script>
@endpush