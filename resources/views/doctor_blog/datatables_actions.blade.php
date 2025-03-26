<div class='btn-group btn-group-sm'>
    <!-- Bouton d'icône pour afficher les détails -->
    @can('doctor_blog.show')
        <a href="{{ route('doctor_blog.show', $id) }}" class="btn btn-link">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('doctor_blog.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.doctor_blog_edit') }}" href="{{ route('doctor_blog.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> 
        </a> 
    @endcan
    @can('doctor_blog.destroy')
    <button type="button" class="btn btn-link text-danger" onclick="confirmDelete('{{ $id }}')">
        <i class="fas fa-trash"></i>
    </button>
    <form id="delete-form-{{ $id }}" action="{{ route('doctor_blog.destroy', $id) }}" method="POST" style="display: none;">
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

