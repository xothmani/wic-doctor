@extends('layouts.settings.default')

@push('css_lib')
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Summernote -->
    <link rel="stylesheet" href="{{ asset('vendor/summernote/summernote-bs4.min.css') }}">
    <!-- Dropzone -->
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
@endpush

@section('settings_title', trans('lang.doctor_user_create'))
@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-bold" style="font-size: 1.5rem;">{{ trans('lang.doctor_user_table') }}
                    <small class="mx-3">|</small>
                    <small style="font-size: 1rem; color: #6c757d;">{{ trans('lang.manage_users_desc') }}</small>
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-3 py-1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> {{ trans('lang.dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{!! route('Doctors_users.index') !!}">{{ trans('lang.doctor_user_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.doctor_user_create') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@include('flash::message')
@include('adminlte-templates::common.errors')
<div class="clearfix"></div>
<div class="card shadow-sm">
    <div class="card-header">
        <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
            <li class="nav-item">
                <a class="nav-link" href="{!! route('Doctors_users.index') !!}">
                    <i class="fas fa-list mr-2"></i>{{ trans('lang.doctor_user_table') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{!! url()->current() !!}">
                    <i class="fas fa-plus mr-2"></i>{{ trans('lang.doctor_user_create') }}
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        {!! Form::open(['route' => 'Doctors_users.store']) !!}
        <div class="row">
            @include('profile_management.users.fields')




        </div>
        {!! Form::close() !!}
        <div class="clearfix"></div>
    </div>
</div>
@endsection

@push('scripts_lib')
    <!-- Select2 -->
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('vendor/summernote/summernote.min.js') }}"></script>
    <!-- Dropzone -->
    <script src="{{ asset('vendor/dropzone/min/dropzone.min.js') }}"></script>

@endpush