@extends('layouts.app')

@section('content')


<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
        <h1 class="m-0 text-dark">{{trans('lang.fiche_details')}}  | <small> {{ $fiche->code }}</small></h1>
      </div><!-- /.col -->
      <div class="col-md-6">
        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
          <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('patients.index') !!}">{{trans('lang.patient_plural')}}</a></li>
          <li class="breadcrumb-item active">{{trans('lang.fiche_details')}}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<div class="content">
  <div class="card shadow-sm">
    <div class="card-body">

      <!-- Alerte allergie en haut -->
      @if($fiche->patient->allergie)
  <div class="alert" style="background-color: #F8D7DA; color: #842029; border-color: #F5C2C7;">
    <strong>
      <i class="fas fa-exclamation-triangle mr-2"></i>
      {{ trans('lang.patient_allergie') }} :
    </strong> 
    {{ $fiche->patient->allergie }}
  </div>
@endif


      <!-- Première section: Données personnelles et signes vitaux -->
      <div class="row">
  <!-- Données personnelles -->
  <div class="col-md-6">
    <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
  <strong>
    <i class="fas fa-user mr-2"></i>{{ trans('lang.patient_details') }}
  </strong>
  <a href="{{ route('patients.edit', $fiche->patient_id) }}" class="ml-auto" data-toggle="tooltip" data-placement="left" title="{{ trans('lang.patient_edit') }}">
    <i class="fas fa-edit" style="cursor: pointer; color: #5784BA;"></i>
</a>
</div>

      <div class="card-body">
        <div class="row">
          <!-- Première ligne : Prénom et Nom -->
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_first_name')}}:</strong> {{ $fiche->patient->first_name }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_last_name')}}:</strong> {{ $fiche->patient->last_name }}</p>
          </div>
        </div>
        
        <div class="row">
          <!-- Deuxième ligne : Numéro de téléphone et Mobile -->
          <div class="col-md-6">
            <p><strong>{{trans('lang.phone_number')}}:</strong> {{ $fiche->patient->phone_number ?? 'N/A' }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>{{trans('lang.mobile_number')}}:</strong> {{ $fiche->patient->mobile_number ?? 'N/A' }}</p>
          </div>
        </div>
        
        <div class="row">
          <!-- Troisième ligne : matricule cnss et date -->
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_matricule_cnss')}}:</strong> {{ $fiche->patient->matriculeCNSS }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_date_expiration')}}:</strong> {{ $fiche->patient->dateExpiration }}</p>
          </div>
        </div>
        <div class="row">
          <!-- 4eme ligne :assurance-->
          <div class="col-md-6">
    <p><strong>{{ trans('lang.patient_assurance') }}:</strong> 
    {{ $nomAssurance }}    </p>
</div>

          <!-- <div class="col-md-6">
            <p><strong>{{trans('lang.patient_assurance_name')}}:</strong></p>
          </div> -->
        </div>
      </div>
    </div>
  </div>

  <!-- Signes vitaux -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <strong><i class="fas fa-heartbeat mr-2"></i>{{trans('lang.vital_signs')}}</strong>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Première ligne :gendre et age -->
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_gender')}}:</strong> {{ $fiche->patient->gender }}</p>
          </div>
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_age')}}:</strong> {{ $fiche->patient->age }}</p>
          </div>
        </div>
        
        <div class="row">
          <!-- Deuxième ligne : height et weight-->
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_height')}}:</strong> {{ $fiche->patient->height }}</p>

          </div>
          <div class="col-md-6">
          <p><strong>{{trans('lang.patient_weight')}}:</strong> {{ $fiche->patient->weight }}</p>
          </div>
        </div>
        <div class="row">
          <!-- Troisième ligne :  groupe sanguin -->
          <div class="col-md-6">
            <p><strong>{{trans('lang.patient_groupe_sanguin')}}:</strong> {{ $fiche->patient->groupe_sanguin }}</p>
          </div>
          
        </div>
        <div class="row">
          <!-- 4eme ligne : notes -->
          <div class="col-md-12">
            <p><strong>{{trans('lang.notes')}}:</strong> {{ $fiche->patient->notes }}</p>
          </div>
          
        </div>
      </div>
    </div>
  </div>
