@extends('layouts.app')

@section('content')
    @php
        $doctorId = auth()->user()->getDoctorId();
        $permissionKey = 'appointment-event.index';
        // Retrieve the permission with its related readable record
        $permission = Spatie\Permission\Models\Permission::where('name', $permissionKey)
            ->with('readable')
            ->first();

        // Use the dynamic attribute for the display name; fall back to the key if not found
        $readablePermission = $permission ? $permission->display_name : $permissionKey;
    @endphp

    @if(auth()->user()->hasPermissionInContext($permissionKey, $doctorId))

        <!-- Content Header (Page header) -->

        <!-- Second Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="confirmationModalLabel">Créer des heures disponibles</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Cette date n'est pas disponible. Voulez-vous créer une heure disponible pour cette date ?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" id="confirmCreateAvailability" class="btn btn-primary">Créer</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Past Date Modal -->
        <div class="modal fade" id="pastDateModal" tabindex="-1" aria-labelledby="pastDateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pastDateModalLabel">Date Antérieure Sélectionnée</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    </div>
                    <div class="modal-body">
                        <p id="pastDateModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Appointment Details Modal -->
        <!-- Appointment Details Modal -->
        <div class="modal fade" id="appointmentDetailsModal" tabindex="-1" role="dialog"
            aria-labelledby="appointmentDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentDetailsModalLabel">{{ trans('lang.appointment_details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="appointmentTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab"
                                    aria-controls="details" aria-selected="true">{{ trans('lang.details') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="edit-tab" data-toggle="tab" href="#edit" role="tab" aria-controls="edit"
                                    aria-selected="false">{{ trans('lang.edit_appointment') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content" id="appointmentTabContent">
                            <!-- Details and Status Tab -->
                            <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <div class="appointment-info">
                                    <p><strong>{{ trans('lang.patient_nom') }}:</strong> <span id="patientName"></span></p>
                                    <p><strong>{{ trans('lang.appointment_status') }}:</strong> <span
                                            id="appointmentStatus"></span></p>
                                    <p><strong>{{ trans('lang.motif_name') }}:</strong> <span id="motifName"></span></p>
                                    <p><strong>{{ trans('lang.note') }}:</strong> <span id="note"></span></p>
                                </div>
                            </div>
                            <!-- Edit Appointment Tab -->
                            <div class="tab-pane fade" id="edit" role="tabpanel" aria-labelledby="edit-tab">
                                <form id="appointmentForm">
                                    <input type="hidden" id="appointmentId">
                                    <div class="form-group">
                                        <label><strong>{{ trans('lang.patient_nom') }}:</strong></label>
                                        <input type="text" class="form-control" id="patientNameEdit" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>{{ trans('lang.appointment_date') }}:</strong></label>
                                        <input type="datetime-local" class="form-control" id="appointmentDate">
                                    </div>
                                    <div class="form-group pattern-select-group">
                                        <label><strong>{{ trans('lang.availability_hour_pattern') }}:</strong></label>
                                        <select name="patern_id" id="patern_id_cabinet" class="form-control">
                                            <option value="">{{ trans('lang.select_pattern') }}</option>
                                            <!-- Populated dynamically -->
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>{{ trans('lang.note') }}:</strong></label>
                                        <textarea class="form-control" id="notes" rows="4"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="delete-button-container">
                            <button type="button" class="btn btn-danger btn-modern"
                                id="deleteAppointment">{{ trans('lang.delete') }}</button>
                        </div>
                        <div class="status-buttons">
                            <button type="button" class="btn btn-primary btn-modern d-none" id="createTeleconsultation"
                                style="background-color: #AEC6CF; color: #000;">{{ trans('lang.create_teleconsultation') }}</button>
                            <button type="button" class="btn btn-danger btn-modern" id="markAsFailed"
                                style="background-color: #F4C2C2; color: #000;">{{ trans('lang.mark_failed') }}</button>
                            <button type="button" class="btn btn-success btn-modern" id="markAsDone"
                                style="background-color: #B1E5D6; color: #000;">{{ trans('lang.mark_ready') }}</button>
                            <button type="button" class="btn btn-primary btn-modern" id="updateAppointment"
                                style="display: none;">{{ trans('lang.update') }}</button>
                            <button type="button" class="btn btn-secondary btn-modern"
                                id="closeModal">{{ trans('lang.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Cancel Reason Modal -->
        <div class="modal fade" id="cancelReasonModal" tabindex="-1" role="dialog" aria-labelledby="cancelReasonModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelReasonModalLabel">{{ trans('lang.cancel_appointment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="cancelReason">{{ trans('lang.cancel_reason') }}:</label>
                            <textarea class="form-control" id="cancelReason" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-modern"
                            id="confirmCancel">{{ trans('lang.confirm') }}</button>
                        <button type="button" class="btn btn-secondary btn-modern"
                            data-dismiss="modal">{{ trans('lang.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Creating Appointment -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">{{ trans('lang.create_modal_name') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->hasPermissionInContext('appointments.store', $doctorId))
                            <form id="appointmentForm">
                                @csrf
                                <!-- Type Tabs -->
                                <ul class="nav nav-tabs mb-3" id="appointmentTypeTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="cabinet-tab" data-toggle="tab" href="#cabinet-content"
                                            role="tab" data-type="cabinet">
                                            <i class="fas fa-hospital"></i> {{ trans('lang.cabinet') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="teleconsultation-tab" data-toggle="tab"
                                            href="#teleconsultation-content" role="tab" data-type="teleconsultation">
                                            <i class="fas fa-video"></i> {{ trans('lang.teleconsultation') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="home-visit-tab" data-toggle="tab" href="#home-visit-content"
                                            role="tab" data-type="home_visit">
                                            <i class="fas fa-home"></i> {{ trans('lang.home_visit') }}
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content" id="appointmentTypeContent">
                                    <!-- Common form fields for all types -->
                                    <div class="form-group">
                                        <label for="patientDropdown"
                                            class="font-weight-bold">{{ trans('lang.Select_Patient') }}</label>
                                        <div class="d-flex align-items-center">
                                            <select id="patientDropdown" class="form-control select2-ajax" required
                                                style="flex-grow: 1;">
                                                <option value="" disabled selected>{{ trans('lang.Select_Patient') }}</option>
                                            </select>
                                            <a href="{{ route('patients.create') }}" class="btn btn-success d-flex"
                                                id="addNewPatient">
                                                <i class="fa fa-user-plus"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="form-group" id="dateGroup">
                                        <label class="font-weight-bold">{{ trans('lang.date') }}</label>
                                        <input type="date" class="form-control" id="appointmentDate" name="appointment_date"
                                            readonly>
                                        <input type="hidden" name="appointment_type" id="appointmentType" value="cabinet">
                                    </div>

                                    <!-- Time slots section -->
                                    <div id="time-slots-wrapper" class="mt-4">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <label class="font-weight-bold mb-0">{{ trans('lang.select_time') }}</label>
                                        </div>
                                        <div id="time-slots" class="d-flex flex-wrap">
                                            <!-- Time slots will be dynamically populated -->
                                        </div>
                                        <input type="hidden" id="appointment_time" name="appointment_time">
                                    </div>

                                    <!-- Pattern Selection -->
                                    <div class="form-group pattern-select-group" data-type="cabinet">
                                        <label>{{ trans('lang.availability_hour_pattern') }}</label>
                                        <select name="patern_id" id="patern_id_cabinet" class="form-control">
                                            @foreach($patternsByType[1] ?? [] as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group pattern-select-group d-none" data-type="teleconsultation">
                                        <label>{{ trans('lang.availability_hour_pattern') }}</label>
                                        <select name="patern_id" id="patern_id_teleconsultation" class="form-control">
                                            @foreach($patternsByType[4] ?? [] as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group pattern-select-group d-none" data-type="home_visit">
                                        <label>{{ trans('lang.availability_hour_pattern') }}</label>
                                        <select name="patern_id" id="patern_id_home_visit" class="form-control">
                                            @foreach($patternsByType[3] ?? [] as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <!-- Add this after the Pattern Selection div -->
                                    <div class="form-group">
                                        <label for="appointment_notes" class="font-weight-bold">{{ trans('lang.notes') }}</label>
                                        <textarea id="appointment_notes" name="appointment_notes" class="form-control" rows="3"
                                            placeholder="{{ trans('lang.enter_appointment_notes') }}"></textarea>
                                    </div>
                                </div>


                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" id="saveAppointment">
                                        {{ trans('lang.save') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-danger">
                                {{ __('Vous n’avez pas la permission de créer un rendez-vous.') }}
                            </div>
                            <!-- Start and End Time Fields -->
                            <div id="time-slots-wrapper" class="mt-4" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <label class="font-weight-bold mb-0">{{ trans('lang.select_time') }}</label>
                                    <button type="button" id="addMoreAvailable" class="btn btn-primary ml-3"><i
                                            class="fas fa-stopwatch"></i></button>
                                </div>

                                <div id="time-slots" class="d-flex flex-wrap">
                                    <!-- Time slots will be dynamically populated by JavaScript -->
                                </div>
                                <input type="hidden" id="appointment_time" name="appointment_time">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this after your existing modals -->
        <div class="modal fade" id="cancelReasonModal" tabindex="-1" role="dialog" aria-labelledby="cancelReasonModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelReasonModalLabel">{{ trans('lang.cancel_reason') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="cancelReasonForm">
                            <div class="form-group">
                                <label for="cancelReason">{{ trans('lang.reason') }}</label>
                                <textarea class="form-control" id="cancelReason" rows="3"
                                    placeholder="{{ trans('lang.enter_cancel_reason') }}"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('lang.close') }}</button>
                        <button type="button" class="btn btn-danger"
                            id="confirmCancel">{{ trans('lang.confirm_cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="clearfix"></div>
            @include('flash::message')
            <div class="card shadow-sm">

                <div class="card-body">
                    <!-- Calendar Container -->
                    <div id="calendar-container">
                        <div id="calendar"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <!-- Left Section (Legend Boxes) -->
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center me-4">
                        <span class="legend-box" style="background-color: #9FCDA8;"></span>
                        <span class="ms-1">Accepté&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center me-4">
                        <span class="legend-box" style="background-color: #7DC2A5;"></span>
                        <span class="ms-1">Terminé&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center me-4">
                        <span class="legend-box" style="background-color: #9EDF9C;"></span>
                        <span class="ms-1">Prêt&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center me-4">
                        <span class="legend-box" style="background-color: #F5DF4D;"></span>
                        <span class="ms-1">En cours&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center me-4">
                        <span class="legend-box" style="background-color: #F38071;"></span>
                        <span class="ms-1">Annulé&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="legend-box" style="background-color: #A594F9;"></span>
                        <span class="ms-1">Reçu</span>
                    </div>
                </div>

                <!-- Right Section (Circles) -->
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center me-4">
                        <span class="circle-indicator" style="background-color: #28a745;"></span>
                        <span class="ms-2">Disponible&nbsp;&nbsp;</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="circle-indicator" style="background-color: #dc3545;"></span>
                        <span class="ms-2">Non Disponible</span>
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

@push('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
    <link rel="stylesheet" href="{{ asset('css/eventcustom.css') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let availableDays = [];
        let availabilityDays = @json($availabilityDays);
        let vacations = @json($vacations);
        let urgencies = @json($urgencies);
        //console.log("🩺 Urgencies loaded:", urgencies);
        window.activeDoctorId = {{ $doctorId ?? 'null' }};
        $(document).ready(function () {
            //
            fetchAvailableDaysAndInitializeCalendar();
            // Initialize field visibility based on patient type selection
            function toggleFields() {
                $('#referencedFields').show();
                $('#walkInFields').hide();
                $('#time-slots-wrapper').show();
                $('#saveAppointmentRef').show();
                $('#saveAppointmentPass').hide();
            }

            toggleFields();

            $('#patientRef, #patientPass').on('change', toggleFields);
            function refreshCalendarEvents() {
                $('#calendar').fullCalendar('refetchEvents'); // Fetch and reload events
                //console.log("Calendar events refreshed");
            }

            // Set interval to refresh calendar every 30 seconds
            //setInterval(refreshCalendarEvents, 10000);

            //const timeSlots = ["14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];
            const timeSlotsContainer = document.getElementById('time-slots');
            function updateAvailableTimeSlots(response) {
                const { all_slots, taken_slots, type } = response;
                //console.log("Response received:", response);

                const timeSlotsWrapper = $("#time-slots");
                timeSlotsWrapper.empty();

                // Handle vacation case
                if (response.vacation) {
                    timeSlotsWrapper.append(` <div class="alert alert-warning text-center"> Le docteur est en vacances pour ce jour. Aucune disponibilité n'est disponible. </div> `);

                    return;
                }

                // If no slots available for this type
                if (!all_slots || all_slots.length === 0) {
                    timeSlotsWrapper.append(` <div class="alert alert-info text-center"> Aucun créneau disponible pour ${getTypeLabel(type)}. </div> `);

                    return;
                }

                const selectedDate = $("#appointmentDate").val();
                const isToday = selectedDate === moment().format("YYYY-MM-DD");



                all_slots.forEach(slot => {
                    const time = typeof slot === "object" ? slot.time || slot.slot : slot;
                    const slotElement = $('<div>')
                        .addClass('time-slot')
                        .text(time);

                    if (taken_slots.includes(time)) {
                        slotElement.addClass('taken-slot')
                            .css('background-color', '#e9ecef');
                    } else if (isToday && moment(`${selectedDate} ${time}`, "YYYY-MM-DD HH:mm").isBefore(moment())) {
                        slotElement.addClass('passed-slot')
                            .css({
                                'background-color': '#e9ecef',
                                'pointer-events': 'none',
                                'opacity': '0.6'
                            });
                    } else {
                        slotElement.addClass('available-slot')
                            .on('click', function () {
                                $('.time-slot').removeClass('selected');
                                $(this).addClass('selected');
                                $('#appointment_time').val(time);
                            });
                    }
                    timeSlotsWrapper.append(slotElement);
                });
            }

            function getTypeLabel(type) {
                const typeLabels = {
                    'cabinet': 'Cabinet',
                    'teleconsultation': 'teleconsultation',
                    'home_visit': 'Visite à domicile',
                };
                return typeLabels[type] || type;
            }

            function selectTimeSlot(element, time) {
                document.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('selected'));
                element.classList.add('selected');
                $('#appointment_time').val(time);
            }
            ////////////////////////////////////////////////
            $('#addMoreAvailable').click(function () {
                const selectedDate = $('#appointmentDate').val(); // Get the selected date from the input field

                // Check if a date is selected
                if (selectedDate) {
                    // Redirect to the URL with the selected date
                    window.location.href = `/availability`;
                } else {
                    alert("Please select a date before adding availability.");
                }
            });
            ///////////////////////////////////////////////
            $('#appointmentModal').on('hidden.bs.modal', function () {
                // Reset form
                $('#appointmentForm')[0].reset();
                // Clear time slots
                $('#time-slots').empty();
                // Reset hidden type input
                $('#appointmentType').val('cabinet');
                $('#patientDropdown').val(null).trigger('change');
                $('.pattern-select-group').addClass('d-none');
                $('#patern_id_cabinet').closest('.pattern-select-group').removeClass('d-none');

            });
            ///////////////////////////////////////////////
            $("#appointmentTypeTabs a").on("click", function (e) {
                e.preventDefault();
                //console.log("Tab Clicked:", this);
                const selectedType = $(this).data("type");
                const selectedDate = $("#appointmentDate").val();

                // Update hidden type input
                $('#appointmentType').val(selectedType);

                // Show this tab
                $(this).tab('show');
                //console.log("Selected Type:", selectedType);
                $('.pattern-select-group').addClass('d-none');
                $('#patern_id_' + selectedType).closest('.pattern-select-group').removeClass('d-none');

                if (selectedDate) {
                    fetchTimeSlotsForType(selectedDate, selectedType);
                }
            });
            $('#appointmentTypeTabs a').on('shown.bs.tab', function (e) {
                //console.log("Tab Shown:", e.target);
                const selectedType = $(e.target).data('type');
                const selectedDate = $('#appointmentDate').val();
                $('#appointmentType').val(selectedType);
                $('.pattern-select-group').addClass('d-none');
                $('#patern_id_' + selectedType).closest('.pattern-select-group').removeClass('d-none');

                if (selectedDate) {
                    fetchTimeSlotsForType(selectedDate, selectedType);
                }
            });
            function fetchAvailableDaysAndInitializeCalendar() {
                $.ajax({
                    url: "/get-available-time-slots", // Ensure this API returns the available days
                    type: "GET",
                    success: function (response) {
                        //console.log("Fetched Available Days:", response);
                        availabilityDays = response.available_days.map(day => day.toLowerCase()); // Convert to lowercase

                        // Now initialize the calendar AFTER fetching availability days
                        initializeCalendar();
                    },
                    error: function () {
                        console.error("Erreur lors de la récupération des jours disponibles.");
                        initializeCalendar(); // Still initialize the calendar to prevent UI blocking
                    }
                });
            }

            // Modify fetchTimeSlotsForType function
            function fetchTimeSlotsForType(date, type) {
                $.ajax({
                    url: "/get-available-time-slots-open",
                    type: "GET",
                    data: {
                        date: date,
                        type: type
                    },
                    success: function (response) {
                        //console.log("Fetched Time Slots Response:", response);

                        if (response.vacation) {
                            // If doctor is on vacation, show a warning alert (still using Swal)
                            Swal.fire({
                                title: "Le docteur est en vacances",
                                text: "Aucune disponibilité n'est possible ce jour.",
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                            return;
                        }

                        // Show the appointment modal first (it should be visible regardless of slots)
                        $('#appointmentModal').modal('show');

                        if (!response.all_slots || response.all_slots.length === 0) {
                            // No available slots → Show a message inside the modal
                            $("#time-slots").html(` <div class="alert alert-warning text-center"> Aucune disponibilité pour ce type de rendez-vous à cette date. </div> `);
                            return;
                        }

                        // If slots are available, update the available slots section
                        updateAvailableTimeSlots(response);
                    },
                    error: function (xhr) {
                        // General error handling
                        Swal.fire({
                            title: "Erreur",
                            text: "Une erreur s'est produite lors de la récupération des créneaux horaires. Veuillez réessayer.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }

            // Function to clear the time slots container
            function clearTimeSlots() {
                const timeSlotsWrapper = $("#time-slots");
                timeSlotsWrapper.empty(); // Clear the container
                timeSlotsWrapper.append(` <div class="alert alert-info text-center"> Aucun créneau disponible trouvé. </div> `);

            }
            //////////////////////////////////////////////////////////////////////////////
            function fetchSubstitutes(doctorId) {
                return $.ajax({
                    url: `/substitutes/${doctorId}`,
                    method: 'GET',
                    dataType: 'json'
                });
            }
            //////////////////////////////////////////////////////////////////////////////#
            function fetchAppointmentStats(doctorId, selectedDate) {
                return $.ajax({
                    url: `/appointments/stats/${doctorId}/${selectedDate}`,
                    method: 'GET',
                    dataType: 'json'
                });
            }

            //////////////////////////////////////////////////////////////////////////////
            function refreshAppointmentStats(doctorId) {
                $('.custom-number-label').remove(); // Clear existing numbers
                $('.fc-day-header').each(function () {
                    fetchAppointmentStats(doctorId).then(stats => {
                        let staticNumber = `${stats.appointments_taken} / ${stats.total_appointments}`;
                        $(this).append(`<div class="custom-number-label">${staticNumber}</div>`);
                    }).catch(error => {
                        console.error("Error refreshing appointment stats:", error);
                    });
                });
            }
            //    ////////////////////////////////////////////////////////////////////////////
            $('#patientDropdown').select2({
                allowClear: false,
                ajax: {
                    url: "{{ route('patients.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
            let existingEventIds = []; // Array to store existing event IDs

            //console.log("Availability Days at Load:", availabilityDays);
            function initializeCalendar() {
                calendar = $('#calendar').fullCalendar({
                    locale: 'fr',
                    editable: true,
                    height: 670,
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    defaultView: 'agendaWeek',
                    minTime: "08:00:00",
                    allDaySlot: true,
                    allDayText: '',
                    events: '/appointment-event',
                    eventLimit: true,
                    viewRender: function (view) {
                        console.log('Calendar view changed:', view);

                        // console.log('[DEBUG] viewRender triggered:', view.name);

                        if (view.name === 'agendaWeek') {
                            const doctorId = {{ auth()->user()->getDoctorId() }};

                            // Create tooltip container once
                            if (!$('#substitute-tooltip').length) {
                                $('body').append('<div id="substitute-tooltip" class="substitute-tooltip"></div>');
                            }

                            fetchSubstitutes(doctorId).then(substitutes => {
                                //console.log("Substitutes:", substitutes);
                                $('.fc-day-header').each(function () {
                                    //$(this).find('.day-header-divider, .custom-day-label, .custom-number-label').remove();
                                    if ($(this).hasClass('substitute-initialized')) {
                                        return;
                                    }
                                    // Mark it as done before we do anything else
                                    $(this).addClass('substitute-initialized');
                                    $(this).find('.day-header-divider, .custom-day-label, .custom-number-label').remove();
                                    //console.log('[DEBUG] Setting up header for:', $(this).data('date'));
                                    // Prevent multiple double-renders in the same pass
                                    if (!$(this).hasClass('substitute-initialized')) {
                                        $(this).addClass('substitute-initialized');
                                        // ... fetch & append your custom elements ...
                                    }

                                    let dayDate = $(this).data('date');
                                    let dayMoment = moment(dayDate);

                                    // Find the substitute active on this day
                                    let activeSubstitute = substitutes.find(sub => {
                                        let startDate = moment(sub.start_date);
                                        let endDate = moment(sub.end_date);
                                        return dayMoment.isBetween(startDate, endDate, 'day', '[]');
                                    });

                                    // Fetch and set the dynamic number
                                    fetchAppointmentStats(doctorId, dayDate).then(stats => {
                                        let totalAppointments = stats && stats.total_appointments ? stats.total_appointments : 0;
                                        let appointmentsTaken = stats && stats.appointments_taken ? stats.appointments_taken : 0;
                                        let staticNumber = `${appointmentsTaken}/${totalAppointments}`;
                                        let substituteName = activeSubstitute ? activeSubstitute.name : "&nbsp;";

                                        // Create custom label with hover functionality
                                        // Append all elements with proper structure
                                        $(this).append(` <hr class="day-header-divider"> <div class="custom-day-label substitute-hover">${substituteName}</div> <hr class="day-header-divider"> <div class="custom-number-label">${staticNumber}</div> `);

                                        if (activeSubstitute) {
                                            $(this).find('.custom-day-label').hover(
                                                function (e) {
                                                    let tooltip = $('#substitute-tooltip');
                                                    let tooltipContent = ` <div class="substitute-info-container"> <div class="substitute-info"> <span class="substitute-info-label">Nom:</span> <span class="substitute-info-value">${activeSubstitute.name}</span> </div> <div class="substitute-info"> <span class="substitute-info-label">Début:</span> <span class="substitute-info-value">${moment(activeSubstitute.start_date).format('DD/MM/YYYY HH:mm')}</span> </div> <div class="substitute-info"> <span class="substitute-info-label">Fin:</span> <span class="substitute-info-value">${moment(activeSubstitute.end_date).format('DD/MM/YYYY HH:mm')}</span> </div> ${activeSubstitute.notes ? ` <div class="substitute-info"> <span class="substitute-info-label">Notes:</span> <span class="substitute-info-value">${activeSubstitute.notes}</span> </div> ` : ''} </div> `;

                                                    tooltip.html(tooltipContent);

                                                    // Position the tooltip
                                                    let pos = $(this).offset();
                                                    tooltip.css({
                                                        top: pos.top + $(this).outerHeight() + 5,
                                                        left: pos.left
                                                    }).fadeIn(200);
                                                },
                                                function () {
                                                    $('#substitute-tooltip').fadeOut(200);
                                                }
                                            );
                                        }


                                    }).catch(error => {
                                        console.error("Error fetching appointment stats:", error);
                                    });
                                });
                            }).catch(error => {
                                console.error("Error fetching substitutes:", error);
                            });
                        }
                    },
                    dayRender: function (date, cell) {

                        //console.log("Day Rendered:", date.format());
                        const formattedDayName = date.locale('en').format('dddd').toLowerCase();
                        const normalizedAvailabilityDays = availabilityDays.map(day => day.toLowerCase());
                        const today = moment().startOf('day');
                        const currentDay = date.startOf('day');
                        const formattedDate = currentDay.format('YYYY-MM-DD');

                        // Check vacation days first
                        const isVacationDay = vacations.some(vacation => {
                            const start = moment(vacation.start_date, 'YYYY-MM-DD').startOf('day');
                            const end = moment(vacation.end_date, 'YYYY-MM-DD').endOf('day');
                            return currentDay.isSameOrAfter(start) && currentDay.isSameOrBefore(end);
                        });
                        const isUrgentDay = urgencies.some(urgency => {

                            const urgencyDate = moment(urgency.jour, 'YYYY-MM-DD');
                            //console.log(`Comparing ${urgencyDate.format('YYYY-MM-DD')} === ${currentDay.format('YYYY-MM-DD')}`);
                            return currentDay.isSame(urgencyDate, 'day');
                        });
                        //console.log("Is Urgent Day:", isUrgentDay);

                        //console.log("Is Vacation Day:", isVacationDay);
                        if (currentDay.isBefore(today)) {
                            cell.css('background-color', '#e9ecef');
                            cell.css('cursor', 'not-allowed');
                            cell.css('color', '#721c24');
                            cell.css('position', 'relative');
                            cell.append('<span class="dot past-dot"></span>');
                            cell.attr('title', 'Ce jour est dans le passé.');
                        } else if (isVacationDay) {
                            //console.log("Rendering urgent UI for:", formattedDate);
                            cell.addClass('cell-with-background');
                            cell.css('cursor', 'not-allowed');
                            cell.css('background-color', '#f2f2f2');
                            cell.attr('title', 'Le docteur est en vacances ce jour.');
                        } else if (isUrgentDay) {
                            cell.addClass('cell-with-background');
                            cell.css('background-color', '#ffe6e6'); // light red
                            cell.attr('title', 'Urgence: Le docteur est en urgence ce jour.');
                        } else if (normalizedAvailabilityDays.includes(formattedDayName)) {
                            // Check availability for all appointment types
                            $.ajax({
                                url: "/get-available-time-slots",
                                type: "GET",
                                data: {
                                    date: formattedDate,
                                    checkAllTypes: true // Add a flag to check all types
                                },
                                success: function (response) {
                                    const allSlots = response.all_slots || [];
                                    const takenSlots = response.taken_slots || [];

                                    if (allSlots.length > 0 && allSlots.length === takenSlots.length) {
                                        // All slots are taken
                                        cell.css('position', 'relative');
                                        cell.find('.dot').remove(); // Remove any existing dots
                                        cell.append('<span class="dot unavailable-dot"></span>');
                                        cell.attr('title', 'Tous les créneaux sont pris pour ce jour');
                                    } else if (allSlots.length > 0) {
                                        // Some slots are available
                                        cell.css('position', 'relative');
                                        cell.find('.dot').remove(); // Remove any existing dots
                                        cell.append('<span class="dot available-dot"></span>');
                                        cell.attr('title', 'Créneaux disponibles');
                                    } else {
                                        // No slots configured
                                        cell.css('position', 'relative');
                                        cell.find('.dot').remove(); // Remove any existing dots
                                        cell.append('<span class="dot unavailable-dot"></span>');
                                        cell.attr('title', 'Aucun créneau configuré');
                                    }
                                },
                                error: function () {
                                    cell.css('position', 'relative');
                                    cell.append('<span class="dot unavailable-dot"></span>');
                                }
                            });
                        } else {
                            cell.css('position', 'relative');
                            cell.append('<span class="dot unavailable-dot"></span>');
                            cell.attr('title', 'Jour non disponible');
                        }
                    },
                    events: function (start, end, timezone, callback) {
                        $.ajax({
                            url: "/appointment-event",
                            type: "GET",
                            data: {
                                start: start.format("YYYY-MM-DD HH:mm:ss"),
                                end: end.format("YYYY-MM-DD HH:mm:ss")
                            },
                            dataType: "json",
                            success: function (data) {
                                const statusTranslation = {
                                    "Received": "Reçu",
                                    "In Progress": "En cours",
                                    "On the Way": "En route",
                                    "Accepted": "Accepté",
                                    "Ready": "Prêt",
                                    "Done": "Terminé",
                                    "Failed": "Annulé"
                                };

                                const events = data.map(event => {
                                    //console.log("Event:", event);
                                    let color = '';
                                    let translatedStatus = statusTranslation[event.status] || event.status; // Default to original if no translation

                                    switch (translatedStatus) {
                                        case 'Accepté':
                                            color = '#9FCDA8';
                                            break;
                                        case 'Terminé':
                                            color = '#7DC2A5';
                                            break;
                                        case 'En cours':
                                            color = '#F5DF4D';
                                            break;
                                        case 'Annulé':
                                            color = '#F38071';
                                            break;
                                        case 'Reçu':
                                            color = '#A594F9';
                                            break;
                                        case 'Prêt':
                                            color = '#9EDF9C';
                                            break;
                                        default:
                                            color = '#B4BAFF';
                                    }

                                    return {
                                        id: event.id,
                                        online: event.online,
                                        title: `${event.patient_name} - ${event.motif_name}`,
                                        start: moment(event.start_at).format(), // Adjust for timezone here
                                        end: moment(event.ends_at).format(),
                                        patient_phone_number: event.patient_phone_number,
                                        email: event.patient_email,
                                        patient_first_name: event.patient_first_name,
                                        patient_last_name: event.patient_last_name,
                                        patient_id: event.patient_id,
                                        backgroundColor: color,
                                        borderColor: color,
                                        description: `Patient: ${event.patient_name}\nStatus: ${translatedStatus}\nDetails: ${event.motif_name || 'N/A'}`,
                                        patient_name: event.patient_name,
                                        status: translatedStatus,              // Add status here
                                        details: event.motif_name || 'N/A',
                                        motif_name: event.motif_name,
                                        appointment_type: event.type,
                                        note: event.note,
                                    };
                                });
                                callback(events);
                            }
                        });
                    },
                    loading: function (isLoading, view) {
                        if (!isLoading) {
                            // Delay highlighting to ensure events are fully loaded
                            setTimeout(() => {
                                //console.log("Events loaded, highlighting new events...");

                                // Log all events fetched by FullCalendar
                                const allEvents = $('#calendar').fullCalendar('clientEvents');
                                //console.log('All Events:', allEvents);

                                highlightNewEvents();
                            }, 100); // Small delay to ensure events are fully loaded
                        }
                    },
                    eventRender: function (event, element) {
                        // Add a custom data-id attribute to the event element
                        element.attr('data-id', event.id);

                        //console.log('Rendering event:', event);

                        // Highlight the event if it's marked as new
                        if (event.isNew) {
                            console.log('Highlighting new event:', event);
                            element.addClass('highlight-event'); // Add the highlight class

                            setTimeout(() => {
                                element.removeClass('highlight-event'); // Remove the class after 2 seconds
                                event.isNew = false; // Reset the isNew flag
                            }, 2000);
                        } else {
                            //console.log('Event is not new:', event);
                        }
                        // Adding title attribute for simple tooltip
                        let icon;

                        // Determine the icon based on the `online` field
                        switch (event.online) {
                            case 'cabinet':
                                icon = '<i class="fas fa-briefcase-medical" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            case 'teleconsultation':
                                icon = '<i class="fas fa-video" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Video icon
                                break;
                            case 'home_visit':
                                icon = '<i class="fas fa-home" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Home visit icon
                                break;
                            case 'web':
                                icon = '<i class="fas fa-globe" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Web icon
                                break;
                            case 'mobile':
                                icon = '<i class="fas fa-mobile-alt" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Mobile icon
                                break;
                            default:
                                icon = '<i class="fas fa-question-circle" style="margin-right: 5px; color: #ccc;"></i>'; // Default icon
                                break;
                        }
                        element.find('.fc-title').prepend(icon);
                        element.attr('title', event.description);
                        element.find('.fc-title').css('white-space', 'nowrap');
                        element.find('.fc-time').css('font-size', '1em');

                    },
                    selectable: true,
                    selectAllow: function (selectInfo) {
                        // Only allow selection if the day is in availabilityDays
                        //return availabilityDays.includes(selectInfo.start.format("YYYY-MM-DD"));

                    },
                    selectHelper: true,
                    select: function (start, end) {
                        const selectedDate = start.format("YYYY-MM-DD");
                        //const selectedDayName = start.format('dddd').toLowerCase(); // Get day name in lowercase
                        const selectedDayName = moment(selectedDate).locale('en').format("dddd").toLowerCase(); // Ensure English name
                        const today = moment().format("YYYY-MM-DD");
                        //console.log("Selected Date:", selectedDate);
                        //console.log("Selected Day:", selectedDay);
                        //console.log("Selected Day Name:", selectedDayName);
                        const currentDay = start.clone().startOf('day');

                        // First check if date is in the past
                        if (moment(selectedDate).isBefore(today)) {
                            $('#pastDateModal').modal('show');
                            $('#pastDateModalMessage').text("Vous avez sélectionné une date antérieure. Veuillez sélectionner une date future.");
                            return;
                        }

                        // Check if it's a vacation day
                        const isVacationDay = vacations.some(vacation => {
                            const start = moment(vacation.start_date, 'YYYY-MM-DD').startOf('day');
                            const end = moment(vacation.end_date, 'YYYY-MM-DD').endOf('day');
                            return currentDay.isSameOrAfter(start) && currentDay.isSameOrBefore(end);
                        });


                        if (isVacationDay) {
                            Swal.fire({
                                title: "Le docteur est en vacances",
                                text: "Vous ne pouvez pas sélectionner cette date.",
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                            return;
                        }

                        // Check if the day is available
                        $.ajax({
                            url: "/get-available-time-slots",
                            type: "GET",
                            success: function (response) {
                                //console.log("Available Days Response:", response);
                                const availableDays = response.available_days.map(day => day.toLowerCase());

                                if (!availableDays.includes(selectedDayName)) {
                                    Swal.fire({
                                        title: "Jour non disponible",
                                        text: "Le docteur n'est pas disponible ce jour-là.",
                                        icon: "warning",
                                        confirmButtonText: "OK"
                                    });
                                    return;
                                }

                                // If day is available, proceed to check time slots for each type
                                $('#appointmentDate').val(selectedDate);

                                // Check availability for all types
                                const types = ['cabinet', 'teleconsultation', 'home_visit'];
                                let foundSlots = false;
                                let checkedTypes = 0;

                                types.forEach(type => {
                                    $.ajax({
                                        url: "/get-available-time-slots-open",
                                        type: "GET",
                                        data: { date: selectedDate, type: type },
                                        success: function (response) {
                                            checkedTypes++;

                                            if (response.all_slots && response.all_slots.length > 0) {
                                                foundSlots = true;

                                                // If this is the first type with available slots, select its tab
                                                if (!$('#appointmentModal').is(':visible')) {
                                                    $('#appointmentModal').modal('show');
                                                    $(`#appointmentTypeTabs a[data-type="${type}"]`).tab('show');
                                                    fetchTimeSlotsForType(selectedDate, type);
                                                }
                                            }

                                            // If we've checked all types and found no slots
                                            if (checkedTypes === types.length && !foundSlots) {
                                                $('#confirmationModal').modal('show');
                                                $('#confirmCreateAvailability').off('click').on('click', function () {
                                                    window.location.href = `/availability`;
                                                });
                                            }
                                        },
                                        error: function () {
                                            checkedTypes++;
                                            if (checkedTypes === types.length && !foundSlots) {
                                                $('#confirmationModal').modal('show');
                                            }
                                        }
                                    });
                                });
                            },
                            error: function () {
                                Swal.fire({
                                    title: "Erreur",
                                    text: "Impossible de vérifier les jours disponibles.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    },
                    editable: true,
                    eventResize: function (event) {
                        $.ajax({
                            url: "/appointment-event/action",
                            type: "POST",
                            data: {
                                id: event.id,
                                start_at: event.start.utc().format('YYYY-MM-DD HH:mm:ss'),
                                ends_at: event.end.utc().format('YYYY-MM-DD HH:mm:ss'),
                                type: 'update'

                            },
                            success: function () {
                                calendar.fullCalendar('refetchEvents');
                                alert("Rendez-vous mis à jour avec succès.");
                                //console.log("Start At:", start_at);
                                //console.log("End At:", ends_at);
                            }
                        });
                    },
                    eventDrop: function (event) {
                        $.ajax({
                            url: "/appointment-event/action",
                            type: "POST",
                            data: {
                                id: event.id,
                                start_at: event.start.format(),
                                ends_at: event.end.format(),
                                type: 'update'
                            },
                            success: function () {
                                calendar.fullCalendar('refetchEvents');
                                alert("Rendez-vous mis à jour avec succès.");
                            }
                        });
                    },
                    eventClick: function (event) {
                        const appointment = {
                            appointment_id: event.id,
                            patient_name: event.patient_name,
                            patient_first_name: event.patient_first_name,
                            patient_last_name: event.patient_last_name,
                            email: event.email,
                            patient_id: event.patient_id,
                            status_id: event.status_id,
                            status: event.status,
                            motif_id: event.motif_id,
                            motif_name: event.motif_name,
                            online: event.online,
                            start: event.start.format(),
                            end: event.end ? event.end.format() : null,
                            notes: event.notes,
                            phone: event.patient_phone_number
                        };

                        // Populate modal fields
                        $('#appointmentId').val(appointment.appointment_id);
                        $('#patientName').val(appointment.patient_name);
                        $('#appointmentDate').val(moment(appointment.start).format('YYYY-MM-DDTHH:mm'));
                        $('#notes').val(appointment.notes || '');

                        // Populate motif dropdown
                        $.get('/motifs', function (motifs) {
                            $('#motifId').empty();
                            $('#motifId').append('<option value="">Select Motif</option>');
                            motifs.forEach(motif => {
                                $('#motifId').append(`<option value="${motif.id}" ${motif.id == appointment.motif_id ? 'selected' : ''}>${motif.name}</option>`);
                            });
                        });

                        // Populate status dropdown
                        $.get('/appointment-statuses', function (statuses) {
                            $('#appointmentStatus').empty();
                            $('#appointmentStatus').append('<option value="">Select Status</option>');
                            statuses.forEach(status => {
                                $('#appointmentStatus').append(`<option value="${status.id}" ${status.id == appointment.status_id ? 'selected' : ''}>${status.status}</option>`);
                            });
                        });

                        // Show modal
                        $('#appointmentDetailsModal').modal('show');

                        // Event handler for "Mark as Failed"
                        $('#markAsFailed').off('click').on('click', function () {
                            $('#appointmentDetailsModal').modal('hide');
                            $('#cancelReasonModal').modal('show');

                            $('#confirmCancel').off('click').on('click', function () {
                                const reason = $('#cancelReason').val() || "Aucune raison fournie";
                                updateAppointmentStatus(appointment.appointment_id, 7, reason);
                            });
                        });

                        // Event handler for "Mark as Done"
                        $('#markAsDone').off('click').on('click', function () {
                            updateAppointmentStatus(appointment.appointment_id, 5);
                        });

                        // Event handler for "Save Changes"
                        $('#saveAppointment').off('click').on('click', function () {
                            const data = {
                                id: $('#appointmentId').val(),
                                appointment_at: $('#appointmentDate').val(),
                                motif_id: $('#motifId').val(),
                                appointment_status_id: $('#appointmentStatus').val(),
                                notes: $('#notes').val(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            };

                            $.ajax({
                                url: '/appointments/update',
                                method: 'POST',
                                data: data,
                                success: function (response) {
                                    Swal.fire({
                                        title: 'Succès',
                                        text: 'Appointment updated successfully',
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        $('#appointmentDetailsModal').modal('hide');
                                        $('#calendar').fullCalendar('refetchEvents');
                                    });
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        title: 'Erreur',
                                        text: xhr.responseJSON.error || 'Failed to update appointment',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        });

                        // Event handler for "Delete Appointment"
                        $('#deleteAppointment').off('click').on('click', function () {
                            Swal.fire({
                                title: 'Are you sure?',
                                text: 'This action cannot be undone!',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, delete it!',
                                cancelButtonText: 'No, keep it'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: `/appointments/delete/${$('#appointmentId').val()}`,
                                        method: 'DELETE',
                                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                                        success: function () {
                                            Swal.fire({
                                                title: 'Succès',
                                                text: 'Appointment deleted successfully',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            }).then(() => {
                                                $('#appointmentDetailsModal').modal('hide');
                                                $('#calendar').fullCalendar('refetchEvents');
                                            });
                                        },
                                        error: function (xhr) {
                                            Swal.fire({
                                                title: 'Erreur',
                                                text: xhr.responseJSON.error || 'Failed to delete appointment',
                                                icon: 'error',
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
            }
            $('#saveAppointment').on('click', function (e) {
                e.preventDefault();
                //console.log('Save button clicked'); // Debug log
                const activeTab = $('#appointmentTypeTabs .nav-link.active');
                const appointmentType = activeTab.data('type');
                //console.log('Active tab type:', appointmentType); // Debug log
                const patternSelectId = '#patern_id_' + appointmentType;
                // Get form data
                const appointmentData = {
                    patient_id: $('#patientDropdown').val(),
                    appointment_date: $('#appointmentDate').val(),
                    appointment_time: $('#appointment_time').val(),
                    patern_id: $(patternSelectId).val(),
                    appointment_type: appointmentType, // Use the active tab's type
                    notes: $('#appointment_notes').val(), // Add notes field
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                //console.log('Appointment Data:', appointmentData); // Debug log

                // Validate form data
                if (!appointmentData.patient_id) {
                    Swal.fire({
                        title: "Erreur",
                        text: "Veuillez sélectionner un patient",
                        icon: "error"
                    });
                    return;
                }

                if (!appointmentData.appointment_time) {
                    Swal.fire({
                        title: "Erreur",
                        text: "Veuillez sélectionner une heure de rendez-vous",
                        icon: "error"
                    });
                    return;
                }

                if (!appointmentData.patern_id) {
                    Swal.fire({
                        title: "Erreur",
                        text: "Veuillez sélectionner un motif",
                        icon: "error"
                    });
                    return;
                }

                // Send AJAX request
                $.ajax({
                    url: "{{ route('appointments.store') }}",
                    method: "POST",
                    data: appointmentData,
                    success: function (response) {
                        //console.log('Success:', response); // Debug log

                        Swal.fire({
                            title: "Succès",
                            text: "Rendez-vous créé avec succès",
                            icon: "success"
                        }).then((result) => {
                            $('#appointmentModal').modal('hide');
                            // $('#calendar').fullCalendar('refetchEvents');
                        });
                    },
                    error: function (xhr) {
                        //console.log('Error:', xhr); // Debug log

                        let errorMessage = "Une erreur s'est produite";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: "Erreur",
                            text: errorMessage,
                            icon: "error"
                        });
                    }
                });
            });

            // Add form submit prevention
            $('#appointmentForm').on('submit', function (e) {
                e.preventDefault();
            });

            function updateAppointmentStatus(appointmentId, statusId, reason = null) {
                const data = {
                    id: appointmentId,
                    appointment_status_id: statusId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
                //console.log("Update Status Data:", data);
                if (reason) {
                    data.cancel_reason = reason;
                }

                $.ajax({
                    url: "/appointment-event/status",
                    method: "POST",
                    data: data,
                    success: function (response) {
                        Swal.fire({
                            title: "Succès",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            $('#cancelReasonModal').modal('hide');
                            $('#appointmentDetailsModal').modal('hide');
                            $('#calendar').fullCalendar('refetchEvents');
                            //console.log("Appointment status updated successfully.");

                        });
                        $('#calendar').fullCalendar('refetchEvents');
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: "Erreur",
                            text: xhr.responseJSON.error || "Une erreur s'est produite lors de la mise à jour du statut.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }

            $('#saveAppointmentPass').click(function () {
                // Gather form data from "Patient de passage" fields
                let formData = {
                    first_name: $('input[name="first_name"]').val(),
                    last_name: $('input[name="last_name"]').val(),
                    email: $('input[name="email"]').val(),
                    phone_number: $('input[name="phone_number"]').val(),
                    mobile_number: $('input[name="mobile_number"]').val(),
                    age: $('input[name="age"]').val(),
                    gender: $('select[name="gender"]').val(),
                    weight: $('input[name="weight"]').val(),
                    height: $('input[name="height"]').val(),
                    medical_history: $('textarea[name="medical_history"]').val(),
                    notes: $('textarea[name="notes"]').val(),
                    password: $('input[name="password"]').val(),
                    appointment_date: $('#appointmentDate').val(),
                    appointment_time: $('#appointment_time').val(),
                    _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
                };

                $.ajax({
                    url: "{{ route('appointments.storePatientPassage') }}", // Route to your controller method
                    method: "POST",
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            alert(response.message); // Notify the user of success
                            $('#appointmentModal').modal('hide'); // Hide the modal
                            $('#appointmentForm')[0].reset(); // Reset the form
                            $('#calendar').fullCalendar('refetchEvents'); // Refresh the calendar
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) { // Validation error from Laravel
                            let errors = xhr.responseJSON.errors;
                            let errorMessages = Object.values(errors).flat().join('\n');
                            alert("Validation Errors:\n" + errorMessages);
                        } else {
                            alert("An unexpected error occurred. Please try again.");
                        }
                    }
                });
            });
            function openAppointmentModal(appointment) {
                const { start, patient_id, appointment_id, patient_name, patient_first_name, patient_last_name, email, status, motif_name, online, phone } = appointment;


                // Update modal fields
                document.getElementById("patientName").innerText = patient_name || "{{ trans('lang.unknown_patient') }}";
                document.getElementById("appointmentStatus").innerText = status || "{{ trans('lang.unknown_status') }}";
                document.getElementById("motifName").innerText = motif_name || "{{ trans('lang.no_motif_name') }}";

                // Pass phone number to teleconsultation button
                const teleconsultationButton = document.getElementById("createTeleconsultation");
                // Hide "Mark as Ready" button if status is "Canceled"
                const doneButton = document.getElementById("markAsDone");
                const failedButton = document.getElementById("markAsFailed");
                teleconsultationButton.setAttribute("data-phone", phone);
                const hasUpdateStatusPermission = {{ auth()->user()->hasPermissionInContext('updateStatus', $doctorId) ? 'true' : 'false' }};
                const isDoctor = {{ auth()->user()->hasRole('doctor') ? 'true' : 'false' }};

                // Show/hide the "Create Teleconsultation" button
                if (isDoctor && online === "teleconsultation" && status !== "Annulé" && status !== "Terminé") {

                    teleconsultationButton.classList.remove("d-none");
                    teleconsultationButton.onclick = () => {
                        const url = `{{ route('show.meeting.info.form') }}?patient_name=${encodeURIComponent(patient_name)}&appointment_id=${encodeURIComponent(appointment_id)}&phone=${encodeURIComponent(phone)}&motif_name=${encodeURIComponent(motif_name)}&patient_id=${encodeURIComponent(patient_id)}&start_at=${encodeURIComponent(start)}&patient_first_name=${encodeURIComponent(patient_first_name)}&patient_last_name=${encodeURIComponent(patient_last_name)}&patient_Email=${encodeURIComponent(email)}`;
                        window.location.href = url;
                    };

                } else {
                    teleconsultationButton.classList.add("d-none");
                }

                if (hasUpdateStatusPermission) {
                    if (online === "teleconsultation") {
                        doneButton.style.display = "none";
                        failedButton.style.display = "inline-block";
                    }
                    else if (status === "Terminé" || status === "Annulé") {
                        doneButton.style.display = "none"; // Hide the Ready button
                        failedButton.style.display = "none"; // Optionally hide the Failed button too

                    } else if (status === "Prêt") {
                        doneButton.style.display = "none"; // Hide the Ready button
                        failedButton.style.display = "inline-block";
                    }
                    else {
                        doneButton.style.display = "inline-block"; // Show the Ready button
                        failedButton.style.display = "inline-block"; // Show the Failed button
                    }
                } else {
                    // If the user does not have permission, hide both buttons
                    doneButton.style.display = "none";
                    failedButton.style.display = "none";
                }
                // Show the modal
                $("#appointmentDetailsModal").modal("show");
            }
            // Function to highlight new events
            function highlightNewEvents() {
                const currentEvents = $('#calendar').fullCalendar('clientEvents'); // Get all current events
                const currentEventIds = currentEvents.map(event => event.id); // Extract event IDs

                //console.log('Existing Event IDs:', existingEventIds);
                //console.log('Current Event IDs:', currentEventIds);

                // Identify new events
                const newEventIds = currentEventIds.filter(id => !existingEventIds.includes(id));

                //console.log('New Event IDs:', newEventIds);

                // Directly highlight new events
                newEventIds.forEach(id => {
                    const eventElement = $(`.fc-event[data-id="${id}"]`); // Find the event element
                    //console.log(`Event Element for ID ${id}:`, eventElement);
                    if (eventElement.length > 0) {
                        //console.log(`Highlighting new event with ID: ${id}`);
                        eventElement.addClass('highlight-event'); // Add the highlight class

                        setTimeout(() => {
                            eventElement.removeClass('highlight-event'); // Remove the class after 2 seconds
                        }, 2000);
                    }
                });

                // Update the list of existing event IDs
                existingEventIds = currentEventIds;

                console.log(`Highlighted ${newEventIds.length} new events.`);
            }
        });
    </script>

@endpush