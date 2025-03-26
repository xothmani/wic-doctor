<div class='btn-group btn-group-sm'>
    @can('assurances.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.assurance_edit')}}" href="{{ route('assurances.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i> </a> @endcan

    @can('assurances.destroy') {!! Form::open(['route' => ['assurances.destroy', $id], 'method' => 'delete']) !!} {!! Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Are you sure?')" ]) !!} {!! Form::close() !!} @endcan
</div>
