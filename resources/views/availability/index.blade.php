@extends('layouts.app')

@section('content')
    @php
        $doctorId = auth()->user()->getDoctorId();
        $permissionKey = 'availability.index';
        $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)->with('readable')->first();
        $readablePermission = $permission ? $permission->display_name : $permissionKey;
        $doctor = auth()->user()->doctor;
        // $currentMode now comes from the controller
        // Define days to be used in the table (ensure the keys match the day names in your DB)
        $days = [
            trans("lang.lundi"),
            trans("lang.mardi"),
            trans("lang.mercredi"),
            trans("lang.jeudi"),
            trans("lang.vendredi"),
            trans("lang.samedi"),
            trans("lang.dimanche")
        ];

        // Get doctor's patterns for precise mode (dynamic list)
        $doctorPatterns = \App\Models\Pattern::where('doctor_id', $doctorId)->get();
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
            document.addEventListener("DOMContentLoaded", function () {
                setTimeout(function () {
                    document.getElementById('error-alert')?.remove();
                    document.getElementById('success-alert')?.remove();
                }, 5000);
            });
        </script>

        <div class="content">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="scheduleTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="availability-tab" data-toggle="tab" href="#availability" role="tab"
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
                    </ul>

                    <!-- Availability Form -->
                    <div class="tab-content" id="scheduleTabsContent">
                        <div class="tab-pane fade show active" id="availability" role="tabpanel"
                             aria-labelledby="availability-tab">
                            <form action="{{ route('availability.store') }}" method="POST">
                                @csrf
                                <!-- Hidden input to pass the current mode -->
                                <input type="hidden" name="mode" value="{{ $currentMode }}">

                                @if($currentMode === 'open')
                                    <!-- Open Availability UI -->
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>{{ trans("lang.availability") }}</th>
                                            <th>{{ trans("lang.jourDispo") }}</th>
                                            <th>{{ trans("lang.from") }}</th>
                                            <th>{{ trans("lang.to") }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($days as $dayIndex => $day)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="availability[{{ $dayIndex }}][is_available]" value="1"
                                                           @if(isset($availability[$day]) && $availability[$day]->is_available) checked @endif>
                                                    <!-- Save the day -->
                                                    <input type="hidden" name="availability[{{ $dayIndex }}][day]" value="{{ $day }}">
                                                </td>
                                                <td>{{ $day }}</td>
                                                <td>
                                                    <input type="time" name="availability[{{ $dayIndex }}][from]" class="form-control"
                                                           value="{{ isset($availability[$day]) ? $availability[$day]->start_at : '' }}">
                                                </td>
                                                <td>
                                                    <input type="time" name="availability[{{ $dayIndex }}][to]" class="form-control"
                                                           value="{{ isset($availability[$day]) ? $availability[$day]->end_at : '' }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                @elseif($currentMode === 'precise')
                                    <!-- Precise Availability UI -->
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>{{ trans("lang.availability") }}</th>
                                            <th>{{ trans("lang.jourDispo") }}</th>
                                            <th>{{ trans("lang.timeSlots") }}</th>
                                            <th>{{ trans("lang.actions") }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($days as $dayIndex => $day)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="availability[{{ $dayIndex }}][is_available]" value="1"
                                                           @if(isset($availability[$day]) && $availability[$day]->first()->is_available) checked @endif>
                                                    <!-- Save the day -->
                                                    <input type="hidden" name="availability[{{ $dayIndex }}][day]" value="{{ $day }}">
                                                </td>
                                                <td>{{ $day }}</td>
                                                <td>
                                                    <div id="slots-container-{{ $dayIndex }}">
                                                        @if(isset($availability[$day]))
                                                            @foreach($availability[$day] as $slot)
                                                                <div class="slot-entry d-flex align-items-center mb-2">
                                                                    <input type="time" name="availability[{{ $dayIndex }}][slots][start][]" class="form-control mr-2" required
                                                                           value="{{ \Carbon\Carbon::parse($slot->start_at)->format('H:i') }}">
                                                                    <input type="time" name="availability[{{ $dayIndex }}][slots][end][]" class="form-control mr-2" required
                                                                           value="{{ \Carbon\Carbon::parse($slot->end_at)->format('H:i') }}">
                                                                    <select name="availability[{{ $dayIndex }}][slots][pattern][]" class="form-control mr-2" required>
                                                                        @foreach($doctorPatterns as $pattern)
                                                                            <option value="{{ $pattern->id }}" @if($pattern->id == $slot->patern_id) selected @endif>
                                                                                {{ $pattern->nom }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="number" name="availability[{{ $dayIndex }}][slots][duration][]" class="form-control mr-2" placeholder="Duration (min)" required min="1"
                                                                           value="{{ $slot->session_duration }}">
                                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)">❌</button>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        <button type="button" class="btn btn-primary btn-sm mt-2" onclick="addSlot('{{ $dayIndex }}')">
                                                            ➕ {{ trans("lang.addSlot") }}
                                                        </button>
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                <button type="submit" class="btn bg-{{ setting('theme_color') }} mt-4">{{ trans("lang.saveDispo") }}</button>
                            </form>
                        </div>
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

@section('scripts')
    <script>
        // Pass the doctor's patterns to JavaScript as JSON
        const doctorPatterns = @json($doctorPatterns);

        function addSlot(dayIndex) {
            const container = document.getElementById('slots-container-' + dayIndex);
            const div = document.createElement('div');
            div.classList.add('slot-entry', 'd-flex', 'align-items-center', 'mb-2');

            // Build the select options from doctorPatterns dynamically
            let options = '';
            doctorPatterns.forEach(function(pattern) {
                options += `<option value="${pattern.id}">${pattern.nom}</option>`;
            });

            div.innerHTML = `
                <input type="time" name="availability[${dayIndex}][slots][start][]" class="form-control mr-2" required placeholder="Start Time">
                <input type="time" name="availability[${dayIndex}][slots][end][]" class="form-control mr-2" required placeholder="End Time">
                <select name="availability[${dayIndex}][slots][pattern][]" class="form-control mr-2" required>
                    ${options}
                </select>
                <input type="number" name="availability[${dayIndex}][slots][duration][]" class="form-control mr-2" placeholder="Duration (min)" required min="1">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)">❌</button>
            `;
            container.appendChild(div);
        }

        function removeSlot(button) {
            button.parentElement.remove();
        }

        // (The other JavaScript functions remain unchanged)
    </script>
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