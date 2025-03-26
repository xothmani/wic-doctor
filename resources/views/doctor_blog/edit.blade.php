@extends('layouts.app')

@push('css_lib')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- dropzone (same version as old code) -->
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
<!-- CSS de Slick -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>

<!-- JS de Slick -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>


    <!-- ADD THIS STYLE to reveal delete-media on hover -->
    <style>
        .card.clickble:hover .delete-media {
            display: block !important;
        }
    </style>
@endpush

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-bold">{{trans('lang.my_blog_plural')}} <small class="mx-3">|</small><small>{{trans('lang.blog_desc')}}</small></h1>
            </div><!-- /.col -->
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt mx-1"></i> {{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item">
                        <a href="{!! route('doctor_blog.index') !!}">{{trans('lang.my_blog_plural')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.doctor_blog_edit')}}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<div class="content">
    <div class="clearfix"></div>
    @include('flash::message')
    @include('adminlte-templates::common.errors')
    <div class="clearfix"></div>

    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                @can('doctor_blog.index')
                <li class="nav-item">
                    <a class="nav-link" href="{!! route('doctor_blog.index') !!}"><i class="fas fa-list mr-2"></i>{{trans('lang.blog_table')}}</a>
                </li>
                @endcan
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
                    <a class="nav-link" href="{!! route('doctor_blog.create') !!}"><i class="fas fa-plus mr-2"></i>{{trans('lang.blog_create')}}</a>
                </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-edit mr-2"></i>{{trans('lang.doctor_blog_edit')}}</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            {!! Form::model($doctorBlog, ['route' => ['doctor_blog.update', $doctorBlog->id], 'method' => 'patch']) !!}
            <div class="row">
                @include('doctor_blog.fields')  <!-- Ici on inclut le fichier fields.blade.php -->
            </div>
        </div>
    </div>
</div>

@include('layouts.media_modal')

@endsection

@push('scripts_lib')
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
</script>
@endpush
