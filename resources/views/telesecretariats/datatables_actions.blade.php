
<div class='btn-group btn-group-sm'>

 <!-- Bouton d'icône pour afficher les détails -->
    @can('telesecretariats.show')
        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#telesecretariatModal-{{ $id }}" title="{{ trans('lang.telesecretariat_view') }}" onclick="loadTelesecretariatDetails('{{ $id }}')">
            <i class="fas fa-eye"></i>
        </button>

    @endcan

    @can('telesecretariats.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.telesecretariat_edit') }}" href="{{ route('telesecretariats.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> 
        </a> 
    @endcan
    @can('telesecretariats.destroy')
    <button type="button" class="btn btn-link text-danger" onclick="confirmDelete('{{ $id }}')">
        <i class="fas fa-trash"></i>
    </button>
    <form id="delete-form-{{ $id }}" action="{{ route('telesecretariats.destroy', $id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endcan

<script>
    function confirmDelete(id) {
        if (confirm('{{ trans('lang.are_you_sure') }}')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
</script>


</div>
<!-- Modal pour afficher les détails -->
<div class="modal fade" id="telesecretariatModal-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="telesecretariatModalLabel-{{ $id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="telesecretariatModalLabel-{{ $id }}">{{ trans('lang.telesecretariat_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="telesecretariatDetailsBody-{{ $id }}">
                <!-- Les détails seront remplis ici via JavaScript -->
                <p><strong>{{ trans('lang.telesecretariat_nom_centre') }}:</strong> <span id="nomCentre-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.responsable') }}:</strong> <span id="responsable-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.telesecretariat_phone_number') }}:</strong> <span id="phone-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.telesecretariat_email') }}:</strong> <span id="email-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.telesecretariat_adresse') }}:</strong> <span id="adresse-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.telesecretariat_etat') }}:</strong> <span id="etat-{{ $id }}"></span></p>
                <p><strong>{{ trans('lang.telesecretariat_description') }}:</strong> <span id="description-{{ $id }}"></span></p>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('lang.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
   function loadTelesecretariatDetails(id) {
    fetch(`/telesecretariats/show/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.telesecretariat && data.user) {
                // Utilisez des ID uniques pour chaque modal
                document.getElementById(`nomCentre-${id}`).textContent = data.telesecretariat.nomCentre || 'N/A';
                document.getElementById(`adresse-${id}`).textContent = data.telesecretariat.adresse || 'N/A';
                document.getElementById(`etat-${id}`).textContent = data.telesecretariat.etat || 'N/A';
                document.getElementById(`description-${id}`).textContent = data.telesecretariat.description
    ? data.telesecretariat.description.replace(/<\/?[^>]+(>|$)/g, "")
    : 'N/A';
                document.getElementById(`responsable-${id}`).textContent = `${data.user.name} ${data.user.lastname}` || 'N/A';
                document.getElementById(`email-${id}`).textContent = data.user.email || 'N/A';
                document.getElementById(`phone-${id}`).textContent = data.user.phone_number || 'N/A';
            } else {
                console.error('Données manquantes pour telesecretariat ou utilisateur');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des détails:', error);
        });
}

</script>
