@extends('layouts.app')

@section('content')
    @php
        $doctorId = auth()->user()->getDoctorId();
        $permissionKey = 'availability.index';
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionKey)->with('readable')->first();
        $readablePermission = $permission ? $permission->display_name : $permissionKey;
        $doctor = auth()->user()->doctor;
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

        @if(session('duration_changed'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h5><i class="icon fas fa-info"></i> {{ trans('messages.duration_changed_notice') }}</h5>
                @if(session('has_existing_appointments'))
                    {{ trans('messages.duration_changed_future', [
                                'old' => session('old_duration'),
                                'new' => session('new_duration'),
                                'type' => ucfirst(session('affected_type'))
                            ]) }}
                @else
                    {{ trans('messages.appointments_adjusted_warning', [
                                'old' => session('old_duration'),
                                'new' => session('new_duration'),
                                'type' => ucfirst(session('affected_type'))
                            ]) }}
                @endif
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('appointments_adjusted'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ trans('messages.appointments_adjusted_warning') }}
                <br>
                {{ trans('messages.appointments_adjusted') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                setTimeout(function () {
                    document.getElementById('error-alert')?.remove();
                    document.getElementById('success-alert')?.remove();
                }, 5000);
            });
        </script>
        @if ($currentMode === 'open')
            <div class="content">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Type Tabs Navigation -->
                        <ul class="nav nav-tabs mb-3" id="typeTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="cabinet-tab" data-toggle="tab" href="#cabinet" role="tab">
                                    <i class="fas fa-hospital"></i> {{ trans('lang.cabinet') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="teleconsultation-tab" data-toggle="tab" href="#teleconsultation" role="tab">
                                    <i class="fas fa-video"></i> {{ trans('lang.teleconsultation') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="home-visit-tab" data-toggle="tab" href="#home_visit" role="tab">
                                    <i class="fas fa-home"></i> {{ trans('lang.home_visit') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="closures-tab" data-toggle="tab" href="#closures" role="tab">
                                    <i class="fas fa-clock"></i> {{ trans('lang.early_closures') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vacation-tab" data-toggle="tab" href="#vacation" role="tab">
                                    <i class="fas fa-umbrella-beach"></i> {{ trans('lang.vacation') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="substitute-tab" data-toggle="tab" href="#substitute" role="tab">
                                    <i class="fas fa-user-md"></i> {{ trans('lang.substitute') }}
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="typeTabsContent">
                            @foreach(['cabinet', 'teleconsultation', 'home_visit'] as $type)
                                    <div class="tab-pane fade {{ $type === 'cabinet' ? 'show active' : '' }}" id="{{ $type }}"
                                        role="tabpanel">
                                        <form action="{{ route('availability.store.open') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="type" value="{{ $type }}">

                                            <!-- Session Duration -->
                                            <div class="form-group d-flex align-items-baseline mb-4">
                                                {!! Form::label('session_duration', trans("lang.sessionDuration") . ' *', ['class' => 'col-md-2 control-label text-md-right']) !!}
                                                <div class="col-md-2">
                                                    {!! Form::text('session_duration', $sessionDurations[$type], [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => 'e.g., 01:00 (hh:mm)'
                                ]) !!}
                                                </div>
                                            </div>

                                            <!-- Availability Table -->
                                            <table class="table table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 10%;">{{ trans("lang.availability") }}</th>
                                                        <th style="width: 20%;">{{ trans("lang.jourDispo") }}</th>
                                                        <th style="width: 20%;">{{ trans("lang.from") }} *</th>
                                                        <th style="width: 20%;">{{ trans("lang.to") }} *</th>
                                                        <th style="width: 30%;">{{ trans("lang.breaks") }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(['Lundi' => 'monday', 'Mardi' => 'tuesday', 'Mercredi' => 'wednesday', 'Jeudi' => 'thursday', 'Vendredi' => 'friday', 'Samedi' => 'saturday', 'Dimanche' => 'sunday'] as $frenchDay => $englishDay)
                                                                        @php

                                                                            $dayData = $availabilities[$type][$englishDay] ?? ['is_available' => 0];
                                                                            $isAvailable = $dayData['is_available'] ?? 0;
                                                                            $startAt = $dayData['start_at'] ?? '09:00';
                                                                            $endAt = $dayData['end_at'] ?? '17:00';
                                                                            $pauseFrom = $dayData['pause_from'] ?? '';
                                                                            $pauseTo = $dayData['pause_to'] ?? '';
                                                                        @endphp
                                                                        <tr>
                                                                            <td class="text-center align-middle">
                                                                                <label class="switch">
                                                                                    <input type="checkbox" name="availability[{{ $loop->index }}][is_available]"
                                                                                        value="1" {{ $isAvailable ? 'checked' : '' }}>
                                                                                    <span class="slider round"></span>
                                                                                </label>
                                                                            </td>
                                                                            <td class="align-middle">
                                                                                <input type="hidden" name="availability[{{ $loop->index }}][day]"
                                                                                    value="{{ $frenchDay }}">
                                                                                {{ $frenchDay }}
                                                                            </td>
                                                                            <td>
                                                                                <input type="time" class="form-control timepicker"
                                                                                    name="availability[{{ $loop->index }}][from]" value="{{ $startAt }}">
                                                                            </td>
                                                                            <td>
                                                                                <input type="time" class="form-control timepicker"
                                                                                    name="availability[{{ $loop->index }}][to]" value="{{ $endAt }}">
                                                                            </td>
                                                                            <td>
                                                                                <div class="d-flex">
                                                                                    <input type="time" class="form-control mr-2 break-time"
                                                                                        name="availability[{{ $loop->index }}][pause_from]"
                                                                                        value="{{ $pauseFrom }}" data-pair="pause_to"
                                                                                        placeholder="{{ trans('lang.break_start') }}">
                                                                                    <input type="time" class="form-control break-time"
                                                                                        name="availability[{{ $loop->index }}][pause_to]" value="{{ $pauseTo }}"
                                                                                        data-pair="pause_from" placeholder="{{ trans('lang.break_end') }}">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                            <button type="submit" class="btn bg-{{setting('theme_color')}} mt-4">
                                                {{ trans("lang.saveDispo") }}
                                            </button>
                                        </form>
                                    </div>
                            @endforeach
                            <div class="tab-pane fade" id="closures" role="tabpanel">
                                <form action="{{ route('availability.closures.store') }}" method="POST">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.add_closure') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.date') }} *</label>
                                                        <input type="date" name="jour" class="form-control"
                                                            min="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.start_time') }} *</label>
                                                        <input type="time" name="heurDebut" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.end_time') }} *</label>
                                                        <input type="time" name="heurFin" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reason Field -->
                                            <div class="form-group">
                                                <label>{{ trans('lang.reason') }} *</label>
                                                <textarea name="reason" class="form-control" rows="2" required></textarea>
                                            </div>

                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans('lang.save_closure') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Closures List -->
                                @if(isset($dailyClosures) && $dailyClosures->count() > 0)
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.closure_list') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.date') }}</th>
                                                            <th>{{ trans('lang.start_time') }}</th>
                                                            <th>{{ trans('lang.end_time') }}</th>
                                                            <th>{{ trans('lang.reason') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dailyClosures as $closure)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($closure->jour)->format('d/m/Y') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($closure->heurDebut)->format('H:i') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($closure->heurFin)->format('H:i') }}</td>
                                                                <td>{{ $closure->reason }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-primary btn-sm mr-2"
                                                                        onclick="editClosure('{{ $closure->id }}', '{{ $closure->jour }}', '{{ $closure->heurDebut }}', '{{ $closure->heurFin }}', '{{ $closure->reason }}')">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form
                                                                        action="{{ route('availability.closures.destroy', $closure->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="closure_id" value="{{ $closure->id }}">
                                                                        <button type="button" class="btn btn-danger btn-sm"
                                                                            onclick="confirmDelete(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- Vacation Tab -->
                            <div class="tab-pane fade" id="vacation" role="tabpanel">
                                <form action="{{ route('holidays.store') }}" method="POST" id="vacationForm"
                                    onsubmit="return validateVacationForm()">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans("lang.add_vacation") }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <!-- Add hidden method field for PUT -->
                                            <input type="hidden" name="_method" value="POST">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="required">{{ trans("lang.start_date") }} *</label>
                                                        <input type="date" name="start_date" class="form-control" +
                                                            min="{{ date('Y-m-d') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="required">{{ trans("lang.end_date") }} *</label>

                                                        <input type="date" name="end_date" class="form-control" +
                                                            min="{{ date('Y-m-d') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="required">{{ trans("lang.reason") }} *</label>
                                                <textarea name="reason" class="form-control" rows="3" required
                                                    oninvalid="this.setCustomValidity('{{ trans("lang.reason_required") }}')"
                                                    oninput="this.setCustomValidity('')"></textarea>
                                                <div class="invalid-feedback">{{ trans("lang.reason_required") }}</div>
                                            </div>
                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans("lang.save_vacation") }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                @if(isset($vacations) && count($vacations) > 0)
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h4>{{ trans("lang.vacation_list") }}</h4>
                                        </div>
                                        <div class="card-body vacation-list-container">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans("lang.start_date") }}</th>
                                                            <th>{{ trans("lang.end_date") }}</th>
                                                            <th>{{ trans("lang.reason") }}</th>
                                                            <th>{{ trans("lang.actions") }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($vacations as $vacation)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($vacation->start_date)->format('d/m/Y') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($vacation->end_date)->format('d/m/Y') }}</td>
                                                                <td>{{ $vacation->reason }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-primary btn-sm mr-2"
                                                                        onclick="editVacation('{{ $vacation->id }}', '{{ $vacation->start_date }}', '{{ $vacation->end_date }}', '{{ $vacation->reason }}')">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form action="{{ route('vacances.destroy', $vacation->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" class="btn btn-danger btn-sm"
                                                                            onclick="confirmDelete(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="substitute" role="tabpanel">
                                <form action="{{ route('substitute.store') }}" method="POST">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.add_substitute') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ trans('lang.substitute_name') }}</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.start_date') }}</label>
                                                        <input type="datetime-local" name="start_date" class="form-control"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.end_date') }}</label>
                                                        <input type="datetime-local" name="end_date" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ trans('lang.notes') }}</label>
                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans('lang.save_substitute') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                @if(isset($substitutes) && count($substitutes) > 0)
                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h4>{{ trans('lang.substitute_list') }}</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ trans('lang.substitute_name') }}</th>
                                                                <th>{{ trans('lang.start_date') }}</th>
                                                                <th>{{ trans('lang.end_date') }}</th>
                                                                <th>{{ trans('lang.notes') }}</th>
                                                                <th>{{ trans('lang.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($substitutes as $substitute)
                                                                                        @php
                                                                                            $now = now();
                                                                                            $startDate = \Carbon\Carbon::parse($substitute->start_date);
                                                                                            $endDate = \Carbon\Carbon::parse($substitute->end_date);

                                                                                            $status = 'inactive';
                                                                                            if ($now->between($startDate, $endDate)) {
                                                                                                $status = 'active';
                                                                                            } elseif ($now->lt($startDate)) {
                                                                                                $status = 'pending';
                                                                                            }
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $substitute->name }}</td>
                                                                                            <td>{{ $substitute->formatted_start_date }}</td>
                                                                                            <td>{{ $substitute->formatted_end_date }}</td>
                                                                                            <td>{{ $substitute->notes }}</td>
                                                                                            <td>
                                                                                                <form action="{{ route('substitute.destroy', $substitute->id) }}"
                                                                                                    method="POST" class="d-inline">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                                                                        <i class="fas fa-trash"></i>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </td>
                                                                                        </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($currentMode === 'precise')
            <div class="content">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs mb-3" id="availabilityTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="cabinet-tab" data-toggle="tab" href="#cabinet" role="tab">
                                    <i class="fas fa-hospital"></i> {{ trans('lang.cabinet') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="teleconsultation-tab" data-toggle="tab" href="#teleconsultation" role="tab">
                                    <i class="fas fa-video"></i> {{ trans('lang.teleconsultation') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="home_visit-tab" data-toggle="tab" href="#home_visit" role="tab">
                                    <i class="fas fa-home"></i> {{ trans('lang.home_visit') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="closures-tab" data-toggle="tab" href="#closures" role="tab">
                                    <i class="fas fa-clock"></i> {{ trans('lang.early_closures') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vacation-tab" data-toggle="tab" href="#vacation" role="tab">
                                    <i class="fas fa-umbrella-beach"></i> {{ trans('lang.vacation') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="substitute-tab" data-toggle="tab" href="#substitute" role="tab">
                                    <i class="fas fa-user-md"></i> {{ trans('lang.substitute') }}
                                </a>
                            </li>
                        </ul>

                        <!-- Availability Form -->
                        <div class="tab-content" id="availabilityTabContent">
                            @foreach(['cabinet' => 'Cabinet', 'teleconsultation' => 'Téléconsultation', 'home_visit' => 'Visite à domicile'] as $type => $label)
                                <div class="tab-pane fade {{ $type === 'cabinet' ? 'show active' : '' }}" id="{{ $type }}"
                                    role="tabpanel">
                                    <form action="{{ route('availability.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <input type="hidden" name="mode" value="{{ $currentMode }}">

                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 10%;">{{ trans('lang.availability') }}</th>
                                                    <th style="width: 20%;">{{ trans('lang.day') }}</th>
                                                    <th style="width: 70%;">{{ trans('lang.timeSlots') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($days as $dayIndex => $day)
                                                                    <tr>
                                                                        <td class="text-center align-middle">
                                                                            <label class="switch">
                                                                                <input type="checkbox" name="availability[{{ $dayIndex }}][is_available]"
                                                                                    value="1" @if(isset($availabilities[$type][$day]) && $availabilities[$type][$day]->contains('is_available', true)) checked
                                                                                    @endif>
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                            <input type="hidden" name="availability[{{ $dayIndex }}][day]"
                                                                                value="{{ $day }}">
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            {{ trans('lang.' . strtolower($day)) }}
                                                                        </td>
                                                                        <td>
                                                                            <div id="{{ $type }}-slots-{{ $dayIndex }}" class="slots-container">
                                                                                @php
                                                                                    $daySlots = $availabilities[$type][$day] ?? collect();
                                                                                @endphp

                                                                                @if($daySlots->isNotEmpty())
                                                                                    @foreach($daySlots as $slot)
                                                                                        <div class="slot-entry d-flex align-items-center mb-2">
                                                                                            <input type="time" name="availability[{{ $dayIndex }}][slots][start][]"
                                                                                                class="form-control mr-2" required
                                                                                                value="{{ \Carbon\Carbon::parse($slot->start_at)->format('H:i') }}">
                                                                                            <input type="time" name="availability[{{ $dayIndex }}][slots][end][]"
                                                                                                class="form-control mr-2" required
                                                                                                value="{{ \Carbon\Carbon::parse($slot->end_at)->format('H:i') }}">
                                                                                            <select name="availability[{{ $dayIndex }}][slots][pattern][]"
                                                                                                class="form-control mr-2" required>
                                                                                                <option value="">{{ trans('lang.select_pattern') }}</option>
                                                                                                @foreach($doctorPatterns as $pattern)
                                                                                                    <option value="{{ $pattern->id }}" {{ $slot->patern_id == $pattern->id ? 'selected' : '' }}>
                                                                                                        {{ $pattern->nom }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                            <input type="number"
                                                                                                name="availability[{{ $dayIndex }}][slots][duration][]"
                                                                                                class="form-control mr-2" placeholder="{{ trans('lang.duration') }}"
                                                                                                required min="15" value="{{ $slot->session_duration ?? 30 }}">
                                                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                                                onclick="removeSlot(this)">
                                                                                                <i class="fas fa-trash"></i>
                                                                                            </button>

                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                                <button type="button" class="btn btn-primary btn-sm mt-2"
                                                                                    onclick="addSlot('{{ $type }}', {{ $dayIndex }})">
                                                                                    <i class="fas fa-plus"></i> {{ trans('lang.add_slot') }}
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button type="submit" class="btn bg-{{ setting('theme_color') }} mt-4">
                                            {{ trans("lang.saveDispo") }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                            <div class="tab-pane fade" id="closures" role="tabpanel">
                                <form action="{{ route('availability.closures.store') }}" method="POST">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.add_closure') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.date') }} *</label>
                                                        <input type="date" name="jour" class="form-control"
                                                            min="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.start_time') }} *</label>
                                                        <input type="time" name="heurDebut" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.end_time') }} *</label>
                                                        <input type="time" name="heurFin" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Reason Field -->
                                            <div class="form-group">
                                                <label>{{ trans('lang.reason') }} *</label>
                                                <textarea name="reason" class="form-control" rows="2" required></textarea>
                                            </div>

                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans('lang.save_closure') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Closures List -->
                                @if(isset($dailyClosures) && $dailyClosures->count() > 0)
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.closure_list') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('lang.date') }}</th>
                                                            <th>{{ trans('lang.start_time') }}</th>
                                                            <th>{{ trans('lang.end_time') }}</th>
                                                            <th>{{ trans('lang.reason') }}</th>
                                                            <th>{{ trans('lang.actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($dailyClosures as $closure)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($closure->jour)->format('d/m/Y') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($closure->heurDebut)->format('H:i') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($closure->heurFin)->format('H:i') }}</td>
                                                                <td>{{ $closure->reason }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-primary btn-sm mr-2"
                                                                        onclick="editClosure('{{ $closure->id }}', '{{ $closure->jour }}', '{{ $closure->heurDebut }}', '{{ $closure->heurFin }}', '{{ $closure->reason }}')">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form
                                                                        action="{{ route('availability.closures.destroy', $closure->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="closure_id" value="{{ $closure->id }}">
                                                                        <button type="button" class="btn btn-danger btn-sm"
                                                                            onclick="confirmDelete(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <!-- Vacation Tab -->
                            <div class="tab-pane fade" id="vacation" role="tabpanel">
                                <form action="{{ route('holidays.store') }}" method="POST" id="vacationForm"
                                    onsubmit="return validateVacationForm()">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans("lang.add_vacation") }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <!-- Add hidden method field for PUT -->
                                            <input type="hidden" name="_method" value="POST">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="required">{{ trans("lang.start_date") }} *</label>
                                                        <input type="date" name="start_date" class="form-control" +
                                                            min="{{ date('Y-m-d') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="required">{{ trans("lang.end_date") }} *</label>

                                                        <input type="date" name="end_date" class="form-control" +
                                                            min="{{ date('Y-m-d') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="required">{{ trans("lang.reason") }} *</label>
                                                <textarea name="reason" class="form-control" rows="3" required
                                                    oninvalid="this.setCustomValidity('{{ trans("lang.reason_required") }}')"
                                                    oninput="this.setCustomValidity('')"></textarea>
                                                <div class="invalid-feedback">{{ trans("lang.reason_required") }}</div>
                                            </div>
                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans("lang.save_vacation") }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                @if(isset($vacations) && count($vacations) > 0)
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h4>{{ trans("lang.vacation_list") }}</h4>
                                        </div>
                                        <div class="card-body vacation-list-container">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans("lang.start_date") }}</th>
                                                            <th>{{ trans("lang.end_date") }}</th>
                                                            <th>{{ trans("lang.reason") }}</th>
                                                            <th>{{ trans("lang.actions") }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($vacations as $vacation)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($vacation->start_date)->format('d/m/Y') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($vacation->end_date)->format('d/m/Y') }}</td>
                                                                <td>{{ $vacation->reason }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-primary btn-sm mr-2"
                                                                        onclick="editVacation('{{ $vacation->id }}', '{{ $vacation->start_date }}', '{{ $vacation->end_date }}', '{{ $vacation->reason }}')">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form action="{{ route('vacances.destroy', $vacation->id) }}"
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" class="btn btn-danger btn-sm"
                                                                            onclick="confirmDelete(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="substitute" role="tabpanel">
                                <form action="{{ route('substitute.store') }}" method="POST">
                                    @csrf
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>{{ trans('lang.add_substitute') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>{{ trans('lang.substitute_name') }}</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.start_date') }}</label>
                                                        <input type="datetime-local" name="start_date" class="form-control"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ trans('lang.end_date') }}</label>
                                                        <input type="datetime-local" name="end_date" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ trans('lang.notes') }}</label>
                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn bg-{{ setting('theme_color') }}">
                                                {{ trans('lang.save_substitute') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                @if(isset($substitutes) && count($substitutes) > 0)
                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h4>{{ trans('lang.substitute_list') }}</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ trans('lang.substitute_name') }}</th>
                                                                <th>{{ trans('lang.start_date') }}</th>
                                                                <th>{{ trans('lang.end_date') }}</th>
                                                                <th>{{ trans('lang.notes') }}</th>
                                                                <th>{{ trans('lang.status') }}</th>
                                                                <th>{{ trans('lang.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($substitutes as $substitute)
                                                                                        @php
                                                                                            $now = now();
                                                                                            $startDate = \Carbon\Carbon::parse($substitute->start_date);
                                                                                            $endDate = \Carbon\Carbon::parse($substitute->end_date);

                                                                                            $status = 'inactive';
                                                                                            if ($now->between($startDate, $endDate)) {
                                                                                                $status = 'active';
                                                                                            } elseif ($now->lt($startDate)) {
                                                                                                $status = 'pending';
                                                                                            }
                                                                                        @endphp
                                                                                        <tr>
                                                                                            <td>{{ $substitute->name }}</td>
                                                                                            <td>{{ $substitute->formatted_start_date }}</td>
                                                                                            <td>{{ $substitute->formatted_end_date }}</td>
                                                                                            <td>{{ $substitute->notes }}</td>
                                                                                            <td>
                                                                                                <span
                                                                                                    class="badge badge-{{ $status === 'active' ? 'success' : ($status === 'pending' ? 'warning' : 'secondary') }}">
                                                                                                    {{ trans('lang.substitute_status_' . $status) }}
                                                                                                </span>
                                                                                            </td>
                                                                                            <td>
                                                                                                <form action="{{ route('substitute.destroy', $substitute->id) }}"
                                                                                                    method="POST" class="d-inline">
                                                                                                    @csrf
                                                                                                    @method('DELETE')
                                                                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                                                                        <i class="fas fa-trash"></i>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </td>
                                                                                        </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
    <style>
        /* Modern Table Styles */

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-collapse: collapse;
        }

        .table thead th {
            background-color: var(--primary);
            color: #fff;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            padding: 12px;
            border: none;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }



        .mode-switch label {
            position: relative;
            cursor: pointer;
            padding: 8px 20px;
            border-radius: 25px;
            margin: 0;
            transition: all 0.3s ease;
        }

        .mode-switch label:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .mode-switch input[type="radio"] {
            display: none;
        }

        .mode-switch input[type="radio"]:checked+label {
            background-color: var(--primary);
            color: white;
        }

        /* Slot Entry Styles */
        .slot-entry {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid #dee2e6;
        }

        .slot-entry:hover {
            border-color: var(--primary);
        }

        /* Input Enhancements */
        .form-control {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        }

        /* Switch Enhancement */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary);
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }

        /* Tab Enhancement */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            padding: 12px 20px;
            margin-bottom: -2px;
            color: #495057;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            background: transparent;
        }

        /* Button Enhancement */
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-sm {
            padding: 4px 8px;
        }

        .btn i {
            margin-right: 4px;
        }

        @keyframes glow {
            0% {
                box-shadow: 0 0 5px #fff, 0 0 10px var(--primary);
            }

            50% {
                box-shadow: 0 0 20px #fff, 0 0 30px var(--primary);
            }

            100% {
                box-shadow: 0 0 5px #fff, 0 0 10px var(--primary);
            }
        }

        .btn-glow {
            animation: glow 2s infinite;
        }

        .unsaved-warning {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #ff4444;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            display: none;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .unsaved-warning.show {
            display: block;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }

            to {
                transform: translateX(0);
            }
        }

        .field-editing {
            animation: glowField 2s infinite;
            border-color: var(--primary) !important;
            background-color: rgba(var(--primary-rgb), 0.05) !important;
        }

        @keyframes glowField {
            0% {
                box-shadow: 0 0 5px rgba(var(--primary-rgb), 0.2);
            }

            50% {
                box-shadow: 0 0 15px rgba(var(--primary-rgb), 0.4);
            }

            100% {
                box-shadow: 0 0 5px rgba(var(--primary-rgb), 0.2);
            }
        }

        .editing-mode-header {
            background-color: rgba(var(--primary-rgb), 0.1);
            border-left: 4px solid var(--primary);
            padding: 10px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
@endsection

<!-- Replace the mode selection radio buttons with this -->
<div class="mode-switch mb-4">
    <input type="radio" id="openMode" name="mode" value="open" {{ $currentMode === 'open' ? 'checked' : '' }}>
    <label for="openMode">Mode Ouvert</label>

    <input type="radio" id="preciseMode" name="mode" value="precise" {{ $currentMode === 'precise' ? 'checked' : '' }}>
    <label for="preciseMode">Mode Précis</label>
</div>

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const doctorPatterns = @json($doctorPatterns);

        function checkOverlap(startTime, endTime, container, currentSlot = null) {
            const slots = container.querySelectorAll('.slot-entry');
            for (const slot of slots) {
                if (slot === currentSlot) continue; // Skip comparing with itself when editing

                const slotStart = slot.querySelector('input[name$="[start][]"]').value;
                const slotEnd = slot.querySelector('input[name$="[end][]"]').value;

                if (slotStart && slotEnd) {
                    // Check if the new slot overlaps with existing slot
                    if ((startTime >= slotStart && startTime < slotEnd) ||
                        (endTime > slotStart && endTime <= slotEnd) ||
                        (startTime <= slotStart && endTime >= slotEnd)) {
                        return true;
                    }
                }
            }
            return false;
        }

        function validateTimeSlot(input) {
            const slotEntry = input.closest('.slot-entry');
            const startInput = slotEntry.querySelector('input[name$="[start][]"]');
            const endInput = slotEntry.querySelector('input[name$="[end][]"]');
            const container = input.closest('.slots-container');

            if (startInput.value && endInput.value) {
                if (startInput.value >= endInput.value) {
                    Swal.fire({
                        title: '{{ trans("lang.time_conflict") }}',
                        text: '{{ trans("lang.end_time_error") }}',
                        icon: 'warning',
                        confirmButtonText: '{{ trans("lang.close") }}',
                        confirmButtonColor: '#3085d6'
                    });
                    input.value = '';
                    return false;
                }

                if (checkOverlap(startInput.value, endInput.value, container, slotEntry)) {
                    Swal.fire({
                        title: '{{ trans("lang.time_conflict") }}',
                        text: '{{ trans("lang.time_conflict_message") }}',
                        icon: 'warning',
                        confirmButtonText: '{{ trans("lang.close") }}',
                        confirmButtonColor: '#3085d6'
                    });
                    input.value = '';
                    return false;
                }
            }
            return true;
        }

        function addSlot(type, dayIndex) {
            const container = document.getElementById(`${type}-slots-${dayIndex}`);
            const div = document.createElement('div');
            div.classList.add('slot-entry', 'd-flex', 'align-items-center', 'mb-2');

            let options = '';
            doctorPatterns.forEach(function (pattern) {
                options += `<option value="${pattern.id}">${pattern.nom}</option>`;
            });

            div.innerHTML = ` <input type="time" name="availability[${dayIndex}][slots][start][]" class="form-control mr-2" required onchange="validateTimeSlot(this)"> <input type="time" name="availability[${dayIndex}][slots][end][]" class="form-control mr-2" required onchange="validateTimeSlot(this)"> <select name="availability[${dayIndex}][slots][pattern][]" class="form-control mr-2" required> <option value="">{{ trans('lang.select_pattern') }}</option> ${options} </select> <input type="number" name="availability[${dayIndex}][slots][duration][]" class="form-control mr-2" placeholder="{{ trans('lang.duration') }}" required min="15" value="30"> <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)"> <i class="fas fa-trash"></i> </button> `;

            container.insertBefore(div, container.lastElementChild);
        }

        function removeSlot(button) {
            const confirmDeletion = confirm("Voulez-vous vraiment supprimer ce slot ?");

            if (confirmDeletion) {
                button.parentElement.remove();
            }

            return confirmDeletion;
        }

        // Add form submission validation
        document.querySelectorAll('form').forEach(form => {
            if (!form.action.includes('holidays')) {
                form.addEventListener('submit', function (e) {
                    const slots = form.querySelectorAll('.slot-entry');
                    let isValid = true;

                    slots.forEach(slot => {
                        const startInput = slot.querySelector('input[name$="[start][]"]');
                        if (!validateTimeSlot(startInput)) {
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add tooltip for home visits
            const homeVisitTab = document.getElementById('home-visit-tab');
            if (homeVisitTab) {
                new bootstrap.Tooltip(homeVisitTab, {
                    title: "{{ trans('lang.home_visit_info') }}",
                    placement: 'top'
                });
            }

            // Add info text under home visit tab
            const homeVisitContent = document.getElementById('home_visit');
            if (homeVisitContent) {
                const infoDiv = document.createElement('div');
                infoDiv.className = 'alert alert-info mt-2';
                infoDiv.innerHTML = "<i class='fas fa-info-circle'></i> {{ trans('lang.home_visit_schedule_info') }}";
                homeVisitContent.insertBefore(infoDiv, homeVisitContent.firstChild);
            }
        });
    </script>
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

@push('scripts')

    <script>
        function updateCheckboxState(container) {
            const dayRow = container.closest('tr');
            const checkbox = dayRow.querySelector('input[type="checkbox"]');
            const slots = container.querySelectorAll('.slot-entry');

            // Set checkbox state based on whether there are any slots
            checkbox.checked = slots.length > 0;
        }

        function addSlot(type, dayIndex) {
            const container = document.getElementById(`${type}-slots-${dayIndex}`);
            const div = document.createElement('div');
            div.classList.add('slot-entry', 'd-flex', 'align-items-center', 'mb-2');

            let options = '';
            doctorPatterns.forEach(function (pattern) {
                options += `<option value="${pattern.id}">${pattern.nom}</option>`;
            });

            div.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="time" name="availability[${dayIndex}][slots][start][]" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control mr-2" required onchange="validateTimeSlot(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="time" name="availability[${dayIndex}][slots][end][]" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control mr-2" required onchange="validateTimeSlot(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <select name="availability[${dayIndex}][slots][pattern][]" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control mr-2" required>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <option value="">{{ trans('lang.select_pattern') }}</option>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ${options}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </select>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <input type="number" name="availability[${dayIndex}][slots][duration][]" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                class="form-control mr-2" placeholder="{{ trans('lang.duration') }}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                required min="15" value="30">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-trash"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;

            container.insertBefore(div, container.lastElementChild);

            // Update checkbox state after adding slot
            updateCheckboxState(container);
        }

        function removeSlot(button) {
            const slotEntry = button.closest('.slot-entry');
            const container = slotEntry.closest('.slots-container');
            const form = container.closest('form');

            Swal.fire({
                title: 'Confirmation',
                text: 'Voulez-vous vraiment supprimer ce créneau ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer et sauvegarder',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Set a flag to indicate intentional form submission
                    window.removeSlotSubmission = true;

                    // Remove the slot
                    slotEntry.remove();

                    // Update checkbox state
                    updateCheckboxState(container);

                    // Automatically submit the form
                    form.submit();
                }
            });
        }

        // Modify the beforeunload event listener
        window.addEventListener('beforeunload', (e) => {
            // Only show warning if there are unsaved changes and it's not a removeSlot submission
            if (hasChanges && !window.removeSlotSubmission) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
            }
            // Reset the flag
            window.removeSlotSubmission = false;
        });

        // Add this to your DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize checkbox states for all days and types
            document.querySelectorAll('.slots-container').forEach(container => {
                updateCheckboxState(container);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll('input[type="checkbox"][name*="[is_available]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function (e) {
                    const row = this.closest('tr');
                    const container = row.querySelector('.slots-container');
                    const slots = container.querySelectorAll('.slot-entry');

                    if (this.checked && slots.length === 0) {
                        // If checking the box and no slots exist, automatically add one
                        const dayIndex = this.name.match(/\[(\d+)\]/)[1];
                        const type = container.id.split('-slots-')[0];
                        addSlot(type, dayIndex);
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function () {
            // Make time inputs required when checkbox is checked
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const row = this.closest('tr');
                    const timeInputs = row.querySelectorAll('input[type="time"]');
                    timeInputs.forEach(input => {
                        if (!input.classList.contains('break-time')) {
                            input.required = this.checked;
                        }
                    });
                });
            });

            // Break time validation
            const breakInputs = document.querySelectorAll('.break-time');
            breakInputs.forEach(input => {
                input.addEventListener('change', function () {
                    const pairType = this.dataset.pair;
                    const row = this.closest('tr');
                    const pairInput = row.querySelector(`[name$="[${pairType}]"]`);

                    if (this.value && !pairInput.value) {
                        pairInput.required = true;
                        this.required = true;
                    } else {
                        pairInput.required = false;
                        this.required = false;
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let hasChanges = false;
            let isIntentionalSubmit = false; // New flag to track intentional submissions
            const forms = document.querySelectorAll('form');
            const submitButtons = document.querySelectorAll('button[type="submit"]');

            // Create warning element
            const warning = document.createElement('div');
            warning.className = 'unsaved-warning';
            warning.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="d-flex align-items-center">
                                                                                                                                                                                                                                                                                                                                                                                                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                <div>
                                                                                                                                                                                                                                                                                                                                                                                                                                    N'oubliez pas d'enregistrer vos disponibilités !<br>
                                                                                                                                                                                                                                                                                                                                                                                                                            <small>Cliquez sur le bouton "Enregistrer" en bas de page pour ne pas perdre vos modifications</small>
                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                            </div>`;
            document.body.appendChild(warning);

            // Function to show warning and glow button
            function showWarning() {
                warning.classList.add('show');
                submitButtons.forEach(btn => btn.classList.add('btn-glow'));
                // Scroll to the first submit button
            }

            // Function to hide warning and remove glow
            function hideWarning() {
                warning.classList.remove('show');
                submitButtons.forEach(btn => btn.classList.remove('btn-glow'));
            }

            // Track changes on all form inputs
            forms.forEach(form => {
                // Track all input changes
                form.addEventListener('input', () => {
                    hasChanges = true;
                    showWarning();
                });

                // Track changes when adding/removing slots
                form.addEventListener('click', (e) => {
                    if (e.target.matches('button[onclick*="addSlot"], button[onclick*="removeSlot"]')) {
                        hasChanges = true;
                        showWarning();
                    }
                });

                // Reset on form submit
                form.addEventListener('submit', () => {
                    hasChanges = false;
                    hideWarning();
                });
            });

            // Handle tab navigation
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.addEventListener('click', (e) => {
                    if (hasChanges) {
                        e.preventDefault();
                        e.stopPropagation();

                        Swal.fire({
                            title: 'Attention !',
                            text: 'Vous avez des modifications non sauvegardées. Voulez-vous sauvegarder avant de changer d\'onglet ?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Oui, sauvegarder',
                            cancelButtonText: 'Non, ignorer',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Set intentional submit flag
                                isIntentionalSubmit = true;
                                // Submit current form
                                const activeTab = document.querySelector('.tab-pane.active');
                                const currentForm = activeTab.querySelector('form');
                                if (currentForm) {
                                    currentForm.submit();
                                }
                            } else {
                                // Discard changes and switch tab
                                hasChanges = false;
                                hideWarning();
                                const tabInstance = new bootstrap.Tab(tab);
                                tabInstance.show();
                            }
                        });
                    }
                });
            });

            // Modify form submit handling
            forms.forEach(form => {
                form.addEventListener('submit', () => {
                    isIntentionalSubmit = true;
                    hasChanges = false;
                    hideWarning();
                });
            });

            // Modify beforeunload event
            window.addEventListener('beforeunload', (e) => {
                if (hasChanges && !isIntentionalSubmit) {
                    e.preventDefault();
                    e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
                }
                // Reset intentional submit flag after handling the event
                isIntentionalSubmit = false;
            });
        });
        function toggleClosureFields() {
            const closureType = document.getElementById('closure_type').value;
            const singleDayFields = document.getElementById('single_day_fields');
            const periodFields = document.getElementById('period_fields');

            // Reset form fields
            singleDayFields.querySelectorAll('input').forEach(input => {
                input.required = false;
                if (!closureType) input.value = '';
            });

            periodFields.querySelectorAll('input').forEach(input => {
                input.required = false;
                if (!closureType) input.value = '';
            });

            if (closureType === 'day') {
                singleDayFields.classList.remove('d-none');
                periodFields.classList.add('d-none');
                singleDayFields.querySelectorAll('input').forEach(input => input.required = true);
            } else if (closureType === 'period') {
                singleDayFields.classList.add('d-none');
                periodFields.classList.remove('d-none');
                periodFields.querySelectorAll('input').forEach(input => input.required = true);
            } else {
                singleDayFields.classList.add('d-none');
                periodFields.classList.add('d-none');
            }
        }
        function confirmDelete(button) {
            Swal.fire({
                title: '{{ trans("lang.confirm_deletion") }}',
                text: '{{ trans("lang.confirm_closure_deletion_text") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ trans("lang.yes_delete") }}',
                cancelButtonText: '{{ trans("lang.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = button.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
        function editClosure(id, date, startTime, endTime, reason) {
            // Update form action to update route
            const form = document.querySelector('#closures form');
            const cardHeader = form.querySelector('.card-header');
            form.action = "{{ route('availability.closures.update', '') }}/" + id;

            // Add editing mode header if it doesn't exist
            let editingHeader = form.querySelector('.editing-mode-header');
            if (!editingHeader) {
                editingHeader = document.createElement('div');
                editingHeader.className = 'editing-mode-header';
                editingHeader.innerHTML = '<i class="fas fa-edit"></i> {{ trans("lang.editing_mode") }}';
                cardHeader.after(editingHeader);
            }
            editingHeader.style.display = 'block';

            // Add method spoofing for PUT
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';

            // Fill in and highlight the form fields
            const fields = {
                'jour': date,
                'heurDebut': startTime,
                'heurFin': endTime,
                'reason': reason
            };

            Object.entries(fields).forEach(([name, value]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) {
                    field.value = value;
                    field.classList.add('field-editing');

                    // Add floating label effect
                    const label = field.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        label.classList.add('field-editing');
                    }
                }
            });

            // Change button text and style
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> {{ trans("lang.update_closure") }}';
            submitButton.classList.add('btn-primary');

            // Add cancel button if it doesn't exist
            let cancelButton = form.querySelector('.btn-cancel');
            if (!cancelButton) {
                cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'btn btn-secondary ml-2 btn-cancel';
                cancelButton.innerHTML = '<i class="fas fa-times"></i> {{ trans("lang.cancel") }}';
                cancelButton.onclick = resetForm;
                submitButton.parentNode.insertBefore(cancelButton, submitButton.nextSibling);
            }

            // Scroll to form with highlight effect
            form.scrollIntoView({ behavior: 'smooth' });
            form.classList.add('field-editing');
            setTimeout(() => form.classList.remove('field-editing'), 1000);
        }

        function resetForm() {
            const form = document.querySelector('#closures form');

            // Remove all editing highlights
            form.querySelectorAll('.field-editing').forEach(el => {
                el.classList.remove('field-editing');
            });

            // Hide editing mode header
            const editingHeader = form.querySelector('.editing-mode-header');
            if (editingHeader) {
                editingHeader.style.display = 'none';
            }

            // Reset to store route
            form.action = "{{ route('availability.closures.store') }}";

            // Remove method spoofing
            const methodField = form.querySelector('input[name="_method"]');
            if (methodField) {
                methodField.value = 'POST';
            }

            // Clear form fields
            form.reset();

            // Reset button text and style
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> ' + '{{ trans("lang.save_closure") }}';
            submitButton.classList.remove('btn-primary');

            // Remove cancel button
            const cancelButton = form.querySelector('.btn-cancel');
            if (cancelButton) {
                cancelButton.remove();
            }
        }
        function editVacation(id, startDate, endDate, reason) {
            const form = document.getElementById('vacationForm');
            const cardHeader = form.querySelector('.card-header');

            // Update form action to update route
            form.action = `/vacances/${id}`;

            // Add editing mode header
            let editingHeader = form.querySelector('.editing-mode-header');
            if (!editingHeader) {
                editingHeader = document.createElement('div');
                editingHeader.className = 'editing-mode-header';
                editingHeader.innerHTML = '<i class="fas fa-edit"></i> {{ trans("lang.editing_vacation") }}';
                cardHeader.after(editingHeader);
            }
            editingHeader.style.display = 'block';

            // Update method to PUT
            let methodField = form.querySelector('input[name="_method"]');
            methodField.value = 'PUT';

            // Fill in the form fields
            form.querySelector('input[name="start_date"]').value = startDate;
            form.querySelector('input[name="end_date"]').value = endDate;
            form.querySelector('textarea[name="reason"]').value = reason;

            // Change button text and style
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> {{ trans("lang.update_vacation") }}';
            submitButton.classList.add('btn-primary');

            // Add cancel button
            let cancelButton = form.querySelector('.btn-cancel');
            if (!cancelButton) {
                cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'btn btn-secondary ml-2 btn-cancel';
                cancelButton.innerHTML = '<i class="fas fa-times"></i> {{ trans("lang.cancel") }}';
                cancelButton.onclick = resetVacationForm;
                submitButton.parentNode.insertBefore(cancelButton, submitButton.nextSibling);
            }

            // Add visual feedback
            form.querySelectorAll('input, textarea').forEach(field => {
                field.classList.add('field-editing');
            });

            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        }

        function resetVacationForm() {
            const form = document.getElementById('vacationForm');

            // Reset form action
            form.action = "{{ route('holidays.store') }}";

            // Reset method to POST
            form.querySelector('input[name="_method"]').value = 'POST';

            // Clear fields
            form.reset();

            // Remove editing styles
            form.querySelectorAll('.field-editing').forEach(el => {
                el.classList.remove('field-editing');
            });

            // Hide editing header
            const editingHeader = form.querySelector('.editing-mode-header');
            if (editingHeader) {
                editingHeader.style.display = 'none';
            }

            // Reset button
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> {{ trans("lang.save_vacation") }}';
            submitButton.classList.remove('btn-primary');

            // Remove cancel button
            const cancelButton = form.querySelector('.btn-cancel');
            if (cancelButton) {
                cancelButton.remove();
            }
        }
        function validateVacationForm() {
            const form = document.getElementById('vacationForm');
            const startDate = form.querySelector('input[name="start_date"]');
            const endDate = form.querySelector('input[name="end_date"]');
            const reason = form.querySelector('textarea[name="reason"]');
            const isUpdateMode = form.querySelector('input[name="_method"]')?.value === 'PUT';

            // Reset previous error states
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            let isValid = true;

            // For new vacations, require all fields
            if (!isUpdateMode) {
                if (!startDate.value) {
                    startDate.classList.add('is-invalid');
                    isValid = false;
                }
                if (!endDate.value) {
                    endDate.classList.add('is-invalid');
                    isValid = false;
                }
            }

            // In update mode, only validate dates if either of them has changed
            if (isUpdateMode) {
                const originalStartDate = startDate.getAttribute('data-original');
                const originalEndDate = endDate.getAttribute('data-original');

                if (startDate.value !== originalStartDate || endDate.value !== originalEndDate) {
                    // Only validate if dates have been modified
                    if (startDate.value || endDate.value) {
                        if (!startDate.value) {
                            startDate.classList.add('is-invalid');
                            isValid = false;
                        }
                        if (!endDate.value) {
                            endDate.classList.add('is-invalid');
                            isValid = false;
                        }
                    }
                }
            }

            // Always validate end date is after start date if both dates are present
            if (startDate.value && endDate.value && endDate.value < startDate.value) {
                endDate.classList.add('is-invalid');
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans("lang.error") }}',
                    text: '{{ trans("lang.end_date_after_start_date") }}'
                });
                isValid = false;
            }

            // Always validate reason
            if (!reason.value.trim()) {
                reason.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: '{{ trans("lang.form_error") }}',
                    text: '{{ trans("lang.please_fill_required_fields") }}'
                });
            }

            return isValid;
        }
    </script>
@endpush