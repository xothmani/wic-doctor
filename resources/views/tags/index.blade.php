@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-bold">{{ trans('lang.tag_plural') }} 
                    <small class="mx-3">|</small><small>{{ trans('lang.tag_desc') }}</small>
                </h1>
            </div><!-- /.col -->
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fas fa-tachometer-alt mx-1"></i> {{ trans('lang.dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{!! route('tags.index') !!}">{{ trans('lang.tag_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.tag_table') }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Alert messages -->
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
                            <i class="fa fa-list mr-2"></i>{{ trans('lang.tag_table') }}
                        </a>
                    </li>
                    @can('tags.create')
                    <li class="nav-item">
                        <button class="nav-link" data-toggle="modal" data-target="#createTagModal">
                            <i class="fa fa-plus mr-2"></i>{{ trans('lang.tag_create') }}
                        </button>
                    </li>
                    @endcan
                </div>
                @include('layouts.right_toolbar', compact('dataTable'))
            </ul>
        </div>
        <div class="card-body">
            @include('tags.table')
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<!-- Inclure le modal -->
@include('tags.createTagModal')

@endsection

<style>
        /* Si vous voulez personnaliser le badge */
        .badge {
            background-color:#5c6bc0; /* Couleur de fond */
            color: white; /* Couleur du texte */
        }
    </style>