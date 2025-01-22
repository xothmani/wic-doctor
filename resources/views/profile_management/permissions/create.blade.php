@extends('layouts.settings.default')

@section('settings_title', trans('lang.permission_create'))

@section('settings_content')
@include('flash::message')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">{{ trans('lang.permission_create') }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('Doctors_permissions.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">{{ trans('lang.permission_name') }}</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="guard_name">{{ trans('lang.permission_guard_name') }}</label>
                <input type="text" name="guard_name" class="form-control" value="web" required>
            </div>
            <div class="form-group">
                <label for="role_id">{{ trans('lang.role_plural') }}</label>
                <select name="role_id" class="form-control" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ trans('lang.save') }}</button>
            <a href="{{ route('Doctors_permissions.index') }}" class="btn btn-default">{{ trans('lang.cancel') }}</a>
        </form>
    </div>
</div>
@endsection