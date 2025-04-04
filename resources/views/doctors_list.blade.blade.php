@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Liste des Médecins</h2>
        <ul class="list-group">
            @foreach ($doctors as $doctor)
                <li class="list-group-item">
                    <a href="{{ url('/chat/' . auth()->id() . '/' . $doctor->user_id) }}">
                        {{ $doctor->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
