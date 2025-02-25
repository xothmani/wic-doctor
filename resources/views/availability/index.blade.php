@extends('layouts.app')

@section('content')
    @php
        $doctorId = auth()->user()->getDoctorId();
        $permissionKey = 'availability.index';
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionKey)->with('readable')->first();
        $readablePermission = $permission ? $permission->display_name : $permissionKey;
        $doctor = auth()->user()->doctor;
        // $currentMode and $days are passed from the controller.
        // $days is an array of English day names: ["Monday", "Tuesday", ...]
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
                    document.getElementById('error-alert')?.remove();
                    document.getElementById('success-alert')?.remove();
                }, 5000);
            });
        </script>

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
                            <a class="nav-link" id="teleconsultation-tab" data-toggle="tab" href="#Téléconsultation" role="tab">
                                <i class="fas fa-video"></i> {{ trans('lang.teleconsultation') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="home_visit-tab" data-toggle="tab" href="#home_visit" role="tab">
                                <i class="fas fa-home"></i> {{ trans('lang.home_visit') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="vacation-tab" data-toggle="tab" href="#vacation" role="tab">
                                <i class="fas fa-umbrella-beach"></i> {{ trans('lang.vacation') }}
                            </a>
                        </li>
                    </ul>

                    <!-- Availability Form -->
                    <div class="tab-content" id="availabilityTabContent">
                        @foreach(['cabinet' => 'Cabinet', 'Téléconsultation' => 'Téléconsultation', 'home_visit' => 'Visite à domicile'] as $type => $label)
                            <div class="tab-pane fade {{ $type === 'cabinet' ? 'show active' : '' }}" id="{{ $type }}" role="tabpanel">
                                <form action="{{ route('availability.store') }}" method="POST">
                                    @csrf
                                    
                                    <input type="hidden" name="type" value="{{ $type }}">
                                    <input type="hidden" name="mode" value="{{ $currentMode }}">
                                    
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('lang.availability') }}</th>
                                                <th>{{ trans('lang.day') }}</th>
                                                <th>{{ trans('lang.timeSlots') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($days as $dayIndex => $day)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="availability[{{ $dayIndex }}][is_available]" value="1"
                                                            @if(isset($availabilities[$type][$day]) && $availabilities[$type][$day]->contains('is_available', true)) checked @endif>
                                                        <input type="hidden" name="availability[{{ $dayIndex }}][day]" value="{{ $day }}">
                                                    </td>
                                                    <td>{{ trans('lang.' . strtolower($day)) }}</td>
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
                                                                        <select name="availability[{{ $dayIndex }}][slots][pattern][]" class="form-control mr-2" required>
                                                                            <option value="">{{ trans('lang.select_pattern') }}</option>
                                                                            console.log($)
                                                                            @foreach($doctorPatterns as $pattern)
                                                                                <option value="{{ $pattern->id }}" 
                                                                                    {{ $slot->patern_id == $pattern->id ? 'selected' : '' }}>
                                                                                    {{ $pattern->nom }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <input type="number" name="availability[{{ $dayIndex }}][slots][duration][]" 
                                                                            class="form-control mr-2" placeholder="{{ trans('lang.duration') }}" 
                                                                            required min="15" value="{{ $slot->session_duration ?? 30 }}">
                                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                            <button type="button" class="btn btn-primary btn-sm" 
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

                        <!-- Vacation Tab -->
                        <div class="tab-pane fade" id="vacation" role="tabpanel">
                            <form action="{{ route('holidays.store') }}" method="POST">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h4>{{ trans("lang.add_vacation") }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ trans("lang.start_date") }}</label>
                                                    <input type="date" name="start_date" class="form-control" required 
                                                        min="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>{{ trans("lang.end_date") }}</label>
                                                    <input type="date" name="end_date" class="form-control" required 
                                                        min="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans("lang.reason") }}</label>
                                            <textarea name="reason" class="form-control" rows="3"></textarea>
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
                                                                <form action="{{ route('vacances.destroy', $vacation->id) }}" 
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

            div.innerHTML = `
                <input type="time" name="availability[${dayIndex}][slots][start][]" 
                    class="form-control mr-2" required onchange="validateTimeSlot(this)">
                <input type="time" name="availability[${dayIndex}][slots][end][]" 
                    class="form-control mr-2" required onchange="validateTimeSlot(this)">
                <select name="availability[${dayIndex}][slots][pattern][]" class="form-control mr-2" required>
                    <option value="">{{ trans('lang.select_pattern') }}</option>
                    ${options}
                </select>
                <input type="number" name="availability[${dayIndex}][slots][duration][]" 
                    class="form-control mr-2" placeholder="{{ trans('lang.duration') }}" required min="15" value="30">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeSlot(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.insertBefore(div, container.lastElementChild);
        }

        function removeSlot(button) {
            button.parentElement.remove();
        }

        // Add form submission validation
        document.querySelectorAll('form').forEach(form => {
            if (!form.action.includes('holidays')) {
                form.addEventListener('submit', function(e) {
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