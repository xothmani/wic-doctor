<div class='btn-group btn-group-sm'>
@can('doctor_requests.createUserFromDoctorRequest') <!-- Replace with the correct permission -->
        <a href="{{ route('doctor_requests.createUserFromDoctorRequest', ['id' => $id]) }}"  
           data-toggle="tooltip" 
           data-placement="left" 
           title="{{ trans('lang.create_user') }}" 
           class='btn btn-link text-success'>
           <i class="fas fa-user-plus"></i> 
        </a>
    @endcan
</div>
