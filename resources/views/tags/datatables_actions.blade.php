
<div class='btn-group btn-group-sm'>

 <!-- Bouton d'icône pour afficher les détails -->


    @can('tags.edit')
        <button type="button" class="btn btn-link" data-toggle="modal" data-target="#tagModal-{{ $id }}" title="{{ trans('lang.tag_edit') }}" onclick="editTag('{{ $id }}')">
            <i class="fas fa-edit"></i>
        </button>
    @endcan


    @can('tags.destroy')
    <button type="button" class="btn btn-link text-danger" onclick="confirmDelete('{{ $id }}')">
        <i class="fas fa-trash"></i>
    </button>
    <form id="delete-form-{{ $id }}" action="{{ route('tags.destroy', $id) }}" method="POST" style="display: none;">
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
    function editTag(tagId) {
    $.ajax({
        url: '/tags/' + tagId + '/edit',
        method: 'GET',
        success: function(response) {
            console.log(response);

            if (response && response.name && response.specialities && response.country) {
                // Remplir les champs avec les ID uniques
                $('#tagName-' + tagId).val(response.name);
                $('#editCountry-' + tagId).val(response.country).trigger('change');

                // Charger les spécialités initiales
                updateEditSpecialities(response.country, response.speciality_id, tagId);

                // Attacher l'événement change pour le pays
                $('#editCountry-' + tagId).off('change').on('change', function() {
                    updateEditSpecialities($(this).val(), null, tagId);
                });
            } else {
                alert('Les données du tag ou des spécialités sont manquantes.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors du chargement des détails.');
        }
    });
}
function updateEditSpecialities(selectedCountry, selectedSpecialityId, tagId) {
    if (!selectedCountry) {
        $('#speciality-' + tagId).empty().append('<option value="">{{ trans("lang.select_speciality") }}</option>').prop('disabled', true);
        return;
    }

    fetch(`/specialitiesByPays?pays=${selectedCountry}`)
        .then(response => response.json())
        .then(data => {
            $('#speciality-' + tagId).empty().append('<option value="">{{ trans("lang.select_speciality") }}</option>');
            if (data.length === 0) {
                $('#speciality-' + tagId).append('<option value="" disabled>Aucune spécialité disponible pour ce pays</option>').prop('disabled', true);
                return;
            }
            data.forEach(speciality => {
                $('#speciality-' + tagId).append(
                    `<option value="${speciality.id}" ${speciality.id == selectedSpecialityId ? 'selected' : ''}>${speciality.name.fr}</option>`
                );
            });
            $('#speciality-' + tagId).prop('disabled', false);
        })
        .catch(error => console.error('Erreur lors du chargement des spécialités:', error));
}
// Déclencher le chargement des spécialités quand le pays change dans le modal d'édition
$('#editCountry-' + tagId).change(function() {
    updateEditSpecialities($(this).val(), null, tagId);
});


</script>


</div>
<!-- Modal d'édition pour le tag -->
<!-- Modal d'édition pour le tag -->
<div class="modal fade" id="tagModal-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="tagModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tagModalLabel">{{ trans('lang.tag_edit') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulaire pour éditer le tag -->
                <!-- Formulaire pour éditer le tag -->
<form id="editTagForm" method="POST" action="{{ route('tags.update', $id) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="tagName">{{ trans('lang.tag_name') }}</label>
        <input type="text" class="form-control" id="tagName-{{ $id }}" name="name" required>
    </div>

    <div class="form-group">
    <label for="editCountry">{{ trans('lang.country') }}</label>
    <select class="form-control" id="editCountry-{{ $id }}" name="country" required>
        <option value="" disabled selected>{{ trans('lang.select_country') }}</option>
        <option value="Tunisie">Tunisie</option>
        <option value="France">France</option>
    </select>
</div>


    <div class="form-group">
        <label for="speciality">{{ trans('lang.speciality') }}</label>
        <select class="form-control" id="speciality-{{ $id }}" name="speciality_id" required>
            <option value="" disabled selected>{{ trans('lang.select_speciality') }}</option>
            <!-- Les options des spécialités seront ajoutées dynamiquement avec JavaScript -->
        </select>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('lang.close') }}</button>
        <button type="submit" class="btn bg-{{setting('theme_color')}}">{{ trans('lang.save') }}</button>

    </div>
</form>

            </div>
        </div>
    </div>
</div>