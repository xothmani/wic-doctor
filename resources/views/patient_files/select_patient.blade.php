<!-- resources/views/patient_files/select_patient.blade.php -->
@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
    $permissionKey = 'patient_files.view';
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
                            {{ trans('lang.select_patient') }}
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex shadow-sm">
                            <li class="breadcrumb-item">
                                <a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                                    {{ trans('lang.dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('patients.index') }}">{{ trans('lang.patients_plural') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ trans('lang.select_patient') }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('flash::message')
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <div class="card shadow-sm patients-card">
                            <div class="card-header bg-gradient-info text-white">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-user-injured mr-2"></i>
                                    {{ trans('lang.patients_plural') }}
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                @if($myPatients->isEmpty())
                                    <div class="empty-state text-center py-5">
                                        <div class="empty-icon mb-3">
                                            <i class="fas fa-user-injured text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                        </div>
                                        <p class="text-muted mb-3">{{ trans('lang.no_patients_assigned') }}</p>
                                        <a href="{{ route('patients.index') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1"></i>{{ trans('lang.view_all_patients') }}
                                        </a>
                                    </div>
                                @else
                                    <div class="patients-list">
                                        @foreach($myPatients as $index => $patient)
                                            <a href="{{ route('patient_files.index', $patient) }}" style="text-decoration: none; display: flex; flex: 1;">
                                                <div class="patient-item" style="animation-delay: {{ $index * 0.1 }}s">
                                                    <div class="patient-avatar">
                                                        @if($patient->user && $patient->user->media->isNotEmpty())
                                                            <img src="{{ $patient->user->media->first()->getUrl() }}" alt="{{ $patient->first_name }} {{ $patient->last_name }}" class="avatar-img">
                                                        @else
                                                            <div class="avatar-placeholder">
                                                                <i class="fas fa-user-injured"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="patient-info">
                                                        <h6 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
                                                        @if($patient->user && $patient->user->email)
                                                            <small class="text-muted">{{ $patient->user->email }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="patient-actions">
                                                        <a href="{{ route('patient_files.index', $patient) }}" class="action-btn view-btn" title="{{ trans('lang.view_files') }}">
                                                            <i class="fas fa-folder-open"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Custom Styles -->
        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
                --success-gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
                --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
                --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
            }

            .bg-gradient-info {
                background: var(--info-gradient);
            }

            .patients-card {
                border-radius: 20px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .patients-card:hover {
                transform: translateY(-5px);
                box-shadow: var(--shadow-medium);
            }

            .card-header {
                border: none;
            }

            .patients-list {
                padding: 0;
            }

            .patient-item {
                display: flex;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #f1f3f4;
                transition: all 0.3s ease;
                opacity: 0;
                animation: slideInUp 0.6s ease forwards;
            }

            .patient-item:hover {
                background: linear-gradient(90deg, rgba(116, 185, 255, 0.05) 0%, rgba(162, 155, 254, 0.05) 100%);
                transform: translateX(5px);
            }

            .patient-item:last-child {
                border-bottom: none;
            }

            .patient-avatar {
                position: relative;
                margin-right: 15px;
                flex-shrink: 0;
                height: 50px;
                width: 50px;
                overflow: hidden;
            }

            .avatar-img {
                width: 50px;
                height: 50px;
                border-radius: 15px;
                object-fit: cover;
                border: 3px solid #fff;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .avatar-placeholder {
                width: 50px;
                height: 50px;
                border-radius: 15px;
                background: var(--info-gradient);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.2rem;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .patient-info {
                flex-grow: 1;
                min-width: 0;
            }

            .patient-name {
                margin: 0 0 5px 0;
                font-weight: 600;
                color: #2d3436;
                font-size: 1rem;
            }

            .patient-actions {
                display: flex;
                gap: 8px;
                flex-shrink: 0;
            }

            .action-btn {
                width: 35px;
                height: 35px;
                border: none;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                text-decoration: none;
                font-size: 0.9rem;
            }

            .view-btn {
                background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
                color: white;
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
                color: white;
                text-decoration: none;
            }

            .empty-state {
                margin: 40px 0;
            }

            .empty-icon i {
                font-size: 4rem;
                opacity: 0.3;
            }

            /* Animations */
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .badge-soft-info {
                color: var(--info, #17a2b8);
                background-color: rgba(23, 162, 184, 0.1);
                border: 1px solid rgba(23, 162, 184, 0.2);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .patient-item {
                    padding: 15px;
                }

                .action-btn {
                    width: 30px;
                    height: 30px;
                    font-size: 0.8rem;
                }
            }
        </style>
    @else
        <div class="content-header">
            <div class="container-fluid">
                <div class="alert alert-danger">
                    {{ __('Vous n\'avez pas la permission d\'accéder à cette page.', ['permission' => $readablePermission]) }}
                </div>
            </div>
        </div>
    @endif
@endsection