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
        <div class="modal fade" id="confirmDoneModal" tabindex="-1" role="dialog" aria-labelledby="confirmDoneModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDoneModalLabel">{{ trans('lang.confirmation') }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ trans('lang.do_you_want_to_end_appointment_and_start_consultation') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> {{ trans('lang.cancel') }}
                        </button>
                        <button type="button" class="btn btn-success"
                            id="confirmDoneButton">{{ trans('lang.confirm') }}</button>
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

        <div id="appointmentSidebar" class="appointment-sidebar">
            <div class="sidebar-header d-flex justify-content-between align-items-center">
                <button id="deleteAppointmentBtn" class="btn delete-btn" title="Delete Appointment">
                    <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 448 512" fill="white">
                        <path
                            d="M135.2 17.7C141.4 7.1 152.7 0 165.1 0H282.9c12.4 0 23.7 7.1 29.9 17.7L328 32H432c8.8 0 16 7.2 16 16s-7.2 16-16 16H416l-20.6 372.2c-1.8 32.1-28.3 57.8-60.5 57.8H113.1c-32.2 0-58.7-25.7-60.5-57.8L32 64H16C7.2 64 0 56.8 0 48s7.2-16 16-16H120l15.2-14.3zM144 96v336c0 8.8 7.2 16 16 16s16-7.2 16-16V96c0-8.8-7.2-16-16-16s-16 7.2-16 16zm80 0v336c0 8.8 7.2 16 16 16s16-7.2 16-16V96c0-8.8-7.2-16-16-16s-16 7.2-16 16zm96 0v336c0 8.8 7.2 16 16 16s16-7.2 16-16V96c0-8.8-7.2-16-16-16s-16 7.2-16 16z" />
                    </svg>
                </button>

                <h5 class="m-0 flex-grow-1 text-center">{{ trans('lang.appointment_details') }}</h5>
                <button id="editAppointmentBtn" class="btn update-btn" title="Edit Appointment">
                    <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 512 512" fill="white">
                        <path
                            d="M362.7 19.3c25.8-25.8 67.6-25.8 93.4 0l36.6 36.6c25.8 25.8 25.8 67.6 0 93.4L177.3 464.7c-9.1 9.1-20.6 15.3-33.1 18L25.3 508.6c-16.3 3.6-31.3-11.5-27.7-27.7l25.9-118.9c2.7-12.5 9-24 18-33.1L362.7 19.3zM388.1 70.6L112.6 346.1c-4.1 4.1-7 9.2-8.4 14.8l-18.4 84.4 84.4-18.4c5.6-1.2 10.7-4.2 14.8-8.4L441.4 123.9 388.1 70.6z" />
                    </svg>
                </button>

            </div>
            <div class="sidebar-body">
                <form id="appointmentForm">
                    <input type="hidden" id="sidebarAppointmentId" name="appointment_id">

                    <div class="form-group">
                        <label>{{ trans('lang.patient_name') }}</label>
                        <input type="text" id="sidebarPatientName" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('lang.status') }}</label>
                        <input type="text" id="sidebarStatus" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('lang.email') }}</label>
                        <input type="text" id="sidebarEmail" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('lang.phone') }}</label>
                        <input type="text" id="sidebarPhone" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('lang.appointment_type') }}</label>
                        <select id="updateAppointmentType" class="form-control" disabled>
                            <option value="cabinet">🏥 Cabinet</option>
                            <option value="teleconsultation">📹 Téléconsultation</option>
                            <option value="home_visit">🏠 Visite à domicile</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('lang.date') }}</label>
                        <input type="date" id="updateappointmentDate" class="form-control" disabled>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('lang.start_time') }}</label>
                        <select id="updatestartTime" class="form-control" disabled>
                            <option value="">{{ trans('lang.select_time') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('lang.motif') }}</label>
                        <select id="sidebarMotif" class="form-control">
                            <!-- Options will be populated dynamically based on selected type -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ trans('lang.notes') }}</label>
                        <textarea id="sidebarNote" class="form-control" rows="3" readonly></textarea>
                    </div>
                </form>
            </div>





            <div class="sidebar-footer">
                <button id="markAsFailed" class="btn btn-danger">
                    <i class="fas fa-times-circle"></i> {{ trans('lang.mark_failed') }}
                </button>
                <button id="markAsDone" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> {{ trans('lang.creatCons') }}
                </button>
                <button id="createTeleconsultation" class="btn btn-primary">
                    <i class="fas fa-video"></i> {{ trans('lang.create_teleconsultation') }}
                </button>
                <button id="saveAppointmentBtn" class="btn btn-primary d-none">
                    <i class="fas fa-save"></i> {{ trans('lang.save') }}
                </button>
            </div>

        </div>
        <div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>
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
                                            data-type="teleconsultation" role="tab" aria-controls="tele-pane" aria-selected="false">
                                            {{ __('Téléconsultation') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#home-pane" data-type="home_visit"
                                            role="tab" aria-controls="home-pane" aria-selected="false">
                                            {{ __('Visite à domicile') }}
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

                                    <!-- Single pattern display (will be shown when only one pattern) -->
                                    <div id="patternDisplay" class="text-white p-2 rounded text-center"
                                        style="background-color: #cccccc; font-weight: bold; font-size: 16px;">
                                        {{ trans('lang.no_pattern_selected') }}
                                    </div>

                                    <!-- Multiple patterns dropdown (initially hidden) -->
                                    <select id="patternSelector" class="form-control mt-2" style="display: none;">
                                        <option value="">{{ trans('lang.select_pattern') }}</option>
                                    </select>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- French Locale for Datepicker -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.fr.min.js"></script>
    <script>
        function closeSidebar() {
            // Close the sidebar
            $('#appointmentSidebar').css('right', '-400px');
            $('#sidebarOverlay').hide();

            // Reset all form fields
            $('#appointmentForm')[0].reset();

            // Clear all selects
            $('#sidebarMotif, #updatestartTime').empty();

            // Hide any validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Reset all fields to disabled/readonly state
            //$('#appointmentForm input, #appointmentForm textarea').prop('readonly', true);
            //$('#appointmentForm select').prop('disabled', true);

            // Hide save button, show default buttons based on permissions
            $('#saveAppointmentBtn').addClass('d-none');

            // Remove any alerts or warning messages
            $('#appointmentForm .alert').remove();

            // Clear any stored data attributes
            $('#updateAppointmentType').removeData('previous-type');
            $('#updateAppointmentType').prop('disabled', true);
            $('#updateappointmentDate').prop('disabled', true);
            $('#updatestartTime').prop('disabled', true);

            // Reset button state
            $('#editAppointmentBtn').show();

            // Reset any custom styling that might have been applied
            $('#appointmentForm input, #appointmentForm select, #appointmentForm textarea')
                .css('background-color', '');
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // ✅ Define global variables
        window.patternData = {};
        let selectedAppointmentId = null; // Store appointment ID
        let vacations = @json($vacations);
        let urgencies = @json($urgencies);
        window.activeDoctorId = {{ $doctorId ?? 'null' }};
        const patternsByType = @json($patternsByType);
        let isLoadingSlots = false;


        const currentLocale = '{{ app()->getLocale() }}';
        function loadAvailabilitySlots(viewStart) {
            // console.log("📡 Fetching background colors for:", viewStart);

            $.ajax({
                url: "/get-available-time-slots-presice",
                type: "GET",
                data: { date: viewStart.format("YYYY-MM-DD") }, // Send the current week's start date
                dataType: "json",
                success: function (availabilityResponse) {
                    console.log("📌 Availability Data:", availabilityResponse);

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
                //console.log("Calendar events refreshed");
            }
            $('<style>').text(`
                                                                                                                                                                                                                                                                                                                                                                                    .time-slot-button.active, 
                                                                                                                                                                                                                                                                                                                                                                                    .slot-btn.time-slot-button.active {
                                                                                                                                                                                                                                                                                                                                                                                        background-color: #001f3f !important;
                                                                                                                                                                                                                                                                                                                                                                                        color: #fff !important;
                                                                                                                                                                                                                                                                                                                                                                                        transform: translateY(-2px) !important;
                                                                                                                                                                                                                                                                                                                                                                                        border-color: #4a5aa5 !important;
                                                                                                                                                                                                                                                                                                                                                                                        font-weight: bold !important;
                                                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                                                `).appendTo('head');
            function resetFormFields() {
                // Reset patient selection
                $('#patientDropdown').val(null).trigger('change');


                // Reset time slots
                $('#timeSlotContainer').empty()
                    .append('<span class="text-muted">{{ __("lang.no_slots_available") }}</span>');
                $('#appointmentTime').val('');

                // Reset notes
                $('#notes').val('');

                // Hide submit button if no slots are available
                toggleSubmitButton(false);
            }

            // Function to toggle submit button visibility
            function toggleSubmitButton(hasSlots) {
                const submitBtn = $('.save-btn');
                if (hasSlots) {
                    submitBtn.show();
                } else {
                    submitBtn.hide();
                }
            }
            // Set interval to refresh calendar every 30 seconds
            //setInterval(refreshCalendarEvents, 10000);
            ////////////////////////////////
            function updateAllPatterns(date, time, activeType = 'cabinet') {
                console.log("Updating all patterns for date:", date, "time:", time, "active type:", activeType);

                // Store the selected date and time globally for reference when switching tabs
                window.globalSelectedDate = date;
                window.globalSelectedTime = time;

                // Set the active tab based on the activeType parameter
                $('#apptTypeTabs a').removeClass('active');
                $(`#apptTypeTabs a[data-type="${activeType}"]`).addClass('active').tab('show');

                // Update the appointment type input
                $('#appointmentType').val(activeType);

                // Initialize patternData object if not exists
                if (!window.patternData) {
                    window.patternData = {};
                }

                // Define all appointment types
                const types = [
                    { type: 'cabinet' },
                    { type: 'Téléconsultation' },
                    { type: 'home_visit' }
                ];

                // Fetch patterns for all types
                types.forEach(item => {
                    $.ajax({
                        url: "/get-pattern-for-time-slot",
                        method: 'GET',
                        data: {
                            date: date,
                            time: time,
                            type: item.type,
                            fetch_all_patterns: true // Add this parameter to tell backend we want all patterns
                        },
                        success: function (response) {
                            console.log("Pattern data for type:", item.type, response);
                            window.patternData[item.type] = response;

                            // If this is the active tab type, update UI
                            if (item.type === activeType) {
                                updateUIForType(item.type);
                            }
                        },
                        error: function () {
                            console.error('Error fetching data for type:', item.type);
                        }
                    });
                });
            }
            function fetchTimeSlotsForTypeForUpdateAppointment(date, type) {
                console.log("Fetching time slots for update appointment:", date, type);

                $.ajax({
                    url: "/get-available-time-slots-for-update",
                    type: "GET",
                    data: { date: date, type: type },
                    success: function (response) {
                        console.log("Fetched Time Slots Response:", response);
                        isLoadingSlots = false;
                        updateSessionDuration = response.session_duration;

                        const $timeSelect = $('#updatestartTime');
                        const previousValue = $timeSelect.val();
                        const shouldOpen = $timeSelect.data('shouldOpenAfterLoad');

                        // Remove loading indicators
                        $timeSelect.removeClass('loading-select');
                        $timeSelect.siblings('.loading-overlay').remove();
                        $timeSelect.prop('disabled', false);

                        $timeSelect.empty();

                        if (response.vacation) {
                            Swal.fire({
                                title: "Le docteur est en vacances",
                                text: "Aucune disponibilité n'est possible ce jour.",
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                            $timeSelect.append(`<option value="">Aucun créneau (vacances)</option>`);
                            $timeSelect.prop('disabled', true);
                            $timeSelect.removeData('shouldOpenAfterLoad');
                            return;
                        }

                        if (!response.available_slots || response.available_slots.length === 0) {
                            $timeSelect.append(`<option value="">Aucun créneau disponible</option>`);
                            $timeSelect.prop('disabled', true);
                            $timeSelect.removeData('shouldOpenAfterLoad');
                            return;
                        }

                        $timeSelect.prop('disabled', false);
                        $timeSelect.append(`<option value="">{{ trans('lang.select_time') }}</option>`);

                        const taken = new Set(response.taken_slots || []);
                        let currentTimeFound = false;

                        // First, add the current time if it exists
                        if (previousValue && previousValue !== "" && previousValue !== "Chargement..." && !previousValue.includes('Chargement des créneaux')) {
                            $timeSelect.append(`<option value="${previousValue}">${previousValue} (Actuel)</option>`);
                            currentTimeFound = true;
                        }

                        // Then add available slots
                        response.available_slots.forEach(slot => {
                            if (!taken.has(slot) && slot !== previousValue) {
                                $timeSelect.append(`<option value="${slot}">${slot}</option>`);
                            }
                        });

                        // Restore the previous selection
                        if (previousValue && currentTimeFound && previousValue !== "Chargement..." && !previousValue.includes('Chargement des créneaux')) {
                            $timeSelect.val(previousValue);
                        }

                        if (!previousValue || previousValue === "Chargement..." || previousValue.includes('Chargement des créneaux')) {
                            $('#sidebarMotif').empty().append(`<option value="">{{ trans('lang.select_time_first') }}</option>`).prop('disabled', true);
                        }

                        // Open dropdown if it was requested
                        if (shouldOpen) {
                            $timeSelect.removeData('shouldOpenAfterLoad');

                            // Use a more reliable method to open the dropdown
                            setTimeout(() => {
                                $timeSelect.focus();

                                // Try multiple methods to ensure the dropdown opens
                                if ($timeSelect[0].showPicker) {
                                    // Modern browsers
                                    $timeSelect[0].showPicker();
                                } else {
                                    // Fallback for older browsers
                                    $timeSelect.trigger('click');

                                    // Alternative fallback
                                    const event = new Event('mousedown', { bubbles: true });
                                    $timeSelect[0].dispatchEvent(event);
                                }
                            }, 150);
                        }
                    },
                    error: function (xhr) {
                        console.error("Error fetching time slots:", xhr);
                        const $timeSelect = $('#updatestartTime');

                        // Remove loading indicators
                        $timeSelect.removeClass('loading-select');
                        $timeSelect.siblings('.loading-overlay').remove();
                        $timeSelect.prop('disabled', false);

                        $timeSelect.empty().append(`<option value="">Erreur de chargement</option>`);
                        $timeSelect.removeData('shouldOpenAfterLoad');

                        Swal.fire({
                            title: "Erreur",
                            text: "Une erreur s'est produite lors de la récupération des créneaux horaires. Veuillez réessayer.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
            function fetchMotifsForTimeSlot(date, time, type) {
                console.log("Fetching motifs for time slot:", date, time, type);

                $.ajax({
                    url: "/get-pattern-for-time-slot",
                    type: "GET",
                    data: {
                        date: date,
                        time: time,
                        type: type
                    },
                    success: function (response) {
                        console.log("Fetched Motifs Response:", response);

                        const $motifSelect = $('#sidebarMotif');
                        $motifSelect.empty();
                        $motifSelect.prop('disabled', false);

                        if (response.patterns && response.patterns.length > 0) {
                            $motifSelect.append(`<option value="">{{ trans('lang.select_motif') }}</option>`);

                            response.patterns.forEach(pattern => {
                                $motifSelect.append(`<option value="${pattern.pattern_id}">${pattern.pattern_name}</option>`);
                            });
                        } else if (response.pattern_id) {
                            // Single pattern case
                            $motifSelect.append(`<option value="${response.pattern_id}">${response.pattern_name}</option>`);
                            $motifSelect.val(response.pattern_id);
                        } else {
                            $motifSelect.append(`<option value="">Aucun motif disponible</option>`);
                        }
                    },
                    error: function (xhr) {
                        console.error("Error fetching motifs:", xhr);
                        $('#sidebarMotif').empty().append(`<option value="">Erreur lors du chargement</option>`);
                    }
                });
            }
            // Update the UI for a given appointment type using its returned data
            // Main function to update UI based on appointment type
            // Modify the updateUIForType function to not show slots initially when multiple patterns exist
            function updateUIForType(apptType) {
                console.log("Updating UI for type:", apptType);

                const data = window.patternData[apptType];
                console.log("Pattern data for", apptType, ":", data);

                // Check if we have patterns data
                let patterns = [];

                if (data && data.patterns && Array.isArray(data.patterns)) {
                    patterns = data.patterns;
                    console.log("Found", patterns.length, "patterns from patterns array");
                } else if (data && data.pattern_id) {
                    patterns = [{
                        pattern_id: data.pattern_id,
                        pattern_name: data.pattern_name,
                        pattern_color: data.pattern_color
                    }];
                    console.log("Found single pattern from legacy format");
                }

                // Clear time slots container initially
                $('#patternDisplay').hide();
                $('#patternSelector').hide();
                $('.custom-pattern-selector').remove();
                const $container = $('#timeSlotContainer');
                $container.empty();

                // For multiple patterns, don't show slots yet - ask user to select a pattern first
                if (patterns.length > 1) {
                    $container.append('<div class="text-center w-100 p-3 text-muted">{{ trans("lang.select_pattern_first") }}</div>');
                    // Hide time slots until pattern is selected
                    $('#appointmentTime').val('');
                }

                // Update the pattern display based on patterns count
                if (patterns.length > 1) {
                    console.log("Multiple patterns found, showing dropdown");
                    showMultiplePatterns(patterns, apptType);
                } else if (patterns.length === 1) {
                    console.log("Single pattern found, showing regular display");
                    showSinglePattern(patterns[0]);
                    // For single pattern, show slots immediately
                    displaySlotsForType(apptType, data);
                } else {
                    console.log("No patterns found");
                    showSinglePattern(null);
                    // No patterns, clear slots and show message
                    $container.append('<div class="text-center w-100 p-3 text-muted">{{ trans("lang.no_pattern_available") }}</div>');
                }

                // Reset form fields
                resetFormFields();
            }

            // Function to display slots from the existing data
            // Function to display slots from the existing data
            function displaySlotsForType(apptType, data) {
                console.log("Displaying slots for type:", apptType);

                // First, clear any existing protection interval
                if (window.slotProtectionInterval) {
                    clearInterval(window.slotProtectionInterval);
                    window.slotProtectionInterval = null;
                }

                // Get container and hidden input
                const $container = $('#timeSlotContainer');
                const $hiddenInput = $('#appointmentTime');

                // Store the current apptType - important for tab switching
                $container.data('currentApptType', apptType);

                // Clear container
                $container.empty();

                // Check if we have slots data
                const availableSlots = data.available_slots || [];
                const takenSlots = Array.isArray(data.taken_slots) ? data.taken_slots : [];

                console.log("Available slots:", availableSlots);
                console.log("Taken slots:", takenSlots);

                // If no available slots, show message and disable save button
                if (availableSlots.length === 0) {
                    $container.append('<span class="text-muted">{{ __("lang.no_slots_available") }}</span>');
                    $hiddenInput.val('');
                    $('.save-btn').prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-secondary');
                    return;
                }

                // Get selected date
                const selectedDate = $("#appointmentDate").val();
                const dateToUse = window.globalSelectedDate || selectedDate;

                // Store the current date - important for reopening the modal
                $container.data('currentDate', selectedDate);

                // Check for urgency
                const matchedUrgency = window.urgencies ? window.urgencies.find(urgency => urgency.jour === selectedDate) : null;
                console.log("Matched urgency:", matchedUrgency);

                // Keep track of slots added
                let slotsAdded = 0;
                let availableVisibleSlots = 0;

                if (matchedUrgency) {
                    console.log("Processing with urgency consideration");
                    const urgencyStart = moment(matchedUrgency.heurDebut, "HH:mm:ss").format("HH:mm");
                    const urgencyEnd = moment(matchedUrgency.heurFin, "HH:mm:ss").format("HH:mm");

                    console.log("Urgency time range:", urgencyStart, "to", urgencyEnd);

                    availableSlots.forEach(slot => {
                        // Check if slot is within urgency range
                        const slotMoment = moment(slot, "HH:mm");
                        const urgencyStartMoment = moment(urgencyStart, "HH:mm");
                        const urgencyEndMoment = moment(urgencyEnd, "HH:mm");

                        const isInUrgencyRange =
                            slotMoment.isSameOrAfter(urgencyStartMoment) &&
                            slotMoment.isSameOrBefore(urgencyEndMoment);

                        console.log(`Slot ${slot} in urgency range? ${isInUrgencyRange}`);

                        // Skip rendering if in urgency range
                        if (isInUrgencyRange) {
                            return; // Skip this iteration
                        }

                        const isTaken = takenSlots.includes(slot);
                        // Check if the dateToUse is valid before using it
                        const isPast = dateToUse ? moment(`${dateToUse} ${slot}`, "YYYY-MM-DD HH:mm").isBefore(moment()) : false;

                        console.log(`Slot ${slot} - Taken: ${isTaken}, Past: ${isPast}`);

                        const $btn = $(` <button type="button" class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}" data-slot="${slot}" ${isTaken || isPast ? 'disabled' : ''} > ${slot} </button> `);

                        // Only add click handler for available slots
                        if (!isTaken && !isPast) {
                            $btn.on('click', function () {
                                $container.find('.slot-btn').removeClass('active').css({
                                    'background-color': '',
                                    'color': '',
                                    'transform': ''
                                });
                                $(this).addClass('active').css({
                                    'background-color': '#001f3f',
                                    'color': '#fff',
                                    'transform': 'translateY(-2px)'
                                });
                                $hiddenInput.val(slot);
                            });

                            $btn.addClass('available-slot');
                            availableVisibleSlots++;
                        }

                        $container.append($btn);
                        slotsAdded++;
                    });
                } else {
                    console.log("Processing without urgency");
                    availableSlots.forEach(slot => {
                        const isTaken = takenSlots.includes(slot);
                        const isPast = dateToUse ? moment(`${dateToUse} ${slot}`, "YYYY-MM-DD HH:mm").isBefore(moment()) : false;

                        console.log(`Slot ${slot} - Taken: ${isTaken}, Past: ${isPast}`);

                        const $btn = $(` <button type="button" class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}" data-slot="${slot}" ${isTaken || isPast ? 'disabled' : ''}> ${slot} </button> `);

                        // If it's not taken and not in the past, let the user pick it
                        if (!isTaken && !isPast) {
                            $btn.on('click', function () {
                                console.log("Slot clicked1:", slot);
                                $container.find('.slot-btn').removeClass('active').css({
                                    'background-color': '',
                                    'color': '',
                                    'transform': ''
                                });
                                $(this).addClass('active').css({
                                    'background-color': '#001f3f',
                                    'color': '#fff',
                                    'transform': 'translateY(-2px)'
                                });
                                $hiddenInput.val(slot);
                            });

                            // Add a class to make it more noticeable
                            $btn.addClass('available-slot');

                            // Count this as a visibly available slot
                            availableVisibleSlots++;
                        }

                        $container.append($btn);
                        slotsAdded++;
                    });
                }

                console.log(`Total slots added: ${slotsAdded}, Available slots: ${availableVisibleSlots}`);

                // Enable save button if slots were added
                if (slotsAdded > 0) {
                    console.log("Enabling save button");
                    $('.save-btn').prop('disabled', false)
                        .removeClass('btn-secondary')
                        .addClass('btn-primary');

                    // Store these values for context checking
                    const currentType = apptType;
                    const currentDate = selectedDate;

                    // ---- IMPROVED SLOT PROTECTION MECHANISM ----
                    // Instead of storing the entire HTML, track the slots and their state
                    const originalSlots = [];
                    $container.find('.slot-btn').each(function () {
                        originalSlots.push({
                            slot: $(this).data('slot'),
                            disabled: $(this).prop('disabled'),
                            isTaken: $(this).hasClass('slot-taken')
                        });
                    });

                    const intervalId = setInterval(function () {
                        // Only restore if we're still on the same tab/type and date
                        const activeType = $('#appointmentType').val();
                        const activeDate = $("#appointmentDate").val();

                        // Get currently selected slot
                        const selectedSlot = $hiddenInput.val();

                        if (activeType === currentType && activeDate === currentDate) {
                            // Check if buttons count has changed or buttons have been modified
                            const currentButtons = $container.find('.slot-btn');

                            if (currentButtons.length !== originalSlots.length) {
                                console.log("Button count changed, restoring slots");
                                restoreSlots();
                                return;
                            }

                            // Check if any buttons are missing their data-slot attribute
                            let buttonsMissingData = false;
                            currentButtons.each(function () {
                                if ($(this).data('slot') === undefined) {
                                    buttonsMissingData = true;
                                    return false; // Break the loop
                                }
                            });

                            if (buttonsMissingData) {
                                console.log("Buttons missing data, restoring slots");
                                restoreSlots();
                            }
                        }

                        function restoreSlots() {
                            // Save the selected slot
                            const selectedSlot = $hiddenInput.val();

                            // Clear container and rebuild
                            $container.empty();

                            originalSlots.forEach(slotData => {
                                const $btn = $(` <button type="button" 
                                                                                                                                                                                                                                                                                                                                                                                                class="slot-btn time-slot-button m-1 ${slotData.isTaken ? 'slot-taken' : ''}" 
                                                                                                                                                                                                                                                                                                                                                                                                data-slot="${slotData.slot}" 
                                                                                                                                                                                                                                                                                                                                                                                                ${slotData.disabled ? 'disabled' : ''}>
                                                                                                                                                                                                                                                                                                                                                                                                ${slotData.slot}
                                                                                                                                                                                                                                                                                                                                                                                            </button> `);

                                // Add click handler if not disabled
                                if (!slotData.disabled && !slotData.isTaken) {
                                    $btn.on('click', function () {
                                        console.log("Slot clicked (restored):", slotData.slot);
                                        $container.find('.slot-btn').removeClass('active').css({
                                            'background-color': '',
                                            'color': '',
                                            'transform': ''
                                        });
                                        $(this).addClass('active').css({
                                            'background-color': '#001f3f',
                                            'color': '#fff',
                                            'transform': 'translateY(-2px)'
                                        });
                                        $hiddenInput.val(slotData.slot);
                                    });

                                    $btn.addClass('available-slot');

                                    // If this was the selected slot, make it active
                                    if (slotData.slot === selectedSlot) {
                                        $btn.addClass('active').css({
                                            'background-color': '#001f3f',
                                            'color': '#fff',
                                            'transform': 'translateY(-2px)'
                                        });
                                    }
                                }

                                $container.append($btn);
                            });

                            // Restore the selected slot value
                            $hiddenInput.val(selectedSlot);
                        }
                    }, 300); // Reduced frequency to be less aggressive

                    // Store interval ID to clear it later if needed
                    window.slotProtectionInterval = intervalId;
                } else {
                    console.log("No slots were added to container");
                    if (availableSlots.length > 0) {
                        console.log("WARNING: Had available slots but none were displayed");
                        $container.append('<div class="text-center w-100 p-3 text-warning">{{ trans("lang.no_valid_slots_found") }}</div>');
                    }
                }
            }

            // Improved tab change handler setup 
            /**
    * Set up handlers for tab changes
    */
            /**
    * Set up handlers for tab changes
    */


            // Modify the showMultiplePatterns function to include appointmentType parameter
            function showMultiplePatterns(patterns, apptType) {
                console.log("Setting up dropdown for patterns:", patterns);

                // Hide the single pattern display
                $('#patternDisplay').hide();

                // Get the container
                const $container = $('#patternDisplay').parent();

                // Remove existing dropdown if any
                $('#patternSelector').remove();
                $('.custom-pattern-selector').remove();

                // Create a custom dropdown replacement with rounded corners and matching widths
                const $customDropdown = $(`
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="custom-pattern-selector" style="position: relative;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="selected-pattern p-2 text-center" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     style="background-color: #fff; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border: 1px solid #ced4da; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            cursor: pointer; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            height: 38px; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            display: flex; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            align-items: center; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            justify-content: space-between;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border-radius: 8px; /* Curved corners */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            overflow: hidden;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            width: 100%;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <span>{{ trans('lang.select_pattern') }}</span>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-chevron-down"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="pattern-options" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     style="display: none; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            position: absolute;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            width: 100%; /* Same width as parent */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            z-index: 1000; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            background: white; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border: 1px solid #ced4da; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border-top: 1px solid #ced4da; /* Add visible top border */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            max-height: 200px; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            overflow-y: auto;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border-radius: 0 0 8px 8px; /* Rounded corners at bottom */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Add subtle shadow */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            left: 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            right: 0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            top: 100%; /* Position directly below the selector */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            margin-top: -1px; /* Slightly overlap to avoid double-border */
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <!-- Divider line -->
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="dropdown-divider" style="height: 1px; background-color: #ced4da; margin: 0;"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <input type="hidden" id="pattern-value">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `);

                // Add pattern options
                const $optionsContainer = $customDropdown.find('.pattern-options');

                // Add each pattern as an option
                patterns.forEach(pattern => {
                    $optionsContainer.append(`
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="pattern-option p-2" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     data-value="${pattern.pattern_id}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     data-color="${pattern.pattern_color}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     data-name="${pattern.pattern_name}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     style="cursor: pointer; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border-bottom: 1px solid #f0f0f0;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            transition: background-color 0.2s;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${pattern.pattern_name}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `);
                });

                // Toggle dropdown on click
                $customDropdown.find('.selected-pattern').on('click', function () {
                    console.log("test1");
                    const $selectedDisplay = $(this);
                    console.log($selectedDisplay);
                    // Toggle options visibility
                    $optionsContainer.toggle();

                    // Adjust border radius based on dropdown state
                    if ($optionsContainer.is(':visible')) {
                        // When open, round only the top corners
                        $selectedDisplay.css({
                            'border-radius': '8px 8px 0 0',
                            'border-bottom-color': '#ced4da' // Make bottom border visible
                        });
                    } else {
                        // When closed, fully rounded
                        $selectedDisplay.css({
                            'border-radius': '8px',
                            'border-bottom-color': '#ced4da'
                        });
                    }
                });

                // Handle option selection
                $customDropdown.find('.pattern-option').on('click', function () {
                    console.log("tesssssssssssssssssssssssssssst");
                    const value = $(this).data('value');
                    const name = $(this).data('name') || '{{ trans("lang.select_pattern") }}';
                    const color = $(this).data('color');

                    // Update the selected display
                    const $selectedDisplay = $customDropdown.find('.selected-pattern');
                    $selectedDisplay.find('span').text(name);

                    // Update the hidden inputs
                    $('#motif_id').val(value);
                    $customDropdown.find('#pattern-value').val(value);

                    // Apply color only if a pattern is selected
                    if (value) {
                        $selectedDisplay.css({
                            'background-color': color,
                            'color': getContrastYIQ(color),
                            'font-weight': 'bold',
                            'border-radius': '8px' // Fully rounded when closed
                        });

                        // Show loading message in the time slots container
                        const $container = $('#timeSlotContainer');
                        $container.empty();
                        $container.append('<div class="text-center w-100 p-3"><i class="fas fa-spinner fa-spin"></i> {{ trans("lang.loading_slots") }}</div>');

                        // Get selected date
                        const selectedDate = $("#appointmentDate").val();
                        console.log("Selected date:", globalSelectedDate, "time:", globalSelectedTime);
                        const datetime = globalSelectedDate + ' ' + globalSelectedTime;
                        //console.log("Selected date:", selectedDate);
                        // Fetch the available slots for this pattern
                        fetchSlotsForPattern(value, apptType, globalSelectedDate, globalSelectedTime);
                    } else {
                        //console.log("No pattern selected, resetting display");
                        $selectedDisplay.css({
                            'background-color': '#fff',
                            'color': '#495057',
                            'font-weight': 'normal',
                            'border-radius': '8px' // Fully rounded when closed
                        });

                        // Clear slots if no pattern selected
                        $('#timeSlotContainer').empty();
                        $('#timeSlotContainer').append('<div class="text-center w-100 p-3 text-muted">{{ trans("lang.select_pattern_first") }}</div>');
                        $('#appointmentTime').val('');
                    }

                    // Hide the options
                    $optionsContainer.hide();
                });

                // Close dropdown when clicking outside
                $(document).mouseup(function (e) {
                    if (!$customDropdown.is(e.target) && $customDropdown.has(e.target).length === 0) {
                        $optionsContainer.hide();
                        $customDropdown.find('.selected-pattern').css('border-radius', '8px'); // Restore full border radius
                    }
                });

                // Add the custom dropdown to the container
                $container.append($customDropdown);

                // Add some basic CSS to make it look nice
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                .pattern-option:hover {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    background-color: #f8f9fa;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                .pattern-option:last-child {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    border-bottom: none !important;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    border-radius: 0 0 8px 8px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `)
                    .appendTo('head');

                // Set a specific width after rendering to ensure they match
                setTimeout(function () {
                    const selectorWidth = $customDropdown.find('.selected-pattern').outerWidth();
                    $customDropdown.find('.pattern-options').css('width', selectorWidth + 'px');
                }, 0);
            }


            // Function to fetch slots for a specific pattern
            function fetchSlotsForPattern(patternId, apptType, date, time) {
                console.log("Fetching slots for pattern ID:", patternId, "Type:", apptType, "Date:", date);

                // Show loading message
                const $container = $('#timeSlotContainer');
                $container.empty();
                $container.append('<div class="text-center w-100 p-3"><i class="fas fa-spinner fa-spin"></i> {{ trans("lang.loading_slots") }}</div>');


                // Make the AJAX request
                $.ajax({
                    url: "/get-slots-for-pattern",
                    method: 'GET',
                    data: {
                        date: date,
                        pattern_id: patternId,
                        type: apptType,
                        time: time
                    },
                    success: function (response) {
                        console.log("Slots received for pattern:", response);

                        // Display the slots
                        $container.empty();

                        // Check if we have slots
                        const availableSlots = response.available_slots || [];
                        const takenSlots = response.taken_slots || [];

                        if (availableSlots.length === 0) {
                            $container.append('<span class="text-muted">{{ __("lang.no_slots_available") }}</span>');
                            $('#appointmentTime').val('');
                            $('.save-btn').prop('disabled', true)
                                .removeClass('btn-primary')
                                .addClass('btn-secondary');
                            return;
                        }

                        // Display the slots
                        displaySlotsForPattern(availableSlots, takenSlots, date, patternId);

                        // Enable save button
                        $('.save-btn').prop('disabled', false)
                            .removeClass('btn-secondary')
                            .addClass('btn-primary');

                        // Set up protection mechanism
                        setupSlotProtection($container, patternId, apptType, date);
                    },
                    error: function (error) {
                        console.error("Error fetching slots for pattern:", error);

                        $container.empty();
                        $container.append('<div class="text-center w-100 p-3 text-danger">{{ trans("lang.error_loading_slots") }}</div>');
                    }
                });
            }
            function displaySlotsForPattern(availableSlots, takenSlots, date, patternId) {
                console.log("Displaying slots for pattern ID:", patternId);
                const $container = $('#timeSlotContainer');
                const $hiddenInput = $('#appointmentTime');

                // Add CSS for the slots if not already added
                //addSlotStyles();

                // Check if there's a matched urgency
                const matchedUrgency = urgencies.find(urgency => urgency.jour === date);

                // Track available slots
                let availableVisibleSlots = 0;

                // Create a wrapper for the slots
                const $slotsWrapper = $('<div class="d-flex flex-wrap justify-content-center" style="gap: 5px;"></div>');
                $container.append($slotsWrapper);

                // Process slots
                availableSlots.forEach(slot => {
                    // Skip if slot is in urgency range
                    if (matchedUrgency) {
                        const urgencyStart = moment(matchedUrgency.heurDebut, "HH:mm:ss").format("HH:mm");
                        const urgencyEnd = moment(matchedUrgency.heurFin, "HH:mm:ss").format("HH:mm");

                        const slotMoment = moment(slot, "HH:mm");
                        const urgencyStartMoment = moment(urgencyStart, "HH:mm");
                        const urgencyEndMoment = moment(urgencyEnd, "HH:mm");

                        if (slotMoment.isSameOrAfter(urgencyStartMoment) && slotMoment.isSameOrBefore(urgencyEndMoment)) {
                            return; // Skip this slot
                        }
                    }

                    // Check if slot is taken for this specific pattern
                    const isTaken = checkIfSlotIsTaken(slot, takenSlots);
                    const isPast = moment(`${date} ${slot}`, "YYYY-MM-DD HH:mm").isBefore(moment());

                    // Create button
                    const $btn = $(`<button type="button" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              data-slot="${slot}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              data-pattern="${patternId}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              ${isTaken || isPast ? 'disabled' : ''}>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              ${slot}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </button>`);

                    // Add click handler for available slots
                    if (!isTaken && !isPast) {
                        $btn.on('click', function () {
                            console.log("Slot clicked111:", slot);
                            $slotsWrapper.find('.slot-btn').removeClass('active');
                            $(this).addClass('active');
                            $hiddenInput.val(slot);

                        });

                        // Add available-slot class
                        $btn.addClass('available-slot');

                        availableVisibleSlots++;
                    }

                    $slotsWrapper.append($btn);
                });

                // If no available slots, show message
                if (availableVisibleSlots === 0) {
                    $container.empty();
                    $container.append('<span class="text-muted">{{ __("lang.no_slots_available") }}</span>');
                    $hiddenInput.val('');
                    $('.save-btn').prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-secondary');
                }

                // Store pattern ID with the container
                $container.data('patternId', patternId);
            }

            function checkIfSlotIsTaken(slot, takenSlots) {
                // Check if takenSlots is an array
                if (Array.isArray(takenSlots)) {
                    return takenSlots.includes(slot);
                }

                // Check if takenSlots is an object with numeric keys
                if (typeof takenSlots === 'object' && takenSlots !== null) {
                    return Object.values(takenSlots).includes(slot);
                }

                return false;
            }
            function setupSlotProtection($container, patternId, apptType, date) {
                // Clear any existing protection interval
                if (window.slotProtectionInterval) {
                    clearInterval(window.slotProtectionInterval);
                    window.slotProtectionInterval = null;
                }

                // Save slots state instead of HTML
                const originalSlots = [];
                $container.find('.slot-btn').each(function () {
                    originalSlots.push({
                        slot: $(this).data('slot'),
                        disabled: $(this).prop('disabled'),
                        isTaken: $(this).hasClass('slot-taken')
                    });
                });

                // Set up interval to check if content has changed
                const intervalId = setInterval(function () {
                    // Get current values
                    const currentPatternId = $container.data('patternId');
                    const currentButtons = $container.find('.slot-btn');
                    const selectedSlot = $('#appointmentTime').val();

                    // Check if the number of buttons changed or pattern changed
                    if (currentPatternId === patternId &&
                        (currentButtons.length !== originalSlots.length ||
                            currentButtons.length === 0)) {

                        console.log("Container content changed2, restoring original");
                        restoreSlots();
                    }

                    // Also check if any buttons are missing their data-slot attribute
                    let buttonsMissingData = false;
                    currentButtons.each(function () {
                        if ($(this).data('slot') === undefined) {
                            buttonsMissingData = true;
                            return false; // Break the loop
                        }
                    });

                    if (currentPatternId === patternId && buttonsMissingData) {
                        console.log("Buttons missing data, restoring slots");
                        restoreSlots();
                    }

                    function restoreSlots() {
                        // Clear container and rebuild
                        $container.empty();

                        originalSlots.forEach(slotData => {
                            const $btn = $(` <button type="button" 
                                                                                                                                                                                                                                                                                                                                                                                        class="slot-btn time-slot-button m-1 ${slotData.isTaken ? 'slot-taken' : ''}" 
                                                                                                                                                                                                                                                                                                                                                                                        data-slot="${slotData.slot}" 
                                                                                                                                                                                                                                                                                                                                                                                        data-pattern="${patternId}"
                                                                                                                                                                                                                                                                                                                                                                                        ${slotData.disabled ? 'disabled' : ''}>
                                                                                                                                                                                                                                                                                                                                                                                        ${slotData.slot}
                                                                                                                                                                                                                                                                                                                                                                                    </button> `);

                            // Add click handler if not disabled
                            if (!slotData.disabled && !slotData.isTaken) {
                                $btn.on('click', function () {
                                    $container.find('.slot-btn').removeClass('active').css({
                                        'background-color': '',
                                        'color': '',
                                        'transform': ''
                                    });
                                    $(this).addClass('active').css({
                                        'background-color': '#001f3f',
                                        'color': '#fff',
                                        'transform': 'translateY(-2px)'
                                    });
                                    $('#appointmentTime').val(slotData.slot);
                                });

                                $btn.addClass('available-slot');

                                // If this was the selected slot, make it active
                                if (slotData.slot === selectedSlot) {
                                    $btn.addClass('active').css({
                                        'background-color': '#001f3f',
                                        'color': '#fff',
                                        'transform': 'translateY(-2px)'
                                    });
                                }
                            }

                            $container.append($btn);
                        });

                        // Restore the selected slot value
                        $('#appointmentTime').val(selectedSlot);
                    }
                }, 300);

                // Store interval ID to clear it later
                window.slotProtectionInterval = intervalId;
            }
            // Helper function to display slots
            function displaySlots(availableSlots, takenSlots, date) {
                const $container = $('#timeSlotContainer');
                const $hiddenInput = $('#appointmentTime');

                // Check if there's a matched urgency
                const matchedUrgency = urgencies.find(urgency => urgency.jour === date);

                if (matchedUrgency) {
                    const urgencyStart = moment(matchedUrgency.heurDebut, "HH:mm:ss").format("HH:mm");
                    const urgencyEnd = moment(matchedUrgency.heurFin, "HH:mm:ss").format("HH:mm");

                    availableSlots.forEach(slot => {
                        // Check if slot is within urgency range
                        const slotMoment = moment(slot, "HH:mm");
                        const urgencyStartMoment = moment(urgencyStart, "HH:mm");
                        const urgencyEndMoment = moment(urgencyEnd, "HH:mm");

                        const isInUrgencyRange =
                            slotMoment.isSameOrAfter(urgencyStartMoment) &&
                            slotMoment.isSameOrBefore(urgencyEndMoment);

                        // Skip rendering if in urgency range
                        if (isInUrgencyRange) {
                            return; // Skip this iteration
                        }

                        const isTaken = takenSlots.includes(slot);
                        const isPast = moment(`${date} ${slot}`, "YYYY-MM-DD HH:mm").isBefore(moment());

                        const $btn = $(` <button type="button" class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}" data-slot="${slot}" ${isTaken || isPast ? 'disabled' : ''} > ${slot} </button> `);

                        // Only add click handler for available slots
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
                    // Regular slot display without urgency
                    availableSlots.forEach(slot => {
                        const isTaken = takenSlots.includes(slot);
                        const isPast = moment(date + ' ' + slot).isBefore(moment());

                        const $btn = $(` <button type="button" class="slot-btn time-slot-button m-1 ${isTaken || isPast ? 'slot-taken' : ''}" data-slot="${slot}" ${isTaken || isPast ? 'disabled' : ''}> ${slot} </button> `);

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
                }
            }

            // Function to show single pattern display
            function showSinglePattern(pattern) {
                console.log("Setting up display for single pattern:", pattern);

                // Hide the dropdown and show the single pattern display
                $('#patternSelector').hide();

                // Update the single pattern display
                const $display = $('#patternDisplay');

                if (pattern) {
                    $display.text(pattern.pattern_name);
                    $display.css({
                        'background-color': pattern.pattern_color,
                        'color': getContrastYIQ(pattern.pattern_color)
                    });
                    $('#motif_id').val(pattern.pattern_id);
                } else {
                    $display.text('{{ trans("lang.no_pattern_selected") }}');
                    $display.css({
                        'background-color': '#cccccc',
                        'color': '#000000'
                    });
                    $('#motif_id').val('');
                }

                // Show the display
                $display.show();
            }

            // Helper function for text contrast
            function getContrastYIQ(hexcolor) {
                if (!hexcolor || hexcolor === '#cccccc' || hexcolor === '') {
                    return '#000000';
                }

                // Remove hash if present
                hexcolor = hexcolor.replace('#', '');

                // Convert to RGB values
                const r = parseInt(hexcolor.substr(0, 2), 16);
                const g = parseInt(hexcolor.substr(2, 2), 16);
                const b = parseInt(hexcolor.substr(4, 2), 16);

                // Calculate luminance
                const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;

                // Return black for light colors, white for dark
                return (yiq >= 128) ? '#000000' : '#ffffff';
            }


            /**
    * Set up handlers for tab changes
    */
            function setupTabChangeHandlers() {
                // Only set up once
                if (window.tabHandlersInitialized) {
                    return;
                }

                console.log("Setting up tab change handlers");

                // When a tab is about to be shown (but not yet active)
                $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
                    console.log("Tab is about to change");

                    // Clear any existing protection interval
                    if (window.slotProtectionInterval) {
                        clearInterval(window.slotProtectionInterval);
                        window.slotProtectionInterval = null;
                    }

                    // Empty the slot container immediately
                    $('#timeSlotContainer').empty();
                    console.log("Cleared time slot container");

                    // Reset time slot selection
                    $('#appointmentTime').val('');

                    // Disable save button until a slot is selected
                    $('.save-btn').prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-secondary');
                });

                // When a tab has been fully shown (now active)
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    // Get the new tab type
                    const newTabType = $(e.target).data('type');
                    console.log("Tab changed to type:", newTabType);

                    // Update hidden input with new appointment type
                    $('#appointmentType').val(newTabType);

                    // Get the stored date and time from when the user clicked on the agenda
                    const selectedDate = window.globalSelectedDate;
                    const selectedTime = window.globalSelectedTime;

                    if (!selectedDate || !selectedTime) {
                        console.error("Missing date or time context!");
                        return;
                    }

                    console.log("Using stored date:", selectedDate, "time:", selectedTime);

                    // Check if we already have pattern data for this type
                    if (window.patternData && window.patternData[newTabType]) {
                        console.log("Using existing pattern data for type:", newTabType);
                        // We already have the data, just update the UI
                        updateUIForType(newTabType);
                    } else {
                        console.log("No pattern data found for type:", newTabType, "- fetching now");
                        // We don't have data yet, so fetch it using the stored date/time
                        // This is a fallback case
                        updateAllPatterns(selectedDate, selectedTime, newTabType);
                    }
                });

                window.tabHandlersInitialized = true;
            }
            // Date change handler
            function setupDateChangeHandler() {
                // Only set up once
                if (window.dateHandlerInitialized) {
                    return;
                }

                console.log("Setting up date change handler");

                $('#appointmentDate').on('change', function () {
                    // Clear any existing protection interval
                    if (window.slotProtectionInterval) {
                        clearInterval(window.slotProtectionInterval);
                        window.slotProtectionInterval = null;
                    }

                    const newDate = $(this).val();
                    console.log("Date changed to:", newDate);

                    // Get current type
                    const currentType = $('#appointmentType').val();

                    // Fetch fresh data for the new date
                    console.log("Fetching fresh data for date:", newDate);
                    //updateAllPatterns(newDate, '');
                });

                window.dateHandlerInitialized = true;
            }

            // Function to initialize the modal when opened
            function initializeAppointmentModal() {
                console.log("Initializing appointment modal");

                // Reset tab to Cabinet
                //$('#apptTypeTabs a[href="#cabinet-pane"]').tab('show');
                //$('#appointmentType').val('cabinet');

                // Clear any previous state
                //$('#motif_id').val('');
                //$('#appointmentTime').val('');

                // Get current date
                //const currentDate = $("#appointmentDate").val() || moment().format('YYYY-MM-DD');
                //$("#appointmentDate").val(currentDate);

                //console.log("Initial date:", currentDate);
                //window.globalSelectedDate = currentDate;

                // Set up handlers if not already done
                //setupTabChangeHandlers();
                //setupDateChangeHandler();


            }


            $("#apptTypeTabs a").on("click", function (e) {
                e.preventDefault(); // Prevent default tab behavior
                console.log("Tab clicked:", $(this).data('type'));

                // Get the new tab type
                const newTabType = $(this).data('type');

                // Only proceed if this isn't already the active tab
                if (!$(this).hasClass('active')) {
                    // Clear any existing slot protection interval
                    if (window.slotProtectionInterval) {
                        clearInterval(window.slotProtectionInterval);
                        window.slotProtectionInterval = null;
                    }

                    // Clear the slots container immediately
                    $('#timeSlotContainer').empty();

                    // Reset time slot selection
                    $('#appointmentTime').val('');

                    // Disable save button until slot is selected
                    $('.save-btn').prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-secondary');

                    // Update appointment type in the hidden field
                    $('#appointmentType').val(newTabType);

                    // Show the tab
                    $(this).tab('show');

                    // Get the stored date and time from when user clicked the agenda
                    const selectedDate = window.globalSelectedDate;
                    const selectedTime = window.globalSelectedTime;

                    if (!selectedDate || !selectedTime) {
                        console.error("Missing date or time context!");
                        return;
                    }

                    console.log("Using stored date:", selectedDate, "time:", selectedTime);

                    // Check if we already have pattern data for this tab type
                    if (window.patternData && window.patternData[newTabType]) {
                        console.log("Using existing pattern data for type:", newTabType);

                        // We already have the data, just update the UI
                        updateUIForType(newTabType);
                    } else {
                        console.log("No pattern data for type:", newTabType, "- fetching now");

                        // We don't have data yet for this tab, fetch it specifically
                        $.ajax({
                            url: "/get-pattern-for-time-slot",
                            method: 'GET',
                            data: {
                                date: selectedDate,
                                time: selectedTime,
                                type: newTabType,
                                fetch_all_patterns: true
                            },
                            success: function (response) {
                                console.log("Pattern data for type:", newTabType, response);

                                // Store the data
                                if (!window.patternData) {
                                    window.patternData = {};
                                }
                                window.patternData[newTabType] = response;

                                // Update the UI
                                updateUIForType(newTabType);
                            },
                            error: function () {
                                console.error('Error fetching data for type:', newTabType);
                                $('#timeSlotContainer').html(
                                    '<div class="alert alert-danger">Error loading patterns for this appointment type.</div>'
                                );
                            }
                        });
                    }
                }
            });










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
                    //console.log("Unavailable Slots Response:", response);
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

                    $("#forcedAppointmentStartTime, #forcedAppointmentEndTime").off("change").on("change", function (e) {
                        let startTime = $("#forcedAppointmentStartTime").val();
                        let endTime = $("#forcedAppointmentEndTime").val();
                        console.log(startTime);
                        console.log(endTime);
                        /* if (!startTime || !endTime || startTime === "" || endTime === "") {
                            e.preventDefault(); // Now 'e' is properly defined
                            Swal.fire("Erreur", "Veuillez sélectionner une heure de début et de fin valide.", "error");
                            return;
                        } */

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

                    $("#forcedAppointmentForm").on("submit", function (e) {
                        // Add event parameter here too for consistency
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
            /**
             * Reset the pattern display when opening the modal
             */
            function resetPatternDisplay() {
                console.log("Resetting pattern display and form to initial state");

                // FIRST: Clear any existing protection interval
                if (window.slotProtectionInterval) {
                    clearInterval(window.slotProtectionInterval);
                    window.slotProtectionInterval = null;
                }

                // IMMEDIATELY clear slot container and reset selection
                $('#timeSlotContainer').empty();
                $('#appointmentTime').val('');

                // Reset the pattern display
                $('#patternDisplay').show().text('{{ trans("lang.no_pattern_selected") }}');
                $('#patternDisplay').css({
                    'background-color': '#cccccc',
                    'color': '#000000',
                    'font-weight': 'bold',
                    'font-size': '16px'
                });

                // Remove any custom dropdown and hide pattern selector
                $('.custom-pattern-selector').remove();
                $('#patternSelector').hide();

                // Reset the motif_id
                $('#motif_id').val('');

                // Reset to first tab (Cabinet) and update appointment type
                $('#apptTypeTabs a').removeClass('active');
                $('#cabinet-tab').addClass('active');
                $('.tab-pane').removeClass('active show');
                $('#cabinet-pane').addClass('active show');
                $('#appointmentType').val('cabinet');

                // Disable save button
                $('.save-btn').prop('disabled', true)
                    .removeClass('btn-primary')
                    .addClass('btn-secondary');

                // Reset patient selection if using select2
                if ($.fn.select2) {
                    $('#patientDropdown').val('').trigger('change');
                } else {
                    $('#patientDropdown').val('');
                }

                // Clear notes
                $('#notes').val('');

                // Clear any active slot button
                $('.slot-btn').removeClass('active');

                // Reset pattern data storage completely
                window.patternData = {};

                console.log("Reset complete - modal is ready for new appointment");
            }

            // Function to validate the appointment form
            function validateAppointmentForm(formElement) {
                let isValid = true;
                let errorMessage = '';

                // Determine if this is a forced appointment form or regular form
                const isForcedForm = $(formElement).attr('id') === 'forcedAppointmentForm';

                if (isForcedForm) {
                    // Validation for forced appointment form

                    // Validate patient selection
                    if (!$('#patientDropdownForced').val()) {
                        errorMessage += '{{ trans("lang.please_select_patient") }}\n';
                        isValid = false;
                    }

                    // Validate motif selection
                    if (!$('#forcedMotifDropdown').val()) {
                        errorMessage += '{{ trans("lang.please_select_pattern") }}\n';
                        isValid = false;
                    }

                    // Validate start time
                    if (!$('#forcedAppointmentStartTime').val()) {
                        errorMessage += '{{ trans("lang.please_select_start_time") }}\n';
                        isValid = false;
                    }

                    // Validate end time
                    if (!$('#forcedAppointmentEndTime').val()) {
                        errorMessage += '{{ trans("lang.please_select_end_time") }}\n';
                        isValid = false;
                    }

                    // Validate time logic (end time should be after start time)
                    const startTime = $('#forcedAppointmentStartTime').val();
                    const endTime = $('#forcedAppointmentEndTime').val();

                    if (startTime && endTime && startTime >= endTime) {
                        errorMessage += '{{ trans("lang.end_time_must_be_after_start_time") }}\n';
                        isValid = false;
                    }

                } else {
                    // Validation for regular appointment form

                    // Validate motif/pattern selection
                    if (!$('#motif_id').val()) {
                        errorMessage += '{{ trans("lang.please_select_pattern") }}\n';
                        isValid = false;
                    }

                    // Validate time selection
                    if (!$('#appointmentTime').val()) {
                        errorMessage += '{{ trans("lang.please_select_time") }}\n';
                        isValid = false;
                    }
                }

                // Display error message if validation fails
                if (!isValid) {
                    // Use SweetAlert if available (matches your existing UI)
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '{{ trans("lang.validation_error") }}',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: '{{ trans("lang.close") }}',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        // Fallback to regular alert
                        alert(errorMessage);
                    }
                }

                return isValid;
            }

            // Updated form submission handler
            $('form').on('submit', function (e) {
                console.log("Form submission triggered for:", this.id);

                // Only validate appointment-related forms
                const formId = $(this).attr('id');
                if (formId === 'forcedAppointmentForm' || $(this).find('#motif_id').length > 0) {
                    // Validate the form before submission
                    if (!validateAppointmentForm(this)) {
                        // Prevent form submission if validation fails
                        e.preventDefault();
                        return false;
                    }
                }

                // Form is valid, allow submission
                return true;
            });

            // Alternative: Separate validation functions
            function validateForcedAppointmentForm() {
                let isValid = true;
                let errorMessage = '';

                // Validate patient selection
                if (!$('#patientDropdownForced').val()) {
                    errorMessage += '{{ trans("lang.please_select_patient") }}\n';
                    isValid = false;
                }

                // Validate motif selection
                if (!$('#forcedMotifDropdown').val()) {
                    errorMessage += '{{ trans("lang.please_select_pattern") }}\n';
                    isValid = false;
                }

                // Validate start time
                if (!$('#forcedAppointmentStartTime').val()) {
                    errorMessage += '{{ trans("lang.please_select_start_time") }}\n';
                    isValid = false;
                }

                // Validate end time
                if (!$('#forcedAppointmentEndTime').val()) {
                    errorMessage += '{{ trans("lang.please_select_end_time") }}\n';
                    isValid = false;
                }

                // Display error if validation fails
                if (!isValid) {
                    Swal.fire({
                        title: '{{ trans("lang.validation_error") }}',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: '{{ trans("lang.close") }}'
                    });
                }

                return isValid;
            }

            // Specific handler for forced appointment form
            $('#forcedAppointmentForm').on('submit', function (e) {
                console.log("Forced appointment form submission triggered");

                if (!validateForcedAppointmentForm()) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            /////////////////////////////////////////////////////////////////////////////
            $('#patientDropdown').select2({
                language: currentLocale,
                allowClear: false,
                ajax: {
                    url: "/appointment-event/search",
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







            $(document).on('click', '#editAppointmentBtn', async function () {
                console.log('Update clicked');
                const selectedDate = $('#updateappointmentDate').val();
                const selectedType = $('#updateAppointmentType').val();
                const currentMotifId = $('#sidebarMotif').val();
                const currentMotifName = $('#sidebarMotif option:selected').text();
                const motifSelect = $('#sidebarMotif');
                const today = new Date().toISOString().split('T')[0];
                $('#updateappointmentDate').attr('min', today);


                // Keep patient info fields gray/disabled
                $('#sidebarPatientName, #sidebarEmail, #sidebarPhone, #sidebarStatus').prop('readonly', true)
                    .css('background-color', '#f8f9fa');

                // Make other fields editable
                $('#updateAppointmentType, #updateappointmentDate, #updatestartTime, #sidebarMotif').prop('disabled', false);
                $('#sidebarNote').prop('readonly', false);

                // Hide status buttons
                $('#markAsFailed, #markAsDone, #createTeleconsultation, #editAppointmentBtn').hide();

                // Show save button
                $('#saveAppointmentBtn').removeClass('d-none');

                motifSelect.empty();
                // Fetch time slots
                //fetchTimeSlotsForTypeForUpdateAppointment(selectedDate, selectedType);

                let actualMotifId = currentMotifId;
                if (currentMotifId === 'placeholder') {
                    // Find the actual ID by searching patternsByType
                    const typeId = getTypeIdFromType(selectedType);
                    if (patternsByType[typeId]) {
                        for (const [id, name] of Object.entries(patternsByType[typeId])) {
                            if (name.toLowerCase().trim() === currentMotifName.toLowerCase().trim()) {
                                actualMotifId = id;
                                break;
                            }
                        }
                    }
                }
                // Populate motif dropdown
                populateMotifDropdown(selectedType, currentMotifId, currentMotifName);

                // Add change handler for type
                $('#updateAppointmentType').off('change').on('change', function () {
                    console.log('Appointment type changed');
                    /* console.log('Appointment type changed');
                    const selectedType = $(this).val();

                    const timeSelect = $('#updatestartTime');
                    const dateInput = $('#updateappointmentDate');

                    // Clear date and time when type changes
                    dateInput.val('');
                    timeSelect.empty();
                    timeSelect.prop('disabled', true);
                    const motifSelect = $('#sidebarMotif');
                    motifSelect.empty();
                    // Repopulate motif dropdown
                    populateMotifDropdown(selectedType); */

                    const selectedType = $(this).val();
                    const selectedDate = $('#updateappointmentDate').val();

                    console.log("Appointment type changed to:", selectedType);

                    // Clear time and motif selections
                    $('#updatestartTime').empty().append(`<option value="">{{ trans('lang.select_date_first') }}</option>`).prop('disabled', true);
                    $('#sidebarMotif').empty().append(`<option value="">{{ trans('lang.select_time_first') }}</option>`).prop('disabled', true);

                    // If date is already selected, fetch time slots
                    if (selectedDate && selectedType) {
                        fetchTimeSlotsForTypeForUpdateAppointment(selectedDate, selectedType);
                    }
                });

                // Date change handler
                $('#updateappointmentDate').off('change').on('change', function () {
                    console.log('Date changed');
                    /* const timeSelect = $('#updatestartTime');
                    const selectedType = $('#updateAppointmentType').val();
                    const selectedDate = $(this).val();

                    timeSelect.empty();

                    if (selectedDate && selectedType) {
                        fetchTimeSlotsForTypeForUpdateAppoitment(selectedDate, selectedType);
                    } */

                    const selectedDate = $(this).val();
                    const selectedType = $('#updateAppointmentType').val();

                    console.log("Date changed to:", selectedDate);

                    // Clear time and motif selections
                    $('#updatestartTime').empty().append(`<option value="">{{ trans('lang.select_time') }}</option>`).prop('disabled', true);
                    $('#sidebarMotif').empty().append(`<option value="">{{ trans('lang.select_time_first') }}</option>`).prop('disabled', true);

                    // Fetch time slots if type is selected
                    if (selectedDate && selectedType) {
                        fetchTimeSlotsForTypeForUpdateAppointment(selectedDate, selectedType);
                    }
                });
                $('#updatestartTime').on('mousedown.update', function (event) {
                    console.log('Time dropdown mousedown - about to open');
                    const $timeSelect = $(this);
                    const selectedDate = $('#updateappointmentDate').val();
                    const selectedType = $('#updateAppointmentType').val();

                    // Only check hasRealOptions when we need it
                    if (selectedDate && selectedType) {
                        const hasRealOptions = $timeSelect.find('option').length > 1 &&
                            !$timeSelect.find('option:first').text().includes('{{ trans("lang.select_time") }}') &&
                            !$timeSelect.find('option:first').text().includes('Chargement...');

                        console.log("Time dropdown mousedown - has options:", hasRealOptions);

                        // Only load slots if we don't have real options yet
                        if (!hasRealOptions) {
                            console.log("Loading available time slots...");

                            // Prevent the dropdown from opening while loading
                            event.preventDefault();

                            // Store current selection
                            const currentValue = $timeSelect.val();

                            // Show loading state with spinner
                            $timeSelect.empty().append(`<option value=""><i class="fas fa-spinner fa-spin"></i> Chargement des créneaux...</option>`);
                            $timeSelect.prop('disabled', true);

                            // Add visual loading indicator to the select element
                            $timeSelect.addClass('loading-select');

                            // Add loading overlay if it doesn't exist
                            if (!$timeSelect.siblings('.loading-overlay').length) {
                                $timeSelect.after(`
                            <div class="loading-overlay" style="
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                background: rgba(255, 255, 255, 0.8);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                z-index: 10;
                                border-radius: 4px;
                            ">
                                <div style="display: flex; align-items: center; gap: 8px; color: #001f3f;">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Chargement...</span>
                                </div>
                            </div>
                        `);

                                // Make parent position relative if not already
                                $timeSelect.parent().css('position', 'relative');
                            }

                            // Set a flag to indicate we should open after loading
                            $timeSelect.data('shouldOpenAfterLoad', true);

                            // Load available slots
                            fetchTimeSlotsForTypeForUpdateAppointment(selectedDate, selectedType);
                        }
                    }
                });
                $('#updatestartTime').on('change', function () {
                    console.log('Time changed');
                    const selectedTime = $(this).val();
                    const selectedDate = $('#updateappointmentDate').val();
                    const selectedType = $('#updateAppointmentType').val();

                    console.log("Time changed to:", selectedTime);

                    // Clear motif selection
                    $('#sidebarMotif').empty().append(`<option value="">{{ trans('lang.loading') }}</option>`).prop('disabled', true);

                    // Fetch motifs if all required fields are selected
                    if (selectedTime && selectedDate && selectedType) {
                        fetchMotifsForTimeSlot(selectedDate, selectedTime, selectedType);
                    } else {
                        $('#sidebarMotif').empty().append(`<option value="">{{ trans('lang.select_time_first') }}</option>`);
                    }
                });



            });
            function getTypeIdFromType(type) {
                switch (type) {
                    case 'cabinet': return 1;
                    case 'teleconsultation': return 4;
                    case 'home_visit': return 3;
                    default: return 1;
                }
            }
            // Helper function to populate motif dropdown
            function populateMotifDropdown(selectedType, currentMotifId = null, currentMotifName = null) {
                const motifSelect = $('#sidebarMotif');
                motifSelect.empty();

                // Add default option
                motifSelect.append('<option value="">{{ trans(key: 'lang.select_motif') }}</option>');

                // Track added motif names to avoid duplicates
                const addedMotifNames = new Set();

                // Determine type ID
                let typeId;
                switch (selectedType) {
                    case 'cabinet':
                        typeId = 1;
                        break;
                    case 'teleconsultation':
                        typeId = 4;
                        break;
                    case 'home_visit':
                        typeId = 3;
                        break;
                    default:
                        typeId = 1;
                }

                // If we have a current motif and need to preserve it
                if (currentMotifId && currentMotifName) {
                    motifSelect.append(`<option value="${currentMotifId}">${currentMotifName}</option>`);
                    addedMotifNames.add(currentMotifName.toLowerCase().trim());
                }

                // Add options from patternsByType
                if (patternsByType[typeId]) {
                    Object.entries(patternsByType[typeId]).forEach(([id, name]) => {
                        // Skip if we already added a motif with this name
                        const normalizedName = name.toLowerCase().trim();
                        if (!addedMotifNames.has(normalizedName)) {
                            motifSelect.append(`<option value="${id}">${name}</option>`);
                            addedMotifNames.add(normalizedName);
                        }
                    });

                    // Set current value if available
                    if (currentMotifId) {
                        motifSelect.val(currentMotifId);
                    }
                } else {
                    console.log(`No patterns found for type ID ${typeId}`);
                }
            }
            $('#saveAppointmentBtn').on('click', function () {
                const $btn = $(this);

                // Sauvegarder le contenu original du bouton
                const originalContent = $btn.html();

                // Afficher le spinner et désactiver le bouton
                $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ trans("lang.loading") }}');
                $btn.prop('disabled', true);

                const type = $('#updateAppointmentType').val();
                const date = $('#updateappointmentDate').val();
                const time = $('#updatestartTime').val();
                let motif = $('#sidebarMotif').val();

                // Gestion du motif placeholder
                if (motif === 'placeholder' || motif === 'current') {
                    const motifName = $('#sidebarMotif option:selected').text().trim();

                    let typeId;
                    switch (type) {
                        case 'cabinet': typeId = 1; break;
                        case 'teleconsultation': typeId = 4; break;
                        case 'home_visit': typeId = 3; break;
                        default: typeId = 1;
                    }

                    if (patternsByType && patternsByType[typeId]) {
                        let foundId = null;
                        Object.entries(patternsByType[typeId]).forEach(([id, name]) => {
                            if (name.toLowerCase().trim() === motifName.toLowerCase().trim()) {
                                foundId = id;
                            }
                        });

                        if (foundId) {
                            motif = foundId;
                        } else {
                            toastr.error('{{ trans("lang.please_select_valid_motif") }}');
                            $btn.html(originalContent).prop('disabled', false); // 🔁 Restauration ici
                            return;
                        }
                    } else {
                        toastr.error('{{ trans("lang.motif_data_missing") }}');
                        $btn.html(originalContent).prop('disabled', false); // 🔁 Restauration ici
                        return;
                    }
                }

                let isValid = true;
                let errorMessage = '';

                // Réinitialiser les erreurs précédentes
                $('#updateAppointmentType, #updateappointmentDate, #updatestartTime, #sidebarMotif')
                    .removeClass('is-invalid')
                    .parent()
                    .find('.invalid-feedback')
                    .remove();

                // --- Validation
                if (!type) {
                    $('#updateAppointmentType').addClass('is-invalid')
                        .parent().append('<div class="invalid-feedback">{{ trans("lang.type_required") }}</div>');
                    isValid = false;
                    errorMessage = '{{ trans("lang.type_required") }}';
                }

                if (!motif) {
                    $('#sidebarMotif').addClass('is-invalid')
                        .parent().append('<div class="invalid-feedback">{{ trans("lang.motif_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.motif_required") }}';
                }

                if (!date) {
                    $('#updateappointmentDate').addClass('is-invalid')
                        .parent().append('<div class="invalid-feedback">{{ trans("lang.date_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.date_required") }}';
                }

                if (!time) {
                    $('#updatestartTime').addClass('is-invalid')
                        .parent().append('<div class="invalid-feedback">{{ trans("lang.time_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.time_required") }}';
                }

                if (!isValid) {
                    toastr.error(errorMessage);
                    $btn.html(originalContent).prop('disabled', false); // 🔁 Restauration ici
                    return;
                }

                // --- Envoi AJAX
                const id = $('#sidebarAppointmentId').val();

                $.ajax({
                    url: `/update-appointments/${id}`,
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        appointment_type: type,
                        motif_id: motif,
                        date: date,
                        start_time: time,
                        note: $('#sidebarNote').val(),
                        session_duration: updateSessionDuration
                    },
                    success: function () {
                        toastr.success('{{ __("lang.updated_successfully") }}');

                        $('#saveAppointmentBtn').addClass('d-none');
                        $('#updateAppointmentType, #updatestartTime, #sidebarMotif').prop('disabled', true);
                        $('#sidebarNote').prop('readonly', true);

                        closeSidebar();
                        $('#calendar').fullCalendar('refetchEvents');
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : '{{ __("lang.error_updating") }}';

                        toastr.error(errorMsg);
                    },
                    complete: function () {
                        // 🔁 Toujours restaurer le bouton, même après AJAX
                        $btn.html(originalContent).prop('disabled', false);
                    }
                });
            });


            $('#deleteAppointmentBtn').on('click', function () {
                const id = $('#sidebarAppointmentId').val();

                Swal.fire({
                    title: '{{ __("lang.are_you_sure") }}',
                    text: '{{ __("lang.confirm_delete") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __("lang.yes_delete") }}',
                    cancelButtonText: '{{ __("lang.cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/update-appointments/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function () {
                                toastr.success('{{ __("lang.deleted_successfully") }}');
                                closeSidebar();
                                $('#calendar').fullCalendar('refetchEvents');
                            },
                            error: function () {
                                toastr.error('{{ __("lang.error_deleting") }}');
                            }
                        });
                    }
                });
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
                allDaySlot: true,
                allDayText: '',
                eventLimit: true,
                viewRender: function (view) {
                    let viewStart = $('#calendar').fullCalendar('getView').intervalStart;
                    //console.log(`View Changed to: ${view.name}, Start Date: ${viewStart.format("YYYY-MM-DD")}`);

                    loadAvailabilitySlots(viewStart);

                    // Clear previous custom labels
                    $('.custom-day-label').remove();
                    $('.custom-number-label').remove();
                    $('.day-header-divider').remove();

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
                                    $(this).append(`

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <hr class="day-header-divider">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="custom-day-label substitute-hover">${substituteName}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <hr class="day-header-divider">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="custom-number-label">${staticNumber}</div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `);

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
                        const start = moment(vacation.start_date, 'YYYY-MM-DD').startOf('day');
                        const end = moment(vacation.end_date, 'YYYY-MM-DD').endOf('day');
                        return currentDay.isSameOrAfter(start) && currentDay.isSameOrBefore(end);
                    });
                    const isUrgentDay = urgencies.some(urgency => {
                        const urgencyDate = moment(urgency.jour, 'YYYY-MM-DD');
                        //console.log(`Comparing ${urgencyDate.format('YYYY-MM-DD')} === ${currentDay.format('YYYY-MM-DD')}`);
                        return currentDay.isSame(urgencyDate, 'day');
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
                        //console.log("Rendering urgent UI for:", formattedDate);
                        cell.addClass('cell-with-background');
                        cell.css('cursor', 'not-allowed');
                        cell.css('background-color', '#f2f2f2');
                        cell.attr('title', 'Le docteur est en vacances ce jour.');
                    }
                    else if (normalizedAvailabilityDays.includes(formattedDayName)) {
                        // 3) Available day
                        //console.log("Marking as available day:", date.format("YYYY-MM-DD"));
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
                    //console.log("Selected Date:", selectedDate);
                    //console.log("Selected Time:", selectedTime);
                    globalSelectedDate = start.format("YYYY-MM-DD");
                    globalSelectedTime = start.format("HH:mm");
                    const currentDay = start.clone().startOf('day');

                    const today = moment().format("YYYY-MM-DD");

                    // Check if the selected date is before today's date
                    if (moment(selectedDate).isBefore(today)) {
                        $('#pastDateModal').modal('show');
                        $('#pastDateModalMessage').text("Vous avez sélectionné une date antérieure. Veuillez sélectionner une date future.");
                        return;
                    }

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
                    console
                    // When user selects a time slot on the agenda
                    if (view.name === "agendaWeek" || view.name === "agendaDay") {
                        checkAvailability(selectedDate, selectedTime).then((isAvailable) => {
                            if (!isAvailable) return; // Stop if no availability

                            console.log("Selected agenda slot:", selectedDate, selectedTime);

                            // Set the date in the date picker
                            $("#appointmentDate").val(selectedDate);

                            // Reset modal state
                            resetPatternDisplay();

                            // Initialize tab handlers if not already done
                            setupTabChangeHandlers();

                            // Update patterns with selected date/time and default to cabinet tab
                            updateAllPatterns(selectedDate, selectedTime, 'cabinet');

                            // Show the modal
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

                    const hasUpdateStatusPermission = {{ auth()->user()->hasPermissionInContext('updateStatus', $doctorId) ? 'true' : 'false' }};
                    const appointment = {
                        appointment_id: event.id,
                        patient_name: event.patient_name,
                        patient_first_name: event.patient_first_name,
                        patient_last_name: event.patient_last_name,
                        email: event.email,
                        patient_id: event.patient_id,
                        status: event.status,
                        motif_name: event.motif_name,
                        note: event.note,
                        start: event.start.format(),
                        phone: event.patient_phone_number,
                        type: event.online
                    };

                    const startDateTime = new Date(event.start.format());
                    const startDate = startDateTime.toISOString().split('T')[0];
                    const startTime = startDateTime.toTimeString().slice(0, 5);

                    // Populate fields
                    $('#sidebarPatientName').val(appointment.patient_name).prop('readonly', true);
                    $('#sidebarEmail').val(appointment.email).prop('readonly', true);
                    $('#sidebarPhone').val(appointment.phone).prop('readonly', true);
                    $('#sidebarStatus').val(appointment.status).prop('readonly', true);
                    $('#sidebarNote').val(appointment.note || '').prop('readonly', true);
                    $('#sidebarAppointmentId').val(event.id);

                    // Get references to the buttons
                    const doneButton = document.getElementById('markAsDone');
                    const failedButton = document.getElementById('markAsFailed');

                    // Always hide save button initially
                    $('#saveAppointmentBtn').addClass('d-none');

                    if (hasUpdateStatusPermission) {
                        // Check appointment type and status
                        const appointmentType = appointment.type;
                        const status = appointment.status;
                        $('#createTeleconsultation').hide();

                        // Reset visibility
                        $(doneButton).show();
                        $(failedButton).show();
                        $('#editAppointmentBtn').show();

                        // Handle status-specific visibility
                        if (status === "Annulé" || status === 7) {
                            // Hide action buttons for canceled appointments
                            $(doneButton).hide();
                            $(failedButton).hide();
                            $('#editAppointmentBtn').hide();
                        }
                        else if (status === "Terminé" || status === 5) {
                            // Hide action buttons for completed appointments
                            $(doneButton).hide();
                            $(failedButton).hide();
                        }
                        else if (status === "Prêt" || status === 3) {
                            // Hide ready button for ready appointments
                            $(doneButton).hide();
                        }

                        // Special handling for teleconsultation
                        if (appointmentType && appointmentType.toLowerCase() === "teleconsultation") {
                            console.log("Teleconsultation appointment detected.");

                            // Only show teleconsultation button if the appointment is not completed or canceled
                            if (status !== "Terminé" && status !== "Annulé" && status !== 5 && status !== 7) {
                                $(doneButton).hide();
                                $('#createTeleconsultation').show();
                                $('#createTeleconsultation').off('click').on('click', function () {
                                    const url = `{{ route('show.meeting.info.form') }}?patient_name=${encodeURIComponent(appointment.patient_first_name)}&appointment_id=${encodeURIComponent(appointment.appointment_id)}&phone=${encodeURIComponent(appointment.phone)}&motif_name=${encodeURIComponent(appointment.motif_name)}&patient_id=${encodeURIComponent(appointment.patient_id)}&start_at=${encodeURIComponent(appointment.start)}&patient_first_name=${encodeURIComponent(appointment.patient_first_name)}&patient_last_name=${encodeURIComponent(appointment.patient_last_name)}&patient_Email=${encodeURIComponent(appointment.email)}`;
                                    window.location.href = url;
                                });
                            } else {
                                // Status is completed or canceled, don't show teleconsultation button
                                $('#createTeleconsultation').hide();
                            }
                        }
                    } else {
                        // No permission - hide all action buttons
                        $(doneButton).hide();
                        $(failedButton).hide();
                        $('#createTeleconsultation').hide();
                        $('#editAppointmentBtn').hide();
                    }

                    // Set appointment type, date, and time
                    $('#updateAppointmentType').val(appointment.type);
                    $('#updateappointmentDate').val(startDate);

                    // Set motif
                    const motifSelect = $('#sidebarMotif');
                    motifSelect.empty();
                    if (event.motif_id) {
                        motifSelect.append(`<option value="${event.motif_id}">${appointment.motif_name}</option>`);
                        motifSelect.val(event.motif_id);
                    } else {
                        // Temporarily add with placeholder value
                        motifSelect.append(`<option value="placeholder">${appointment.motif_name}</option>`);
                        motifSelect.val("placeholder");

                        // Store the motif name for later use
                        motifSelect.data('motif-name', appointment.motif_name);
                    }
                    motifSelect.prop('disabled', true);

                    // Set time
                    const $timeSelect = $('#updatestartTime');
                    $timeSelect.empty();
                    $timeSelect.append(`<option value="${startTime}">${startTime}</option>`);
                    $timeSelect.val(startTime);
                    $timeSelect.prop('disabled', true);

                    // Open sidebar
                    $('#appointmentSidebar').css('right', '0');
                    $('#sidebarOverlay').show();

                    // Event handlers for status buttons
                    $('#markAsFailed').off('click').on('click', function () {
                        selectedAppointmentId = event.id;
                        $('#cancelReason').val('');
                        $('#cancelAppointmentModal').modal('show');
                        closeSidebar(); // Close the sidebar after confirming

                    });

                    let currentAppointment = null;
                    $('#markAsDone').off('click').on('click', function () {
                        currentAppointment = appointment;

                        // Fermer les autres modals actifs
                        $('.modal').modal('hide'); // ça ferme tous les modals ouverts

                        // Attendre un peu avant d’ouvrir celui-ci (laisser le temps de fermer l’autre)
                        setTimeout(() => {
                            $('#confirmDoneModal').modal('show');
                        }, 300);

                        closeSidebar(); // si nécessaire
                    });


                    $('#confirmDoneButton').off('click').on('click', function () {
                        if (currentAppointment) {
                            // 1. Mise à jour du statut à 6 (Done)
                            updateAppointmentStatus(currentAppointment.appointment_id, 6, "Done");

                            // 2. Redirection vers la création de consultation
                            setTimeout(() => {
                                const url = `{{ route('consultations.create', ['patient_id' => '__PATIENT_ID__']) }}`.replace('__PATIENT_ID__', encodeURIComponent(currentAppointment.patient_id));
                                window.location.href = url;
                            }, 500);
                        }

                        $('#confirmDoneModal').modal('hide'); // fermer le modal
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
                console.log('saveAppointmentRef clicked');
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

                //console.log("Selected Day:", dayOfWeek); // Output the day for debugging
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

                //console.log("Appointment Data to be Sent:", appointmentData);

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
                            //console.log("Validation Errors:", errors);
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
                        $('#appointmentDetailsModal').modal('hide'); // Close the modal
                        $('#calendar').fullCalendar('refetchEvents'); // Refresh the calendar
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON.error || "An error occurred while updating the status.");
                    }
                });
            }
            $('#saveAppointmentPass').click(function () {
                consolelog("Save Appointment Pass clicked");
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
                        const url = `{{ route('show.meeting.info.form') }}?patient_name=${encodeURIComponent(patient_name)}&appointment_id=${encodeURIComponent(appointment_id)}&phone=${encodeURIComponent(phone)}&motif_name=${encodeURIComponent(motif_name)}&patient_id=${encodeURIComponent(patient_id)}&start_at=${encodeURIComponent(start)}&patient_first_name=${encodeURIComponent(patient_first_name)}&patient_last_name=${encodeURIComponent(patient_last_name)}&patient_Email=${encodeURIComponent(email)}`;
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
            $('#forcedApptTypeTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                const selectedType = $(e.target).data('type');
                $('#forcedAppointmentType').val(selectedType);
                console.log('[DEBUG] Switched to type:', selectedType); // Check in dev tools
            });
            $('#forcedAppointmentForm').on('submit', function (e) {
                e.preventDefault();

                const $btn = $(this).find('button[type="submit"]');
                const originalHtml = $btn.html();

                // Prevent double submission
                if ($btn.prop('disabled')) return;

                // Set loading state
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#forcedAppointmentModal').modal('hide');
                        Swal.fire('Succès', response.message, 'success').then(() => location.reload());
                    },
                    error: function (xhr) {
                        const errorMessage = xhr.responseJSON?.errors
                            ? Object.values(xhr.responseJSON.errors)[0]
                            : 'Veuillez vérifier le formulaire et réessayer';
                        Swal.fire('Erreur', errorMessage, 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endpush