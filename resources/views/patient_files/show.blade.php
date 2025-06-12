@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.show';
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
                        <h1 class="m-0 text-bold">{{trans('lang.patient_file_details') }}
                            <small class="mx-3">|</small><small>{{ $patient->first_name }} {{ $patient->last_name }}</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                            <li class="breadcrumb-item">
                                <a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i>
                                    {{trans('lang.dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{trans('lang.patients_plural')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patient_files.index', $patient) }}">{{trans('lang.patient_files_plural')}}</a>
                            </li>
                            <li class="breadcrumb-item active">{{trans('lang.patient_file_details')}}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="clearfix"></div>
            @include('flash::message')
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                        <div class="d-flex flex-row">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('patient_files.index', $patient) }}"><i
                                        class="fa fa-list mr-2"></i>{{trans('lang.patient_files_table')}}
                                </a>
                            </li>
                            @if(auth()->user()->hasPermissionInContext('patient_files.create', $doctorId))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('patient_files.create', $patient) }}"><i
                                            class="fa fa-plus mr-2"></i>{{trans('lang.patient_files_create')}}
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                        class="fa fa-eye mr-2"></i>{{trans('lang.patient_file_details')}}
                                </a>
                            </li>
                        </div>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{trans('lang.file_name')}}</th>
                                            <th>{{trans('lang.description')}}</th>
                                            <th>{{trans('lang.uploaded_by')}}</th>
                                            <th>{{trans('lang.uploaded_at')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($files as $file)
                                            <tr>
                                                <td>
                                                    <i class="fas fa-file mr-2"></i>
                                                    {{ $file->file_name }}
                                                </td>
                                                <td>{{ $file->description ?? trans('lang.no_description') }}</td>
                                                <td>{{ $file->uploader->name }}</td>
                                                <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        @if(auth()->user()->hasPermissionInContext('patient_files.download', $doctorId))
                                                            <a href="{{ route('patient_files.download', [$patient, $file]) }}" 
                                                               class="btn btn-sm btn-success" 
                                                               data-toggle="tooltip" 
                                                               title="{{trans('lang.download')}}">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @endif
                                                        @if(auth()->user()->hasPermissionInContext('patient_files.destroy', $doctorId))
                                                            <form action="{{ route('patient_files.destroy', [$patient, $file]) }}" 
                                                                  method="POST" 
                                                                  style="display:inline;" 
                                                                  onsubmit="return confirm('{{trans('lang.confirm_delete_file')}}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="btn btn-sm btn-danger" 
                                                                        data-toggle="tooltip" 
                                                                        title="{{trans('lang.delete')}}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                                    <p>{{trans('lang.no_files_found')}}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group col-12 text-right mt-3">
                        <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-default">
                            <i class="fas fa-undo"></i> {{trans('lang.back')}}
                        </a>
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                            <i class="fas fa-users"></i> {{trans('lang.back_to_patients')}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
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