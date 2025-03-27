@extends('layouts.app')

@php
  $doctorId = auth()->user()->getDoctorId();
  $permissionKey = 'patients.index';
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
      <div class="col-md-6">
      <h1 class="m-0 text-bold">{{trans('lang.patient_plural')}} <small
      class="mx-3">|</small><small>{{trans('lang.patient_desc')}}</small></h1>
      </div><!-- /.col -->
      <div class="col-md-6">
      <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
      <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt mx-1"></i>
        {{trans('lang.dashboard')}}</a></li>
      <li class="breadcrumb-item">
      <a href="{!! route('patients.index') !!}">{{trans('lang.patient_plural')}}</a>
      </li>
      <li class="breadcrumb-item active">{{trans('lang.patient_table')}}</li>
      </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
    </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="content">
    <div class="clearfix"></div>
    @include('flash::message')
    <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
      <div class="d-flex flex-row">
      <li class="nav-item">
      <a class="nav-link active" href="{!! url()->current() !!}"><i
        class="fa fa-list mr-2"></i>{{trans('lang.patient_table')}}</a>
      </li>
      @if(auth()->user()->hasPermissionInContext('patients.create', $doctorId))
      <li class="nav-item">
      <a class="nav-link" href="{!! route('patients.create') !!}"><i
      class="fa fa-plus mr-2"></i>{{trans('lang.patient_create')}}</a>
      </li>
    @endif
      </div>
      @include('layouts.right_toolbar', compact('dataTable'))
      </ul>
    </div>
    <div class="card-body">
      @include('patients.table')
      <div class="clearfix"></div>
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