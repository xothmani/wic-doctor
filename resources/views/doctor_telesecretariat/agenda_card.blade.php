@extends('layouts.app')

@section('content')

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
<!-- Modal for Viewing and Updating Appointment Details -->
<div class="modal fade" id="appointmentDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="appointmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentDetailsModalLabel">{{ trans('lang.appointment_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="appointment-info">
                    <!-- Patient Details will be dynamically filled here -->
                    <p><strong>{{ trans('lang.patient_nom') }}:</strong> <span id="patientName"></span></p>
                    <p><strong>{{ trans('lang.appointment_status') }}:</strong> <span id="appointmentStatus"></span></p>
                    <p><strong>{{ trans('lang.motif_name') }}:</strong> <span id="motifName"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary d-none" id="createTeleconsultation"
                    style="background-color: #AEC6CF; color: #000;">{{ trans('lang.create_teleconsultation') }}</button>
                <button type="button" class="btn btn-danger" id="markAsFailed"
                    style="background-color: #F4C2C2; color: #000;">{{ trans('lang.mark_failed') }}</button>
                <button type="button" class="btn btn-success" id="markAsDone"
                    style="background-color: #B1E5D6; color: #000;">{{ trans('lang.mark_ready') }}</button>
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
                <form id="appointmentForm">
                    @csrf
                    <!-- Toggle for Appointment Type -->
                    <div class="form-group">
                        <label class="font-weight-bold">{{ trans('lang.select_appointment_type') }}</label>
                        <div class="d-flex align-items-center">
                            <div class="form-check mr-3">
                                <input class="form-check-input" type="radio" name="appointmentType" id="inCabinet"
                                    value="cabinet" checked>
                                <label class="form-check-label" for="inCabinet">
                                    {{ trans('lang.in_cabinet') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="appointmentType"
                                    id="Téléconsultation" value="Téléconsultation">
                                <label class="form-check-label" for="Téléconsultation">
                                    {{ trans('lang.teleconsultation') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Fields for Patient référencé -->
                    <div id="referencedFields">
                        <div class="form-group">
                            <label for="patientDropdown"
                                class="font-weight-bold">{{ trans('lang.Select_Patient') }}</label>
                            <div class="d-flex align-items-center">
                                <select id="patientDropdown" class="form-control select2-ajax" required
                                    style="flex-grow: 1;">
                                    <option value="" disabled selected>{{ trans('lang.Select_Patient') }}</option>
                                </select>
                                <a href="{{ route('patients.create') }}" class="btn btn-success d-flex "
                                    id="addNewPatient">
                                    <i class="fa fa-user-plus"></i>
                                </a>
                            </div>
                        </div>
                        <div class="form-group" id="dateGroup">
                            <label class="font-weight-bold">{{ trans('lang.date') }}</label>
                            <input type="date" class="form-control" id="appointmentDate" name="appointment_date"
                                readonly>
                        </div>
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
                    <!-- Pattern Selection -->
                    <div class="form-group">
                        <label for="patternDropdown" class="font-weight-bold">
                            {{ trans('lang.availability_hour_pattern') }}
                        </label>
                        <div class="d-flex align-items-center">
                            <select id="patternDropdown" name="patern_id" class="form-control select2-ajax" required
                                style="flex-grow: 1;">
                                <option value="" disabled selected>{{ trans('lang.select_pattern') }}</option>
                            </select>
                            <a href="{{ route('patterns.create') }}" class="btn btn-success d-flex " id="addNewPatient">
                                <i class="fa fa-plus"></i>
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="saveAppointmentRef"
                            style="display: none;">{{ trans('lang.save_patient_refer') }}</button>
                        <button type="button" class="btn btn-secondary" id="saveAppointmentPass"
                            style="display: none;">Save Patient de Passage</button>
                    </div>
                </form>
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

            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content">
    <div class="row">
        <div class="col-md-2 col-sm-12 d-flex flex-column">
            <!-- Doctor Selection -->
            <div class="card shadow-sm flex-grow-1" style="max-height: 300px; overflow-y: auto; padding: 0;">
                <div class="card-header py-2 px-3" style="background-color: #f8f9fa;">
                    <input type="text" id="doctorSearch" class="form-control"
                        placeholder="{{ trans('lang.search_doctor') }}" style="height: 35px; font-size: 14px;">
                </div>
                <div class="card-body">

                    <ul class="list-group doctor-list">
                        @foreach ($doctors as $doctor)
                            <li class="list-group-item doctor-item {{ $loop->first ? 'active' : '' }}"
                                data-doctor-id="{{ $doctor->doctor->id }}" style="cursor: pointer; height: 35px">
                                {{ $doctor->doctor->name  }}
                            </li>
                        @endforeach
                    </ul>

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
            </div>
        </div>
    </div>


</div>

</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
    <link rel="stylesheet" href="{{ asset('css/teleagenda.css') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
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
        function refreshCalendarEvents() {
            $('#calendar').fullCalendar('refetchEvents'); // Fetch and reload events
            console.log("Calendar events refreshed");
        }

        // Set interval to refresh calendar every 30 seconds
        setInterval(refreshCalendarEvents, 10000);
        let selectedDoctorId = {{ $doctors->first()->id ?? 'null' }};
        let calendar; // Reference to FullCalendar instance
        let availabilityDays = @json($availabilityDays);
        let vacations = @json($vacations);
        //console.log("Availability Days at Load:", availabilityDays);
        ///////////////////////////////////////////////////////////////////////////////////////
        function updateAvailableTimeSlots(response) {
            const { all_slots, taken_slots } = response;
            const totalSlots = all_slots.length;
            const takenSlots = taken_slots.length;
            const availableSlots = totalSlots - takenSlots;
            //console.log("Response received:", response);

            const timeSlotsWrapper = $("#time-slots");
            timeSlotsWrapper.empty(); // Clear existing slots

            // Check if the doctor is on vacation
            if (response.vacation) {
                //console.log("Doctor is on vacation. No slots to display.");
                const vacationMessage = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="alert alert-warning text-center">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    Le docteur est en vacances pour ce jour. Aucune disponibilité n'est disponible.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `;
                timeSlotsWrapper.append(vacationMessage);

                // Show a confirmation dialog to the user
                Swal.fire({
                    title: "Le docteur est en vacances",
                    text: "Voulez-vous créer une nouvelle disponibilité pour ce jour ?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Oui, créer une disponibilité",
                    cancelButtonText: "Non, annuler",
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to create new availability
                        const selectedDate = $("#appointmentDate").val();
                        window.location.href = `/availabilityHours/create?date=${selectedDate}`;
                    } else {
                        console.log("User chose not to create a new availability.");
                    }
                });

                return; // Exit the function to avoid processing further
            }
            const selectedDate = $("#appointmentDate").val();
            const isToday = selectedDate === moment().format("YYYY-MM-DD");
            // Ensure `all_slots` is an array of strings or extract the appropriate property
            all_slots.forEach(slot => {
                // If `slot` is an object, extract the desired property (e.g., `time` or `slot.time`)
                const time = typeof slot === "object" ? slot.time || slot.slot : slot;

                const slotElement = $('<div>')
                    .addClass('time-slot') // Apply your custom CSS class
                    .text(time); // Set the time as the displayed text

                // Check if the time is in taken_slots
                if (taken_slots.includes(time)) {
                    slotElement.addClass('taken-slot').css({
                        'background-color': '#e9ecef'
                    });
                } else {
                    // If today and the slot's time is in the past, disable it and style it gray
                    if (isToday && moment(`${selectedDate} ${time}`, "YYYY-MM-DD HH:mm").isBefore(moment())) {
                        slotElement.addClass('passed-slot').css({
                            'background-color': '#e9ecef',
                            'pointer-events': 'none',
                            'opacity': '0.6'
                        });
                    } else {
                        // Otherwise, mark the slot as available and attach a click event handler
                        slotElement.addClass('available-slot').on('click', function () {
                            $('.time-slot').removeClass('selected');
                            $(this).addClass('selected');
                            $('#appointment_time').val(time);
                        });
                    }
                }
                timeSlotsWrapper.append(slotElement);
            });
            return { totalSlots, takenSlots, availableSlots };
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////
        function updateAppointmentStatus(appointmentId, statusId) {
            $.ajax({
                url: "/appointment-event/status", // Route URL
                method: "POST",
                data: {
                    id: appointmentId,
                    appointment_status_id: statusId,
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
        //////////////////////////////////////////////////////////////////////////////////////////////
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

            // Show/hide the "Create Teleconsultation" button
            if (online === "Téléconsultation" && status !== "Annulé" && status !== "Terminé") {

                teleconsultationButton.classList.remove("d-none");
                teleconsultationButton.onclick = () => {
                    const url = `{{ route('teleconsultations.createMeet') }}?patient_name=${encodeURIComponent(patient_name)}&appointment_id=${encodeURIComponent(appointment_id)}&phone=${encodeURIComponent(phone)}&motif_name=${encodeURIComponent(motif_name)}&patient_id=${encodeURIComponent(patient_id)}&start_at=${encodeURIComponent(start)}&patient_first_name=${encodeURIComponent(patient_first_name)}&patient_last_name=${encodeURIComponent(patient_last_name)}&patient_Email=${encodeURIComponent(email)}`;
                    window.location.href = url;
                };

            } else {
                teleconsultationButton.classList.add("d-none");
            }


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

            // Show the modal
            $("#appointmentDetailsModal").modal("show");
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////
        //the agenda
        //////////////////////////////////////////////////////////////////////////////////////////////
        function initializeCalendar() {
            if (calendar) {
                $('#calendar').fullCalendar('destroy'); // Destroy the existing calendar
            }
            calendar = $('#calendar').fullCalendar({
                locale: 'fr',
                height: 600,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: 'agendaWeek',
                minTime: "08:00:00",
                eventLimit: true, // Allow "more" link for overflow events
                allDaySlot: true,

                eventOverlap: false, // Prevent overlapping of events
                dayRender: function (date, cell) {
                    //console.log('fetchhhhhhh');
                    // Format date as YYYY-MM-DD for comparison
                    const formattedDayName = date.locale('fr').format('dddd').toLowerCase(); // Normalize to lowercase
                    // console.log('formatted', formattedDayName);
                    const normalizedAvailabilityDays = availabilityDays.map(day => day.toLowerCase()); // Normalize backend days to lowercase
                    //console.log('normalize', normalizedAvailabilityDays);
                    const today = moment().startOf('day'); // Get today's date
                    const currentDay = date.startOf('day');
                    // Check if the doctor is on vacation
                    const isVacationDay = vacations.some(vacation => {
                        const vacationStart = moment(vacation.dateDebut, 'YYYY-MM-DD').startOf('day');
                        const vacationEnd = moment(vacation.dateFin, 'YYYY-MM-DD').endOf('day');
                        return currentDay.isSameOrAfter(vacationStart) && currentDay.isSameOrBefore(vacationEnd);
                    });

                    if (currentDay.isBefore(today)) {
                        // Mark as past day
                        cell.css('background-color', '#e9ecef'); // Light red for past days
                        cell.css('cursor', 'not-allowed');
                        cell.css('color', '#721c24'); // Dark red for text
                        cell.css('position', 'relative');
                        cell.append('<span class="dot unavailable-dot"></span>'); // Red dot
                        cell.attr('title', 'Ce jour est dans le passé.'); // Tooltip for past days
                        //console.log('dayisleft');
                    } else if (isVacationDay) {
                        cell.addClass('cell-with-background'); // Apply custom background for vacation days
                        cell.attr('title', 'Le docteur est en vacances ce jour.');
                    } else if (normalizedAvailabilityDays.includes(formattedDayName)) {
                        // Mark as available day
                        cell.css('background-color', ''); // Green for available days
                        cell.css('cursor', 'pointer');
                        cell.css('position', 'relative');
                        cell.append('<span class="dot available-dot"></span>'); // Green dot
                        //console.log('dayavai');
                    } else {
                        // Mark as unavailable day
                        cell.css('background-color', '#e9ecef'); // Gray for unavailable days
                        cell.css('position', 'relative');
                        cell.append('<span class="dot unavailable-dot"></span>'); // Red dot
                        //console.log('daynotavai');
                    }
                    const selectedDate = date.format('YYYY-MM-DD');
                    $.ajax({
                        url: "/tele-get-available-time-slots",
                        type: "GET",
                        data: { date: selectedDate, doctor_id: selectedDoctorId },
                        success: function (response) {
                            const totalSlots = response.all_slots ? response.all_slots.length : 0; // Default to 0 if no slots
                            const takenSlots = response.taken_slots ? response.taken_slots.length : 0; // Default to 0 if no slots

                            // Display the taken/total ratio in the cell
                            const ratioHtml = `<div class="static-number">${takenSlots}/${totalSlots}</div>`;
                            cell.append(ratioHtml);
                        },
                        error: function () {
                            // If there's an error (e.g., no availability), default to 0/0
                            const ratioHtml = `<div class="static-number">0/0</div>`;
                            cell.append(ratioHtml);
                            console.warn(`No availability data found for ${selectedDate}`);
                        }
                    });

                },
                events: function (start, end, timezone, callback) {
                    $.ajax({
                        url: "/tele-doctor-agenda-data",
                        type: "GET",
                        data: {
                            start: start.format("YYYY-MM-DD HH:mm:ss"),
                            end: end.format("YYYY-MM-DD HH:mm:ss"),
                            doctor_id: selectedDoctorId
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
                                    motif_name: event.motif_name // motif_name is passed here
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

                    element.on('mouseenter', function () {
                        const detailsHtml = `<p><strong>${translations.appointment_date}:</strong> ${appointmentDate}</p><p><strong>${translations.appointment_time}:</strong> ${appointmentTime}</p> <p><strong>${translations.patient_nom}:</strong> ${event.patient_name || translations.unknown_patient}</p> <p><strong>${translations.appointment_status}:</strong> ${event.status || translations.unknown_status}</p> <p><strong>${translations.motif_name}:</strong> ${event.motif_name || translations.no_motif_name}</p> <p><strong>${translations.phone}:</strong> ${event.patient_phone_number || 'N/A'}</p> <p><strong>${translations.email}:</strong> ${event.email || 'N/A'}</p> `;

                        $('#appointment-details').html(detailsHtml);
                    });

                    element.on('mouseleave', function () {
                        $('#appointment-details').html(`<p>${translations.hover_to_view_details}</p>`);
                    });



                    //element.attr('title', event.description);
                    //element.find('.fc-title').css('white-space', 'nowrap');
                    //element.find('.fc-time').css('font-size', '1em');

                },
                selectable: true,
                selectAllow: function (selectInfo) {
                    // Only allow selection if the day is in availabilityDays
                    //return availabilityDays.includes(selectInfo.start.format("YYYY-MM-DD"));

                },
                selectHelper: true,
                select: function (start, end) {
                    //console.log('aaaaaaattttt')
                    const selectedDate = start.format("YYYY-MM-DD");
                    const today = moment().format("YYYY-MM-DD"); // Get today's date

                    // Check if the selected date is before today's date
                    if (moment(selectedDate).isBefore(today)) {
                        // Show a modal with a French message for past dates
                        $('#pastDateModal').modal('show');
                        $('#pastDateModalMessage').text("Vous avez sélectionné une date antérieure. Veuillez sélectionner une date future.");
                        return; // Exit the function to prevent further actions
                    }
                    const isVacationDay = vacations.some(vacation => {
                        const vacationStart = moment(vacation.dateDebut, 'YYYY-MM-DD').startOf('day');
                        const vacationEnd = moment(vacation.dateFin, 'YYYY-MM-DD').endOf('day');
                        return moment(selectedDate).isSameOrAfter(vacationStart) && moment(selectedDate).isSameOrBefore(vacationEnd);
                    });
                    if (isVacationDay) {
                        // Show a modal with a French message for vacation days
                        Swal.fire({
                            title: "Le docteur est en vacances",
                            text: "Vous ne pouvez pas sélectionner cette date.",
                            icon: "warning",
                            confirmButtonText: "OK"
                        });

                        return; // Exit the function to prevent further actions
                    }

                    $('#appointmentDate').val(start.format("YYYY-MM-DD"));
                    $('#startHour').val(start.format("HH"));
                    $('#startMinute').val(start.format("mm"));
                    $('#endHour').val(end.format("HH"));
                    $('#endMinute').val(end.format("mm"));

                    $.ajax({
                        url: "/tele-get-available-time-slots",
                        type: "GET",
                        data: { date: selectedDate, doctor_id: selectedDoctorId },
                        success: function (takenSlots) {

                            updateAvailableTimeSlots(takenSlots);
                            $('#appointmentModal').modal('show');
                        },
                        error: function (xhr) {
                            // Gestion des erreurs en français
                            if (xhr.status === 404) {
                                $('#confirmationModal').modal('show');

                                // Pass the selected date to the confirm button
                                $('#confirmCreateAvailability')
                                    .off('click')
                                    .on('click', function () {
                                        // Redirect to the create view with the selected date
                                        window.location.href = `/availability`;
                                    });
                            } else {
                                alert("Erreur lors de la récupération des créneaux horaires. Veuillez réessayer.");
                            }
                        }
                    });
                },
                editable: true,
                eventClick: function (event) {
                    console.log("Event object:", event);
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
                    console.log(event.patient_name);
                    $('#patientName').text(event.patient_name);
                    $('#appointmentStatus').text(event.status);
                    $('#appointmentDetails').text(event.details || 'No additional details');
                    $('#motifName').text(event.motif_name || 'No motif name available');
                    //console.log(event.motif_name);
                    //console.log(event.patient_name);
                    // Show the modal
                    openAppointmentModal(appointment);
                    $('#appointmentDetailsModal').modal('show');

                    // Event handler for "Mark as Failed"
                    $('#markAsFailed').off('click').on('click', function () {
                        updateAppointmentStatus(event.id, 7, "Failed");
                    });

                    // Event handler for "Mark as Done"
                    $('#markAsDone').off('click').on('click', function () {
                        updateAppointmentStatus(event.id, 5, "Done");
                    });
                }
            });
        }
        ///////////////////////////
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
            hover_to_view_details: "{{ trans('lang.hover_to_view_details') }}"
        };
        ///////////////////////////////////////////////
        function fetchCountslotsData() {
            //console.log('Fetching doctor availability data...');
            if (!selectedDoctorId) {
                console.warn("No doctor selected.");
                return;
            }

            $.ajax({
                url: "/tele-get-available-time-slots",
                type: "GET",
                data: { doctor_id: selectedDoctorId },
                success: function (response) {
                    const totalSlots = response.all_slots ? response.all_slots.length : 0; // Default to 0 if no slots
                    const takenSlots = response.taken_slots ? response.taken_slots.length : 0; // Default to 0 if no slots

                    // Display the taken/total ratio in the cell
                    const ratioHtml = `<div class="static-number">${takenSlots}/${totalSlots}</div>`;
                    cell.append(ratioHtml);
                },
                error: function () {
                    // If there's an error (e.g., no availability), default to 0/0
                    const ratioHtml = `<div class="static-number">0/0</div>`;
                    cell.append(ratioHtml);
                    console.warn(`No availability data found for ${selectedDate}`);
                }
            });
        }
        //////////////////////////
        function fetchDoctorAvailabilityData() {
            //console.log('Fetching doctor availability data...');
            if (!selectedDoctorId) {
                console.warn("No doctor selected.");
                return;
            }

            $.ajax({
                url: "/tele-get-doctor-availability-data",
                type: "GET",
                data: { doctor_id: selectedDoctorId },
                success: function (response) {
                    if (response.success) {
                        availabilityDays = response.availabilityDays || [];
                        vacations = response.vacations || [];

                        console.log("Updated Availability Days:", availabilityDays);
                        console.log("Updated Vacations:", vacations);

                        // Initialize or refresh the calendar after data is updated
                        initializeCalendar();
                    } else {
                        console.error("Failed to fetch doctor availability data:", response.message);
                    }
                },
                error: function (xhr) {
                    console.error("Error fetching doctor availability data:", xhr.responseText);
                }
            });
        }

        // Function to refresh the patterns dropdown
        function refreshPatternDropdown() {
            //console.log('refpatt');
            $('#patternDropdown').select2({
                ajax: {
                    url: "/tele-patterns", // Endpoint to fetch patterns
                    dataType: 'json',
                    delay: 250,
                    data: function () {
                        return {
                            doctor_id: selectedDoctorId // Pass the selected doctor ID
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (pattern) {
                                return { id: pattern.id, text: pattern.nom };
                            })
                        };
                    },
                    cache: true
                },
                placeholder: "{{ trans('lang.select_pattern') }}",
                allowClear: false,
                minimumResultsForSearch: -1 // Disable search box
            });
        }

        // Function to refresh the patients dropdown
        function refreshPatientDropdown() {
            //console.log('refpatient');
            $('#patientDropdown').select2({
                allowClear: false,
                ajax: {
                    url: "/tele-patients/search",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            doctor_id: selectedDoctorId // Pass the selected doctor ID
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        }

        $('.doctor-item').on('click', function () {
            $('.doctor-item').removeClass('active');
            $(this).addClass('active');
            selectedDoctorId = $(this).data('doctor-id');

            // Store the selected doctor in sessionStorage
            sessionStorage.setItem('selectedDoctorId', selectedDoctorId);
            fetchDoctorAvailabilityData();
            refreshPatternDropdown();
            refreshPatientDropdown();
            // Refresh calendar events for the selected doctor
            $('#calendar').fullCalendar('refetchEvents');
        });

        $(document).ready(function () {
            fetchDoctorAvailabilityData();
            refreshPatternDropdown();
            refreshPatientDropdown();

            //////////////////////////////////////////////////////////////////////
            $('.doctor-item').on('click', function () {
                const savedDoctorId = sessionStorage.getItem('selectedDoctorId');

                if (savedDoctorId) {

                    // Reapply the active class to the previously selected doctor
                    $('.doctor-item').removeClass('active');
                    $(`.doctor-item[data-doctor-id="${savedDoctorId}"]`).addClass('active');
                    selectedDoctorId = savedDoctorId;

                    // Refresh calendar events for the saved doctor
                    $('#calendar').fullCalendar('refetchEvents');
                } else {
                    // Default to the first doctor if no doctor is saved
                    selectedDoctorId = $('.doctor-item.active').data('doctor-id');
                }

            });
            //////////////////////////////////////////////////////////////////////////
            $('#appointmentModal').on('show.bs.modal', function () {
                //console.log("Opening modal, refreshing dropdowns...");
                resetAppointmentType();
                // Reset and refresh the patient dropdown
                $('#patientDropdown').val(null).trigger('change'); // Clear selected value
                refreshPatientDropdown(); // Reinitialize with current doctor

                // Reset and refresh the pattern dropdown
                $('#patternDropdown').val(null).trigger('change'); // Clear selected value
                refreshPatternDropdown(); // Reinitialize with current doctor
            });
            ////////////////////////////////////////////////////////////////////
            // Initialize field visibility based on patient type selection
            function toggleFields() {
                $('#referencedFields').show();
                $('#walkInFields').hide();
                $('#time-slots-wrapper').show();
                $('#saveAppointmentRef').show();
                $('#saveAppointmentPass').hide();
            }

            toggleFields();
            ////////////////////////////////////////////////////////////////////
            $('#patientRef, #patientPass').on('change', toggleFields);
            function refreshCalendarEvents() {
                $('#calendar').fullCalendar('refetchEvents'); // Fetch and reload events
                console.log("Calendar events refreshed");
            }

            // Set interval to refresh calendar every 30 seconds
            //setInterval(refreshCalendarEvents, 10000);

            //const timeSlots = ["14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];
            const timeSlotsContainer = document.getElementById('time-slots');


            //////////////////////////////////////////////////////////////////////////////////
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
                        ? "/tele-get-teleconsultation-time-slots"
                        : "/tele-get-available-time-slots";

                // Fetch time slots based on the selected type
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { date: selectedDate, doctor_id: selectedDoctorId },
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
            /////////////////////////////////////////////////////////////////
            function getStatusColor(status) {
                const colors = {
                    'Accepté': '#9FCDA8',
                    'Terminé': '#7DC2A5',
                    'Prêt': '#9EDF9C',
                    'En cours': '#F5DF4D',
                    'Annulé': '#F38071',
                    'Reçu': '#A594F9'
                };
                return colors[status] || '#B4BAFF';
            }
            ////////////////////////////////////////////////////////////////
            $('#doctorSearch').on('keyup', function () {
                const searchText = $(this).val().toLowerCase();

                // Filter the list items
                $('.doctor-list .doctor-item').filter(function () {
                    const doctorName = $(this).text().toLowerCase();
                    $(this).toggle(doctorName.includes(searchText));
                });
            });
            //////////////////////////////////////////////////////////////////
            $('#saveAppointmentRef').click(function () {

                let appointmentDate = $('#appointmentDate').val();
                let patientId = $('#patientDropdown').val();
                let selectedTime = $('#appointment_time').val();
                let patternId = $('#patternDropdown').val();
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

                    $('#patternDropdown').addClass('error-input')
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
                    doctor_id: selectedDoctorId,
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
                    url: "/tele-save-appointment",
                    method: "POST",
                    data: appointmentData,
                    success: function (response) {
                        $('#appointmentModal').modal('hide'); // Close the modal
                        alert("Rendez-vous enregistré avec succès."); // Success message in French
                        refreshPatternDropdown();
                        refreshPatientDropdown();
                        const savedDoctorId = sessionStorage.getItem('selectedDoctorId');

                        // Refetch calendar events
                        if (savedDoctorId) {
                            selectedDoctorId = savedDoctorId;
                            $('#calendar').fullCalendar('refetchEvents');

                            // Ensure the previously selected doctor remains active
                            $('.doctor-item').removeClass('active');
                            $(`.doctor-item[data-doctor-id="${savedDoctorId}"]`).addClass('active');
                        } else {
                            // Default to the first doctor if none is saved
                            selectedDoctorId = $('.doctor-item.active').data('doctor-id');
                        }
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
                            refreshDoctorList(selectedDoctorId);
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

        });
        function resetAppointmentType() {
            // Set the "cabinet" radio button as checked
            $('#inCabinet').prop('checked', true);
            // Uncheck the "Téléconsultation" radio button
            $('#Téléconsultation').prop('checked', false);
        }
        function refreshDoctorList(selectedDoctorId) {
            $.ajax({
                url: "/get-doctors-list", // Adjust this endpoint as needed
                type: "GET",
                success: function (response) {
                    // Update the doctor list dropdown
                    const doctorDropdown = $('#doctorDropdown');
                    doctorDropdown.empty(); // Clear the current options
                    response.doctors.forEach(doctor => {
                        const selected = doctor.id === selectedDoctorId ? 'selected' : '';
                        doctorDropdown.append(`<option value="${doctor.id}" ${selected}>${doctor.name}</option>`);
                    });
                }
            });
        }
    </script>
@endpush