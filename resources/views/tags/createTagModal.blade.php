<div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-labelledby="createTagModalLabel" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center" role="document" style="min-height: 100vh;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTagModalLabel">{{ trans('lang.tag_create') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('tags.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">{{ trans('lang.tag_name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('lang.tag_name_placeholder') }}" required>
                    </div>

                    <!-- Sélecteur de pays avec liste déroulante -->
                    <div class="form-group">
                        <label for="country">{{ trans('lang.country') }}</label>
                        <select class="form-control" id="country" name="country" required>
                            <option value="" disabled selected>{{ trans('lang.select_country') }}</option>
                            <option value="Tunisie">Tunisie</option>
                            <option value="France">France</option>
                        </select>

                    </div>

                    <!-- Sélecteur de spécialité, désactivé par défaut -->
                    <div class="form-group">
                        <label for="speciality">{{ trans('lang.speciality') }}</label>
                        <select class="form-control" id="speciality" name="speciality" disabled required>
                            <option value="">{{ trans('lang.select_speciality') }}</option>
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

<!-- Script pour activer le select et charger les spécialités -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const specialitySelect = document.getElementById('speciality');
    const countrySelect = document.getElementById('country');

    // Fonction pour mettre à jour les spécialités selon le pays sélectionné
    function updateSpecialities() {
        const selectedCountry = countrySelect.value;

        if (!selectedCountry) {
            specialitySelect.disabled = true;
            specialitySelect.innerHTML = '<option value="">{{ trans("lang.select_speciality") }}</option>';
            return;
        }

        specialitySelect.disabled = false; // Active le select

        fetch(`/specialitiesByPays?pays=${selectedCountry}`)
            .then(response => response.json())
            .then(data => {
                specialitySelect.innerHTML = '<option value="">{{ trans("lang.select_speciality") }}</option>';
                if (data.length === 0) {
                    specialitySelect.innerHTML = '<option value="" disabled>Aucune spécialité disponible pour ce pays</option>';
                    return;
                }
                data.forEach(speciality => {
                    const option = document.createElement('option');
                    option.value = speciality.id;
                    option.textContent = speciality.name.fr;
                    specialitySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des spécialités:', error);
            });
    }

    // Ajout de l'événement pour le changement de pays
    countrySelect.addEventListener('change', updateSpecialities);

    // Vérification initiale si un pays est déjà sélectionné
    updateSpecialities();
});
</script>
