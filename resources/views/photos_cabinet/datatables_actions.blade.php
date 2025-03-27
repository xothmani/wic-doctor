<div class='btn-group btn-group-sm'>
    @can('photos_cabinet.show')
        <a class="btn btn-link" href="{{ route('photos_cabinet.show', $id) }}" target="_blank">
            <i class="fas fa-eye"></i> <!-- Icône de site web -->
        </a>
    @endcan
</div>
