<div class='btn-group btn-group-sm'>

    @can('Doctors_users.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.doctor_user_edit')}}"
            href="{{ route('Doctors_users.edit', $id) }}" class='btn btn-link'>
    <i class="fas fa-edit"></i> </a> @endcan

    @can('Doctors_users.destroy') {!! Form::open(['route' => ['Doctors_users.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Êtes-vous sûr ?')"]) !!}
    {!! Form::close() !!} @endcan
</div>