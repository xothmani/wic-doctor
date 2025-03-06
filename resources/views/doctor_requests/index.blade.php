@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-bold">
                    {{trans('lang.doctor_request_plural')}}
                    <small class="mx-3">|</small>
                    <small>{{trans('lang.doctor_request_desc')}}</small>
                </h1>
            </div><!-- /.col -->
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item">
                        <a href="{{url('/dashboard')}}">
                            <i class="fas fa-tachometer-alt mx-1"></i> {{trans('lang.dashboard')}}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{!! route('doctor_requests.index') !!}">{{trans('lang.doctor_request_plural')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.doctor_request_table')}}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

@if(session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div id="error-alert" class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');

            if (errorAlert) {
                errorAlert.style.transition = "opacity 1s";
                errorAlert.style.opacity = "0";
                setTimeout(() => errorAlert.remove(), 1000); // Supprime l'élément après l'animation
            }

            if (successAlert) {
                successAlert.style.transition = "opacity 1s";
                successAlert.style.opacity = "0";
                setTimeout(() => successAlert.remove(), 1000); // Supprime l'élément après l'animation
            }
        }, 5000); // 5 secondes
    });
</script>

<div class="content">
    <div class="clearfix"></div>
    @include('flash::message')
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                <div class="d-flex flex-row">
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}">
                            <i class="fa fa-list mr-2"></i>{{trans('lang.doctor_request_table')}}
                        </a>
                    </li>
                </div>
                @include('layouts.right_toolbar', compact('dataTable'))
            </ul>
        </div>
        <div class="card-body">
            @include('doctor_requests.table')
            <div class="clearfix"></div>
        </div>
    </div>
</div>
@endsection

<!-- Firebase Configuration -->
<script>
    const firebaseConfig = {
        apiKey: "{{ env('FIREBASE_API_KEY') }}",
        authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
        databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}",
        projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
        storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
        messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
        appId: "{{ env('FIREBASE_APP_ID') }}",
        measurementId: "{{ env('FIREBASE_MEASUREMENT_ID', '') }}"
    };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
</script>
