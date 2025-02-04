@extends('layouts.app')

@section('content')
@php
  $doctorId = auth()->user()->getDoctorId();
  $permissionKey = 'availability.index';
  // Retrieve the permission with its related readable record
  $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
    ->with('readable')
    ->first();

  // Use the dynamic attribute for the display name; fall back to the key if not found
  $readablePermission = $permission ? $permission->display_name : $permissionKey;
@endphp

@if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ trans("lang.manageCalender") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ trans("lang.dashboard") }}</a>
                    </li>
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
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(function () {
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
                    <a class="nav-link active" id="availability-tab" data-toggle="tab"
                        onclick="window.location.href='/availability';" href="#availability" role="tab"
                        aria-controls="availability" aria-selected="true">{{ trans("lang.availability") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="breaks-tab" data-toggle="tab" href="#breaks" role="tab"
                        aria-controls="breaks" aria-selected="false">{{ trans("lang.breaks") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="holidays-tab" data-toggle="tab" href="#holidays" role="tab"
                        aria-controls="holidays" aria-selected="false">{{ trans("lang.holidays") }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="teleconsultations-tab"
                        onclick="window.location.href='/availability-tele';">{{ trans("lang.teleconsultations") }}</a>
                </li>



            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="scheduleTabsContent">
                <!-- Availability Tab -->
                <div class="tab-pane fade show active" id="availability" role="tabpanel"
                    aria-labelledby="availability-tab">
                    <form action="{{ route('availability.store') }}" method="POST">
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
                                @foreach([trans("lang.lundi"), trans("lang.mardi"), trans("lang.mercredi"), trans("lang.jeudi"), trans("lang.vendredi"), trans("lang.samedi"), trans("lang.dimanche")] as $day)
                                                                @php
                                                                    $dayData = $availability->firstWhere('day', $day);
                                                                    $isAvailable = $dayData['is_available'] ?? 0;
                                                                    $startAt = $dayData['start_at'] ?? '09:00';
                                                                    $endAt = $dayData['end_at'] ?? '17:00';
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center align-middle">
                                                                        <label class="switch">
                                                                            <input type="checkbox" id="toggleSwitch"
                                                                                name="availability[{{ $loop->index }}][is_available]" value="1" {{ $isAvailable ? 'checked' : '' }}>
                                                                            <span class="slider round"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td class="align-middle">
                                                                        <input type="text" class="form-control-plaintext text-center"
                                                                            name="availability[{{ $loop->index }}][day]" value="{{ $day }}" readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="time" class="form-control"
                                                                            name="availability[{{ $loop->index }}][from]" value="{{ $startAt }}"
                                                                            required>
                                                                    </td>
                                                                    <td>
                                                                        <input type="time" class="form-control"
                                                                            name="availability[{{ $loop->index }}][to]" value="{{ $endAt }}" required>
                                                                    </td>
                                                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button type="submit"
                            class="btn bg-{{setting('theme_color')}} mt-4">{{ trans("lang.saveDispo") }}</button>
                    </form>
                </div>

                <!-- Breaks Tab -->
                <div class="tab-pane fade" id="breaks" role="tabpanel" aria-labelledby="breaks-tab">
                    <form action="{{ route('urgency.store') }}" method="POST">
                        @csrf
                        <div>
                            <br>
                        </div>
                        <div id="break_single_day_section" class="form-group">
                            <label for="jour">{{ trans("lang.selectDate") }}</label>
                            <input type="date" class="form-control" name="jour">
                        </div>
                        <div id="break_every_day_section" class="form-row">
                            <div class="col-md-6">
                                <label>{{ trans("lang.from")  }}</label>
                                <input type="time" class="form-control" name="heurDebut" required>
                            </div>
                            <div class="col-md-6">
                                <label>{{ trans("lang.to")  }}</label>
                                <input type="time" class="form-control" name="heurFin" required>
                            </div>
                        </div>
                        <button type="button" onclick="window.location='{{ route('urgency.index') }}'"
                            class="btn btn-default mt-4">
                            {{ trans("lang.listUrgence") }}
                        </button>
                        <button type="submit"
                            class="btn bg-{{setting('theme_color')}} mt-4">{{ trans("lang.saveBreaks") }}</button>

                    </form>

                </div>
                <!-- holidays Tab -->
                <div class="tab-pane fade" id="holidays" role="tabpanel" aria-labelledby="holidays-tab">
                    <form action="{{ route('holidays.store') }}" method="POST">
                        @csrf
                        <div>
                            <br>
                        </div>
                        <div class="form-group">
                            <label for="holiday_type">{{ trans("lang.holidayType") }}</label>
                            <select class="form-control" id="holiday_type" name="type" onchange="toggleHolidayType()">
                                <option value="journée">{{ trans("lang.journee") }}</option>
                                <option value="période">{{ trans("lang.periode") }}</option>
                            </select>
                        </div>

                        <div id="holiday_range_section" class="form-row align-items-center">
                            <div class="col-md-6">
                                <label for="dateDebut" class="mb-0">{{ trans("lang.dateDebut") }}</label>
                                <input type="date" class="form-control" name="dateDebut" id="dateDebut">
                            </div>
                            <div class="col-md-6" id="fin" style="display: none;">
                                <label for="dateFin" class="mb-0">{{ trans("lang.dateFin") }}</label>
                                <input type="date" class="form-control" name="dateFin" id="dateFin">
                            </div>
                        </div>


                        <div class="form-group mt-3">
                            <label for="holiday_reason">{{ trans("lang.raison") }}</label>
                            <input type="text" class="form-control" name="raison" id="holiday_reason">
                        </div>
                        <button type="button" onclick="window.location='{{ route('vacance.index') }}'"
                            class="btn btn-default mt-4">
                            {{ trans("lang.listVacance") }}
                        </button>
                        <button type="submit"
                            class="btn bg-{{setting('theme_color')}} mt-4">{{trans("lang.saveHolidays") }}</button>
                    </form>
                </div>




                <script>
                    function toggleHolidayType() {
                        var holidayType = document.getElementById('holiday_type').value;
                        var dateFin = document.getElementById('fin');
                        var holidayRangeSection = document.getElementById('holiday_range_section');

                        if (holidayType === 'journée') {
                            dateFin.style.display = 'none';
                        } else if (holidayType === 'période') {
                            dateFin.style.display = 'block';
                        }
                    }

                    // Initial call to set visibility on page load
                    toggleHolidayType();
                </script>



            </div>
        </div>
    </div>
</div>
@else
  <div class="content-header">
    <div class="container-fluid">
    <div class="alert alert-danger">
      {{ __('Vous n’avez pas la permission (:permission) d’accéder à cette page.', ['permission' => $readablePermission]) }}
    </div>
    </div>
  </div>
@endif
@endsection
@section('styles')
<link href="{{ asset('css/availability.css') }}" rel="stylesheet">
@endsection
@section('scripts')
<script>
    function toggleHolidayType() {
        const holidayType = document.getElementById('holiday_type').value;
        const singleDaySection = document.getElementById('holiday_single_day_section');
        const rangeSection = document.getElementById('holiday_range_section');
        singleDaySection.style.display = holidayType === 'single_day' ? 'block' : 'none';
        rangeSection.style.display = holidayType === 'range' ? 'flex' : 'none';
    }

    function toggleBreakType() {
        console.log("Toggling break type...");
        const breakType = document.getElementById('break_type').value;

        // Sections for single day and every day
        const singleDaySection = document.getElementById('break_single_day_section');
        const everyDaySection = document.getElementById('break_every_day_section');

        // Show/hide sections based on the selected type
        if (breakType === 'single_day') {
            singleDaySection.style.display = 'block';
            everyDaySection.style.display = 'flex';
        } else if (breakType === 'every_day') {
            singleDaySection.style.display = 'none';
            everyDaySection.style.display = 'flex'; // Use flex for proper alignment
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const startAt = document.querySelector('input[name="start_at"]');
        const endAt = document.querySelector('input[name="end_at"]');

        console.log("Start At:", startAt.value);
        console.log("End At:", endAt.value);

        startAt.addEventListener('change', () => console.log("Updated Start At:", startAt.value));
        endAt.addEventListener('change', () => console.log("Updated End At:", endAt.value));
    });
</script>
@endsection