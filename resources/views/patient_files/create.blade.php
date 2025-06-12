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
                        <h1 class="m-0 text-bold">{{trans('lang.patient_files_create') }}
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
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url()->current() !!}"><i
                                        class="fa fa-plus mr-2"></i>{{trans('lang.patient_files_create')}}
                                </a>
                            </li>
                        </div>
                    </ul>
                </div>
                <div class="card-body">
                    <form action="{{ route('patient_files.store', $patient) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="file" class="text-color">{{trans('lang.file')}} <span
                                            class="required-field"></span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="fas fa-file"></i></div>
                                        </div>
                                        <input type="file" name="file" class="form-control" required>
                                    </div>
                                    <div class="text-danger">The file must be a file of type: pdf, doc, docx, jpg, jpeg, png,
                                        gif, webp, svg, txt, csv, xls, xlsx, ppt, pptx, zip, rar, 7z, tar, gz, bz2, xml, hl7,
                                        dcm, nii, ecg.</div>

                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description" class="text-color">{{trans('lang.description')}}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><i class="fas fa-comment"></i></div>
                                        </div>
                                        <textarea name="description" class="form-control" rows="4"
                                            placeholder="{{trans('lang.patient_file_description_placeholder')}}">{{ old('description') }}</textarea>
                                    </div>
                                    @error('description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-12 text-right">
                            <button type="submit"
                                class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
                                <i class="fas fa-save"></i> {{trans('lang.save')}}
                            </button>
                            <a href="{{ route('patient_files.index', $patient) }}" class="btn btn-default">
                                <i class="fas fa-undo"></i> {{trans('lang.cancel')}}
                            </a>
                        </div>
                    </form>
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