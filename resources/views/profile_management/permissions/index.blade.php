@extends('layouts.app')

@php
    $doctorId = auth()->user()->getDoctorId();
@endphp

@section('content')
@if(auth()->user()->hasPermissionInContext('patterns.index', $doctorId))
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-bold">{{trans('lang.pattern_plural') }}
                        <small class="mx-3">|</small><small>{{trans('lang.pattern_desc')}}</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item">
                            <a href="{{url('/dashboard')}}"><i class="fas fa-tachometer-alt"></i>
                                {{trans('lang.dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{!! route('patterns.index') !!}">{{trans('lang.pattern_plural')}}</a>
                        </li>
                        <li class="breadcrumb-item active">{{trans('lang.pattern_table')}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                    <div class="d-flex flex-row">
                        <li class="nav-item">
                            <a class="nav-link active" href="{!! url()->current() !!}"><i
                                    class="fa fa-list mr-2"></i>{{trans('lang.pattern_table')}}
                            </a>
                        </li>
                        @if(auth()->user()->hasPermissionInContext('patterns.create', $doctorId))
                            <li class="nav-item">
                                <a class="nav-link" href="{!! route('patterns.create') !!}"><i
                                        class="fa fa-plus mr-2"></i>{{trans('lang.pattern_create')}}
                                </a>
                            </li>
                        @endif
                    </div>
                </ul>
            </div>
            <div class="card-body">
                <div class="container">
                    <h2>Manage Permissions</h2>

                    <div class="form-group">
                        <label for="userDropdown">Sélectionnez un utilisateur</label>
                        <select id="userDropdown" class="form-control">
                            <option value="" selected disabled>Sélectionnez un utilisateur</option>
                            @foreach ($associatedUsers as $association)
                                <option value="{{ $association->user->id }}" {{ $selectedUserId == $association->user->id ? 'selected' : '' }}>
                                    {{ $association->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="permissionsTable">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Permission</th>
                                    <th>Role</th>
                                    <th>Assign</th>
                                </tr>
                            </thead>
                            <tbody id="permissionsBody">
                                @foreach ($permissions as $permission)
                                    <tr data-permission-id="{{ $permission->id }}">
                                        <td>{{ $permission->name }}</td>
                                        <td>
                                            @foreach ($associatedUsers as $association)
                                                <span
                                                    class="badge badge-secondary">{{ $association->user->roles->first()->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <label class="checkbox-container">
                                                <input type="checkbox" class="permissionCheckbox"
                                                    data-permission-id="{{ $permission->id }}">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="pagination-wrapper">
                            {{ $permissions->appends(['selected_user' => $selectedUserId])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="content-header">
        <div class="container-fluid">
            <div class="alert alert-danger">
                {{ __('Vous n’avez pas la permission d’accéder à cette page.') }}
            </div>
        </div>
    </div>
@endif
@endsection


<style>
    /* Scrollable Table */
    .scrollable-table {
        max-height: 400px;
        /* Adjust height as needed */
        overflow-y: auto;
        overflow-x: hidden;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .scrollable-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .scrollable-table th,
    .scrollable-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .scrollable-table th {
        background-color: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .scrollable-table tr:hover {
        background-color: #f1f1f1;
    }

    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    /* Enhanced Table Styling */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .table tr:hover {
        background-color: #f1f1f1;
    }

    /* Enhanced Checkbox Styling */
    .checkbox-container {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 16px;
        user-select: none;
    }

    .checkbox-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: #eee;
        border-radius: 4px;
    }

    .checkbox-container:hover input~.checkmark {
        background-color: #ccc;
    }

    .checkbox-container input:checked~.checkmark {
        background-color: #2196F3;
    }

    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    .checkbox-container input:checked~.checkmark:after {
        display: block;
    }

    .checkbox-container .checkmark:after {
        left: 7px;
        top: 3px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    /* Badge Styling */
    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const userDropdown = document.getElementById('userDropdown');
        const permissionsTable = document.getElementById('permissionsTable');
        const permissionsBody = document.getElementById('permissionsBody');

        userDropdown.addEventListener('change', function () {
            const userId = this.value;
            if (!userId) {
                permissionsTable.style.display = 'none';
                permissionsBody.innerHTML = '';
                return;
            }

            // Fetch roles and permissions for the selected user
            fetch('{{ route('Doctors_permissions.fetchUserRoles') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    doctor_id: '{{ $doctorId }}',
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Populate permissions table dynamically
                    permissionsTable.style.display = 'block';
                    permissionsBody.innerHTML = '';

                    const roles = data.roles;
                    const permissions = data.permissions;
                    const existingPermissions = data.existingPermissions;

                    permissions.forEach(permission => {
                        const roleName = roles.length > 0 ? roles[0].name : 'No Role';
                        const isChecked = existingPermissions.includes(permission.id) ? 'checked' : '';

                        permissionsBody.innerHTML += `
                        <tr data-permission-id="${permission.id}">
                            <td>${permission.name}</td>
                            <td>${roleName}</td>
                            <td>
                                <label class="checkbox-container">
                                    <input type="checkbox" class="permissionCheckbox" data-permission-id="${permission.id}" ${isChecked}>
                                    <span class="checkmark"></span>
                                </label>
                            </td>
                        </tr>
                    `;
                    });
                });
        });

        permissionsBody.addEventListener('change', function (event) {
            if (event.target.classList.contains('permissionCheckbox')) {
                const checkbox = event.target;
                const permissionId = checkbox.dataset.permissionId;
                const userId = userDropdown.value;
                const doctorId = '{{ $doctorId }}';

                fetch('{{ route('Doctors_permissions.update') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        permission_id: permissionId,
                        user_id: userId,
                        doctor_id: doctorId,
                        is_checked: checkbox.checked,
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('An error occurred while updating permissions.');
                        }
                    });
            }
        });
    });
</script>