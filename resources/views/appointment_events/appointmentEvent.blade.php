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

        <!-- Cancellation Reason Modal -->
        <div class="modal fade" id="cancelAppointmentModal" tabindex="-1" role="dialog"
            aria-labelledby="cancelAppointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="cancelAppointmentModalLabel"><i class="fas fa-times-circle"></i> Annuler le
                            rendez-vous</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label for="cancelReason">Veuillez préciser la raison de l'annulation (facultatif) :</label>
                        <textarea class="form-control" id="cancelReason"
                            placeholder="Exemple : Le patient ne s'est pas présenté"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="confirmCancelAppointment">Confirmer
                            l'annulation</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Viewing and Updating Appointment Details -->
        <div class="modal fade" id="appointmentDetailsModal" tabindex="-1" role="dialog"
            aria-labelledby="appointmentDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-header text-white d-flex justify-content-center">
                        <h5 class="modal-title mx-auto" id="appointmentDetailsModalLabel">
                            <i class="fas fa-calendar-check"></i> Détails du rendez-vous
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="container-fluid">
                            <div class="row">

                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <h6 class="mb-2 text-secondary">
                                        <i class="fas fa-user"></i> <strong>{{ trans('lang.patient_nom') }}</strong>
                                    </h6>
                                    <p id="patientName" class="text-dark font-weight-bold" style="font-size: 16px;"></p>

                                    <h6 class="mb-2 text-secondary">
                                        <i class="fas fa-clipboard-list"></i>
                                        <strong>{{ trans('lang.appointment_status') }}</strong>
                                    </h6>
                                    <p>
                                        <span id="appointmentStatus" class="badge badge-pill" style="font-size: 14px;"></span>
                                    </p>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <h6 class="mb-2 text-secondary">
                                        <i class="fas fa-stethoscope"></i> <strong>{{ trans('lang.motif_name') }}</strong>
                                    </h6>
                                    <p id="motifName" class="text-dark font-weight-bold" style="font-size: 16px;"></p>

                                    <h6 class="mb-2 text-secondary">
                                        <i class="fas fa-sticky-note"></i> <strong>{{ trans('lang.note') }}</strong>
                                    </h6>
                                    <div id="note" class="border rounded p-3"
                                        style="background-color: #f8f9fa; min-height: 60px; font-size: 14px; color: #333;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-primary d-none" id="createTeleconsultation">
                            <i class="fas fa-video"></i> {{ trans('lang.create_teleconsultation') }}
                        </button>
                        <button type="button" class="btn btn-danger" id="markAsFailed">
                            <i class="fas fa-times-circle"></i> {{ trans('lang.mark_failed') }}
                        </button>
                        <button type="button" class="btn btn-success" id="markAsDone">
                            <i class="fas fa-check-circle"></i> {{ trans('lang.mark_ready') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
        <!-- Modal for Forced Appointment -->
        <div class="modal fade" id="forcedAppointmentModal" tabindex="-1" aria-labelledby="forcedAppointmentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="forcedAppointmentModalLabel">
                            <i class="fas fa-calendar-plus"></i> {{ trans('lang.force_appointment') }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Appointment Type Tabs -->
                        <ul class="nav nav-tabs mb-3" id="forcedApptTypeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="forced-cabinet-tab" data-toggle="tab" href="#forced-cabinet-pane"
                                    role="tab" data-type="cabinet">
                                    <i class="fas fa-hospital"></i> {{ trans('lang.cabinet') }}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="forced-teleconsultation-tab" data-toggle="tab"
                                    href="#forced-teleconsultation-pane" role="tab" data-type="Téléconsultation">
                                    <i class="fas fa-video"></i> {{ trans('lang.teleconsultation') }}
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="forced-home-tab" data-toggle="tab" href="#forced-home-pane" role="tab"
                                    data-type="home_visit">
                                    <i class="fas fa-home"></i> {{ trans('lang.home_visit') }}
                                </a>
                            </li>
                        </ul>

                        <form id="forcedAppointmentForm" action="{{ route('appointmentsEvent.storeForced') }}" method="POST">
                            @csrf
                            <input type="hidden" name="forced_appointment" value="1">
                            <input type="hidden" name="appointment_type" id="forcedAppointmentType" value="cabinet">

                            <div class="tab-content" id="forcedApptTypeContent">
                                <div class="tab-pane fade show active" id="forced-cabinet-pane" role="tabpanel">
                                    <!-- Patient Selection -->
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ trans('lang.patient') }}</label>
                                        <select id="patientDropdownForced" name="patient_id" class="form-control" required>
                                            <option value="" selected disabled>{{ trans('lang.select_patient') }}</option>
                                        </select>
                                    </div>

                                    <!-- Motif Selection -->
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ trans('lang.motif') }}</label>
                                        <select id="forcedMotifDropdown" name="motif_id" class="form-control" required>
                                            <option value="" disabled selected>{{ trans('lang.select_motif') }}</option>
                                        </select>
                                    </div>

                                    <!-- Date and Time Selection -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ trans('lang.date') }}</label>
                                                <input type="date" class="form-control" id="forcedAppointmentDate"
                                                    name="appointment_date" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ trans('lang.start_time') }}</label>
                                                <input type="time" class="form-control" id="forcedAppointmentStartTime"
                                                    name="appointment_start_time" required />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ trans('lang.end_time') }}</label>
                                                <input type="time" class="form-control" id="forcedAppointmentEndTime"
                                                    name="appointment_end_time" required />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ trans('lang.notes') }}</label>
                                        <textarea class="form-control" id="forcedNotes" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Buttons -->
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> {{ trans('lang.save') }}
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> {{ trans('lang.cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal for Creating Appointment with Tabbed Types -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg"><!-- or modal-md if you prefer smaller -->
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">{{ trans('lang.create_modal_name') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    @if(auth()->user()->hasPermissionInContext('appointments.store', $doctorId))
                        <div class="modal-body">

                            <!-- ONE unified form for all appointment types -->
                            <form id="appointmentForm" action="{{ route('appointmentsEvent.store') }}" method="POST">
                                @csrf

                                <!-- Hidden field that stores the currently selected appointment type -->
                                <input type="hidden" name="appointment_type" id="appointmentType" value="cabinet">
                                <!-- Nav Tabs for 3 Types -->
                                <ul class="nav nav-tabs" id="apptTypeTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="cabinet-tab" data-toggle="tab" href="#cabinet-pane"
                                            data-type="cabinet" role="tab" aria-controls="cabinet-pane" aria-selected="true">
                                            {{ __('En Cabinet') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tele-tab" data-toggle="tab" href="#tele-pane"
                                            data-type="Téléconsultation" role="tab" aria-controls="tele-pane" aria-selected="false">
                                            {{ __('Téléconsultation') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#home-pane" data-type="home_visit"
                                            role="tab" aria-controls="home-pane" aria-selected="false">
                                            {{ __('Home Visit') }}
                                        </a>
                                    </li>
                                </ul>

                                <br>
                                <!-- Patient Selection -->
                                <div class="form-group">
                                    <label for="patientDropdown" class="font-weight-bold">{{ trans('lang.Select_Patient') }}</label>
                                    <div class="d-flex align-items-center">
                                        <select id="patientDropdown" name="patient_id" class="form-control select2-ajax" required>
                                            <option value="" disabled selected>{{ trans('lang.select_patient') }}</option>
                                        </select>
                                        <a href="{{ route('patients.create') }}" class="btn btn-success d-flex ml-2"
                                            id="addNewPatient">
                                            <i class="fa fa-user-plus"></i>
                                        </a>
                                    </div>
                                </div>
                                <input type="hidden" id="motif_id" name="motif_id" />
                                <!-- Motif (Pattern) Display -->
                                <div class="form-group">
                                    <label class="font-weight-bold">{{ trans('lang.motif') }}</label>
                                    <div id="patternDisplay" class="text-white p-2 rounded text-center"
                                        style="background-color: #cccccc; font-weight: bold; font-size: 16px;">
                                        {{ trans('lang.no_pattern_selected') }}
                                    </div>
                                </div>

                                <!-- Appointment Date -->
                                <div class="form-group">
                                    <label class="font-weight-bold" for="appointmentDate">{{ trans('lang.date') }}</label>
                                    <input type="date" class="form-control" style="text-align: center; font-size: 1rem;"
                                        id="appointmentDate" name="appointment_date" readonly />
                                </div>

                                <!-- Container for time slots (shared across all tabs) -->
                                <div class="time-slot-container d-flex flex-wrap justify-content-center"
                                    style="gap: 0.75rem; margin-top: 10px;" id="timeSlotContainer">
                                    <!-- Buttons will be injected by JavaScript -->
                                </div>

                                <!-- Hidden input to store the selected time slot -->
                                <input type="hidden" id="appointmentTime" name="appointment_time" />

                                <!-- Notes -->
                                <div class="form-group">
                                    <label class="font-weight-bold" for="notes">{{ __('lang.notes') }}</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="5"
                                        placeholder="{{ __('lang.write_note') }}"></textarea>
                                </div>



                                <!-- Save / Cancel Buttons -->
                                <div class="d-flex justify-content-end align-items-center mt-3">
                                    <button type="submit"
                                        class="btn btn-primary save-btn d-flex align-items-center px-3 py-2 shadow-sm">
                                        <i class="fas fa-save mr-2"></i> {{ trans('lang.save_patient_refer') }}
                                    </button>
                                    <button type="button"
                                        class="btn btn-light border cancel-btn d-flex align-items-center px-3 py-2 shadow-sm ml-2"
                                        data-dismiss="modal">
                                        <i class="fas fa-times mr-2"></i> {{ __('lang.cancel') }}
                                    </button>
                                </div>

                            </form>
                        </div>
                    @else
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                {{ __('Vous n’avez pas la permission de créer un rendez-vous.') }}
                            </div>
                        </div>
                    @endif

                </div> <!-- end .modal-content -->
            </div> <!-- end .modal-dialog -->
        </div> <!-- end #appointmentModal -->

        <!-- Content -->
        <div class="content">
            <div class="row">
                <div class="col-md-2 col-sm-12 d-flex flex-column">

                    <div class="card shadow-sm">
                        <div class="card-body p-3"> <!-- Adds padding inside the card -->
                            <div id="inline-datepicker"></div>
                            <input type="hidden" id="selected-date">
                        </div>
                    </div>


                    <!-- Appointment Details -->
                    <div class="card shadow-sm flex-grow-1 mt-3" id="appointment-card">
                        <div class="card-body">
                            <div id="appointment-details">
                                <p>{{ trans('lang.hover_to_view_details') }}</p>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- Right Side: Calendar -->
                <div class="col-md-10 col-sm-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div id="calendar-container">
                                <div id="calendar"></div>
                            </div>

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

@push('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
    <link rel="stylesheet" href="{{ asset('css/eventcustom.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">


@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <!-- French Locale for Datepicker -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"></script>
    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // ✅ Define global variables
        window.patternData = {};
        let selectedAppointmentId = null; // Store appointment ID

        function loadAvailabilitySlots(viewStart) {
            // console.log("📡 Fetching background colors for:", viewStart);

            $.ajax({
                url: "/get-available-time-slots-presice",
                type: "GET",
                data: { date: viewStart.format("YYYY-MM-DD") }, // Send the current week's start date
                dataType: "json",
                success: function (availabilityResponse) {
                    // console.log("📌 Availability Data:", availabilityResponse);

                    let backgroundEvents = [];

                    if (availabilityResponse.all_slots.length) {
                        availabilityResponse.all_slots.forEach(slot => {
                            if (slot.color) {
                                const slotStart = moment(`${slot.day} ${slot.time}`, "YYYY-MM-DD HH:mm", true).format("YYYY-MM-DDTHH:mm:ss");
                                const slotEnd = moment(slotStart).add(slot.session_duration, 'minutes').format("YYYY-MM-DDTHH:mm");

                                backgroundEvents.push({
                                    id: `background-${slot.time}`,
                                    start: slotStart,
                                    end: slotEnd,
                                    rendering: 'background',
                                    display: 'background',
                                    color: slot.color,
                                    overlap: false
                                });
                            }
                        });
                    }

                    //console.log("📌 Final Background Events:", backgroundEvents);

                    // Remove old background events and add new ones
                    $('#calendar').fullCalendar('removeEvents', function (event) {
                        return event.rendering === 'background';
                    });

                    $('#calendar').fullCalendar('addEventSource', backgroundEvents);
                },
                error: function (xhr) {
                    console.error("❌ Error fetching availability slots:", xhr);
                }
            });
        }


        $(document).ready(function () {
            //
            $('#inline-datepicker').datepicker({
                format: 'yyyy-mm-dd', // Format de la date
                todayHighlight: true, // Mettre en surbrillance aujourd'hui
                autoclose: true, // Fermer automatiquement après sélection
                inline: true, // Toujours visible
                language: 'fr', // Définit la langue en français
                weekStart: 1 // La semaine commence le lundi
            }).on('changeDate', function (e) {
                var selectedDate = e.format(0, "yyyy-mm-dd");
                //console.log("Selected Date:", selectedDate);
                $('#selected-date').val(selectedDate);

                // Navigate agenda to selected date
                $('#calendar').fullCalendar('changeView', 'agendaDay');
                $('#calendar').fullCalendar('gotoDate', selectedDate);

            });
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
                console.log("Calendar events refreshed");
            }

            // Set interval to refresh calendar every 30 seconds
            //setInterval(refreshCalendarEvents, 10000);
            ////////////////////////////////
            function updateAllPatterns(date, time) {
                console.log(date, time);
                const types = [
                    { type: 'cabinet' },
                    { type: 'Téléconsultation' },
                    { type: 'home_visit' }
                ];

                types.forEach(item => {
                    $.ajax({
                        url: "/get-pattern-for-time-slot",
                        method: 'GET',
                        data: {
                            date: date,
                            time: time,
                            type: item.type
                        },
                        success: function (response) {
                            console.log("Fetched data for", item.type, response);
                            window.patternData[item.type] = response;

                            // If the currently active tab is this type, update UI
                            const activeTab = $('#apptTypeTabs .nav-link.active').data('type');
                            if (activeTab === item.type) {
                                updateUIForType(item.type);
                            }
                        },
                        error: function () {
                            console.error('Error fetching data for type: ' + item.type);
                        }
                    });
                });
            }

            // Update the UI for a given appointment type using its returned data
            function updateUIForType(apptType) {
                const data = window.patternData[apptType];

                // Update motif
                $('#patternDisplay').text(data.pattern_name || '{{ trans("lang.no_pattern_selected") }}');
                $('#patternDisplay').css('background-color', data.pattern_color || '#cccccc');

                if (data.pattern_id) {
                    $("#motif_id").val(data.pattern_id);
                } else {
                    $("#motif_id").val('');
                }

                const availableSlots = data.available_slots || [];
                const takenSlots = data.taken_slots || [];

                const $container = $('#timeSlotContainer');
                const $hiddenInput = $('#appointmentTime');
                $container.empty();

                // Get current date and time
                const currentDate = moment().format("YYYY-MM-DD");
                const currentTime = moment().format("HH:mm");

                if (availableSlots.length > 0) {
                    availableSlots.forEach(slot => {
                        const isTaken = takenSlots.includes(slot);
                        const isPast = moment(globalSelectedDate + ' ' + slot).isBefore(moment());

                        const $btn = $(`
                                                                                                                                                                                                                                                                                                                                                                                                            <button type="button"
                                                                                                                                                                                                                                                                                                                                                                                                                    class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}"
                                                                                                                                                                                                                                                                                                                                                                                                                    data-slot="${slot}"
                                                                                                                                                                                                                                                                                                                                                                                                                    ${isTaken || isPast ? 'disabled' : ''}>
                                                                                                                                                                                                                                                                                                                                                                                                                ${slot}
                                                                                                                                                                                                                                                                                                                                                                                                            </button>
                                                                                                                                                                                                                                                                                                                                                                                                        `);

                        // If it's not taken and not in the past, let the user pick it
                        if (!isTaken && !isPast) {
                            $btn.on('click', function () {
                                $container.find('.slot-btn').removeClass('active');
                                $(this).addClass('active');
                                $hiddenInput.val(slot);
                            });
                        }

                        $container.append($btn);
                    });
                } else {
                    $container.append('<span class="text-muted">{{ __("lang.no_slots_available") }}</span>');
                    $hiddenInput.val('');
                }
            }
            //////////////////////////////////////////////////////////
            function openCreateAppointmentModal(selectedDate, selectedTime, patterns, availableSlots) {
                $("#appointmentDate").val(selectedDate);

                // ✅ Update Motif (Pattern) Dropdown
                let patternDropdown = $("#motif_id");
                patternDropdown.empty();
                patterns.forEach(pattern => {
                    patternDropdown.append(`<option value="${pattern.id}">${pattern.nom}</option>`);
                });

                // ✅ Update Available Slots
                let slotContainer = $("#timeSlotContainer");
                slotContainer.empty();
                availableSlots.forEach(slot => {
                    let slotBtn = `<button type="button" class="slot-btn time-slot-button m-1" data-slot="${slot}">${slot}</button>`;
                    slotContainer.append(slotBtn);
                });

                // ✅ Ensure clicking a slot updates the hidden input
                $(".slot-btn").on("click", function () {
                    $(".slot-btn").removeClass("active");
                    $(this).addClass("active");
                    $("#appointmentTime").val($(this).data("slot"));
                });

                // Open Modal
                $("#appointmentModal").modal("show");
            }

            //////////////////////////////////////////////////
            function checkAvailability(selectedDate, selectedTime) {
                return $.ajax({
                    url: "/get-pattern-for-time-slot-without-type",
                    method: "GET",
                    data: { date: selectedDate, time: selectedTime }
                }).then(function (response) {
                    console.log("Unavailable Slots Response:", response);
                    let sessionDuration = response.session_duration;
                    if (!response.unavailable_slots || response.unavailable_slots.length === 0) {
                        return true;
                    }

                    Swal.fire({
                        title: "Créneaux indisponibles",
                        text: response.message,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Forcer l'ajout",
                        cancelButtonText: "Annuler",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#forcedAppointmentDate").val(selectedDate);

                            // Populate motifs dropdown
                            let patternDropdown = $("#forcedMotifDropdown");
                            patternDropdown.empty();
                            response.patterns.forEach(pattern => {
                                patternDropdown.append(`<option value="${pattern.id}">${pattern.nom}</option>`);
                            });

                            // Open the modal
                            $("#forcedAppointmentModal").modal("show");
                        }
                    });

                    $("#forcedAppointmentStartTime, #forcedAppointmentEndTime").off("change").on("change", function () {
                        let startTime = $("#forcedAppointmentStartTime").val();
                        let endTime = $("#forcedAppointmentEndTime").val();
                        console.log(startTime);
                        if (!startTime || !endTime || startTime === "" || endTime === "") {
                            e.preventDefault(); // Prevent submission if empty
                            Swal.fire("Erreur", "Veuillez sélectionner une heure de début et de fin valide.", "error");

                            return;
                        }

                        let selectedStartDateTime = moment(`${selectedDate} ${startTime}`, "YYYY-MM-DD HH:mm");
                        let selectedEndDateTime = moment(`${selectedDate} ${endTime}`, "YYYY-MM-DD HH:mm");

                        // Validate if the selected range is within the available slots
                        if (!selectedStartDateTime.isValid() || !selectedEndDateTime.isValid()) {
                            Swal.fire({
                                title: "Heure invalide",
                                text: "Les heures sélectionnées sont invalides. Veuillez réessayer.",
                                icon: "error",
                            });
                            return;
                        }
                        // ✅ Validate alignment with dynamic session duration
                        let minutesSinceMidnightStart = selectedStartDateTime.hours() * 60 + selectedStartDateTime.minutes();
                        let minutesSinceMidnightEnd = selectedEndDateTime.hours() * 60 + selectedEndDateTime.minutes();

                        if (Array.isArray(response.available_slots) && response.available_slots.length > 0) {
                            let overlapsAvailability = response.available_slots.some(slot => {
                                let slotStart = moment(`${selectedDate} ${slot}`, "YYYY-MM-DD HH:mm");
                                let slotEnd = moment(slotStart).add(sessionDuration, 'minutes');
                                return selectedStartDateTime.isBetween(slotStart, slotEnd, null, '[)') ||
                                    selectedEndDateTime.isBetween(slotStart, slotEnd, null, '(]') ||
                                    slotStart.isBetween(selectedStartDateTime, selectedEndDateTime, null, '[)');
                            });

                            if (overlapsAvailability) {
                                Swal.fire({
                                    title: "Plage horaire déjà utilisée",
                                    text: "Vous avez déjà des heures de disponibilité pour ce créneau.",
                                    icon: "warning",
                                    confirmButtonText: "D'accord",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $("#forcedAppointmentStartTime").val("");
                                        $("#forcedAppointmentEndTime").val("");
                                    }
                                });
                            }
                        } else {
                            // No availability set: this is a forced appointment
                            //console.log("No availability defined; this is a forced appointment scenario.");
                            // You can skip validation here
                        }
                    });
                    $("#forcedAppointmentForm").on("submit", function () {
                        // Optionally clear the time fields after submission
                        //console.log("startTime", $("#forcedAppointmentStartTime").val());
                        $("#forcedAppointmentStartTime").val("");
                        $("#forcedAppointmentEndTime").val("");
                        // console.log("endTime", $("#forcedAppointmentEndTime").val());
                    });
                    // Clear fields and close modal on cancel
                    $(".cancel-btn").on("click", function () {
                        // Reset time fields
                        $("#forcedAppointmentStartTime").val("");
                        $("#forcedAppointmentEndTime").val("");

                        // Close the modal
                        $("#forcedAppointmentModal").modal("hide");
                    });

                    return false;
                });
            }


            ////////////////////////////////////////////

            $(document).ready(function () {
                $("#apptTypeTabs a").on("click", function (e) {
                    const newType = $(e.target).data('type');  // e.g. "cabinet", "teleconsultation"
                    $('#appointmentType').val(newType);
                    updateUIForType(newType);
                });


            });



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
            $('input[name="appointmentType"]').on('change', function () {
                const appointmentType = $(this).val(); // Get the selected type
                const selectedDate = $('#appointmentDate').val();

                if (!selectedDate) {
                    alert("Veuillez d'abord sélectionner une date.");
                    return;
                }

                // Decide which endpoint to call based on the appointment type
                const url =
                    appointmentType === "Téléconsultation"
                        ? "/get-teleconsultation-time-slots"
                        : "/get-available-time-slots-presice";

                // Fetch time slots based on the selected type
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { date: selectedDate },
                    success: function (response) {
                        if (response.all_slots && response.all_slots.length > 0) {
                            // Update slots if they exist
                            updateAvailableTimeSlots(response);
                        } else {
                            clearTimeSlots(); // Clear the time slots container
                        }
                    },
                    error: function () {
                        alert("Erreur lors de la récupération des créneaux horaires. Veuillez réessayer.");
                    },
                });
            });

            // Function to clear the time slots container
            function clearTimeSlots() {
                const timeSlotsWrapper = $("#time-slots");
                timeSlotsWrapper.empty(); // Clear the container
                timeSlotsWrapper.append(`
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="alert alert-info text-center">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                Aucun créneau disponible trouvé.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `);
            }
            //////////////////////////////////////////////////////////////////////////////

            $('#patientDropdownForced').select2({
                allowClear: false,
                ajax: {
                    url: "{{ route('patients.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
            /////////////////////////////////////////////////////////////////////////////
            $('#patientDropdown').select2({
                allowClear: false,
                ajax: {
                    url: "{{ route('patients.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
            $('#patern_id_home, #patern_id_cab, #patern_id_tele').select2({
                width: '100%',
                allowClear: false
            });
            let availabilityDays = @json($availabilityDays);
            let vacations = @json($vacations);
            //console.log("Availability Days at Load:", availabilityDays);
            var calendar = $('#calendar').fullCalendar({
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
                allDaySlot: false, // Removes "Toute la journée"
                allDayText: '', // Clear the default text
                eventLimit: true,
                viewRender: function (view) {
                    let viewStart = $('#calendar').fullCalendar('getView').intervalStart; // Get the current view's start date
                    //console.log(`🔄 View Changed to: ${view.name}, Start Date: ${viewStart.format("YYYY-MM-DD")}`);

                    loadAvailabilitySlots(viewStart);
                    console.log("View changed to:", view.name);
                    $('.custom-day-label').remove();
                    if (view.name === 'agendaWeek') {
                        $('.fc-axis.fc-widget-header').first().empty();

                        // Add your static text
                        $('.fc-axis.fc-widget-header').first().html(`<span class="static-day-name">151/155</span>`);
                        $('.fc-day-header').each(function () {
                            let dayIndex = $(this).index(); // Get the day column index
                            let customLabel = "Static Name";  // Change this if needed
                            let staticNumber = "37 / 36";  // Example static number

                            $(this).append(`<hr class="day-header-divider"><div class="custom-day-label">${customLabel}</div><hr class="day-header-divider"><div class="custom-number-label">${staticNumber}</div>`);
                        });
                    }
                },
                dayRender: function (date, cell) {
                    let dayNumber = date.format('D/M'); // Format day as "3/3" (day/month)
                    let customLabel = "Custom Label";  // Change this to your desired text

                    // Insert custom text below the date number
                    cell.append(`<div class="custom-day-label">${customLabel}</div>`);
                    // 1) Log the date being rendered (YYYY-MM-DD)
                    //console.log("dayRender called for date:", date.format("YYYY-MM-DD"));

                    // For example, "Monday", "Tuesday" → "monday", "tuesday"
                    const formattedDayName = date.locale('en').format('dddd').toLowerCase();
                    // console.log("Formatted day name (EN):", formattedDayName);

                    // Suppose availabilityDays = ["monday","tuesday","wednesday"] or similar
                    const normalizedAvailabilityDays = availabilityDays.map(day => day.toLowerCase());
                    //console.log("Normalized availability days:", normalizedAvailabilityDays);

                    // Compare with today's date
                    const today = moment().startOf('day');
                    const currentDay = date.clone().startOf('day');
                    //console.log("Comparing currentDay:", currentDay.format("YYYY-MM-DD"), "with today:", today.format("YYYY-MM-DD"));

                    // Check if the doctor is on vacation for this day
                    const isVacationDay = vacations.some(vacation => {
                        const vacationStart = moment(vacation.dateDebut, 'YYYY-MM-DD').startOf('day');
                        const vacationEnd = moment(vacation.dateFin, 'YYYY-MM-DD').endOf('day');
                        //console.log("Vacation range:", vacationStart.format("YYYY-MM-DD"), "to", vacationEnd.format("YYYY-MM-DD"));
                        return currentDay.isSameOrAfter(vacationStart) && currentDay.isSameOrBefore(vacationEnd);
                    });
                    // console.log("Is vacation day:", isVacationDay);

                    // Apply conditional styling
                    if (currentDay.isBefore(today)) {
                        // 1) Past day
                        //console.log("Marking as past day:", date.format("YYYY-MM-DD"));
                        cell.removeClass('fc-today');
                        cell.css({
                            'background-color': '#e9ecef',
                            'cursor': 'not-allowed',
                            'color': '#721c24',
                            'position': 'relative'
                        });
                        cell.append('<span class="dot unavailable-dot"></span>');
                        //cell.attr('title', 'This is a past date.');
                    }
                    else if (isVacationDay) {
                        // 2) Vacation day
                        //console.log("Marking as vacation day:", date.format("YYYY-MM-DD"));
                        cell.removeClass('fc-today');
                        cell.addClass('cell-with-background');
                        //cell.attr('title', 'Doctor is on vacation this day.');
                    }
                    else if (normalizedAvailabilityDays.includes(formattedDayName)) {
                        // 3) Available day
                        console.log("Marking as available day:", date.format("YYYY-MM-DD"));
                        cell.removeClass('fc-today');
                        cell.css({
                            'background-color': '',
                            'cursor': 'pointer',
                            'position': 'relative'
                        });
                        cell.append('<span class="dot available-dot"></span>');
                        // cell.attr('title', 'Available day');
                    }
                    else {
                        // 4) Unavailable day
                        // console.log("Marking as unavailable day:", date.format("YYYY-MM-DD"));
                        cell.removeClass('fc-today');
                        cell.css({
                            'background-color': '#e9ecef',
                            'position': 'relative'
                        });
                        cell.append('<span class="dot unavailable-dot"></span>');
                        //cell.attr('title', 'Unavailable day');
                    }

                    //console.log("Finished rendering day:", date.format("YYYY-MM-DD"));
                },
                events: function (start, end, timezone, callback) {
                    //console.log("📡 Fetching main events...");
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
                                //console.log(event.cancel_reason);
                                let color = '';
                                let translatedStatus = statusTranslation[event.status] || event.status;

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
                                    start: moment(event.start_at).format(),
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
                                    status: translatedStatus,
                                    details: event.motif_name || 'N/A',
                                    motif_name: event.motif_name,
                                    note: event.note,
                                    cancel_reason: event.cancel_reason,
                                };
                            });

                            callback(events);
                        }
                    });
                },

                eventRender: function (event, element) {
                    const appointmentDate = event.start ? moment(event.start).format('YYYY-MM-DD') : 'N/A';
                    const appointmentTime = event.start ? moment(event.start).format('HH:mm') : 'N/A';
                    // Adding title attribute for simple tooltip
                    let icon;

                    // Determine the icon based on the `online` field
                    switch (event.online) {
                        case 'cabinet':
                            icon = '<i class="fas fa-clinic-medical" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Blue clinic icon
                            break;
                        case 'Téléconsultation':
                            icon = '<i class="fas fa-video" style="margin-right: 5px; color: 1A1A1D;"></i>'; // Green video icon
                            break;
                        case 'web':
                            icon = '<i class="fas fa-globe" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Orange globe icon
                            break;
                        case 'mobile':
                            icon = '<i class="fas fa-mobile-alt" style="margin-right: 5px; color: #1A1A1D;"></i>'; // Purple mobile icon
                            break;
                        default:
                            icon = '<i class="fas fa-question-circle" style="margin-right: 5px; color: #ccc;"></i>'; // Default gray icon
                            break;
                    }
                    element.find('.fc-title').prepend(icon);
                    if (event.status === 'Annulé') {
                        //console.log("Event Annulé - Applying strikethrough");

                        // Ensure the text inside the event is affected
                        element.find('.fc-title').css({
                            'text-decoration': 'line-through'
                        });

                        element.find('.fc-time').css({
                            'text-decoration': 'line-through'
                        });

                    }

                },
                selectable: true,
                selectAllow: function (selectInfo) {
                    // Only allow selection if the day is in availabilityDays
                    //return availabilityDays.includes(selectInfo.start.format("YYYY-MM-DD"));

                },
                dayClick: function (date, jsEvent, view) {
                    const currentView = $('#calendar').fullCalendar('getView');

                    // Check if the current view is "month"
                    if (currentView.name === "month") {
                        // Switch to "agendaWeek" and focus on the clicked date
                        $('#calendar').fullCalendar('changeView', 'agendaWeek');
                    }

                },
                selectHelper: true,
                select: function (start, end, jsEvent, view) {
                    // Ensure `view` is defined before accessing `view.name`
                    if (!view || !view.name) {
                        console.error("View parameter is undefined in select function.");
                        return;
                    }

                    const selectedDate = start.format("YYYY-MM-DD");
                    const selectedTime = start.format("HH:mm");
                    globalSelectedDate = start.format("YYYY-MM-DD");
                    globalSelectedTime = start.format("HH:mm");

                    const today = moment().format("YYYY-MM-DD");

                    // Check if the selected date is before today's date
                    if (moment(selectedDate).isBefore(today)) {
                        $('#pastDateModal').modal('show');
                        $('#pastDateModalMessage').text("Vous avez sélectionné une date antérieure. Veuillez sélectionner une date future.");
                        return;
                    }

                    const isVacationDay = vacations.some(vacation => {
                        const vacationStart = moment(vacation.dateDebut, 'YYYY-MM-DD').startOf('day');
                        const vacationEnd = moment(vacation.dateFin, 'YYYY-MM-DD').endOf('day');
                        return moment(selectedDate).isSameOrAfter(vacationStart) && moment(selectedDate).isSameOrBefore(vacationEnd);
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
                    console
                    if (view.name === "agendaWeek" || view.name === "agendaDay") {
                        checkAvailability(selectedDate, selectedTime).then((isAvailable) => {
                            if (!isAvailable) return; // Stop if no availability

                            // Proceed with opening the modal
                            $("#appointmentDate").val(selectedDate);
                            //$("#appointmentDate_tele").val(selectedDate);
                            //$("#appointmentDate_home").val(selectedDate);

                            //$("#timeSlotContainer").val(selectedTime);
                            //$("#timeSlotSelect_tele").val(selectedTime);
                            //$("#timeSlotSelect_home").val(selectedTime);

                            updateAllPatterns(selectedDate, selectedTime);


                            $('#apptTypeTabs a[href="#cabinet-pane"]').tab('show');
                            $('#appointmentModal').modal('show');

                        });
                    }
                },
                eventMouseover: function (event, jsEvent, view) {
                    if (event.rendering === 'background') {
                        return;
                    }

                    // Base details
                    let detailsHtml = `
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.appointment_date}:</strong> ${event.start.format('YYYY-MM-DD')}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.appointment_time}:</strong> ${event.start.format('HH:mm')}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.patient_nom}:</strong> ${event.patient_name || translations.unknown_patient}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.appointment_status}:</strong> ${event.status || translations.unknown_status}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.motif_name}:</strong> ${event.motif_name || translations.no_motif_name}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.phone}:</strong> ${event.patient_phone_number || 'N/A'}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.email}:</strong> ${event.email || 'N/A'}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                    <p><strong>${translations.note}:</strong> ${event.note || 'N/A'}</p>
                                                                                                                                                                                                                                                                                                                                                                                                                `;
                    //console.log(event.cancel_reason);

                    if (event.status === "Annulé" && event.cancel_reason) {
                        //console.log('tetetetet');
                        detailsHtml += `<p><strong>${translations.raison}:</strong> ${event.cancel_reason}</p>`;
                    }

                    $('#appointment-details').html(detailsHtml);
                },

                eventMouseout: function (event, jsEvent, view) {
                    $('#appointment-details').html(`<p>${translations.hover_to_view_details}</p>`);
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
                            console.log("Start At:", start_at);
                            console.log("End At:", ends_at);
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
                        status: event.status,
                        motif_name: event.motif_name,
                        online: event.online,
                        start: event.start.format(),
                        phone: event.patient_phone_number // Include the patient phone number
                    };
                    $('#patientName').text(event.patient_name);
                    $('#appointmentStatus').text(event.status);
                    $('#appointmentDetails').text(event.details || 'No additional details');
                    $('#motifName').text(event.motif_name || 'No motif name available');
                    $('#note').text(event.note || 'No note available');
                    //console.log(event.motif_name);
                    //console.log(event.patient_name);
                    // Show the modal
                    openAppointmentModal(appointment);
                    $('#appointmentDetailsModal').modal('show');

                    // Event handler for "Mark as Failed"
                    // Event handler for "Mark as Failed"


                    $('#markAsFailed').off('click').on('click', function () {
                        selectedAppointmentId = event.id; // Store appointment ID
                        $('#cancelReason').val(''); // Clear previous input
                        $('#cancelAppointmentModal').modal('show'); // Show modal
                        $('#appointmentDetailsModal').modal('hide');

                    });
                    // Event handler for "Mark as Done"
                    $('#markAsDone').off('click').on('click', function () {
                        updateAppointmentStatus(event.id, 5, "Done");
                    });
                }
            });
            ////////////////////////////////////
            $('#confirmCancelAppointment').off('click').on('click', function () {
                let cancelReason = $('#cancelReason').val().trim();
                if (cancelReason === "") {
                    cancelReason = "Aucune raison fournie"; // Default reason if empty
                }

                // Send the cancellation request
                updateAppointmentStatus(selectedAppointmentId, 7, cancelReason);

                // Close the modal
                $('#cancelAppointmentModal').modal('hide');
            });


            //////////////////////////////////
            const translations = {
                appointment_date: "{{ trans('lang.tele_appointment_date') }}",
                appointment_time: "{{ trans('lang.tele_appointment_time') }}",
                patient_nom: "{{ trans('lang.patient_nom') }}",
                appointment_status: "{{ trans('lang.tele_appointment_status') }}",
                motif_name: "{{ trans('lang.motif_name') }}",
                unknown_patient: "{{ trans('lang.tele_appointment_patient') }}",
                unknown_status: "{{ trans('lang.tele_appointment_status') }}",
                no_motif_name: "{{ trans('lang.no_motif_name') }}",
                phone: "{{ trans('lang.tele_patient_phone') }}",
                email: "{{ trans('lang.email') }}",
                note: "{{ trans('lang.note') }}",
                raison: "{{ trans('lang.raison') }}",
                hover_to_view_details: "{{ trans('lang.hover_to_view_details') }}"
            };
            //////////////////////////////////////////
            $('#saveAppointmentRef').click(function () {
                let appointmentDate = $('#appointmentDate').val();
                let patientId = $('#patientDropdown').val();
                let selectedTime = $('#appointment_time').val();
                let patternId = $('#patern_id').val();
                let appointmentType = $('input[name="appointmentType"]:checked').val(); // Get appointment type
                let isValid = true;

                // Clear previous errors
                $('.error-message').remove();
                $('.error-input').removeClass('error-input');

                // Validation checks
                if (!patientId) {
                    $('#patientDropdown').addClass('error-input')
                        .after('<div class="error-message text-danger">Veuillez sélectionner un patient.</div>');
                    isValid = false;
                }
                if (!appointmentDate) {
                    $('#appointmentDate').addClass('error-input')
                        .after('<div class="error-message text-danger">Veuillez sélectionner une date.</div>');
                    isValid = false;
                }
                if (!selectedTime) {
                    $('#appointment_time').addClass('error-input')
                        .after('<div class="error-message text-danger">Veuillez sélectionner une heure.</div>');
                    isValid = false;
                }
                if (!patternId) {
                    $('#patern_id').addClass('error-input')
                        .after('<div class="error-message text-danger">Veuillez sélectionner un motif.</div>');
                    isValid = false;
                }
                if (!appointmentType) {
                    alert("Veuillez sélectionner le type de rendez-vous (cabinet ou téléconsultation).");
                    isValid = false;
                }

                // If validation fails, exit
                if (!isValid) return;
                const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                const selectedDate = new Date(appointmentDate);
                const dayOfWeek = days[selectedDate.getDay()]; // Get the day index and map it to the name

                console.log("Selected Day:", dayOfWeek); // Output the day for debugging
                // Construct the appointment data
                let startAt = `${appointmentDate} ${selectedTime}:00`;
                let appointmentData = {
                    patient_id: patientId,
                    appointment_at: appointmentDate,
                    appointment_time: selectedTime,
                    patern_id: patternId,
                    appointment_type: appointmentType,
                    day_of_week: dayOfWeek,
                };

                console.log("Appointment Data to be Sent:", appointmentData);

                // Make the AJAX call
                $.ajax({
                    url: "{{ route('appointments.store') }}", // Ensure this route exists in your backend
                    method: "POST",
                    data: appointmentData,
                    success: function (response) {
                        $('#appointmentModal').modal('hide'); // Close the modal
                        alert("Rendez-vous enregistré avec succès."); // Success message in French
                        location.reload(true); // Reload the page to reflect the changes
                        $('#calendar').fullCalendar('refetchEvents'); // Refetch calendar events
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            console.log("Validation Errors:", errors);
                            // Display validation errors
                            for (const [field, messages] of Object.entries(errors)) {
                                $(`#${field}`).addClass('error-input')
                                    .after(`<div class="error-message text-danger">${messages[0]}</div>`);
                            }
                        } else {
                            alert("Une erreur inattendue s'est produite. Veuillez réessayer."); // General error message in French
                        }
                    }
                });
            });

            function updateAppointmentStatus(appointmentId, statusId, cancelReason) {
                $.ajax({
                    url: "/appointment-event/status", // Route URL
                    method: "POST",
                    data: {
                        id: appointmentId,
                        appointment_status_id: statusId,
                        cancel_Reason: cancelReason,
                        _token: $('meta[name="csrf-token"]').attr('content') // CSRF Token
                    },
                    success: function (response) {
                        alert(response.message); // Optional: Show a success message
                        $('#appointmentDetailsModal').modal('hide'); // Close the modal
                        $('#calendar').fullCalendar('refetchEvents'); // Refresh the calendar
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error || "An error occurred while updating the status.");
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
            $("#cabinetForm").on("submit", function (e) {
                e.preventDefault();

                // Gather data
                let data = {
                    appointment_type: "cabinet",
                    patient_id: $("#patientDropdown_cab").val(),
                    appointment_date: $("#appointmentDate_cab").val(),
                    appointment_time: $("#timeSlotSelect_cab").val(),
                    patern_id: $("#patern_id_cab").val(),
                    notes: $("#notes_cab").val(),
                    // plus _token if needed
                };

                // AJAX call to create appointment
                $.ajax({
                    url: "{{ route('appointments.store') }}", // Ensure this route exists in your backend
                    method: "POST",
                    data: data,
                    success: function (resp) {
                        // close modal, refetch calendar, etc.
                        $('#appointmentModal').modal('hide');
                        $('#calendar').fullCalendar('refetchEvents');
                        alert("Rendez-vous enregistré avec succès!");
                    },
                    error: function (err) {
                        // handle error
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
                if (isDoctor && online === "Téléconsultation" && status !== "Annulé" && status !== "Terminé") {

                    teleconsultationButton.classList.remove("d-none");
                    teleconsultationButton.onclick = () => {
                        const url = `{{ route('teleconsultations.createMeet') }}?patient_name=${encodeURIComponent(patient_name)}&appointment_id=${encodeURIComponent(appointment_id)}&phone=${encodeURIComponent(phone)}&motif_name=${encodeURIComponent(motif_name)}&patient_id=${encodeURIComponent(patient_id)}&start_at=${encodeURIComponent(start)}&patient_first_name=${encodeURIComponent(patient_first_name)}&patient_last_name=${encodeURIComponent(patient_last_name)}&patient_Email=${encodeURIComponent(email)}`;
                        window.location.href = url;
                    };

                } else {
                    teleconsultationButton.classList.add("d-none");
                }

                if (hasUpdateStatusPermission) {
                    if (online === "Téléconsultation") {
                        doneButton.style.display = "none";
                        failedButton.style.display = "none";
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

            $('#forcedAppointmentForm').on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#forcedAppointmentModal').modal('hide');
                        Swal.fire({
                            title: 'Succès',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        let errorMessage = 'Veuillez vérifier le formulaire et réessayer';

                        if (response && response.errors) {
                            errorMessage = Object.values(response.errors)[0]; // Get first error message
                        }

                        Swal.fire({
                            title: 'Erreur',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
@endpush