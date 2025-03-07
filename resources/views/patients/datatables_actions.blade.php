<div class='btn-group btn-group-sm'>
    @php
        $doctorId = auth()->user()->getActiveDoctorId(); // Fetch the associated doctor ID for the logged-in user
    @endphp

    @if(auth()->user()->hasPermissionInContext('consultations.create', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.add_consultation') }}"
            href="{{ route('consultations.create', ['patient_id' => $id]) }}" class='btn btn-link'>
            <i class="fas fa-plus"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('fiche.show', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_fiche') }}"
            href="{{ route('fiche.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-file-alt"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.show', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.view_details') }}"
            href="{{ route('patients.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.edit', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.patient_edit') }}"
            href="{{ route('patients.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.email', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.send_email') }}"
            href="{{ route('patients.email', $id) }}" class='btn btn-link'>
            <i class="fas fa-envelope"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.whatsapp', $doctorId))
        <a data-toggle="tooltip" data-placement="left" title="{{ trans('lang.send_whatsapp') }}"
            href="{{ route('patients.whatsapp', $id) }}" class='btn btn-link'>
            <i class="fab fa-whatsapp"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.destroy', $doctorId))
        {!! Form::open(['route' => ['patients.destroy', $id], 'method' => 'delete']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer ce patient ?')"
        ]) !!}
        {!! Form::close() !!}
    @endif

</div>