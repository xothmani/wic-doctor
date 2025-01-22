@php
    $doctorRequest = \App\Models\DoctorRequest::find($id);  // Récupère la demande
@endphp

<div class="btn-group btn-group-sm">
    @can('requests.show')
        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#requestModal-{{ $id }}" title="{{ trans('lang.request_view') }}" onclick="loadRequestDetails('{{ $id }}')">
            <i class="fas fa-eye"></i>
        </button>
    @endcan

    @if ($doctorRequest && $doctorRequest->status === 'en cours')  <!-- Vérification du statut -->
        @can('doctor_requests.createUserFromDoctorRequest')
            <a href="{{ route('doctor_requests.createUserFromDoctorRequest', ['id' => $id]) }}"  
               data-toggle="tooltip" 
               data-placement="left" 
               title="{{ trans('lang.create_user') }}" 
               class='btn btn-link text-success'>
               <i class="fas fa-user-plus"></i> 
            </a>
        @endcan
    @endif
</div>

<!-- Modal pour afficher les détails de la demande -->
<div class="modal fade" id="requestModal-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document"> <!-- Ajout de modal-dialog-centered -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestModalLabel">{{ trans('lang.request_view') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestDetails-{{ $id }}">
                <!-- Les détails seront insérés ici par JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('lang.close') }}</button>
            </div>
        </div>
    </div>
</div>



<script>
function loadRequestDetails(requestId) {
    // Faire une requête AJAX pour obtenir les détails de la demande
    $.ajax({
        url: '/doctor-requests/' + requestId,  // URL pour récupérer les détails de la demande
        method: 'GET',
        success: function(response) {
            // Initialiser le contenu de la modal
            let detailsHtml = `
                <strong>{{ trans('lang.nomComplet') }}:</strong> ${response.name} ${response.lastname}<br> 
                <strong>{{ trans('lang.type') }}:</strong> ${response.type}<br>
                <strong>{{ trans('lang.speciality') }}:</strong> ${response.speciality_id}<br>
                <strong>{{ trans('lang.email') }}:</strong> ${response.email}<br>
                <strong>{{ trans('lang.phone') }}:</strong> ${response.Phone}<br>
                <strong>{{ trans('lang.country') }}:</strong> ${response.pays}<br>
            `;
            
            // Ajouter les informations spécifiques au pays sous "pays"
            if (response.pays.toLowerCase() === 'tunisie') {
                detailsHtml += `
                    <strong>{{ trans('lang.gouvernorat') }}:</strong> ${response.gouvernorat}<br>
                    <strong>{{ trans('lang.city') }}:</strong> ${response.ville}<br>
                `;
            } else if (response.pays.toLowerCase() === 'france') {
                detailsHtml += `
                    <strong>{{ trans('lang.departement') }}:</strong> ${response.departement}<br>
                    <strong>{{ trans('lang.region') }}:</strong> ${response.region}<br>
                `;
            }

            // Ajouter le reste des informations
            detailsHtml += `
                <strong>{{ trans('lang.address') }}:</strong> ${response.adresse}<br>
                <strong>{{ trans('lang.description') }}:</strong> ${response.description}<br>
                <strong>{{ trans('lang.status') }}:</strong> ${response.status}<br>
            `;
            
            // Mettre à jour le contenu de la modal avec les détails
            $('#requestDetails-' + requestId).html(detailsHtml);
        },
        error: function(xhr, status, error) {
            // Afficher l'erreur exacte dans la console pour déboguer
            console.error('XHR:', xhr);
            console.error('Status:', status);
            console.error('Error:', error);
            alert('Une erreur est survenue lors du chargement des détails.');
        }
    });
}





</script>
