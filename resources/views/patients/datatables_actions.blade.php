<div class='btn-group btn-group-sm'>
<!--     @can('consultations.create')
    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_consultation') }}" href="{{ route('consultations.create', ['patient_id' => $id]) }}" class='btn btn-link'>
        <i class="fas fa-plus"></i> 
    </a>
    @endcan -->
    @can('fiche.show')
    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_fiche') }}" href="{{ route('fiche.show', $id) }}" class='btn btn-link'>
        <i class="fas fa-file-alt"></i> <!-- Icône pour la fiche -->
    </a>
@endcan


    @can('patients.show')
    <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.view_details')}}" href="{{ route('patients.show', $id) }}" class='btn btn-link'>
        <i class="fas fa-eye"></i> 
    </a> 
    @endcan

    @can('patients.edit')
    <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.patient_edit')}}" href="{{ route('patients.edit', $id) }}" class='btn btn-link'>
        <i class="fas fa-edit"></i> 
    </a> 
    @endcan


    @can('patients.email') <!-- Assurez-vous que l'autorisation est correctement définie -->
    <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.send_email') }}" href="{{ route('patients.email', $id) }}" class='btn btn-link'>
        <i class="fas fa-envelope"></i> 
    </a>
    @endcan


    @can('patients.whatsapp')
<a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.send_whatsapp') }}" href="{{ route('patients.whatsapp', $id) }}" class='btn btn-link'>
    <i class="fab fa-whatsapp"></i> 
</a>
@endcan
@can('patients.destroy') 
    {!! Form::open(['route' => ['patients.destroy', $id], 'method' => 'delete']) !!} 
    {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer ce patient ?')" ]) !!} 
    {!! Form::close() !!} 
    @endcan


</div>
