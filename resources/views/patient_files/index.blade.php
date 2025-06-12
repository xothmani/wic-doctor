@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.index';
    // Retrieve the permission with its related readable record
    $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
        ->with('readable')
        ->first();

    // Use the dynamic attribute for the display name; fall back to the key if not found
    $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@section('content')
    @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-bold">{{ trans('lang.patient_files_plural') }}
                            <small class="mx-3">|</small><small>{{ $patient->first_name }} {{ $patient->last_name }}</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                    {{ trans('lang.dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{ trans('lang.patients_plural') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ trans('lang.patient_files_table') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row">
                    <!-- Files Table (Left/Main Content) -->
                    <div class="col-lg-8 col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <ul
                                    class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                                    <div class="d-flex flex-row">
                                        <li class="nav-item">
                                            <a class="nav-link active" href="{{ url()->current() }}"><i
                                                    class="fa fa-list mr-2"></i>{{ trans('lang.patient_files_table') }}
                                            </a>
                                        </li>
                                        @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                            <li class="nav-item">
                                                <a class="nav-link" href="{{ route('patient_files.create', $patient) }}"><i
                                                        class="fa fa-plus mr-2"></i>{{ trans('lang.patient_files_create') }}
                                                </a>
                                            </li>
                                        @endif
                                    </div>
                                    @if(isset($dataTable))
                                        @include('layouts.right_toolbar', compact('dataTable'))
                                    @endif
                                </ul>
                            </div>
                            <div class="card-body">
                                @include('patient_files.table')
                            </div>
                        </div>
                    </div>

                    <!-- Doctors List (Right Sidebar) -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">{{ trans('lang.doctors_associated') }}</h3>
                                @if(auth()->user()->hasPermissionInContext('patient_files.assign_doctor', $doctorId))
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#assignDoctorModal">
                                        <i class="fas fa-user-plus"></i> {{ trans('lang.assign_doctor') }}
                                    </button>
                                @endif
                            </div>
                            <div class="card-body">
                                @if($patient->doctors->isEmpty())
                                    <p class="text-muted">{{ trans('lang.no_doctors_associated') }}</p>
                                @else
                                    <ul class="list-group list-group-flush">
                                        @foreach($patient->doctors as $doctor)
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $doctor->name }}</strong>
                                                        @if($doctor->specialities->isNotEmpty())
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $doctor->specialities->pluck('name')->join(', ') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <a href="mailto:{{ $doctor->user->email }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-envelope"></i> {{ trans('lang.contact') }}
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Doctor Modal -->
        @if(auth()->user()->hasPermissionInContext('patient_files.assign_doctor', $doctorId))
            <div class="modal fade" id="assignDoctorModal" tabindex="-1" role="dialog" aria-labelledby="assignDoctorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignDoctorModalLabel">{{ trans('lang.assign_doctor') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('patient_files.assign_doctor', $patient) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="doctor_id">{{ trans('lang.select_doctor') }}</label>
                                    <select name="doctor_id" id="doctor_id" class="form-control select2" required>
                                        <option value="">{{ trans('lang.choose') }}</option>
                                        @foreach($availableDoctors as $doctor)
                                            <option value="{{ $doctor->id }}">{{ $doctor->name }}
                                                ({{ $doctor->specialities->pluck('name')->join(', ') }})</option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{ trans('lang.cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ trans('lang.assign') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    {{ __('Vous n\'avez pas la permission (:permission) d\'accéder à cette page.', ['permission' => $readablePermission]) }}
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            console.log('{{ $patient }}');
            console.log('-------------- SELECT DOCTORS --------------');
            console.log('{{ $patient->doctors }}');


            $(document).ready(function () {
                $('.select2').select2({
                    placeholder: "{{ trans('lang.choose') }}",
                    allowClear: true
                });
            });
        </script>
    @endpush
@endsection