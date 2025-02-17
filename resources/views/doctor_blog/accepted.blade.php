@extends('layouts.app')

@section('content')
<!-- Header et autres contenus ici -->
<div class="content">
    <div class="clearfix"></div>
    @include('flash::message')
    <div class="card shadow-sm">
        <div class="card-header">
            <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                <div class="d-flex flex-row">
                    <li class="nav-item">
                        <a class="nav-link active" href="{!! url()->current() !!}">
                            <i class="fa fa-list mr-2"></i>{{trans('lang.blog_table')}}
                        </a>
                    </li>
                </div>
                @include('layouts.right_toolbar', compact('dataTable'))
            </ul>
        </div>
        <div class="card-body">
            @include('doctor_blog.table') <!-- Inclure la même table que pour l'index -->
            <div class="clearfix"></div>
        </div>
    </div>
</div>
@endsection
