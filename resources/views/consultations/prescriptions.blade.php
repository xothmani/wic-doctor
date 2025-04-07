@extends('layouts.app')

@section('content')
<!-- Content Header (Page header) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
        <h1 class="m-0 text-dark">{{trans('lang.fiche_details')}} | <small> {{trans('lang.details_consultation')}} </small></h1>
      </div>
      <div class="col-md-6">
        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
          <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{trans('lang.dashboard')}}</a></li>
          <li class="breadcrumb-item"><a href="{!! route('patients.index') !!}">{{trans('lang.patient_plural')}}</a></li>
          <li class="breadcrumb-item"><a>{{trans('lang.fiche_details')}}</a></li>
          <li class="breadcrumb-item active">{{trans('lang.prescriptions_for_consultation')}}</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-tabs align-items-end card-header-tabs w-100">

      </ul>
    </div>

    <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
  <h4>{{ trans('lang.prescriptions_for_consultation') }}</h4>
  <div class="form-group">
    <a href="{{ route('prescriptions.create', ['consultation_id' => $consultation->id]) }}" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
      <i class="fa fa-plus"></i> {{ trans('lang.add_prescription') }}
    </a>
  </div>
</div>

      <table class="table">
        <thead>
          <tr>
            <th>{{ trans('lang.prescription_date') }}</th>
            <th>{{ trans('lang.prescription_type') }}</th>
            <th>{{ trans('lang.prescription_observation') }}</th>
            <th>{{ trans('lang.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($prescriptions as $prescription)
            <tr>
              <td>{{ $prescription->date }}</td>
              <td>{{ $prescription->type }}</td>
              <td>{{ $prescription->observation ?? 'N/A' }}</td>
              <td>
              <button type="button" class="btn btn-link" data-toggle="modal" data-target="#viewModal"
        data-id="{{ $prescription->id }}">
    <i class="fas fa-eye"></i>
</button>

<!-- <a href="{{ route('send.whatsapp', ['prescription_id' => $prescription->id]) }}" target="_blank" class='btn btn-link'>
    <i class="fab fa-whatsapp"></i>
</a> -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('.whatsapp-btn').on('click', function (e) {
            // Lien ouvert dans une nouvelle fenêtre
            const contactNumber = $(this).data('contact');
            
            // Afficher la confirmation
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Message envoyé avec succès',
                confirmButtonText: 'OK',
                timer: 3000
            });
        });
    });
</script>

<!-- <a href="#" id="sendEmailLink" class="btn btn-link">
  <i class="fa fa-envelope"></i>
</a> -->

<a href="{{ asset($prescription->pdf) }}" class="btn btn-link" target="_blank">
    <i class="fa fa-print"></i>
</a>
<!-- Bouton pour envoyer le PDF par e-mail -->
<!-- Bouton qui ouvre le modal avec l'ID de la prescription -->
<button type="button" class="btn btn-link sendEmailBtn" 
        data-bs-toggle="modal" 
        data-bs-target="#confirmSendEmailModal" 
        data-id="{{ $prescription->id }}"
        data-email="{{ $prescription->consultation->patient->email ?? '' }}">
    <i class="fa fa-envelope"></i>
</button>


<!-- Modal de confirmation -->
<div class="modal fade" id="confirmSendEmailModal" tabindex="-1" aria-labelledby="confirmSendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmSendEmailModalLabel">Confirmer l'envoi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment envoyer la prescription par mail à :</p>
                <input type="email" id="emailInput" name="email" class="form-control" placeholder="Saisissez l'email" required>
                <small id="emailError" class="text-danger d-none">Email obligatoire.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="sendEmailForm" method="POST">
                    @csrf
                    <input type="hidden" id="emailHiddenInput" name="email">
                    <button type="submit" class="btn btn-primary">Confirmer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".sendEmailBtn").forEach(button => {
            button.addEventListener("click", function () {
                let prescriptionId = this.getAttribute("data-id");
                let email = this.getAttribute("data-email") || ""; // Récupère l'email s'il existe
                let emailInput = document.getElementById("emailInput");
                let form = document.getElementById("sendEmailForm");

                form.setAttribute("action", `/prescriptions/${prescriptionId}/send-email`);
                emailInput.value = email; // Pré-remplit l'email s'il existe
            });
        });

        document.getElementById("sendEmailForm").addEventListener("submit", function (event) {
            let emailInput = document.getElementById("emailInput");
            let emailError = document.getElementById("emailError");

            if (!emailInput.value.trim()) {
                event.preventDefault(); // Empêche l'envoi
                emailError.classList.remove("d-none"); // Affiche l'erreur
            } else {
                emailError.classList.add("d-none"); // Cache l'erreur si valide
                document.getElementById("emailHiddenInput").value = emailInput.value; // Transmet l'email dans le champ hidden
            }
        });
    });
