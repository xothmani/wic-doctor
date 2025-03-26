<div class='btn-group btn-group-sm'>
    @can('photos_cabinet.show')
        <a href="{{ route('generateDoctorUrl', $id) }}" class="btn btn-link" target="_blank">
             <i class="fas fa-globe"></i> <!-- Icône de site web -->
        </a>

    @endcan
  

</div>



