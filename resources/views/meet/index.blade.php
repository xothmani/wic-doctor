@extends('layouts.app')

@section('content')

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-dark">{{ trans('lang.teleconsultation_plural') }}
                    <small class="mx-3">|</small>
                    <small>{{ trans('lang.teleconsultation_table') }}</small>
                </h1>
            </div>
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fa fa-dashboard"></i> {{ trans('lang.dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a
                            href="{!! route('teleconsultations.index') !!}">{{ trans('lang.teleconsultation_plural') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.teleconsultation_table') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->

<!-- Main Content -->
<div class="content">
    <!-- Flash Messages -->
    @include('flash::message')

    <!-- Teleconsultation Card -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title">{{ trans('lang.teleconsultation_table') }}</h5>
        </div>
        <div class="card-body">
            <!-- Table Responsive -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('lang.patient_name') }}</th>
                            <th>{{ trans('lang.patient_phone') }}</th>
                            <th>{{ trans('lang.teleconsultation_date') }}</th>
                            <th>{{ trans('lang.teleconsultation_time') }}</th>
                            <th>{{ trans('lang.teleconsultation_status') }}</th>
                            <th>{{ trans('lang.teleconsultation_link') }}</th>
                        </tr>
                    </thead>
                    <tbody>
    @foreach($rooms as $room)
        @php
            $patient = $patients->get($room->patient_id); // Fetch patient data
            $paymentStatus = $room->appointment->appointment_status_id ?? null; // Fetch payment status
            $statusLabel = $paymentStatus == 9
                ? ['text' => 'Payé', 'badge' => 'success']
                : ['text' => 'En attente de paiement', 'badge' => 'warning'];
        @endphp
        <tr>
            <td>{{ $patient ? $patient->first_name . ' ' . $patient->last_name : trans('lang.unknown') }}</td>
            <td>{{ $patient ? $patient->phone_number : trans('lang.not_available') }}</td>
            <td>{{ $room->date ?: trans('lang.not_specified') }}</td>
            <td>{{ $room->time ?: trans('lang.not_specified') }}</td>
            <td>
                <span class="badge badge-{{ $statusLabel['badge'] }}">
                    {{ $statusLabel['text'] }}
                </span>
            </td>
            <td>
                <a href="{{ $room->meet_link }}" target="_blank"
                    class="btn bg-{{setting('theme_color')}} btn-sm">
                    {{ trans('lang.open_link') }}
                </a>
            </td>
        </tr>
    @endforeach
</tbody>

                </table>
            </div>

            <!-- Render Pagination Links -->
            <div class="d-flex justify-content-end">
                {{ $rooms->links() }}
            </div>


        </div>
    </div>
</div>
@endsection

<!-- Add Bootstrap CSS and JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
