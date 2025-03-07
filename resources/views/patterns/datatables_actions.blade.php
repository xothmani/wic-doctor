<div class='btn-group btn-group-sm'>
    @php
        $doctorId = auth()->user()->getActiveDoctorId();
    @endphp

    @if(auth()->user()->hasPermissionInContext('patterns.show', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_details') }}"
            href="{{ route('patterns.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patterns.edit', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.pattern_edit') }}"
            href="{{ route('patterns.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patterns.destroy', $doctorId))
        {!! Form::open(['route' => ['patterns.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endif
</div>