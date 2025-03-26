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
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-bold">{{trans('lang.my_blog_plural')}}
                        <small class="mx-3">|</small><small>{{trans('lang.blog_desc')}}</small>
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
                            <a href="{!! route('doctor_blog.index') !!}">{{trans('lang.my_blog_plural')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{trans('lang.blog_table')}}</li>
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
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
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
                <div class="alert" style="background-color: #FFF3CD; color: #856404; border-color: #FFEEBA;">
                    <strong>
                        <i class="fas fa-info-circle mr-2"></i>
                        Ces blogs sont en cours et seront validés par la communauté.
                    </strong>
                </div>

                <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                    <div class="d-flex flex-row">
                        <li class="nav-item">
                            <a class="nav-link active" href="{!! url()->current() !!}">
                                <i class="fa fa-list mr-2"></i>{{trans('lang.blog_table')}}
                            </a>
                        </li>
                        @can('doctor_blog.accepted')
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('doctor_blog.accepted') !!}">
                                    <i class="fa fa-list mr-2"></i>{{trans('lang.blog_accepted')}}
                                </a>
                            </li>
                        @endcan
                        @can('doctor_blog.rejected')
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('doctor_blog.rejected') !!}">
                                    <i class="fa fa-list mr-2"></i>{{trans('lang.blog_rejected')}}
                                </a>
                            </li>
                        @endcan
                        @can('doctor_blog.create')
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('doctor_blog.create') !!}">
                                    <i class="fa fa-plus mr-2"></i>{{trans('lang.blog_create')}}
                                </a>
                            </li>
                        @endcan
                    </div>
                    @include('layouts.right_toolbar', compact('dataTable'))
                </ul>
            </div>
            <div class="card-body">
                @include('doctor_blog.table')
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

@endsection
<!-- Modale Bootstrap -->
<!-- Modale Bootstrap -->
<div class="modal fade" id="contentModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Lire la suite</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Le contenu sera ajouté ici dynamiquement -->
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal carré */
    .modal-dialog {
        width: 80vh;
        /* Largeur égale à la hauteur de l'écran */
        height: 80vh;
        /* Hauteur égale à la largeur */
        margin: 30px auto;
        /* Espacement autour du modal */
    }

    .modal-content {
        height: 100%;
        /* Prendre toute la hauteur du modal */
        display: flex;
        flex-direction: column;
    }

    .modal-body {
        overflow-y: auto;
        /* Activer le défilement vertical */
        flex-grow: 1;
        /* Prendre l'espace restant */
        max-height: calc(80vh - 150px);
        /* Ajuster la hauteur en fonction de la taille de l'écran et de l'en-tête/pied */
    }
</style>
<script>
    function showModal(content) {
        $('#modalContent').html(content); // Insère le contenu HTML dans la modale
        $('#contentModal').modal('show'); // Affiche la modale
    }
</script>