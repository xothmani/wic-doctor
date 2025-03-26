@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ trans("lang.manageCalender") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans("lang.dashboard") }}</a></li>
                    <li class="breadcrumb-item active">{{ trans("lang.manageCalender") }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@if ($errors->has('error'))
    <div id="error-alert" class="alert alert-danger">
        {{ $errors->first('error') }}
    </div> 
@endif

@if(session('success'))
    <div id="success-alert" class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            const errorAlert = document.getElementById('error-alert');
            const successAlert = document.getElementById('success-alert');

            if (errorAlert) {
                errorAlert.style.transition = "opacity 1s";
                errorAlert.style.opacity = "0";
                setTimeout(() => errorAlert.remove(), 1000); // Supprime l'élément après l'animation
            }

            if (successAlert) {
                successAlert.style.transition = "opacity 1s";
                successAlert.style.opacity = "0";
                setTimeout(() => successAlert.remove(), 1000); // Supprime l'élément après l'animation
            }
        }, 5000); // 5 secondes
    });
</script>


<div class="content">
    <div class="card shadow-sm">
       
        <div class="card-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="scheduleTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link " id="availability-tab" data-toggle="tab"  onclick="window.location.href='/availability';"href="#availability" role="tab" aria-controls="availability" >{{ trans("lang.availability") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="breaks-tab" data-toggle="tab"  onclick="window.location.href='/availability'"; href="#breaks" role="tab" aria-controls="breaks" aria-selected="false">{{ trans("lang.breaks") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="holidays-tab" data-toggle="tab"  onclick="window.location.href='/availability'"; href="#holidays" role="tab" aria-controls="holidays" aria-selected="false">{{ trans("lang.holidays") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" id="teleconsultations-tab" onclick="window.location.href='/availability-tele';" >{{ trans("lang.teleconsultations") }}</a>
                </li>


             
            </ul>
                <!-- Availability Tab -->
                <div class="tab-pane fade show active" id="availability" role="tabpanel" aria-labelledby="availability-tab">
                        <form action="{{ route('availabilityTele.store') }}" method="POST">
                            @csrf
                            <div>
                                <br>
                            </div>

        <!-- Pause Times and Per Patient Time -->
        <div class="form-group d-flex align-items-baseline">

        {!! Form::label('session_duration', trans("lang.sessionDuration"), ['class' => 'col-md-2 control-label text-md-right']) !!}

        <div class="col-md-2">
            {!! Form::text('session_duration', $sessionDurationFormatted, ['class' => 'form-control', 'placeholder' => 'e.g., 01:00 (hh:mm)']) !!}

        </div>

            {!! Form::label('pause_from', trans("lang.debutPause"), ['class' => 'col-md-2 control-label text-md-right']) !!}
            <div class="col-md-2">
            {!! Form::time('pause_from', $pauseFrom ?? '', ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
        </div>

            {!! Form::label('pause_to', trans("lang.finPause"), ['class' => 'col-md-2 control-label text-md-right']) !!}
            <div class="col-md-2">
            {!! Form::time('pause_to', $pauseTo ?? '', ['class' => 'form-control', 'placeholder' => 'HH:MM']) !!}
        </div>

        
        </div>
        <!-- Availability Table -->
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 10%;">{{ trans("lang.availability") }}</th>
                    <th style="width: 30%;">{{ trans("lang.jourDispo") }}</th>
                    <th style="width: 30%;">{{ trans("lang.from") }}</th>
                    <th style="width: 30%;">{{ trans("lang.to") }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach([trans("lang.lundi"), trans("lang.mardi"),trans("lang.mercredi"), trans("lang.jeudi"), trans("lang.vendredi"), trans("lang.samedi"), trans("lang.dimanche")] as $day)
            @php
                        $dayData = $availability->firstWhere('day', $day);
                        $isAvailable = $dayData['is_available'] ?? 0;
                        $startAt = $dayData['start_at'] ?? '18:00';
                        $endAt = $dayData['end_at'] ?? '22:00';
                    @endphp
                    <tr>
                        <td class="text-center align-middle">
                            <label class="switch">
                                <input type="checkbox" id="toggleSwitch" name="availability[{{ $loop->index }}][is_available]" value="1" {{ $isAvailable ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control-plaintext text-center" name="availability[{{ $loop->index }}][day]" value="{{ $day }}" readonly>
                        </td>
                        <td>
                            <input type="time" class="form-control" name="availability[{{ $loop->index }}][from]" value="{{ $startAt }}" required>
                        </td>
                        <td>
                            <input type="time" class="form-control" name="availability[{{ $loop->index }}][to]" value="{{ $endAt }}" required>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn bg-{{setting('theme_color')}} mt-4">{{ trans("lang.saveDispo") }}</button>
        </form>
    </div>
          
        </div>
    </div>
</div>
@endsection
@section('styles')
<link href="{{ asset('css/availability.css') }}" rel="stylesheet">
@endsection
