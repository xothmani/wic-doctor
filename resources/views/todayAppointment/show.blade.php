@extends('layouts.app')

@php
  $doctorId = auth()->user()->getActiveDoctorId();
  // Define the permission key for this page
  $permissionKey = 'appointments.today.completed';
  // Retrieve the permission record along with its human-readable info
  $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
    ->with('readable')
    ->first();
  // Use the display_name accessor to get the localized readable permission name
  $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@section('content')
  @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
    <!-- En-tête du contenu -->
    <div class="content-header">
    <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
      <h1 class="m-0 text-dark">{{ trans('lang.appointments_completed_today') }}</h1>
      </div>
      <div class="col-md-6">
      <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
      <li class="breadcrumb-item">
      <a href="{{ url('/') }}">
        <i class="fa fa-dashboard"></i> {{ trans('lang.dashboard') }}
      </a>
      </li>
      <li class="breadcrumb-item">
      <a href="{!! route('appointments.index') !!}">
        {{ trans('lang.appointment_plural') }}
      </a>
      </li>
      <li class="breadcrumb-item active">{{ trans('lang.appointments_completed_today') }}</li>
      </ol>
      </div>
    </div>
    </div>
    </div>
    <!-- /.content-header -->

    <!-- Contenu principal -->
    <div class="content">
    <!-- Message d'alerte -->
    @if(session('alert'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
    {{ session('alert') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

    <div class="card shadow-sm">
    <div class="card-header">
      <h5 class="card-title">{{ trans('lang.appointments_completed_today') }}</h5>
    </div>
    <div class="card-body">
      @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
      <div class="table-responsive">
      <table class="table table-bordered table-striped">
      <thead>
      <tr>
      <th>{{ trans('lang.appointment_patient') }}</th>
      <th>{{ trans('lang.appointment_motif') }}</th>
      <th>{{ trans('lang.appointment_start_time') }}</th>
      <th>{{ trans('lang.appointment_end_time') }}</th>
      <th>{{ trans('lang.appointment_status') }}</th>
      <th>{{ trans('lang.actions') }}</th>
      </tr>
      </thead>
      <tbody>
      @forelse($appointments as $appointment)
      <tr>
      <td>{{ json_decode($appointment->first_name)->fr ?? $appointment->first_name }}
      {{ json_decode($appointment->last_name)->fr ?? $appointment->last_name }}</td>
      <td>{{ json_decode($appointment->motif_name)->fr ?? trans('lang.no_motif') }}</td>
      <td>{{ \Carbon\Carbon::parse($appointment->start_at)->format('H:i') }}</td>
      <td>{{ \Carbon\Carbon::parse($appointment->ends_at)->format('H:i') }}</td>
      <td> Prêt</td>
      <td>
      @if(auth()->user()->hasPermissionInContext('consultations.create', $doctorId))
      <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_consultation') }}"
      href="{{ route('consultations.create', ['patient_id' => $appointment->patient_id]) }}"
      class="btn btn-link">
      <i class="fas fa-plus"></i>
      </a>
    @endif
      @if(auth()->user()->hasPermissionInContext('fiche.show', $doctorId))
      <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_fiche') }}"
      href="{{ route('fiche.show', $appointment->patient_id) }}" class="btn btn-link">
      <i class="fas fa-file-alt"></i>
      </a>
    @endif
      </td>
      </tr>
    @empty
      <tr>
      <td colspan="7" class="text-center">{{ trans('lang.no_completed_appointments_today') }}</td>
      </tr>
    @endforelse
      </tbody>
      </table>
      </div>
      <!-- Pagination links -->
      <div class="d-flex justify-content-end">
      {{ $appointments->links() }}
      </div>
    @else
      <div class="alert alert-danger">{{ trans('lang.no_permission') }}</div>
    @endif
    </div>
    </div>
    </div>
  @else
    <div class="content-header">
    <div class="container-fluid">
    <div class="alert alert-danger">
      {{ __('Vous n’avez pas la permission (:permission) d’accéder à cette page.', ['permission' => $readablePermission]) }}
    </div>
    </div>
    </div>
  @endif
@endsection

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>