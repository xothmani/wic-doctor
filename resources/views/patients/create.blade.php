@extends('layouts.app')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
@php
  $doctorId = auth()->user()->getDoctorId();
@endphp

@section('content')
  @if(auth()->user()->hasPermissionInContext('patients.create', $doctorId))
    <!-- Content Header (Page header) -->
    <div class="content-header">
    <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
      <h1 class="m-0 text-bold">{{trans('lang.patient_plural')}} <small
      class="mx-3">|</small><small>{{trans('lang.patient_desc')}}</small></h1>
      </div>
      <div class="col-md-6">
      <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
      <li class="breadcrumb-item"><a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt mx-1"></i>
        {{trans('lang.dashboard')}}</a></li>
      <li class="breadcrumb-item">
      <a href="{!! route('patients.index') !!}">{{trans('lang.patient_plural')}}</a>
      </li>
      <li class="breadcrumb-item active">{{trans('lang.patient_create')}}</li>
      </ol>
      </div>
    </div>
    </div>
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
      @can('patients.index')
      <li class="nav-item">
      <a class="nav-link" href="{!! route('patients.index') !!}"><i
      class="fas fa-list mr-2"></i>{{trans('lang.patient_table')}}</a>
      </li>
    @endcan
      <li class="nav-item">
      <a class="nav-link active" href="{!! url()->current() !!}"><i
        class="fas fa-plus mr-2"></i>{{trans('lang.patient_create')}}</a>
      </li>
      </ul>
    </div>
    <div class="card-body">
      {!! Form::open(['route' => 'patients.store']) !!}
      <div class="row">
      @include('patients.fields')
      </div>
      {!! Form::close() !!}
      <div class="clearfix"></div>
    </div>
    </div>
    </div>
    @include('layouts.media_modal')
  @else
    <div class="content-header">
    <div class="container-fluid">
    <div class="alert alert-danger">
      {{ __('You do not have permission to access this page.') }}
    </div>
    </div>
    </div>
  @endif
@endsection

@push('css_lib')
  <link rel="stylesheet" href="{{asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2/css/select2.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('vendor/dropzone/min/dropzone.min.css')}}">
@endpush

@push('scripts_lib')
  <script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
  <script src="{{asset('vendor/dropzone/min/dropzone.min.js')}}"></script>
  <script type="text/javascript">
    Dropzone.autoDiscover = false;
    var dropzoneFields = [];
  </script>
