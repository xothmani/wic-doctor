@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ trans("lang.GestionUrgency") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans("lang.dashboard") }}</a></li>
                    <li class="breadcrumb-item active">{{ trans("lang.GestionUrgency") }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@if ($errors->has('error'))
    <div id="error-alert" class="alert alert-danger">
        {{ $errors->first('error') }}
    </div> 
@endif

@if(session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
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
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ trans('lang.date') }}</th>
                        <th>{{ trans('lang.from') }}</th>
                        <th>{{ trans('lang.to') }}</th>
                        <th>{{ trans('lang.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($urgencies as $urgency)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($urgency->jour)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($urgency->heurDebut)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($urgency->heurFin)->format('H:i') }}</td>
                            <td>
                                <!-- Bouton pour ouvrir la modal -->
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#editUrgencyModal{{ $urgency->id }}">
                                    <i class="fas fa-edit"></i> 
                                </a>

                                <form action="{{ route('urgencies.destroy', $urgency->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette urgence ?')">
                                        <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal de modification -->
                        <div class="modal fade" id="editUrgencyModal{{ $urgency->id }}" tabindex="-1" aria-labelledby="editUrgencyModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editUrgencyModalLabel">{{ trans('lang.modifUrgence') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('urgencies.update', $urgency->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group">
                                                <label for="jour">{{ trans('lang.date') }}</label>
                                                <input type="date" class="form-control" id="jour" name="jour" value="{{ \Carbon\Carbon::parse($urgency->jour)->format('Y-m-d') }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="heurDebut">{{ trans('lang.from') }}</label>
                                                <input type="time" class="form-control" id="heurDebut" name="heurDebut" value="{{ \Carbon\Carbon::parse($urgency->heurDebut)->format('H:i') }}" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="heurFin">{{ trans('lang.to') }}</label>
                                                <input type="time" class="form-control" id="heurFin" name="heurFin" value="{{ \Carbon\Carbon::parse($urgency->heurFin)->format('H:i') }}" required>
                                            </div>

                                            <button type="submit" class="btn bg-{{setting('theme_color')}}">{{ trans('lang.saveModif') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4">{{ trans('lang.noUrgenciesFound') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Bouton pour retourner à la page précédente -->
            <button class="btn btn-default mt-4" onclick="window.history.back();">
    <i class="fas fa-arrow-left mr-2"></i> {{ trans('lang.retour') }}
</button>
        </div>
    </div>
</div>

<style>
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-bordered th, 
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
</style>

@endsection
