@extends('layouts.app')

@push('css_lib')
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- dropzone (same version as old code) -->
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">

    <!-- ADD THIS STYLE to reveal delete-media on hover -->
    <style>
        .card.clickble:hover .delete-media {
            display: block !important;
        }
    </style>
@endpush
@section('content')

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-bold">{{ trans('lang.profil') }} <small
                        class="mx-3">|</small><small>{{ trans('lang.edit_profil') }}</small></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="fas fa-tachometer-alt"></i>
                            {{ trans('lang.dashboard') }}</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('doctors.editProfil') }}">{{ trans('lang.profil') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ trans('lang.edit_profil') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container">
    <div class="card">
        <div class="card-body">

            <!-- *************************GESTION INFOS PERSONNELLES -->
                @if(session('success'))
                    <script>
                        alert("{{ session('success') }}");
                    </script>
                @endif
                @if(session('error'))
                    <script>
                        alert("{{ session('error') }}");
                    </script>
                @endif


                    <!-- Bouton pour afficher/masquer les champs Infos personnelles -->
                    <button class="btn bg-{{setting('theme_color')}} w-100 text-left mt-3 d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#infoPersonnel" aria-expanded="false" aria-controls="infoPersonnel">
                        <span class="d-flex align-items-center">
                            <i class="fas fa-user mr-2"></i> Infos personnelles
                        </span>
                        <i class="fas fa-angle-down fa-lg" id="arrowIcon"></i>
                    </button>
                    <!-- Conteneur des champs Infos personnelles -->
                    {!! Form::open(['route' => 'editInfoPersonnelle', 'method' => 'POST', 'id' => 'editInfoForm']) !!}
                    <div class="collapse mt-3" id="infoPersonnel">
                        <div class="row">
                            <!-- Colonne Gauche -->
                                            <div class="col-md-6">
                                                <!-- Name Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('name', $user->lastname, ['class' => 'form-control', 'placeholder' => trans("lang.user_name_placeholder"), 'required' => 'required']) !!}
                                                    </div>
                                                </div>
                                                <!-- LastName Field -->

                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('lastname', trans("lang.user_lastname"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('lastname', $user->name, ['class' => 'form-control', 'placeholder' => trans("lang.user_lastname_placeholder"), 'required' => 'required']) !!}
                                                    </div>
                                                </div>

                                                <!-- Email Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                    {!! Form::email('email', $user->email, ['class' => 'form-control', 'placeholder' => trans("lang.user_email_placeholder"), 'required' => 'required']) !!}
                                                    </div>
                                                </div>


                                                <!-- Phone Number Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('phone_number', trans("lang.user_phone_number"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                    {!! Form::text('phone_number', $user->phone_number, [
                                                        'class' => 'form-control',
                                                        'placeholder' => trans("lang.user_phone_number_placeholder"),
                                                        'required' => 'required',
                                                        'pattern' => '^\+?[0-9]*$',
                                                        'title' => '+123 12 345 678'
                                                    ]) !!}
                                                    </div>
                                                </div>                            
                                                <!-- Bio -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('bio', 'Biographie', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::textarea('bio', $doctor->bio, ['class' => 'form-control', 'placeholder' => 'Écrivez votre bio', 'style' => 'height: 80px;']) !!}
                                                    </div>
                                                </div>


                                                <!-- Type de consultation -->
                                                <div class="form-group row">
                                                    <label class="col-md-3 control-label text-md-right">Types de consultation</label>
                                                    <div class="col-md-9 d-flex flex-wrap">
                                                        @php
                                                            $methods = ['cabinet', 'domicile', 'téléconsultation', 'urgence'];
                                                        @endphp

                                                        @foreach($methods as $method)
                                                            <div class="form-check me-3 mr-2">
                                                                {!! Form::checkbox('consultation_methods[]', $method, in_array($method, $consultationMethods), ['class' => 'form-check-input', 'id' => $method]) !!}
                                                                <label class="form-check-label" for="{{ $method }}">{{ ucfirst($method) }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>

                                            <!-- Colonne Droite -->
                                            <div class="col-md-6">
                                            
                                                <!-- Numéro de cabinet Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('cabinet_number', 'N° Cabinet', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('cabinet_number', $doctor->fixe, [
                                                            'class' => 'form-control',
                                                            'placeholder' => trans("lang.user_cabinet_number_placeholder"),
                                                            'pattern' => '^\+?[0-9]*$',
                                                            'title' => '+123 12 345 678'
                                                        ]) !!}
                                                    </div>
                                                </div>

                                                <!-- Facebook Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('facebook', 'Facebook', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('facebook', $doctor->facebook, ['class' => 'form-control','placeholder' => trans("lang.user_facebook_placeholder")]) !!}
                                                    </div>
                                                </div>
                                                <!-- Instagram Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('instagram', 'Instagram', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('instagram', $doctor->instagram, ['class' => 'form-control', 'placeholder' => 'Lien du profil Instagram']) !!}
                                                    </div>
                                                </div>

                                                <!-- Website Field -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('website', 'Site Web', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::text('website', $doctor->site_web, ['class' => 'form-control', 'placeholder' => 'Lien du site web']) !!}
                                                    </div>
                                                </div>
                                                
                                                <!-- Description -->
                                                <div class="form-group d-flex align-items-center">
                                                    {!! Form::label('description', 'Description', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::textarea('description', $doctor->description, ['class' => 'form-control', 'placeholder' => 'Écrivez une description', 'style' => 'height: 80px;']) !!}
                                                    </div>
                                                </div>


                                                <!-- Méthodes de paiement -->
                                                <div class="form-group row">
                                                    <label class="col-md-3 control-label text-md-right">Méthodes de paiement</label>
                                                    <div class="col-md-9 d-flex flex-wrap">
                                                        @php
                                                            $paymentOptions = ['Espèce', 'Chèque', 'Carte Bancaire', 'D17'];
                                                        @endphp

                                                        @foreach($paymentOptions as $payment)
                                                            <div class="form-check me-3 mr-2">
                                                                {!! Form::checkbox('payment_methods[]', $payment, in_array($payment, $paymentMethods), ['class' => 'form-check-input', 'id' => $payment]) !!}
                                                                <label class="form-check-label" for="{{ $payment }}">{{ ucfirst($payment) }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>


                                            </div>
                        </div>
                            <!-- Bouton Enregistrer -->
                            <div class="text-right mt-3">
                                {!! Form::submit('Enregistrer', ['class' => 'btn bg-' . setting('theme_color') . ' px-4']) !!}
                            </div>
                    </div>
                {!! Form::close() !!}

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

                <script>
                    alert("{{ session('success') }}");
                </script>
            @endif
            @if(session('error'))
                <script>
                    alert("{{ session('error') }}");
                </script>
            @endif


            <!-- Bouton pour afficher/masquer les champs Infos personnelles -->
            <button
                class="btn bg-{{setting('theme_color')}} w-100 text-left mt-3 d-flex justify-content-between align-items-center"
                type="button" data-toggle="collapse" data-target="#infoPersonnel" aria-expanded="false"
                aria-controls="infoPersonnel">
                <span class="d-flex align-items-center">
                    <i class="fas fa-user mr-2"></i> Infos personnelles
                </span>
                <i class="fas fa-angle-down fa-lg" id="arrowIcon"></i>
            </button>
            <!-- Conteneur des champs Infos personnelles -->
            {!! Form::open(['route' => 'editInfoPersonnelle', 'method' => 'POST', 'id' => 'editInfoForm']) !!}
            <div class="collapse mt-3" id="infoPersonnel">
                <div class="row">
                    <!-- Colonne Gauche -->
                    <div class="col-md-6">
                        <!-- Name Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('name', $user->lastname, ['class' => 'form-control', 'placeholder' => trans("lang.user_name_placeholder"), 'required' => 'required']) !!}
                            </div>
                        </div>
                        <!-- LastName Field -->

                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('lastname', trans("lang.user_lastname"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('lastname', $user->name, ['class' => 'form-control', 'placeholder' => trans("lang.user_lastname_placeholder"), 'required' => 'required']) !!}
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('email', $user->email, ['class' => 'form-control', 'placeholder' => trans("lang.user_email_placeholder"), 'required' => 'required']) !!}
                            </div>
                        </div>


                        <!-- Phone Number Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('phone_number', trans("lang.user_phone_number"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('phone_number', $user->phone_number, ['class' => 'form-control', 'placeholder' => trans("lang.user_phone_number_placeholder"), 'required' => 'required']) !!}
                            </div>
                        </div>
                        <!-- Bio -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('bio', 'Biographie', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::textarea('bio', $doctor->bio, ['class' => 'form-control', 'placeholder' => 'Écrivez votre bio', 'style' => 'height: 80px;']) !!}
                            </div>
                        </div>


                        <!-- Type de consultation -->
                        <div class="form-group row">
                            <label class="col-md-3 control-label text-md-right">Types de consultation</label>
                            <div class="col-md-9 d-flex flex-wrap">
                                @php
                                    $methods = ['cabinet', 'domicile', 'téléconsultation', 'urgence'];
                                @endphp

                                @foreach($methods as $method)
                                    <div class="form-check me-3 mr-2">
                                        {!! Form::checkbox('consultation_methods[]', $method, in_array($method, $consultationMethods), ['class' => 'form-check-input', 'id' => $method]) !!}
                                        <label class="form-check-label" for="{{ $method }}">{{ ucfirst($method) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <!-- Colonne Droite -->
                    <div class="col-md-6">

                        <!-- Numéro de cabinet Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('cabinet_number', 'N° Cabinet', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('cabinet_number', $doctor->fixe, ['class' => 'form-control', 'placeholder' => trans("lang.user_cabinet_number_placeholder")]) !!}
                            </div>
                        </div>
                        <!-- Facebook Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('facebook', 'Facebook', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('facebook', $doctor->facebook, ['class' => 'form-control', 'placeholder' => trans("lang.user_facebook_placeholder")]) !!}
                            </div>
                        </div>
                        <!-- Instagram Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('instagram', 'Instagram', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('instagram', $doctor->instagram, ['class' => 'form-control', 'placeholder' => 'Lien du profil Instagram']) !!}
                            </div>
                        </div>

                        <!-- Website Field -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('website', 'Site Web', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::text('website', $doctor->site_web, ['class' => 'form-control', 'placeholder' => 'Lien du site web']) !!}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('description', 'Description', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::textarea('description', $doctor->description, ['class' => 'form-control', 'placeholder' => 'Écrivez une description', 'style' => 'height: 80px;']) !!}
                            </div>
                        </div>


                        <!-- Méthodes de paiement -->
                        <div class="form-group row">
                            <label class="col-md-3 control-label text-md-right">Méthodes de paiement</label>
                            <div class="col-md-9 d-flex flex-wrap">
                                @php
                                    $paymentOptions = ['Espèce', 'Chèque', 'Carte Bancaire', 'D17'];
                                @endphp

                                @foreach($paymentOptions as $payment)
                                    <div class="form-check me-3 mr-2">
                                        {!! Form::checkbox('payment_methods[]', $payment, in_array($payment, $paymentMethods), ['class' => 'form-check-input', 'id' => $payment]) !!}
                                        <label class="form-check-label" for="{{ $payment }}">{{ ucfirst($payment) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                </div>
                <!-- Bouton Enregistrer -->
                <div class="text-right mt-3">
                    {!! Form::submit('Enregistrer', ['class' => 'btn bg-' . setting('theme_color') . ' px-4']) !!}
                </div>
            </div>
            {!! Form::close() !!}

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script>
                $(document).ready(function () {
                    $('#editInfoForm').submit(function (event) {
                        event.preventDefault(); // Empêche le rechargement de la page

                        $.ajax({
                            url: "{{ route('editInfoPersonnelle') }}",
                            method: "POST",
                            data: $(this).serialize(),
                            success: function (response) {
                                alert(response.success); // Afficher un message de succès
                            },
                            error: function (xhr) {
                                alert("Erreur : " + xhr.responseJSON.error);
                            }
                        });
                    });
                });
            </script>


            <!-- *************************GESTION ADRESSE -->


            <!-- Bouton pour afficher/masquer les champs Adresse -->
            <button
                class="btn bg-{{setting('theme_color')}} w-100 text-left mt-3 d-flex justify-content-between align-items-center"
                type="button" data-toggle="collapse" data-target="#adresseSection" aria-expanded="false"
                aria-controls="adresseSection">
                <span class="d-flex align-items-center">
                    <i class="fas fa-map-marker-alt mr-2"></i> Adresse
                </span>
                <i class="fas fa-angle-down fa-lg" id="arrowIcon"></i>
            </button>
            <!-- formulaire adresse -->
            <form id="adresseForm">
                @csrf
                <div class="collapse mt-3" id="adresseSection">
                    <div class="row">
                        <!-- Pays -->
                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                {!! Form::label('pays', 'Pays', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                <div class="col-md-9">
                                    {!! Form::select(
    'pays',
    ['' => 'Sélectionnez un pays'] + ['tunisie' => 'Tunisie', 'france' => 'France'],
    isset($address->pays) ? json_decode($address->pays)->fr : null,
    ['class' => 'form-control', 'id' => 'pays']
) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Ville -->
                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                {!! Form::label('ville', 'Ville/Région', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                <div class="col-md-9">
                                    {!! Form::select(
    'ville',
    ['' => 'Sélectionnez une ville'],
    isset($address->ville) ? json_decode($address->ville)->fr : null,
    ['class' => 'form-control', 'id' => 'ville', 'disabled', 'required' => 'required']
) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Gouvernorat (Région) -->
                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                {!! Form::label('gouvernorat', 'Gouvernorat/Département', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                <div class="col-md-9">
                                    {!! Form::select(
    'gouvernorat',
    ['' => 'Sélectionnez un gouvernorat'],
    isset($address->gouvernorat) ? json_decode($address->gouvernorat)->fr : null,
    ['class' => 'form-control', 'id' => 'gouvernorat', 'disabled', 'required' => 'required']
) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                {!! Form::label('address', 'Adresse', ['class' => 'col-md-3 control-label text-md-right']) !!}
                                <div class="col-md-9">
                                    {!! Form::text('address', isset($address->address) ? json_decode($address->address)->fr : null, ['class' => 'form-control', 'placeholder' => 'Adresse']) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton Enregistrer -->
                    <div class="text-right mt-3">
                        {!! Form::submit('Enregistrer', ['class' => 'btn bg-' . setting('theme_color') . ' px-4']) !!}
                    </div>
                </div>
            </form>
            <!-- submit du formulaire adresse -->

            <script>

                document.addEventListener('DOMContentLoaded', function () {
                    const paysSelect = document.getElementById('pays');
                    const gouvernoratSelect = document.getElementById('gouvernorat');
                    const villeSelect = document.getElementById('ville');

                    const provinces = {
                        france: ["Auvergne-Rhône-Alpes", "Bourgogne-Franche-Comté", "Bretagne", "Centre-Val de Loire", "Corse",
                            "Grand Est", "Hauts-de-France", "Île-de-France", "Normandie", "Nouvelle-Aquitaine", "Occitanie",
                            "Pays de la Loire", "Provence-Alpes-Côte d'Azur", "Guadeloupe", "Guyane", "La Réunion", "Martinique", "Mayotte"],

                        tunisie: ["Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan", "Kasserine",
                            "Kébili", "La Manouba", "Le Kef", "Mahdia", "Médenine", "Monastir", "Nabeul", "Sfax",
                            "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"]
                    };

                    const cities = {
                        "Auvergne-Rhône-Alpes": ["Lyon", "Saint-Étienne", "Grenoble", "Clermont-Ferrand", "Annecy"],
                        "Bourgogne-Franche-Comté": ["Dijon", "Besançon", "Belfort", "Auxerre", "Chalon-sur-Saône"],
                        "Bretagne": ["Rennes", "Brest", "Quimper", "Lorient", "Vannes"],
                        "Centre-Val de Loire": ["Orléans", "Tours", "Chartres", "Bourges", "Châteauroux"],
                        "Corse": ["Ajaccio", "Bastia", "Calvi", "Corte", "Porto-Vecchio"],
                        "Grand Est": ["Strasbourg", "Reims", "Metz", "Nancy", "Mulhouse"],
                        "Hauts-de-France": ["Lille", "Amiens", "Dunkerque", "Calais", "Roubaix"],
                        "Île-de-France": ["Paris", "Boulogne-Billancourt", "Saint-Denis", "Argenteuil", "Versailles"],
                        "Normandie": ["Rouen", "Caen", "Le Havre", "Évreux", "Cherbourg"],
                        "Nouvelle-Aquitaine": ["Bordeaux", "Limoges", "Pau", "La Rochelle", "Poitiers"],
                        "Occitanie": ["Toulouse", "Montpellier", "Nîmes", "Perpignan", "Albi"],
                        "Pays de la Loire": ["Nantes", "Angers", "Le Mans", "Saint-Nazaire", "Cholet"],
                        "Provence-Alpes-Côte d'Azur": ["Marseille", "Nice", "Toulon", "Aix-en-Provence", "Avignon"],
                        "Guadeloupe": ["Pointe-à-Pitre", "Basse-Terre", "Le Gosier", "Sainte-Anne", "Saint-François"],
                        "Guyane": ["Cayenne", "Kourou", "Saint-Laurent-du-Maroni", "Roura", "Matoury"],
                        "La Réunion": ["Saint-Denis", "Saint-Pierre", "Saint-Paul", "Le Tampon", "Saint-André"],
                        "Martinique": ["Fort-de-France", "Le Lamentin", "Schoelcher", "Le Robert", "Ducos"],
                        "Mayotte": ["Mamoudzou", "Dzaoudzi", "Koungou", "Bandraboua", "Sada"],
                        "Ariana": ["Ariana Ville", "Raoued", "Soukra", "Mnihla", "Kalaat El Andalous"],
                        "Béja": ["Béja Ville", "Medjez el-Bab", "Nefza", "Téboursouk", "Testour"],
                        "Ben Arous": ["Ben Arous", "Hammam-Lif", "Mégrine", "Mornag", "Radès"],
                        "Bizerte": ["Bizerte", "Menzel Bourguiba", "Ras Jebel", "Mateur", "Sejnane"],
                        "Gabès": ["Gabès Ville", "Métouia", "El Hamma", "Mareth", "Chenini Nahal"],
                        "Gafsa": ["Gafsa Ville", "Métlaoui", "Redeyef", "Mdhila", "El Ksar"],
                        "Jendouba": ["Jendouba Ville", "Bou Salem", "Tabarka", "Aïn Draham", "Fernana"],
                        "Kairouan": ["Kairouan Ville", "Hajeb El Ayoun", "Sbikha", "Bou Hajjla", "Nasrallah"],
                        "Kasserine": ["Kasserine Ville", "Sbeitla", "Fériana", "Thala", "Hassi El Ferid"],
                        "Kébili": ["Kébili Ville", "Douz", "Souk Lahad", "El Faouar", "Blidet"],
                        "La Manouba": ["Manouba Ville", "Douar Hicher", "Oued Ellil", "Tebourba", "Borj El Amri"],
                        "Le Kef": ["Le Kef Ville", "Dahmani", "Tajerouine", "Jerissa", "Sakiet Sidi Youssef"],
                        "Mahdia": ["Mahdia Ville", "Chebba", "Ksour Essef", "Bou Merdes", "El Jem"],
                        "Médenine": ["Médenine Ville", "Ben Gardane", "Zarzis", "Beni Khedache", "Sidi Makhlouf"],
                        "Monastir": ["Monastir Ville", "Jemmal", "Ksar Hellal", "Sahline", "Teboulba"],
                        "Nabeul": ["Nabeul Ville", "Hammamet", "Kelibia", "Korba", "Dar Chaabane"],
                        "Sfax": ["Sfax Ville", "Agareb", "Bir Ali Ben Khalifa", "El Hencha", "Mahrès"],
                        "Sidi Bouzid": ["Sidi Bouzid Ville", "Regueb", "Meknassy", "Jilma", "Cebbala"],
                        "Siliana": ["Siliana Ville", "El Krib", "Bou Arada", "Gaâfour", "Bargou"],
                        "Sousse": ["Sousse Ville", "Akouda", "Hammam Sousse", "Kalaa Kebira", "Msaken"],
                        "Tataouine": ["Tataouine Ville", "Bir Lahmar", "Ghomrassen", "Dehiba", "Smar"],
                        "Tozeur": ["Tozeur Ville", "Nefta", "Degache", "Tameghza", "Hazoua"],
                        "Tunis": ["Tunis Centre", "Le Bardo", "La Marsa", "Carthage", "Sidi Hassine"],
                        "Zaghouan": ["Zaghouan Ville", "El Fahs", "Nadhour", "Bir Mcherga", "Saouaf"]
                    };

                    // Réagir au changement de sélection de pays
                    paysSelect.addEventListener('change', function () {
                        const selectedPays = this.value;

                        // Réinitialiser les options
                        gouvernoratSelect.innerHTML = '<option value="">Sélectionnez un gouvernorat</option>';
                        villeSelect.innerHTML = '<option value="">Sélectionnez une ville</option>';
                        villeSelect.setAttribute('disabled', 'true');

                        if (selectedPays && provinces[selectedPays]) {
                            gouvernoratSelect.removeAttribute('disabled');
                            provinces[selectedPays].forEach(region => {
                                let option = new Option(region, region);
                                gouvernoratSelect.add(option);
                            });
                        } else {
                            gouvernoratSelect.setAttribute('disabled', 'true');
                        }

                        // Réactiver le champ ville si un gouvernorat est sélectionné
                        gouvernoratSelect.addEventListener('change', function () {
                            const selectedRegion = this.value;
                            villeSelect.innerHTML = '<option value="">Sélectionnez une ville</option>';
                            if (selectedRegion && cities[selectedRegion]) {
                                villeSelect.removeAttribute('disabled');
                                cities[selectedRegion].forEach(city => {
                                    let option = new Option(city, city);
                                    villeSelect.add(option);
                                });
                            } else {
                                villeSelect.setAttribute('disabled', 'true');
                            }
                        });
                    });

                    // Pré-remplir avec les valeurs existantes
                    setTimeout(() => {
                        const paysValue = "{{ isset($address->pays) ? json_decode($address->pays)->fr : '' }}";
                        if (paysValue) {
                            paysSelect.value = paysValue;
                            paysSelect.dispatchEvent(new Event('change'));
                        }

                        if (paysValue === 'france') {
                            const gouvernoratValue = "<?php echo e(isset($address->Département) && $address->Département ? json_decode($address->Département)->fr : ''); ?>";
                            gouvernoratSelect.value = gouvernoratValue;
                            gouvernoratSelect.dispatchEvent(new Event('change'));

                            const villeValue = "<?php echo e(isset($address->Région) && !empty($address->Région) ? optional(json_decode($address->Région))->fr ?? '' : ''); ?>";
                            villeSelect.value = villeValue;
                        } else if (paysValue === 'tunisie') {
                            const gouvernoratValue = "{{ isset($address->gouvernorat) ? json_decode($address->gouvernorat)->fr : '' }}";
                            gouvernoratSelect.value = gouvernoratValue;
                            gouvernoratSelect.dispatchEvent(new Event('change'));

                            const villeValue = "{{ isset($address->ville) ? json_decode($address->ville)->fr : '' }}";
                            villeSelect.value = villeValue;
                        }
                    }, 100); // Délai pour laisser le temps au DOM de se charger
                });
            </script>
            <!-- submit du formulaire adresse -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('adresseForm');
                    const submitButton = form.querySelector('[type="submit"]');

                    form.addEventListener('submit', function (event) {
                        event.preventDefault(); // Empêche l'envoi classique du formulaire

                        // Récupération des données du formulaire
                        const formData = new FormData(form);

                        // Envoi de la requête AJAX avec Fetch
                        fetch('/adresse/store', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                // Afficher un message de succès
                                alert(data.message);
                            })
                            .catch(error => {
                                // Gérer les erreurs
                                console.error('Erreur:', error);
                                alert('Une erreur est survenue. Veuillez réessayer.');
                            });
                    });
                });
            </script>


         

            <!-- *************************GESTION CV -->
            @if(session('success'))
                <script>
                    alert("{{ session('success') }}");
                </script>
            @endif
            @if(session('error'))
                <script>
                    alert("{{ session('error') }}");
                </script>
            @endif


            <!-- Bouton pour afficher/masquer les champs Diplômes et Langues -->
            <button
                class="btn bg-{{setting('theme_color')}} w-100 text-left mt-3 d-flex justify-content-between align-items-center"
                type="button" data-toggle="collapse" data-target="#curriculumVitae" aria-expanded="false"
                aria-controls="curriculumVitae" id="toggleButton">
                <span class="d-flex align-items-center">
                    <i class="fas fa-file-alt mr-2"></i> Curriculum Vitae
                </span>
                <i class="fas fa-angle-down fa-lg" id="arrowIcon"></i>
            </button>
            {!! Form::open(['route' => 'editCV', 'method' => 'POST', 'id' => 'editCV']) !!}
            <!-- Conteneur des champs Diplômes et Langues -->
            <div class="collapse mt-3" id="curriculumVitae">
                <div class="row">
                    <!-- Specialities Field -->
                    <div class="col-md-6">
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('specialities', trans("lang.doctor_specialities"), ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                {!! Form::select('specialities', $specialities, $specialitiesSelected, ['class' => 'form-control select2', 'data-empty' => trans('lang.doctor_specialities_placeholder'), 'disabled']) !!}
                            </div>
                        </div>
                    </div>




                    <!-- Langues -->
                    <div class="col-md-6">
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('langues', 'Langues', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                <div class="row">
                                    <!-- Première ligne avec 4 langues -->
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Arabe', in_array('Arabe', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Arabe', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Français', in_array('Français', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Français', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Anglais', in_array('Anglais', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Anglais', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Espagnol', in_array('Espagnol', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Espagnol', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!-- Deuxième ligne avec 4 langues -->
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Italien', in_array('Italien', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Italien', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Allemand', in_array('Allemand', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Allemand', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Chinois', in_array('Chinois', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Chinois', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {!! Form::checkbox('langues[]', 'Russe', in_array('Russe', $languesParlees), ['class' => 'form-check-input']) !!}
                                            {!! Form::label('langues', 'Russe', ['class' => 'form-check-label']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Diplômes -->
                    <div class="col-md-6">
                        <div class="form-group d-flex align-items-center">
                            {!! Form::label('diplomes', 'Diplôme', ['class' => 'col-md-3 control-label text-md-right']) !!}
                            <div class="col-md-9">
                                <div id="diplomes-container">
                                    @if(count($diplomes) > 0)
                                        @foreach($diplomes as $diplome)
                                            <div class="diplome-item d-flex align-items-center mb-2">
                                                {!! Form::textarea('diplomes[]', $diplome->name, ['class' => 'form-control', 'placeholder' => 'Insérez votre diplôme', 'style' => 'height: 80px;']) !!}
                                                <div class="ml-2">
                                                    <!-- Delete Icon -->
                                                    <a data-toggle="tooltip" data-placement="left" title="Supprimer" href="#"
                                                        onclick="removeDiplome(this)" class="btn btn-link p-1">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <!-- Ajouter une zone de texte par défaut si aucun diplôme n'existe -->
                                        <div class="diplome-item d-flex align-items-center mb-2">
                                            {!! Form::textarea('diplomes[]', '', ['class' => 'form-control', 'placeholder' => 'Insérez votre diplôme', 'style' => 'height: 80px;']) !!}
                                            <div class="ml-2">
                                                <!-- Delete Icon -->
                                                <a data-toggle="tooltip" data-placement="left" title="Supprimer" href="#"
                                                    onclick="removeDiplome(this)" class="btn btn-link p-1">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <!-- Add Icon -->
                                <a data-toggle="tooltip" data-placement="left" title="Ajouter un diplôme" href="#"
                                    onclick="addDiplome()" class="btn btn-link p-1">
                                    <i class="fas fa-plus mr-2"></i> Ajouter un autre diplôme
                                </a>
                            </div>
                        </div>
                    </div>
                    <script>
                        // Ajouter un nouveau champ de diplôme
                        function addDiplome() {
                            // Créer un nouvel élément de diplôme
                            var newDiplome = document.createElement('div');
                            newDiplome.classList.add('diplome-item', 'd-flex', 'align-items-center', 'mb-2');

                            // Ajouter une zone de texte (textarea)
                            var textareaField = document.createElement('textarea');
                            textareaField.name = 'diplomes[]';
                            textareaField.className = 'form-control';
                            textareaField.placeholder = 'Insérez votre diplôme';
                            textareaField.style.height = '80px';

                            // Ajouter un bouton de suppression avec une icône
                            var deleteButton = document.createElement('div');
                            deleteButton.className = 'ml-2';
                            deleteButton.innerHTML = `
                                <a data-toggle="tooltip" data-placement="left" title="Supprimer" href="#" onclick="removeDiplome(this)" class="btn btn-link p-1">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            `;

                            // Ajouter les éléments au nouvel élément de diplôme
                            newDiplome.appendChild(textareaField);
                            newDiplome.appendChild(deleteButton);

                            // Ajouter le nouvel élément sous les champs existants
                            document.getElementById('diplomes-container').appendChild(newDiplome);
                        }

                        // Supprimer un champ de diplôme
                        function removeDiplome(button) {
                            var allDiplomes = document.querySelectorAll('.diplome-item');
                            if (allDiplomes.length > 1) {
                                button.closest('.diplome-item').remove();
                            } else {
                                alert("Il doit y avoir au moins un diplôme.");
                            }
                        }
                    </script>


                </div>
                <!-- Bouton Enregistrer dans la section Diplômes et Langues -->
                <div class="text-right mt-3">
                    {!! Form::submit('Enregistrer', ['class' => 'btn bg-' . setting('theme_color') . ' px-4']) !!}
                </div>
            </div>
            {!! Form::close() !!}
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script>
                $(document).ready(function () {
                    $('#editCV').submit(function (event) {
                        event.preventDefault(); // Empêche le rechargement de la page

                        $.ajax({
                            url: "{{ route('editCV') }}",
                            method: "POST",
                            data: $(this).serialize(),
                            success: function (response) {
                                alert(response.success); // Afficher un message de succès
                            },
                            error: function (xhr) {
                                alert("Erreur : " + xhr.responseJSON.error);
                            }
                        });
                    });
                });
            </script>


            <!-- ************************* GESTION PHOTOS, VIDÉOS -->
            <!-- Bouton pour afficher/masquer la section du Cabinet -->
            <button
                class="btn bg-{{ setting('theme_color') }} w-100 text-left mt-3 d-flex justify-content-between align-items-center"
                type="button" data-toggle="collapse" data-target="#mediaSection" aria-expanded="false"
                aria-controls="mediaSection" id="toggleMedia">
                <span class="d-flex align-items-center">
                    <i class="fas fa-camera mr-2"></i> Photos et Vidéos du Cabinet
                </span>
                <i class="fas fa-angle-down fa-lg" id="arrowIcon"></i>
            </button>

            <!-- Section Média -->
            <div class="collapse mt-3" id="mediaSection">
                <div class="card-body">
                    <!-- Section d'importation via Dropzone -->
                    <div id="uploadSection">
                        <button id="createMedia" class="btn mb-3"
                            style="background-color: #5c6bc0; border-color: #5c6bc0; color: #fff;">Importer des images
                            du cabinet</button>

                        <div id="createMediaField" class="row" style="display: none;">
                            <div class="col-12">
                                <!-- Conteneur Dropzone dédié au cabinet -->
                                <div id="cabinetDropzone" class="dropzone" data-field="cabinet"></div>
                                <button id="doneMedia"
                                    class="btn btn-outline-primary btn-sm float-right mt-2">Terminer</button>
                                <div class="form-text text-muted">Importer vos images du cabinet ici.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Galerie : Afficher les images importées -->
                    <div class="row medias-items">
                        <div class="card loader">
                            <div class="overlay">
                                <i class="fas fa-redo-alt fa-spin"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_lib')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="{{ asset('vendor/dropzone/min/dropzone.min.js') }}"></script>
@endpush

@push('scripts')
    <script type="text/javascript">
        // Désactive autoDiscover pour initialiser Dropzone manuellement
        Dropzone.autoDiscover = false;

        $(document).ready(function () {
            // Initialiser Dropzone pour les images du cabinet
            var cabinetDropzone = new Dropzone("#cabinetDropzone", {
                url: "{{ route('doctors_gallery.store_cabinet') }}",
                addRemoveLinks: true,
                dictDefaultMessage: '', // Supprimer le message par défaut
                sending: function (file, xhr, formData) {
                    formData.append('_token', '{{ csrf_token() }}');
                    // Forcer l'envoi de "cabinet" comme catégorie
                    formData.append('category', 'cabinet');
                    formData.append('uuid', file.upload.uuid);
                    console.log("Envoi du fichier dans le dossier cabinet.");
                },
                success: function (file) {
                    console.log("Fichier importé avec succès :", file.name);
                    loadMedia(); // Actualiser la galerie après l'importation
                },
                complete: function (file) {
                    console.log("Import terminé pour le fichier :", file.name);
                }
            });

            // Afficher le champ Dropzone lorsque l'utilisateur clique sur "Importer des images du cabinet"
            $('#createMedia').on('click', function () {
                $('#createMediaField').show();
            });

            // Masquer le champ Dropzone et effacer les fichiers lorsque l'utilisateur clique sur "Enregistrer"
            $('#doneMedia').on('click', function () {
                $('#createMediaField').hide();
                cabinetDropzone.removeAllFiles(true);
            });

            // Fonction pour actualiser la galerie des médias du cabinet
            function loadMedia() {
                let mediaItems = $('.medias-items');
                mediaItems.html(`
                                                                                                            <div class="card loader">
                                                                                                                <div class="overlay">
                                                                                                                    <i class="fas fa-redo-alt fa-spin"></i>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        `);
                $.ajax({
                    url: "{{ route('doctors_gallery.all_cabinet') }}",
                    method: 'GET',
                    success: function (data) {
                        let html = '';
                        data.forEach(item => {
                            // Utiliser custom_properties.uuid si disponible, sinon le nom du fichier
                            let theUuid = (item.custom_properties && item.custom_properties.uuid) ? item.custom_properties.uuid : item.file_name;
                            html += `
                                                                                                                        <div class="media-item m-2">
                                                                                                                            <div class="card clickble" style="position: relative;">
                                                                                                                                <button class="btn btn-sm btn-danger delete-media" style="display:none; position:absolute; top:5px; right:5px;" data-uuid="${theUuid}">
                                                                                                                                    <i class="fas fa-trash-alt"></i>
                                                                                                                                </button>
                                                                                                                                <img class="card-img-top" src="${item.thumb}" alt="${item.file_name}">
                                                                                                                                <div class="card-footer text-center">
                                                                                                                                    <small>${item.name} (${item.formated_size})</small>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </div>`;
                        });
                        mediaItems.html(html);
                        initDeleteButtons();
                    },
                    error: function (xhr, status, error) {
                        console.error("Erreur lors du chargement des médias du cabinet :", error);
                        mediaItems.html('<p>Erreur lors du chargement des médias. Veuillez consulter la console ou les logs.</p>');
                    }
                });
            }

            // Initialiser les boutons de suppression avec confirmation
            function initDeleteButtons() {
                $('.delete-media').off('click').on('click', function (e) {
                    e.preventDefault();
                    let btn = $(this);
                    let uuid = btn.data('uuid');
                    swal({
                        title: "Êtes-vous sûr ?",
                        text: "Voulez-vous supprimer cette image ?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $.post("{{ route('doctors_gallery.clear_file') }}", {
                                _token: '{{ csrf_token() }}',
                                uuid: uuid
                            }).done(function (data) {
                                if (data && data.success === true) {
                                    loadMedia(); // Actualiser la galerie après suppression
                                } else {
                                    swal("Erreur", data.message, "error");
                                }
                            }).fail(function () {
                                swal("Erreur", "Erreur lors de la suppression du fichier.", "error");
                            });
                        }
                    });
                });

                // Afficher le bouton de suppression au survol
                $('.card.clickble').hover(function () {
                    $(this).find('.delete-media').show();
                }, function () {
                    $(this).find('.delete-media').hide();
                });
            }

            // Chargement initial de la galerie
            loadMedia();

            // Bouton de rafraîchissement pour actualiser la galerie
            $('#refreshMedia').on('click', function () {
                loadMedia();
            });
        });
    </script>
@endpush