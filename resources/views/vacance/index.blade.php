@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ trans("lang.GestionVacance") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans("lang.dashboard") }}</a></li>
                    <li class="breadcrumb-item active">{{ trans("lang.GestionVacance") }}</li>
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
                        <th>{{ trans('lang.typeVacance') }}</th>
                        <th>{{ trans('lang.dateDebut') }}</th>
                        <th>{{ trans('lang.dateFin') }}</th>
                        <th>{{ trans('lang.raison') }}</th>
                        <th>{{ trans('lang.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vacances as $vacance)
                        <tr>
                            <td>{{ ($vacance->type) }}</td>
                            <td>{{ \Carbon\Carbon::parse($vacance->dateDebut)->format('d-m-Y') }}</td>
                            <td>
                                @if ($vacance->type === 'journée')
                                    N/A
                                @else
                                    {{ \Carbon\Carbon::parse($vacance->dateFin)->format('d-m-Y') }}
                                @endif
                            </td>
                            <td>
                                @if (is_null($vacance->raison) || $vacance->raison === '')
                                    N/A
                                @else
                                    {{ $vacance->raison }}
                                @endif
                            </td>
                            <td>
                                <!-- Bouton pour ouvrir la modal -->
                                       <!-- Bouton pour ouvrir la modal -->
                                <a href="#" class="btn btn-link" data-toggle="modal" data-target="#editVacationModal{{ $vacance->id }}">
                                    <i class="fas fa-edit"></i> 
                                </a>

                                <form action="{{ route('vacances.destroy', $vacance->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vacance ?')">
                                        <i class="fas fa-trash-alt"></i> <!-- Icône de suppression -->
                                    </button>
                                </form>
                               
                            </td>
                        </tr>
<!-- Modal de modification -->
<div class="modal fade" id="editVacationModal{{ $vacance->id }}" tabindex="-1" aria-labelledby="editVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVacationModalLabel">{{ trans('lang.modifVacance') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vacances.update', $vacance->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Type -->
                    <div class="form-group">
                        <label for="type">{{ trans('lang.type') }}</label>
                        <select class="form-control" id="type" name="type" required onchange="toggleDateFin(this)">
                            <option value="journée" {{ $vacance->type == 'journée' ? 'selected' : '' }}>{{ trans('lang.journee') }}</option>
                            <option value="période" {{ $vacance->type == 'période' ? 'selected' : '' }}>{{ trans('lang.periode') }}</option>
                        </select>
                    </div>

                    <!-- Raison -->
                    <div class="form-group">
                        <label for="raison">{{ trans('lang.raison') }}</label>
                        <input type="text" class="form-control" id="raison" name="raison" value="{{ $vacance->raison ?? 'N/A' }}" maxlength="255">
                    </div>

                    <!-- Date de début -->
                    <div class="form-group">
                        <label for="dateDebut">{{ trans('lang.dateDebut') }}</label>
                        <input type="date" class="form-control" id="dateDebut" name="dateDebut" value="{{ \Carbon\Carbon::parse($vacance->dateDebut)->format('Y-m-d') }}" required>
                    </div>

                    <!-- Date de fin -->
                    <div class="form-group" id="dateFinContainer">
                        <label for="dateFin">{{ trans('lang.dateFin') }}</label>
                        <input type="date" class="form-control" id="dateFin" name="dateFin" value="{{ $vacance->dateFin ? \Carbon\Carbon::parse($vacance->dateFin)->format('Y-m-d') : '' }}">
                    </div>

                    <button type="submit" class="btn bg-{{ setting('theme_color') }}">{{ trans('lang.saveModif') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher ou masquer le champ Date de fin
    function toggleDateFin(selectElement) {
        const dateFinContainer = document.getElementById('dateFinContainer');
        if (selectElement.value === 'période') {
            dateFinContainer.style.display = 'block';
        } else {
            dateFinContainer.style.display = 'none';
        }
    }

    // Initialisation : Afficher ou masquer le champ Date de fin en fonction de la valeur initiale
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        toggleDateFin(typeSelect);
    });
</script>



                 
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
