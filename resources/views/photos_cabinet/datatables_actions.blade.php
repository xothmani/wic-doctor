<div class='btn-group btn-group-sm'>
    <!-- Bouton d'icône pour afficher les détails -->
    @can('photos_cabinet.show')
        <a href="{{ route('photos_cabinet.show', $id) }}" class="btn btn-link">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    



</div>

