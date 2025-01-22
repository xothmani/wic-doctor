@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h1 class="m-0 text-bold">{{ trans('lang.appointment_plural') }}
                        <small class="mx-3">|</small><small>{{ trans('lang.appointment_desc') }}</small>
                    </h1>
                </div>
                <div class="col-md-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt mx-1"></i> {{ trans('lang.dashboard') }}</a></li>
                        <li class="breadcrumb-item">
                            <a href="{!! route('appointments.index') !!}">{{ trans('lang.appointment_plural') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ trans('lang.calendar_view') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="clearfix"></div>
        @include('flash::message')
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
                    <div class="d-flex flex-row">
                        <li class="nav-item">
                            <a class="nav-link active" href="{!! url()->current() !!}"><i class="fa fa-calendar mr-2"></i>{{ trans('lang.calendar_view') }}</a>
                        </li>
                    </div>
                </ul>
            </div>
            <div class="card-body">
                <!-- Calendar Container -->
                <div id="calendar-container">
                    <div id="calendar"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLR5pggzLbM2A3b0eFy4sQbJZR1f6UeFAo6B4kaBGFy" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

<script>
$(document).ready(function () {
    // Initialize the calendar with drag-and-drop functionality
    $('#calendar').fullCalendar({
        editable: true,
        locale: 'fr',
        height: 600,
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: "/appointment-event",
                type: "GET",
                data: {
                    start: start.format("YYYY-MM-DD HH:mm:ss"),
                    end: end.format("YYYY-MM-DD HH:mm:ss")
                },
                dataType: "json",
                success: function(data) {
                    var events = data.map(event => ({
                        id: event.id,
                        title: `${event.patient_name} - Status: ${event.status}, Phone: ${event.phone_number}`,
                        start: moment.utc(event.start_at).local().format(),
			end: moment.utc(event.ends_at).local().format(),
                    }));
                    callback(events);
                },
                error: function() {
                    alert("Error loading events. Please try again later.");
                }
            });
        },
        eventDrop: function(event) {
            // Capture the new start and end times after dragging
            let newStart = event.start.format("YYYY-MM-DD HH:mm:ss");
            let newEnd = event.end ? event.end.format("YYYY-MM-DD HH:mm:ss") : null;

            // AJAX request to update the event's date/time in the backend
            $.ajax({
                url: "/appointment-event/action",
                type: "POST",
                data: {
                    id: event.id,
                    start_at: newStart,
                    ends_at: newEnd,
                    type: 'update'
                },
                success: function() {
                    alert("Appointment updated successfully.");
                    $('#calendar').fullCalendar('refetchEvents'); // Refresh the calendar to reflect changes
                },
                error: function() {
                    alert("An error occurred while updating the appointment. Please try again.");
                    console.log("Data being sent:", { id: event.id, start_at: newStart, ends_at: newEnd });

                }
            });
        },
        eventClick: function(event) {
            // Empty function for future customization
        }
    });
});
</script>
@endpush
