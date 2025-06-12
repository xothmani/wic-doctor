<div class="table-responsive">
    <table class="table table-striped table-bordered" id="patient-files-table">
        <thead class="thead-light">
            <tr>
                <th>{{trans('lang.file_name')}}</th>
                <th>{{trans('lang.description')}}</th>
                <th>{{trans('lang.uploaded_by')}}</th>
                <th>{{trans('lang.uploaded_at')}}</th>
                <th>{{trans('lang.file_size')}}</th>
                <th>{{trans('lang.actions')}}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($files as $file)
                <tr>
                    <td>
                        <i class="fas fa-file mr-2 text-primary"></i>
                        <span class="font-weight-medium">{{ $file->file_name }}</span>
                    </td>
                    <td>
                        <span class="text-muted">{{ $file->description ?? trans('lang.no_description') }}</span>
                    </td>
                    <td>
                        <i class="fas fa-user-md mr-1"></i>
                        {{ $file->uploader->name }}
                    </td>
                    <td>
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $file->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @if(isset($file->file_size) && $file->file_size)
                            <span class="badge badge-info">{{ number_format($file->file_size / 1024, 2) }} KB</span>
                        @else
                            <span class="text-muted">{{trans('lang.unknown')}}</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            @if(auth()->user()->hasPermissionInContext('patient_files.show', auth()->user()->getDoctorId()))
                                <a data-toggle="tooltip" 
                                   title="{{trans('lang.view_details')}}"
                                   href="{{ route('patient_files.show', [$patient, $file]) }}" 
                                   class='btn btn-link btn-sm'>
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                            @endif
                            @if(auth()->user()->hasPermissionInContext('patient_files.download', auth()->user()->getDoctorId()))
                                <a data-toggle="tooltip" 
                                   title="{{trans('lang.download')}}"
                                   href="{{ route('patient_files.download', [$patient, $file]) }}" 
                                   class='btn btn-link btn-sm'>
                                    <i class="fas fa-download text-success"></i>
                                </a>
                            @endif
                            @if(auth()->user()->hasPermissionInContext('patient_files.destroy', auth()->user()->getDoctorId()))
                                <form action="{{ route('patient_files.destroy', [$patient, $file]) }}" 
                                      method="POST" 
                                      style="display:inline;" 
                                      onsubmit="return confirm('{{trans('lang.confirm_delete_file')}}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-link btn-sm" 
                                            data-toggle="tooltip" 
                                            title="{{trans('lang.delete')}}">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">{{trans('lang.no_files_found')}}</p>
                            @if(auth()->user()->hasPermissionInContext('patient_files.create', auth()->user()->getDoctorId()))
                                <a href="{{ route('patient_files.create', $patient) }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-plus mr-1"></i>{{trans('lang.upload_first_file')}}
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(isset($files) && $files instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-center">
        {{ $files->links() }}
    </div>
@endif