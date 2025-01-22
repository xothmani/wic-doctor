@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-bold">{{ trans('lang.manage_permissions') }}</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">{{ trans('lang.permissions_management') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('lang.role_name') }}</th>
                            <th>{{ trans('lang.profile_permissions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    <!-- Form to update permissions -->
                                    <form method="POST" action="{{ route('Doctors_permissions.store') }}">
                                        @csrf
                                        <input type="hidden" name="role_id" value="{{ $role->id }}">

                                        <!-- Display permissions in a grid -->
                                        <div class="permissions-grid">
                                            @foreach ($profilePermissions as $profilePermission)
                                                <div class="permission-item">
                                                    <label class="form-check-label">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $profilePermission->id }}"
                                                            {{ isset($roleProfilePermissions[$profilePermission->id]) ? 'checked' : '' }}
                                                        >
                                                        {{ $profilePermission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Save button -->
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">{{ trans('lang.save') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        padding: 10px;
    }
    .permission-item {
        display: flex;
        align-items: center;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
    }
    .permission-item input[type="checkbox"] {
        margin-right: 10px;
    }
</style>