</div>


      <!-- Historique médical et consultations -->
      <div class="row">
        <!-- Historique médical -->
        <div class="col-md-12 mt-4">
          <div class="card">
            <div class="card-header">
              <strong>        <i class="fas fa-notes-medical mr-2"></i>{{trans('lang.medical_history_antecedent')}}</strong>
            </div>

            <div class="card-body">
            <div class="col-md-12">
            <p><strong>{{trans('lang.patient_antecedent')}}:</strong> {{ $fiche->patient->antecedent }}</p>
          </div>
         
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <!-- Historique des consultations -->
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <strong><i class="fas fa-history mr-2"></i>{{trans('lang.historique_consultations')}}</strong>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>{{trans('lang.consultation_date')}}</th>
                      <th>{{trans('lang.consultation_reason')}}</th>
                      <th>{{trans('lang.consultation_motif')}}</th>
                      <th>{{trans('lang.actions')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($fiche->consultations as $consultation)
                      <tr>
                        <td>{{ $consultation->dateConsultation }}</td>
                        <td class="consultation-reason" data-toggle="modal" data-target="#reasonModal{{ $consultation->id }}" style="cursor: pointer;">
                          <!-- Affichage tronqué de la raison -->
                          <span class="reason-text" style="display: inline-block; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $consultation->raison ?? 'N/A' }}
                          </span>
                        </td>
                        <td class="consultation-motif" data-toggle="modal" data-target="#motifModal{{ $consultation->id }}" style="cursor: pointer;">
                          <!-- Affichage tronqué de l'observation -->
                          <span class="motif-text" style="display: inline-block; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $consultation->motif ?? 'N/A' }}
                          </span>
                        </td>
                        <td>
                          <!-- Icône Prescription -->
                          <a href="{{ route('consultation.prescriptions', $consultation->id) }}" class="btn btn-info btn-sm" title="Voir les prescriptions">
                            <i class="fa fa-file-medical-alt"></i> {{ trans('lang.see_prescriptions') }}
                          </a>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>

                @foreach($fiche->consultations as $consultation)
                  <div class="modal fade" id="reasonModal{{ $consultation->id }}" tabindex="-1" role="dialog" aria-labelledby="reasonModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="reasonModalLabel">{{trans('lang.consultation_reason')}}</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <!-- Afficher la raison complète -->
                          <p>{{ $consultation->raison }}</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach

                @foreach($fiche->consultations as $consultation)
                  <div class="modal fade" id="motifModal{{ $consultation->id }}" tabindex="-1" role="dialog" aria-labelledby="motifModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="motifModalLabel">{{trans('lang.consultation_motif')}}</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <!-- Afficher l'observation complète -->
                          <p>{{ $consultation->motif }}</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach

              </div>
            </div>

          </div>
        </div>
      </div> 


      <div class="row mt-3">
        <!-- liste des rapports fichier pdf -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-file-pdf mr-2"></i>{{ trans('lang.reports_list') }}</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ trans('lang.report_title') }}</th>
                                    <th>{{ trans('lang.report_description') }}</th>
                                    <th>{{ trans('lang.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fiche->reports as $report)
                                    <tr>
                                        <td>{{ $report->title ?? 'No Title' }}</td>
                                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $report->description ?? 'No Description' }}
                                        </td>
                                        <td>
                                            <!-- Open PDF -->
                                            <a href="{{ asset($report->file_path) }}" target="_blank" class="btn btn-danger btn-sm">
                                                <i class="fas fa-file-pdf"></i> {{ trans('lang.view_pdf') }}
                                            </a>
                                            
                                            <!-- Download PDF -->
                                            <a href="{{ asset($report->file_path) }}" download class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i> {{ trans('lang.download_pdf') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Show message if no reports exist -->
                        @if($fiche->reports->isEmpty())
                            <p class="text-center text-muted">{{ trans('lang.no_reports_found') }}</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>


      </div>


      <!-- Bouton de retour -->
      <div class="form-group col-12 text-md-right mt-3">
        <a href="{!! route('patients.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.back')}}</a>
      </div>

    </div>
  </div>
</div>

@endsection

<style>
  /* Style pour rendre les cartes carrées */
  .card {
    height: 100%;
    display: flex;
    flex-direction: column;
  }

  .alert-danger {
    margin-bottom: 20px;
  }

  .consultation-reason, .consultation-motif {
    cursor: pointer;
  }

  .modal-dialog {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    margin: auto;
  }

  /* Assurez-vous que le body ou le container principal ait une hauteur de 100% */
html, body {
  height: 100%;
  margin: 0;
}

/* Conteneur du contenu principal qui doit défiler */
.content {
  height: calc(100vh - 60px); /* 100vh (hauteur de l'écran) moins la hauteur de l'en-tête (par exemple 60px) */
  overflow-y: auto;  /* Ajoute la possibilité de faire défiler */
}

/* Footer dans layout.app, assurez-vous qu'il reste en bas */
.footer {
  position: relative;
  bottom: 0;
  width: 100%;
}
/* Limiter la hauteur de la carte des consultations */
.card-body {
  overflow-y: auto; /* Barre de défilement si nécessaire */
}

.table-responsive {
  max-height: 300px; /* Ajustez cette valeur selon vos besoins */
  overflow-y: auto; /* Ajoute une barre de défilement verticale */
}


</style>



