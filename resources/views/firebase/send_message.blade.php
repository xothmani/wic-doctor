@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Send Message via Firebase</h2>
    <form action="{{ route('firebase.sendMessage') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">Choose a User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="4" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>
@endsection