@endpush
<!-- Modal pour sous-profil -->
<div class="modal fade" id="subProfileModal" tabindex="-1" role="dialog" aria-labelledby="subProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subProfileModalLabel">Profil existant détecté</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(session('existingPatient'))
                <p>Ce numéro de téléphone est déjà associé à <strong>{{ session('existingPatient')['name'] }}</strong>.</p>
                <p>Souhaitez-vous créer un sous-profil lié à ce compte?</p>
                
                <form id="subProfileForm" action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    
                    <!-- Informations du nouveau sous-profil -->
                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" class="form-control" name="first_name" id="modalFirstName" value="{{ session('formData')['first_name'] ?? '' }}" required>
                        <div class="invalid-feedback">Veuillez entrer un prénom valide.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" class="form-control" name="last_name" id="modalLastName" value="{{ session('formData')['last_name'] ?? '' }}" required>
                        <div class="invalid-feedback">Veuillez entrer un nom valide.</div>
                    </div>

                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" class="form-control" name="date_naissance" id="modalBirthDate" value="{{ session('formData')['date_naissance'] ?? '' }}" required>
                        <div class="invalid-feedback">Veuillez entrer une date de naissance valide.</div>
                    </div>

                    <div class="form-group">
                        <label for="gender">Genre</label>
                        <select class="form-control" name="gender" id="modalGender" required>
                            <option value="">Sélectionner un genre</option>
                            <option value="homme" {{ (session('formData')['gender'] ?? '') == 'homme' ? 'selected' : '' }}>Homme</option>
                            <option value="femme" {{ (session('formData')['gender'] ?? '') == 'femme' ? 'selected' : '' }}>Femme</option>
                            <option value="autre" {{ (session('formData')['gender'] ?? '') == 'autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un genre.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="type_of_relationship">Relation avec {{ session('existingPatient')['name'] }}</label>
                        <select class="form-control" name="type_of_relationship" id="modalRelationship" required>
                            <option value="">Sélectionner une relation</option>
                            <option value="conjoint">Conjoint(e)</option>
                            <option value="enfant">Enfant</option>
                            <option value="parent">Parent</option>
                            <option value="frere">Frère</option>
                            <option value="soeur">Sœur</option>
                            <option value="autre">Autre</option>
                        </select>
                        <div class="invalid-feedback">Veuillez spécifier la relation.</div>
                    </div>
                    
                    <div id="relatedPatientsContainer" class="mt-3" style="display: none;">
                        <h6>Patients liés existants :</h6>
                        <div class="form-group">
                            <div id="relatedPatientsList" class="list-group"></div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="createNewPatient" name="create_new_patient" checked>
                            <label class="form-check-label" for="createNewPatient">
                                Créer un nouveau patient
                            </label>
                        </div>
                    </div>
                    
                    <input type="hidden" name="existing_patient_id" id="existingPatientId" value="">
                    <input type="hidden" name="phone_number" value="{{ session('existingPatient')['phone_number'] }}">
                    <input type="hidden" name="create_subprofile" value="1">
                </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="submitSubProfileForm">Confirmer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const relationshipSelect = document.getElementById('modalRelationship');
    const relatedContainer = document.getElementById('relatedPatientsContainer');
    const relatedList = document.getElementById('relatedPatientsList');
    const existingPatientId = document.getElementById('existingPatientId');
    const createNewPatientCheckbox = document.getElementById('createNewPatient');
    const form = document.getElementById('subProfileForm');
    const submitBtn = document.getElementById('submitSubProfileForm');

    // Gestion du changement de relation
    relationshipSelect.addEventListener('change', function() {
        const selectedRelation = this.value;
        const mainPatientId = {{ session('existingPatient')['id'] ?? 'null' }};

        if (selectedRelation && mainPatientId) {
            fetch(`/patients/related/${mainPatientId}/${selectedRelation}`)
                .then(response => response.json())
                .then(data => {
                    relatedList.innerHTML = '';
                    
                    if (data.length > 0) {
                        relatedContainer.style.display = 'block';
                        
                        data.forEach(patient => {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            
                            let formattedDate = '';
                            if (patient.date_naissance) {
                                const date = new Date(patient.date_naissance);
                                formattedDate = ` (${date.toLocaleDateString('fr-FR')})`;
                            }
                            
                            item.textContent = `${patient.first_name} ${patient.last_name}${formattedDate}`;
                            
                            item.addEventListener('click', function() {
                                document.querySelectorAll('#relatedPatientsList button').forEach(btn => {
                                    btn.classList.remove('active');
                                });
                                this.classList.add('active');
                                existingPatientId.value = patient.id;
                                createNewPatientCheckbox.checked = false;
                            });
                            
                            relatedList.appendChild(item);
                        });
                    } else {
                        relatedContainer.style.display = 'none';
                        existingPatientId.value = '';
                        createNewPatientCheckbox.checked = true;
                    }
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    relatedContainer.style.display = 'none';
                });
        } else {
            relatedContainer.style.display = 'none';
            existingPatientId.value = '';
            createNewPatientCheckbox.checked = true;
        }
    });

    // Gestion de la checkbox "Créer un nouveau patient"
    createNewPatientCheckbox.addEventListener('change', function() {
        if (this.checked) {
            existingPatientId.value = '';
            document.querySelectorAll('#relatedPatientsList button').forEach(btn => {
                btn.classList.remove('active');
            });
        }
    });

    // Validation du formulaire
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Réinitialiser les messages d'erreur
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        let isValid = true;
        
        // Validation des champs requis seulement si on crée un nouveau patient
        if (createNewPatientCheckbox.checked) {
            const requiredFields = [
                'first_name', 
                'last_name', 
                'date_naissance', 
                'gender', 
                'type_of_relationship'
            ];
            
            requiredFields.forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validation spécifique pour la date de naissance
            const birthDate = form.querySelector('[name="date_naissance"]');
            if (birthDate.value) {
                const birthDateObj = new Date(birthDate.value);
                const today = new Date();
                if (birthDateObj >= today) {
                    birthDate.classList.add('is-invalid');
                    birthDate.nextElementSibling.textContent = 'La date de naissance doit être dans le passé.';
                    isValid = false;
                }
            }
        }
        
        if (isValid) {
            form.submit();
        } else {
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    // Validation en temps réel
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    });
});

// Afficher le modal si nécessaire
@if(session('showSubProfileModal'))
$(document).ready(function() {
    $('#subProfileModal').modal('show');
});
@endif
</script>