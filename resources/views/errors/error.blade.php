@extends('layouts.app')

@section('title', __('Accès interdit'))

@section('content')
<div class="container my-5">
    <div class="alert alert-danger text-center">
        <h1 class="display-4">{{ __('Accès interdit') }}</h1>
        <p class="lead">
            {{-- This will display the error message passed from abort() --}}
            {{ $exception->getMessage() }}
        </p>
        <p>
            {{-- Logout button --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-primary">
                {{ __('Se déconnecter') }}
            </button>
        </form>
        </p>
    </div>
</div>
@endsection