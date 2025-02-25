<div class='btn-group btn-group-sm'>

    @can('photos_cabinet.show')
        <!-- Rediriger vers l'URL générée avec l'ID du médecin -->
        <a href="{{ route('generateDoctorUrl', ['doctorId' => $doctor->id]) }}" class="btn btn-link" target="_blank">
            <i class="fas fa-globe"></i> <!-- Icône de site web -->
        </a>
    @endcan

    



</div>

