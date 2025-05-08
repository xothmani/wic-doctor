@extends('layouts.app')

@push('css_lib')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endpush

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">
                        {{ trans('lang.prescription_plural') }}
                        <small class="mx-3">|</small>
                        <small>{{ trans('lang.prescription_desc') }}</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> {{ trans('lang.dashboard') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('lang.prescription_create') }}</li>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}">
                            <i class="fa fa-plus mr-2"></i>{{ trans('lang.prescription_create') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                @if(!empty($showAlert) && $showAlert)
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                            <h4>Accès non autorisé</h4>
                            <p><strong>Patient non trouvé ou non associé à votre liste.</strong></p>
                            <p>Vous ne pouvez pas créer une prescription sans un patient valide.</p>
                            <p>Veuillez sélectionner un patient valide depuis votre liste des patients.</p>
                            <a href="{{ route('patients.index') }}" class="btn bg-{{ setting('theme_color') }} mt-3">
                                <i class="fas fa-users mr-2"></i> Voir la liste des patients
                            </a>
                        </div>
                    </div>
                @else
                    {!! Form::open(['route' => 'prescriptions.store']) !!}
                    <div class="row">
                        @include('prescriptions.fields')
                    </div>
                    {!! Form::close() !!}
                @endif
                <div class="clearfix"></div>
            </div>
        </div>
    </div>

    @include('layouts.media_modal')
@endsection

@push('scripts_lib')
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/dropzone/min/dropzone.min.js') }}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush
