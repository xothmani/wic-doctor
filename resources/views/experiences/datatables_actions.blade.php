<div class='btn-group btn-group-sm'>
    @php
        $doctorId = auth()->user()->getDoctorId();
    @endphp

    @if(auth()->user()->hasPermissionInContext('experiences.edit', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{trans('lang.experience_edit')}}"
            href="{{ route('experiences.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('experiences.destroy', $doctorId))
        {!! Form::open(['route' => ['experiences.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Are you sure?')"
        ]) !!}
        {!! Form::close() !!}
    @endif
</div>