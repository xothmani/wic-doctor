@extends('layouts.app')
@push('css_lib')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@section('content')
@php
    $doctorId = auth()->user()->getDoctorId();
@endphp

@if(auth()->user()->hasPermissionInContext('patterns.create', $doctorId))
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">{{ trans('lang.pattern_plural') }}
                        <small class="mx-3">|</small><small>{{ trans('lang.pattern_desc') }}</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                {{ trans('lang.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{!! route('patterns.index') !!}">{{ trans('lang.pattern_plural') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('lang.pattern_create') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        @include('adminlte-templates::common.errors')
        <div class="clearfix"></div>
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                    @if(auth()->user()->hasPermissionInContext('patterns.index', $doctorId))
                        <li class="nav-item">
                            <a class="nav-link" href="{!! route('patterns.index') !!}"><i
                                    class="fa fa-list mr-2"></i>{{ trans('lang.pattern_table') }}
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}"><i
                                class="fa fa-plus mr-2"></i>{{ trans('lang.pattern_create') }}</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'patterns.store']) !!}
                <div class="row">
                    @include('patterns.fields')
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
                {{ __('Vous n’avez pas la permission d’accéder à cette page.') }}
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts_lib')
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
@endpush