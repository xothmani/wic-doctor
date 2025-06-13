<!-- resources/views/patient_files/show.blade.php -->
@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.create';
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
                        <h1 class="m-0 text-bold">
                            {{trans('lang.patient_files_create') }}
                            <small class="mx-3 text-muted">|</small>
                            <small class="badge badge-soft-info px-3 py-1">
                                <i class="fas fa-user-injured mr-1"></i>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex shadow-sm">
                            <li class="breadcrumb-item">
                                <a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i>
                                    {{trans('lang.dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{trans('lang.patients_plural')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a
                                    href="{{ route('patient_files.index', $patient) }}">{{trans('lang.patient_files_plural')}}</a>
                            </li>
                            <li class="breadcrumb-item active">{{trans('lang.patient_files_create')}}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">{{ trans('lang.file_details') }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ trans('lang.back_to_list') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">{{ trans('lang.file_name') }}</dt>
                            <dd class="col-sm-8">{{ $file->file_name }}</dd>

                            <dt class="col-sm-4">{{ trans('lang.file_type') }}</dt>
                            <dd class="col-sm-8">{{ $file->file_type }}</dd>

                            <dt class="col-sm-4">{{ trans('lang.file_size') }}</dt>
                            <dd class="col-sm-8">{{ number_format($file->file_size / 1024, 2) }} KB</dd>

                            <dt class="col-sm-4">{{ trans('lang.description') }}</dt>
                            <dd class="col-sm-8">{{ $file->description ?: trans('lang.no_description') }}</dd>

                            <dt class="col-sm-4">{{ trans('lang.uploaded_by') }}</dt>
                            <dd class="col-sm-8">{{ $file->uploader->name ?? trans('lang.unknown') }}
                                {{ $file->uploader->lastname ?? '' }}</dd>

                            <dt class="col-sm-4">{{ trans('lang.uploaded_at') }}</dt>
                            <dd class="col-sm-8">{{ $file->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                        @if(auth()->user()->hasPermissionInContext('patient_files.download', auth()->user()->getDoctorId()))
                            <a href="{{ route('patient_files.download', [$patient, $file]) }}" class="btn btn-primary">
                                <i class="fas fa-download"></i> {{ trans('lang.download') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <style>
            .bg-gradient-primary {
                background: linear-gradient(135deg, var(--primary, #007bff) 0%, var(--info, #17a2b8) 100%);
            }

            .badge-soft-primary {
                color: var(--primary, #007bff);
                background-color: rgba(0, 123, 255, 0.1);
                border: 1px solid rgba(0, 123, 255, 0.2);
            }

            .badge-soft-info {
                color: var(--info, #17a2b8);
                background-color: rgba(23, 162, 184, 0.1);
                border: 1px solid rgba(23, 162, 184, 0.2);
            }
        </style>
    @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    {{ __('Vous n\'avez pas la permission (:permission) d\'accéder à cette page.', ['permission' => $readablePermission]) }}
                </div>
            </div>
        </div>
    @endif
@endsection