</script>



              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="form-group col-12 text-md-right mt-3">
        <a href="javascript:history.back()" class="btn btn-default">
          <i class="fa fa-undo"></i> {{ trans('lang.back') }}
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

<style>
  .modal-dialog {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    margin: auto;
  }
</style>
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">{{ trans('lang.view_prescription') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>{{ trans('lang.loading') }}</p> <!-- Initial loading message -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('lang.close') }}</button>
      </div>
    </div>
  </div>
</div>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
  $(document).ready(function() {
    $('#viewModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      var prescriptionId = button.data('id'); // Get the prescription ID

      // Clear previous modal content
      var modal = $(this);
      modal.find('.modal-body').html('<p>Loading...</p>');

      // Make AJAX request to get the prescription details
      $.ajax({
        url: `/prescriptions/details/${prescriptionId}`, // Adjust the URL to match your route
        method: 'GET',
        success: function(response) {
          // Prepare the header content for the modal
          var prescriptionDetails = `
            <h5>${response.type}</h5>
          `;

          // Format each medicament's details in the desired way
          if(response.medicaments.length > 0) {
    prescriptionDetails += ``;
    response.medicaments.forEach(function(medicament) {
        prescriptionDetails += `
            <p>
                <span style="color: #0080FF;">${medicament.nom_commercial}</span> - 
                <span style="color: darkblue;">${medicament.dosage}</span>, 
                <span style="color: darkblue;">${medicament.nb_de_fois}</span>, 
                ${medicament.horaire ? `<span style="color: darkblue;">${medicament.horaire}</span>` : ''}
          <span style="color: darkblue;">{{ trans('lang.pendant') }} ${medicament.nb_de_jours}</span>.
            </p>
        `;
    });
}
          if(response.analyses.length > 0) {
            prescriptionDetails += ``;
            response.analyses.forEach(function(analyse) {
              prescriptionDetails += `
                <p>
                  <span style="color: #0080FF;">${analyse.Code_Analyse}</span> 

                </p>
              `;
            });
          }
          if(response.radios.length > 0) {
            prescriptionDetails += ``;
            response.radios.forEach(function(radio) {
              prescriptionDetails += `
                <p>
                  <span style="color: #0080FF;">${radio.Nom}</span> 

                </p>
              `;
            });
          }          

          // Format other treatments if available
          if(response.other_treatments.length > 0) {
            prescriptionDetails += ``;
            response.other_treatments.forEach(function(treatment) {
              prescriptionDetails += `<p><span style="color: #0080FF;">${treatment}</span> </p>`;
            });
          }

          // Update the modal content with the formatted details
          modal.find('.modal-body').html(prescriptionDetails);
        },
        error: function() {
          modal.find('.modal-body').html('<p>{{ trans('lang.Erreur') }}</p>');
        }
      });
    });
  });
</script>





<script>
  // Ajouter un événement de clic au lien
  document.getElementById('sendEmailLink').addEventListener('click', function(event) {
    event.preventDefault();  // Empêche le comportement par défaut du lien (navigation)

    // Récupérer les données nécessaires (par exemple, depuis un formulaire ou des variables)
    const recipient = 'guiraschaima5@gmail.com'; // Remplacez par le destinataire
    const subject = 'Objet de l\'email';
    const message = 'Corps de l\'email';
    const pdfId = 123; // Remplacez par l'ID du PDF à envoyer
    
    // Envoi de la requête POST à l'API Express
    fetch('http://localhost:3001//send-email-with-link', { // Remplacez par l'URL de votre API
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ recipient, subject, message, pdfId }),
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('E-mail envoyé avec succès!');
      } else {
        alert('Erreur lors de l\'envoi de l\'email : ' + data.message);
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
      alert('Erreur lors de la requête.');
    });
  });
</script>