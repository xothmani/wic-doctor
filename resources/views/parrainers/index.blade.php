@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-bold">{{ ('Parrain') }}
                    <small class="mx-3">|</small><small>{{ ('Liste des Parrains') }}</small>
                </h1>
            </div><!-- /.col -->
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fas fa-tachometer-alt mx-1"></i> {{ trans('Tableau de bord') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/parrainer') }}">
                            {{ ('Liste des Parrains') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('/parrainer') }}">
                            {{ ('Parrainers') }}
                        </a>
                    </li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>

    <!-- /.content-header -->

    <!-- Alerts -->
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

    <!-- Main Content -->
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')

        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                    <div class="d-flex flex-row">
                        <li class="nav-item">
                            <a class="nav-link active" href="parrainer" id="list-tab">
                                <i class="fa fa-list mr-2"></i>{{ trans('Tableau des Parrains') }}
                            </a>
                        </li>

                    @can('parrainers.parrainer') 
    <li class="nav-item">
        <a class="nav-link {{ request()->is('parrainers/parrainer') ? 'active-tab' : '' }}" href="{!! route('parrainers.parrainer') !!}">
            <i class="fa fa-plus mr-2"></i>{{trans('Créer un parrain')}}
        </a>
    </li>
@endcan
                        
 
                    </div>
                </ul>
            </div>

            <div class="card-body">
                <!-- Contenu dynamique -->
                <div id="dynamic-content">
                    @include('parrainers.table')  <!-- Par défaut, afficher la tale des parrains -->
                </div>
            </div>
        </div>
    </div>
@endsection
  
@push('scripts')
<script>
    $(document).ready(function() {
        // Lorsque l'utilisateur clique sur "Liste des parrains"
        $('#list-tab').click(function() {
            $('#dynamic-content').load('{{ route('parrainers.index') }}');  // Charger la vue de la liste des parrains
            $('#list-tab').addClass('active'); // Ajouter la classe active
            $('#create-tab').removeClass('active'); // Retirer la classe active
        });

        // Lorsque l'utilisateur clique sur "Créer un parrain"
        $('#create-tab').click(function() {
            $('#dynamic-content').load('{{ route('parrainers.parrainer') }}');  // Charger la vue de création de parrain
            $('#create-tab').addClass('active'); // Ajouter la classe active
            $('#list-tab').removeClass('active'); // Retirer la classe active
        });
    });
</script>
@endpush
