<div class='btn-group btn-group-sm'>
    @can('patterns.show')
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_details') }}" href="{{ route('patterns.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i>
        </a>
    @endcan

    @can('patterns.edit')
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.pattern_edit') }}" href="{{ route('patterns.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    @endcan

    @can('patterns.destroy')
        {!! Form::open(['route' => ['patterns.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endcan
</div>
