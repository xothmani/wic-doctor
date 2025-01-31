@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="text-center mb-4 font-weight-bold">Parrainage</h4>

    <div class="card shadow-lg">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des parrainés</h5>
        </div>
        
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <label for="entries">Afficher</label>
                    <select id="entries" class="form-select d-inline-block w-auto mx-2">
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                    </select>
                    éléments
                </div>
                <div class="input-group w-25">
                    <input type="text" class="form-control" placeholder="Rechercher">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped border text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Code Parent</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            <tr>
                                <td>{{ $doctor->name }}</td>
                                <td>{{ $doctor->email }}</td>
                                <td>{{ $doctor->code_parent }}</td>
                                <td>
                                    <span class="badge 
                                        @if($doctor->status == 'Actif') bg-success 
                                        @elseif($doctor->status == 'Inactif') bg-danger 
                                        @else bg-warning 
                                        @endif">
                                        {{ $doctor->status }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-outline-info btn-sm" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Aucun élément correspondant trouvé</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between">
                <p>Affichage de l'élément 1 à {{ count($doctors) }} sur {{ count($doctors) }} éléments</p>
            </div>
        </div>
    </div>
</div>
@endsection
