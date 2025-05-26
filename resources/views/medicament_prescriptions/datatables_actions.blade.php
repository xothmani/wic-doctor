

<div class='btn-group'>
    @if($status_medicament === 'en cours')
        <form action="{{ route('medicament_prescriptions.markAsTreated', $id) }}" method="POST" onsubmit="return confirm('Confirmer le changement de statut ?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fas fa-check"></i> Traiter
            </button>
        </form>
    @endif
</div>

