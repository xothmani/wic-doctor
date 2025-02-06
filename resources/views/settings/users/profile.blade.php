@extends('layouts.app')
@push('css_lib')
    <!-- icheck-bootstrap -->
    <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
    <!-- select2 -->
    <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset('vendor/summernote/summernote-lite.min.css')}}">
    {{--dropzone--}}
    <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{!! trans('lang.user_profile') !!} <small>{{trans('lang.media_desc')}}</small></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i> {{trans('lang.dashboard')}}</a></li>
                        <li class="breadcrumb-item active">{{trans('lang.user_profile')}}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content" >
        <div class="container-fluid" style="min-height: 100vh">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i> {{trans('lang.user_about_me')}}</h3>
                        </div>
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img src="{{auth()->user()->getFirstMediaUrl('avatar','icon')}}" class="profile-user-img img-fluid img-circle" alt="{{auth()->user()->name}}">
                            </div>
                            <h3 class="profile-username text-center">{{auth()->user()->name}}</h3>
                            <p class="text-muted text-center">{{implode(', ',$rolesSelected)}}</p>
                            <a class="btn btn-outline-{{setting('theme_color')}} btn-block" href="mailto:{{auth()->user()->email}}"><i class="fas fa-envelope mr-2"></i>{{auth()->user()->email}}
                            </a>
                        </div>
                    
                        <!-- /.card-body -->
                    </div>

                    <!-- Profile Edit -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-2"></i>  {{trans('lang.edit_profil')}}</h3>
                    </div>
                    <div class="card-body box-profile">
                        <!-- Liste des étapes -->
                        <ul class="task-list">
                            <li class="task completed"><i class="fas fa-check-circle"></i>{{trans('lang.import_avatar')}} </li>
                            <li class="task completed"><i class="fas fa-check-circle"></i> {{trans('lang.complet_adresse')}}</li>
                            <li class="task pending"><i class="far fa-circle"></i>{{trans('lang.complet_cv')}}</li>
                            <li class="task completed"><i class="fas fa-check-circle"></i>{{trans('lang.import_photos')}}</li>
                            <li class="task pending"><i class="far fa-circle"></i> {{trans('lang.final_profil')}}</li>


                        </ul>

                        <!-- Progress Bar -->
                        <div class="progress-container">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 75%; background-color: #5c6bc0;"></div>
                            </div>
                        </div>

                        <a class="btn btn-outline-{{setting('theme_color')}} btn-block" href="{{ route('doctors.editProfil') }}">
                            <i class="fas fa-edit mr-2"></i>{{trans('lang.edit_profil')}}
                        </a>
                    </div>
                </div>


                    <!-- /.card -->

                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    @include('flash::message')
                    @include('adminlte-templates::common.errors')
                    <div class="clearfix"></div>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="{!! url()->current() !!}"><i class="fas fa-cog mr-2"></i>{{trans('lang.app_setting')}}</a>
                                </li>
                           <!--      <li class="nav-item">
                                    <a class="nav-link" href="{{ route('fieldsDoctor') }}">
                                        <i class="fas fa-cog mr-2"></i>{{ trans('lang.profile') }}
                                    </a>
                                </li>  -->
                                @hasrole('customer')
                                <div class="ml-auto d-inline-flex">
                                    <li class="nav-item">
                                        <a class="nav-link pt-1" href="{{ route('clinics.create') }}"><i class="fas fa-check-o"></i> {{trans('lang.app_setting_become_servicclinic')}}
                                        </a>
                                    </li>
                                </div>
                                @endhasrole
                            </ul>
                        </div>
                        <div class="card-body">
                            {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}
                            <div class="row">
                                @include('settings.users.fields')
                            </div>
                            {!! Form::close() !!}
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('layouts.media_modal',['collection'=>null])
@endsection
@push('scripts_lib')
    <!-- select2 -->
    <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
    <script src="{{asset('vendor/summernote/summernote-lite.min.js')}}"></script>
    {{--dropzone--}}
    <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
@endpush

<style>
    /* Liste des tâches */
    .task-list {
        list-style: none;
        padding: 0;
        margin-bottom: 15px;
    }

    .task {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .task i {
        font-size: 16px;
        margin-right: 10px;
    }

    .task.completed i {
        color:#5c6bc0; /* mauve */
    }

    .task.pending i {
        color: #aaa; /* Gris */
    }

    /* Barre de progression */
    .progress-container {
        margin-top: 10px;
        width: 100%;
        margin-bottom: 15px
    }

    .progress-bar-container {
        width: 100%;
        height: 12px;
        background-color: #ddd;
        border-radius: 10px;
        margin-top: 10px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease-in-out;
    }
</style>