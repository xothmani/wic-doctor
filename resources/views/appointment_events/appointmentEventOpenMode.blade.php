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
        <!-- Modal for Viewing and Updating Status -->
        <!-- Modal for Creating Appointment -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="appointmentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">{{ trans('lang.create_modal_name') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <label for="patientDropdown" class="form-label">{{ trans('lang.Select_Patient') }}</label>
                                        <div class="d-flex align-items-center">
                                            <select id="patientDropdown" placeholder="{{ trans('lang.Select_Patient') }}" required
                                                class="form-select" style="flex-grow: 1;">
                                                <option value="">{{ trans('lang.Select_Patient') }}</option>
                                            </select>
                                            <a href="{{ route('patients.create') }}" class="btn btn-success d-flex ms-2"
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
        <!-- Modal for Cancel Reason -->



        <!-- Sidebar for Appointment Details -->
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
                        <label>{{ trans('lang.motif') }}</label>
                        <select id="sidebarMotif" class="form-control">
                            <!-- Options will be populated dynamically based on selected type -->
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

        <!-- Cancellation Reason Modal -->
        <div class="modal fade" id="cancelAppointmentModal" tabindex="-1" role="dialog"
            aria-labelledby="cancelAppointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="cancelAppointmentModalLabel"><i class="fas fa-times-circle"></i> Annuler le
                            rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                    <div class="modal-body">
                        <label for="cancelReason">Veuillez préciser la raison de l'annulation (facultatif) :</label>
                        <textarea class="form-control" id="cancelReason"
                            placeholder="Exemple : Le patient ne s'est pas présenté"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="confirmCancelAppointment">Confirmer
                            l'annulation</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="clearfix"></div>
            @include('flash::message')

            <div class="row">

                <!-- Left Sidebar Column (20% width) -->
                <div class="col-lg-2 col-md-12">
                    <!-- Calendar Toggle Button (hidden by default) -->
                    <button id="calendar-toggle" class="btn btn-sm btn-outline-secondary d-none mb-2">
                        <span>
                            <i class="fas fa-calendar-alt"></i> Calendrier
                        </span>
                        <i class="fas fa-chevron-down toggle-arrow"></i>
                    </button>


                    <!-- Mini Calendar Card -->
                    <div class="card shadow-sm mb-2" id="mini-calendar-card">
                        <div class="card-body p-1">
                            <div id="inline-datepicker" class="compact-datepicker"></div>
                            <input type="hidden" id="selected-date">
                        </div>
                    </div>

                    <!-- Color Filters Card - More Compact -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Filtrer par statut</h5>
                            <div class="color-filters">
                                <div class="filter-item" data-status="Accepté">
                                    <div class="color-box" style="background-color: #78C2AD;"></div>
                                    <div class="filter-label">Accepté</div>
                                </div>
                                <div class="filter-item" data-status="Terminé">
                                    <div class="color-box" style="background-color: #56B4D3;"></div>
                                    <div class="filter-label">Terminé</div>
                                </div>
                                <!--  <div class="filter-item" data-status="Prêt">
                                            <div class="color-box" style="background-color: #90D26D;"></div>
                                            <div class="filter-label">Prêt</div>
                                        </div> -->
                                <div class="filter-item" data-status="En cours">
                                    <div class="color-box" style="background-color: #F3D55B;"></div>
                                    <div class="filter-label">En cours</div>
                                </div>
                                <div class="filter-item" data-status="Annulé">
                                    <div class="color-box" style="background-color: #FF7851;"></div>
                                    <div class="filter-label">Annulé</div>
                                </div>
                                <div class="filter-item" data-status="Reçu">
                                    <div class="color-box" style="background-color: #BC8CDF;"></div>
                                    <div class="filter-label">Reçu</div>
                                </div>
                                <div class="filter-item active" data-status="all">
                                    <div class="color-box" style="background-color: #E9ECEF;"></div>
                                    <div class="filter-label">Tous</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Légende disponibilité</h5>
                            <div class="legend-container">
                                <div class="legend-row">
                                    <span class="legend-dot" style="background-color: #28a745;"></span>
                                    <span class="legend-text">Disponible</span>
                                </div>
                                <div class="legend-row">
                                    <span class="legend-dot" style="background-color: #dc3545;"></span>
                                    <span class="legend-text">Non disponible</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Main Column (80% width) - Expanded -->
                <div class="col-lg-10 col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <div id="calendar-container" class="expanded-calendar">
                                <!-- Slot Duration Dropdown -->
                                <div class="d-flex justify-content-end mb-2">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            id="slotDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="slotDropdownBtn">
                                            <li><a class="dropdown-item slot-option" data-slot="00:15:00" href="#">15
                                                    minutes</a></li>
                                            <li><a class="dropdown-item slot-option" data-slot="00:30:00" href="#">30
                                                    minutes</a></li>
                                        </ul>
                                    </div>
                                </div>

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

    <div class="modal fade" id="confirmDoneModal" tabindex="-1" role="dialog" aria-labelledby="confirmDoneModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDoneModalLabel">{{ trans('lang.confirmation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <div class="modal-body">
                    {{ trans('lang.do_you_want_to_end_appointment_and_start_consultation') }}
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-success"
                        id="confirmDoneButton">{{ trans('lang.confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Bootstrap 5 CSS (single version only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <!-- Tempus Dominus CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/eventcustom.css') }}">

    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">

    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@push('scripts')
    <!-- jQuery first! -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <!-- Popper.js (required for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

    <!-- Only ONE Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>

    <!-- Moment.js (load once) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/locale/fr.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <!-- Tempus Dominus JS -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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
        let availableDays = [];
        let availabilityDays = @json($availabilityDays);
        let vacations = @json($vacations);
        let urgencies = @json($urgencies);
        let sidebarappointmentId = $('#sidebarAppointmentId').val();
        let updateSessionDuration = 0;
        const patternsByType = @json($patternsByType);
        let calendar;
        let existingEventIds = []; // Array to store existing event IDs
        let slotDuration = '00:15:00';
        let labelInterval = '00:30:00'; // default
        let selectedStatuses = ['all'];
        let allCalendarEvents = [];



        //console.log("🩺 Urgencies loaded:", urgencies);
        window.activeDoctorId = {{ $doctorId ?? 'null' }};

        $(document).ready(function () {
            //

            setTimeout(function () {
                allCalendarEvents = $('#calendar').fullCalendar('clientEvents').slice();
                console.log(`Stored ${allCalendarEvents.length} total events for filtering`);
            }, 1000);

            fetchAvailableDaysAndInitializeCalendar();

            $('.slot-option').on('click', function (e) {
                e.preventDefault();
                slotDuration = $(this).data('slot');
                labelInterval = (slotDuration === '00:30:00') ? '01:00:00' : '00:30:00';
                $('#calendar').fullCalendar('destroy');
                initializeCalendar();
            });

            $('#inline-datepicker').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                autoclose: true,
                inline: true,
                language: 'fr',
                weekStart: 1
            }).on('changeDate', function (e) {
                var selectedDate = e.format(0, "yyyy-mm-dd");
                $('#selected-date').val(selectedDate);
                $('#calendar').fullCalendar('changeView', 'agendaDay');
                $('#calendar').fullCalendar('gotoDate', selectedDate);
            });

            // Toggle calendar visibility
            $('#calendar-toggle').click(function () {
                $('#mini-calendar-card').toggleClass('d-none');
            });

            // Check screen size and toggle elements
            function checkScreenSize() {
                if (window.matchMedia("(max-width: 1372px) and (max-height: 845px)").matches) {
                    $('#calendar-toggle').removeClass('d-none');
                    $('#mini-calendar-card').addClass('d-none');
                } else {
                    $('#calendar-toggle').addClass('d-none');
                    $('#mini-calendar-card').removeClass('d-none');
                }
            }

            // Run on load and resize
            checkScreenSize();
            $(window).resize(checkScreenSize);
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
            function populateTimeOptions() {
                const timeSelect = $('#updatestartTime');
                timeSelect.empty(); // Clear existing options

                // Add placeholder
                timeSelect.append('<option value="">{{ trans(key: 'lang.select_time') }}</option>');

                // Add time options in 30-minute increments (adjust as needed)
                for (let hour = 8; hour < 18; hour++) {
                    for (let min = 0; min < 60; min += 30) {
                        const timeValue = `${hour.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`;
                        timeSelect.append(`<option value="${timeValue}">${timeValue}</option>`);
                    }
                }
            }
            function fetchTimeSlotsForTypeForUpdateAppoitment(date, type) {
                console.log("Fetching time slots for update appointment:", date, type);

                $.ajax({
                    url: "/get-available-time-slots-open",
                    type: "GET",
                    data: { date: date, type: type },
                    success: function (response) {
                        console.log("Fetched Time Slots Response:", response);
                        updateSessionDuration = response.session_duration;

                        const $timeSelect = $('#updatestartTime');

                        if (response.vacation) {
                            Swal.fire({
                                title: "Le docteur est en vacances",
                                text: "Aucune disponibilité n'est possible ce jour.",
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                            $timeSelect.append(`<option value="">Aucun créneau (vacances)</option>`);
                            return;
                        }

                        if (!response.all_slots || response.all_slots.length === 0) {
                            $timeSelect.append(`<option value="">Aucun créneau disponible</option>`);
                            return;
                        }

                        // Re-enable the time select
                        $timeSelect.prop('disabled', false);
                        $timeSelect.append(`<option value="">{{ trans(key: 'lang.select_time') }}</option>`);

                        const taken = new Set(response.taken_slots);

                        // Append available slots (excluding taken ones)
                        response.all_slots.forEach(slot => {
                            if (!taken.has(slot)) {
                                $timeSelect.append(`<option value="${slot}">${slot}</option>`);
                            }
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: "Erreur",
                            text: "Une erreur s'est produite lors de la récupération des créneaux horaires. Veuillez réessayer.",
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }
            // Define time slots (adjust based on your requirements)
            function updateAppointment() {
                const appointmentId = currentAppointmentId; // set this when opening the sidebar
                const data = {
                    appointment_type: $('#updateAppointmentType').val(),
                    appointment_date: $('#updateappointmentDate').val(),
                    appointment_time: $('#updatestartTime').val(),
                    appointment_notes: $('#sidebarNote').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: `/update-appointments/${appointmentId}`,
                    type: 'PUT',
                    data: data,
                    success: function (response) {
                        toastr.success(trans('lang.updated_successfully'));
                        closeSidebar(); // function to hide the sidebar
                        // Optionally refresh calendar
                    },
                    error: function (xhr) {
                        toastr.error(trans('lang.error_updating'));
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

            function resetPatientSelection() {
                // Get reference to the TomSelect instance
                const tomSelectInstance = document.querySelector('#patientDropdown').tomselect;

                // If TomSelect instance exists, clear it
                if (tomSelectInstance) {
                    tomSelectInstance.clear();
                } else {
                    // Fallback to standard select reset
                    $('#patientDropdown').val('');
                }
            }




            $(document).ready(function () {
                console.log("jQuery document.ready fired");
                try {
                    const ts = new TomSelect('#patientDropdown', {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: 'text',
                        placeholder: "{{ trans('lang.Select_Patient') }}",
                        create: false,
                        preload: true,  // Load options right away
                        openOnFocus: true,  // Open dropdown when field gets focus

                        load: function (query, callback) {
                            $.ajax({
                                url: "{{ route('patients.search') }}",
                                type: 'GET',
                                data: { q: query },
                                dataType: 'json',
                                error: function () {
                                    callback();
                                },
                                success: function (res) {
                                    callback(res);
                                }
                            });
                        },

                        render: {
                            option: function (item, escape) {
                                return `<div>
                                                                <div class="font-weight-bold">${escape(item.text)}</div>
                                                                ${item.phone ? `<div class="text-muted small">${escape(item.phone)}</div>` : ''}
                                                            </div>`;
                            },
                            item: function (item, escape) {
                                return `<div>${escape(item.text)}</div>`;
                            },
                            no_results: function () {
                                return `<div class="no-results">{{ trans('lang.No_patients_found') }}</div>`;
                            }
                        }
                    });

                    // Force placeholder to show
                    $('.ts-control').attr('placeholder', "{{ trans('lang.Select_Patient') }}");

                    console.log("Tom Select initialized successfully");
                } catch (error) {
                    console.error("Error initializing Tom Select:", error);
                }
            });
            ////////////////////////////////////////////////#
            // "Update" button logic
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
                fetchTimeSlotsForTypeForUpdateAppoitment(selectedDate, selectedType);

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
                    populateMotifDropdown(selectedType);
                });

                // Date change handler
                $('#updateappointmentDate').off('change').on('change', function () {
                    const timeSelect = $('#updatestartTime');
                    const selectedType = $('#updateAppointmentType').val();
                    const selectedDate = $(this).val();

                    timeSelect.empty();

                    if (selectedDate && selectedType) {
                        fetchTimeSlotsForTypeForUpdateAppoitment(selectedDate, selectedType);
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

    // Save original button content to restore later
    const originalContent = $btn.html();

    // Show loading indicator and disable button
    $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ trans("lang.loading") }}');
    $btn.prop('disabled', true);
                // Validate required fields
                const type = $('#updateAppointmentType').val();
                const date = $('#updateappointmentDate').val();
                const time = $('#updatestartTime').val();
                let motif = $('#sidebarMotif').val();
                if (motif === 'placeholder' || motif === 'current') {
                    // Try to find the actual motif ID from the available options
                    const motifName = $('#sidebarMotif option:selected').text().trim();
                    console.log("Looking for motif name:", motifName);

                    // Determine type ID based on selected type
                    let typeId;
                    switch (type) {
                        case 'cabinet': typeId = 1; break;
                        case 'teleconsultation': typeId = 4; break;
                        case 'home_visit': typeId = 3; break;
                        default: typeId = 1;
                    }

                    // Look for matching motif in patternsByType
                    if (patternsByType && patternsByType[typeId]) {
                        let foundId = null;
                        Object.entries(patternsByType[typeId]).forEach(([id, name]) => {
                            if (name.toLowerCase().trim() === motifName.toLowerCase().trim()) {
                                foundId = id;
                                console.log("Found matching motif ID:", id);
                            }
                        });

                        if (foundId) {
                            motif = foundId; // Use the found ID
                        } else {
                            // Still couldn't find a valid ID
                            toastr.error('{{ trans("lang.please_select_valid_motif") }}');
                            return;
                        }
                    } else {
                        toastr.error('{{ trans("lang.motif_data_missing") }}');
                        return;
                    }
                }
                let isValid = true;
                let errorMessage = '';

                // Reset previous error styling
                $('#updateAppointmentType, #updateappointmentDate, #updatestartTime, #sidebarMotif')
                    .removeClass('is-invalid')
                    .parent()
                    .find('.invalid-feedback')
                    .remove();

                if (!type) {
                    $('#updateAppointmentType').addClass('is-invalid');
                    $('#updateAppointmentType').parent().append('<div class="invalid-feedback">{{ trans("lang.type_required") }}</div>');
                    isValid = false;
                    errorMessage = '{{ trans("lang.type_required") }}';
                }

                if (!motif) {
                    $('#sidebarMotif').addClass('is-invalid');
                    $('#sidebarMotif').parent().append('<div class="invalid-feedback">{{ trans("lang.motif_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.motif_required") }}';
                }

                if (!date) {
                    $('#updateappointmentDate').addClass('is-invalid');
                    $('#updateappointmentDate').parent().append('<div class="invalid-feedback">{{ trans("lang.date_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.date_required") }}';
                }

                if (!time) {
                    $('#updatestartTime').addClass('is-invalid');
                    $('#updatestartTime').parent().append('<div class="invalid-feedback">{{ trans("lang.time_required") }}</div>');
                    isValid = false;
                    errorMessage = errorMessage || '{{ trans("lang.time_required") }}';
                }

                if (!isValid) {
                    toastr.error(errorMessage);
                    return;
                }

                // All validation passed, proceed with update
                const id = $('#sidebarAppointmentId').val();
                console.log('motif:', motif);
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

                        // Return to view mode
                        $('#saveAppointmentBtn').addClass('d-none');
                        $('#updateAppointmentType, #updatestartTime, #sidebarMotif').prop('disabled', true);
                        $('#sidebarNote').prop('readonly', true);

                        // Refresh calendar and close sidebar
                        closeSidebar();
                        $('#calendar').fullCalendar('refetchEvents');
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : '{{ __("lang.error_updating") }}';

                        toastr.error(errorMsg);
                    },complete: function () {
            // Always restore the button
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

            function makePastel(hex, alpha = 0.3) {
                //console.log("Making pastel color:", hex);
                const rgb = hexToRgb(hex);
                return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
            }

            // Converts #rrggbb to {r, g, b}
            function hexToRgb(hex) {
                hex = hex.replace(/^#/, '');
                if (hex.length === 3) {
                    hex = hex.split('').map(x => x + x).join('');
                }
                const bigint = parseInt(hex, 16);
                return {
                    r: (bigint >> 16) & 255,
                    g: (bigint >> 8) & 255,
                    b: bigint & 255
                };
            }
            function applyStatusFilter() {
                console.log('Filtering events based on statuses:', selectedStatuses);

                // Start fresh - get all events from our stored copy
                $('#calendar').fullCalendar('removeEvents');

                if (selectedStatuses.includes('all')) {
                    // Show all events
                    allCalendarEvents.forEach(event => {
                        $('#calendar').fullCalendar('renderEvent', Object.assign({}, event), true);
                    });
                    console.log('Showing all events');
                    return;
                }

                // Filter by selected statuses
                let visibleCount = 0;
                allCalendarEvents.forEach(event => {
                    if (selectedStatuses.includes(event.status)) {
                        $('#calendar').fullCalendar('renderEvent', Object.assign({}, event), true);
                        visibleCount++;
                    }
                });

                console.log(`Showing ${visibleCount} out of ${allCalendarEvents.length} events`);
            }

            // Update the click handler for filter items
            $('.filter-item').on('click', function () {
                const status = $(this).data('status');
                console.log('Clicked on status:', status);

                if (status === 'all') {
                    // Handle 'all' selection
                    if ($(this).hasClass('active')) {
                        // Deselect all
                        $('.filter-item').removeClass('active');
                        selectedStatuses = [];
                        console.log('Deselected all statuses');
                    } else {
                        // Select all
                        $('.filter-item').addClass('active');
                        selectedStatuses = ['all'];
                        console.log('Selected all statuses');
                    }
                } else {
                    // Handle individual status selection
                    if ($(this).hasClass('active')) {
                        // Deselect this status
                        $(this).removeClass('active');
                        selectedStatuses = selectedStatuses.filter(s => s !== status);
                        console.log('Deselected status:', status);
                    } else {
                        // Select this status
                        $(this).addClass('active');

                        // If 'all' was selected, deselect it
                        if (selectedStatuses.includes('all')) {
                            $('.filter-item[data-status="all"]').removeClass('active');
                            selectedStatuses = [status]; // Start fresh with just this status
                        } else {
                            // Add to existing selections
                            selectedStatuses.push(status);
                        }
                        console.log('Selected status:', status);
                    }

                    // If nothing is selected, default to 'all'
                    if (selectedStatuses.length === 0) {
                        $('.filter-item[data-status="all"]').addClass('active');
                        selectedStatuses = ['all'];
                        console.log('Defaulting to "all" since nothing was selected');
                    }
                }

                console.log('Currently selected:', selectedStatuses);

                // Refresh the calendar to apply the filter
                $('#calendar').fullCalendar('refetchEvents');
            });



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
                    firstDay: moment().day(),
                    minTime: "08:00:00",
                    allDaySlot: true,
                    allDayText: '',
                    events: '/appointment-event',
                    eventLimit: true,
                    slotDuration: slotDuration,
                    slotLabelInterval: labelInterval,
                    slotLabelFormat: 'HH:mm',
                    viewRender: function (view) {
                        //console.log('Calendar view changed:', view);

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
                                            color = '#78C2AD';
                                            break;
                                        case 'Terminé':
                                            color = '#56B4D3';
                                            break;
                                        case 'En cours':
                                            color = '#F3D55B';
                                            break;
                                        case 'Annulé':
                                            color = '#FF7851';
                                            break;
                                        case 'Reçu':
                                            color = '#BC8CDF';
                                            break;
                                        case 'Prêt':
                                            color = '#90D26D';
                                            break;
                                        default:
                                            color = '#B4BAFF    ';
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
                                        // Remove backgroundColor and borderColor properties
                                        description: `Patient: ${event.patient_name}\nStatus: ${translatedStatus}\nDetails: ${event.motif_name || 'N/A'}`,
                                        patient_name: event.patient_name,
                                        status: translatedStatus,
                                        details: event.motif_name || 'N/A',
                                        motif_name: event.motif_name,
                                        appointment_type: event.type,
                                        note: event.note,
                                        motif_color: event.backgroundColor,
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
                        if (!selectedStatuses.includes('all') && !selectedStatuses.includes(event.status)) {
                            return false; // Skip rendering this event
                        }
                        // Add a custom data-id attribute to the event element
                        element.attr('data-id', event.id);

                        // Get the status color
                        let color = '';
                        switch (event.status) {
                            case 'Accepté':
                                color = '#78C2AD'; // Use your new colors here
                                break;
                            case 'Terminé':
                                color = '#56B4D3';
                                break;
                            case 'En cours':
                                color = '#F3D55B';
                                break;
                            case 'Annulé':
                                color = '#FF7851';
                                break;
                            case 'Reçu':
                                color = '#BC8CDF';
                                break;
                            case 'Prêt':
                                color = '#90D26D';
                                break;
                            default:
                                color = '#B4BAFF';
                        }

                        // Update the element style to match the image
                        // Generate a light translucent version of the status color
                        //console.log("color:", color);
                        //console.log("event.motif_color:", event.motif_color);
                        const statusColor = color;
                        const motifPastel = makePastel(event.motif_color || '#ffffff', 0.3);
                        //console.log("motifPastel:", motifPastel);
                        //console.log("statusColor:", statusColor);
                        element.css({
                            'background-color': motifPastel,
                            'border': '1px solid #e0e0e0',
                            'border-left': `6px solid ${statusColor}`,
                            'box-shadow': '0 1px 3px rgba(0,0,0,0.05)',
                            'border-radius': '2px',
                            'margin-bottom': '2px',
                        });
                        // Determine the icon based on the `online` field
                        let icon;
                        switch (event.online) {
                            case 'cabinet':
                                icon = '<i class="fas fa-briefcase-medical" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            case 'teleconsultation':
                                icon = '<i class="fas fa-video" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            case 'home_visit':
                                icon = '<i class="fas fa-home" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            case 'web':
                                icon = '<i class="fas fa-globe" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            case 'mobile':
                                icon = '<i class="fas fa-mobile-alt" style="margin-right: 5px; color: #1A1A1D;"></i>';
                                break;
                            default:
                                icon = '<i class="fas fa-question-circle" style="margin-right: 5px; color: #ccc;"></i>';
                                break;
                        }

                        // Add checkmark icon for completed events
                        if (event.status === 'Terminé') {
                            icon = '<i class="fas fa-check-circle" style="margin-right: 5px; color: #56B4D3;"></i>' + icon;
                        }

                        // Add icon to the title
                        element.find('.fc-title').prepend(icon);

                        // Set tooltip
                        element.attr('title', event.description);

                        // Set text styles
                        element.find('.fc-title').css({
                            'white-space': 'nowrap',
                            'overflow': 'hidden',
                            'text-overflow': 'ellipsis',
                            'font-size': '0.9em',
                            'font-weight': '500',
                            'color': '#333333'
                        });

                        element.find('.fc-time').css({
                            'font-size': '0.85em',
                            'font-weight': 'bold',
                            'color': '#555555'
                        });

                        // Highlight new events if needed
                        if (event.isNew) {
                            element.addClass('highlight-event');
                            setTimeout(() => {
                                element.removeClass('highlight-event');
                                event.isNew = false;
                            }, 2000);
                        }
                    },
                    selectable: true,
                    selectAllow: function (selectInfo) {
                        // Only allow selection if the day is in availabilityDays
                        //return availabilityDays.includes(selectInfo.start.format("YYYY-MM-DD"));

                    },
                    selectHelper: true,
                    select: function (start, end) {
                        const selectedDate = start.format("YYYY-MM-DD");
                        console.log("Selected Date:", selectedDate);
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
                                console.log("Selected Date:", selectedDate);
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
                                                    resetPatientSelection();
                                                    $('#appointmentModal').modal('show');
                                                    $(`#appointmentTypeTabs a[data-type="cabinet"]`).tab('show');
                                                    fetchTimeSlotsForType(selectedDate, 'cabinet');
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
            }


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


            $('#saveAppointment').on('click', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const originalContent = $btn.html();

    // Affiche le spinner et désactive le bouton
    $btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("lang.loading") }}');
    $btn.prop('disabled', true);

    const activeTab = $('#appointmentTypeTabs .nav-link.active');
    const appointmentType = activeTab.data('type');
    const patternSelectId = '#patern_id_' + appointmentType;

    const appointmentData = {
        patient_id: $('#patientDropdown').val(),
        appointment_date: $('#appointmentDate').val(),
        appointment_time: $('#appointment_time').val(),
        patern_id: $(patternSelectId).val(),
        appointment_type: appointmentType,
        notes: $('#appointment_notes').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // --- Validation : Patient
    if (!appointmentData.patient_id) {
        Swal.fire({
            title: "Erreur",
            text: "Veuillez sélectionner un patient",
            icon: "error"
        });
        $btn.html(originalContent).prop('disabled', false); // RESTAURE LE BOUTON
        return;
    }

    // --- Validation : Heure
    if (!appointmentData.appointment_time) {
        Swal.fire({
            title: "Erreur",
            text: "Veuillez sélectionner une heure de rendez-vous",
            icon: "error"
        });
        $btn.html(originalContent).prop('disabled', false);
        return;
    }

    // --- Validation : Motif
    if (!appointmentData.patern_id) {
        Swal.fire({
            title: "Erreur",
            text: "Veuillez sélectionner un motif",
            icon: "error"
        });
        $btn.html(originalContent).prop('disabled', false);
        return;
    }

    // --- Envoi AJAX
    $.ajax({
        url: "{{ route('appointments.store') }}",
        method: "POST",
        data: appointmentData,
        success: function (response) {
            toastr.success('{{ __("lang.saved_successfully") }}');
            $('#appointmentModal').modal('hide');
            $('#calendar').fullCalendar('refetchEvents');
        },
        error: function (xhr) {
            let errorMessage = "Une erreur s'est produite";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                title: "Erreur",
                text: errorMessage,
                icon: "error"
            });
        },
        complete: function () {
            // Toujours restaurer le bouton après succès ou erreur
            $btn.html(originalContent).prop('disabled', false);
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
                console.log("Update Status Data:", data);
                //console.log("Update Status Data:", data);
                if (reason) {
                    data.cancel_reason = reason;
                }

                $.ajax({
                    url: "/appointment-event/status",
                    method: "POST",
                    data: data,
                    success: function (response) {
                        $('#cancelReasonModal').modal('hide');
                        $('#appointmentDetailsModal').modal('hide');
                        $('#calendar').fullCalendar('refetchEvents');
                        toastr.success('{{ __("lang.updated_successfully") }}');

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