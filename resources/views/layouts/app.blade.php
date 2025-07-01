<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <title>{{setting('app_name')}} | {{setting('app_short_description')}}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/png" href="{{$app_logo ?? ''}}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{asset('vendor/fontawesome-free/css/all.min.css')}}">

    @stack('css_lib')
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,600" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/styles.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/'.setting("theme_color","primary").'.min.css')}}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('css_custom')
    @stack('styles')
	@yield('styles')
</head>

<body class="@if(in_array(app()->getLocale(), ['ar','ku','fa','ur','he','ha','ks'])) rtl @else ltr @endif layout-fixed {{setting('fixed_header',false) ? "layout-navbar-fixed" : ""}} {{setting('fixed_footer',false) ? "layout-footer-fixed" : ""}} sidebar-mini {{setting('theme_color')}} {{setting('theme_contrast','')}}-mode" data-scrollbar-auto-hide="r" data-scrollbar-theme="os-theme-dark">
@yield('scripts')
<div class="wrapper">
<!-- Global Active Doctor Component -->
<x-active-doctor />
    <nav class="main-header navbar navbar-expand {{setting('nav_color','navbar-light navbar-white')}} border-bottom-0">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{url('dashboard')}}" class="nav-link">{{trans('lang.dashboard')}}</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
<style>
    /* Bouton principal */
.installation-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.installation-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.installation-button:hover::before {
    opacity: 1;
}

.installation-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    border-color: rgba(255, 255, 255, 0.5);
}

.installation-button:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
}

.ai-icon {
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ai-icon i {
    font-size: 14px;
    color: white;
}

.arrow::after {
    content: '>';
    color: white;
    font-size: 12px;
    font-weight: bold;
    transition: transform 0.3s ease;
}

.installation-button:hover .arrow::after {
    transform: translateX(2px);
}

.variant-success {
    background: linear-gradient(135deg, #11b8aa 0%, #0d9a8e 100%);
    box-shadow: 0 4px 15px rgba(17, 184, 170, 0.4);
}

.variant-success:hover {
    box-shadow: 0 6px 20px rgba(17, 184, 170, 0.6);
}

/* Overlay */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 999;
}

/* Drawer */
.drawer {
    display: none;
    position: fixed;
    top: 0;
    right: 0;
    height: 100%;
    width: 600px;
    max-width: 100vw;
    background: linear-gradient(135deg, #f8fafc 0%, #e6f3ff 100%);
    z-index: 1000;
    box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.3s ease-out;
    flex-direction: column;
    overflow-y: auto;
}
.footer-drawer{
  position: fixed;
    width: 600px;
    max-width: 100vw;  float : right;
  bottom: 0;
  background: white;
  border-top: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 15px 10px;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
  z-index: 1001;
}
.drawer-content {
    padding-bottom: 100px; /* Espace pour le footer */
}

/* En-tête */
.drawer-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background: linear-gradient(135deg, #f8fafc 0%, #e6f3ff 100%);
    position: relative;
    color: #11b8aa;
}

/* Animation */
@keyframes slideIn {
    from { right: -100%; opacity: 0; }
    to { right: 0; opacity: 1; }
}

/* Bouton flottant */
.floating-close {
    display: none;
    position: fixed;
    top: 20px;
    right: calc(600px + 20px);
    width: 40px;
    height: 40px;
    background-color: #fff;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    font-size: 24px;
    text-align: center;
    line-height: 40px;
    cursor: pointer;
    z-index: 1001;
    color: #555;
    transition: transform 0.2s ease;
}

.floating-close:hover {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .drawer {
        width: 100vw;
    }
    .footer-drawer{
      position: fixed;
    width: 100vw;
      float : right;
  bottom: 0;
  background: white;
  border-top: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 15px 10px;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
  z-index: 1001;
    }
    
    .floating-close {
        right: 20px;
        top: 10px;
    }
    
    .demo-banner {
        flex-direction: column;
        height: auto !important;
        padding: 12px !important;
    }
    
    .demo-banner img {
        width: 100% !important;
        height: auto !important;
        margin-top: 10px;
    }
    
    .service-card {
        flex-direction: column !important;
        padding: 15px !important;
    }
    
    .service-card button {
        margin-top: 10px;
        width: 100%;
    }
    
    .footer-content {
        flex-direction: column !important;
        gap: 15px !important;
        text-align: center !important;
    }
    
    .footer-button {
        width: 100% !important;
    }
}

@media (min-width: 1400px) {
    .drawer {
        width: 700px;
    }
    .footer-drawer{
      position: fixed;
    width: 700px;
  bottom: 0;
  background: white;
  border-top: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 15px 10px;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
  z-index: 1001;
    }
    .floating-close {
        right: calc(700px + 20px);
    }
}

/* Styles spécifiques pour le contenu */
.hero-image {
    width: 100%;
    height: auto;
    border-bottom: 1px solid #eee;
}

.demo-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #f8fafc 0%, #e6f3ff 100%);
    padding: 16px;
    border-radius: 10px;
    margin: 15px;
    height: 80px;
}

.demo-button {
    background-color: #053178;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.demo-button:hover {
    background-color: #0647ad;
}

.demo-thumbnail {
    height: 80px;
    border-radius: 5px;
    object-fit: cover;
}

.services-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 0 15px 15px;
}

.service-card {
    background-color: #fff;
    padding: 20px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 2px solid #0594D0;
}

.service-card:nth-child(2) {
    border-color: #A458E3;
}

.service-card:nth-child(3) {
    border-color: #fd8e26;
}

.service-card:nth-child(4) {
    border-color: #59D189;
}

.service-text {
    flex: 1;
    padding-right: 20px;
}

.service-title {
    color: #4f4b4b;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 8px;
}

.service-description {
    margin: 0;
    color: #4f4b4b;
    font-size: 14px;
}

.info-badge {
    display: inline-block;
    margin-top: 8px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    color: white;
    font-weight: bold;
    transition: transform 0.3s ease;
    cursor: pointer;
}

.service-card:nth-child(1) .info-badge {
    background: #0594D0;
}

.service-card:nth-child(2) .info-badge {
    background: #A458E3;
}

.service-card:nth-child(3) .info-badge {
    background: #fd8e26;
}

.service-card:nth-child(4) .info-badge {
    background: #59D189;
}

.info-badge:hover {
    transform: scale(1.1);
}

.service-button {
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    white-space: nowrap;
    min-width: 100px;
}

.service-card:nth-child(1) .service-button {
    background: linear-gradient(to right, #0594D0, #33bdea);
}

.service-card:nth-child(2) .service-button {
    background: linear-gradient(to right, #A458E3, #c08ff0);
}

.service-card:nth-child(3) .service-button {
    background: linear-gradient(to right, #fd8e26, #ffc38d);
}

.service-card:nth-child(4) .service-button {
    background: linear-gradient(to right, #59D189, #8fe9b4);
}

</style>

<!-- Bouton de déclenchement -->
<li class="nav-item" style="margin: 0 10px;">
    <a href="#" onclick="openModal()" class="installation-button variant-success" style="text-decoration: none; color:white">
        <div class="ai-icon">
            <i class="fas fa-robot"></i>
        </div>
        <span style="font-weight: 700">Essayer Notre Boite IA</span>
        <div class="arrow"></div>
    </a>
</li>

<!-- Modal -->
<div id="iaModal" class="drawer">
    <div class="drawer-content">
        <!-- Image principale -->
        <img src="/images/IA.png" alt="Boîte IA" class="hero-image"/>

        <!-- Bannière démo -->
        <div class="demo-banner">
            <button class="demo-button" onclick="playDemo()">
                Regarder la vidéo de démonstration
                <i class="fas fa-play-circle" style="margin-left: 8px;"></i>
            </button>
            <img src="/images/miniature.jpeg" alt="Miniature vidéo" class="demo-thumbnail" />
        </div>

        <!-- Cartes de services -->
        <div class="services-container">
            <!-- Carte 1 -->
            <div class="service-card">
                <div class="service-text">
                    <span class="service-title">Interaction IA</span>
                    <p class="service-description">
                        Détectez rapidement les interactions à risque grâce à des alertes précises de notre assistant intelligent.
                        <span class="info-badge">En savoir plus</span>
                    </p>
                </div>
                <button class="service-button">Démarrer</button>
            </div>

            <!-- Carte 2 -->
            <div class="service-card">
                <div class="service-text">
                    <span class="service-title">Aide à la prescription</span>
                    <p class="service-description">
                        Prescrivez en toute sécurité grâce à des alertes ciblées selon le profil du patient et les traitements choisis.
                        <span class="info-badge">En savoir plus</span>
                    </p>
                </div>
                <button class="service-button">Explorer</button>
            </div>

            <!-- Carte 3 -->
            <div class="service-card">
                <div class="service-text">
                    <span class="service-title">Rapport IA</span>
                    <p class="service-description">
                        Générez des rapports PDF précis et documentez vos décisions grâce à notre micro-intelligence.
                        <span class="info-badge">En savoir plus</span>
                    </p>
                </div>
                <button class="service-button">Lancer</button>
            </div>

            <!-- Carte 4 -->
            <div class="service-card">
                <div class="service-text">
                    <span class="service-title">Imagerie intelligente</span>
                    <p class="service-description">
                        Générez un rapport structuré en secondes à partir de radios, scanners ou IRM grâce à l'IA.
                        <span class="info-badge">En savoir plus</span>
                    </p>
                </div>
                <button class="service-button">Voir plus</button>
            </div>
        </div>
    </div>

<!-- Footer -->
<div class="footer-drawer">

  <!-- Icône à gauche -->
  <div style="display: flex; align-items: center; gap: 10px;">
    <div style="
      background-color: #e1f5fb;
      color: #11b8aa;
      border-radius: 6px;
      width:40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 20px;
    ">
      ?
    </div>

    <!-- Texte -->
    <div style="color: #4f4b4b; font-weight: 600; font-size: 14px;">
      Visitez notre centre d'aide<br />
      <span style="color: #11b8aa;">
        Découvrez plus de conseils pour démarrer
        <i class="fas fa-chevron-right" style="font-size: 12px; margin-left: 8px"></i>
      </span>
    </div>
  </div>

  <!-- Bouton -->
  <button
    style="
      background-color: white;
      color: #11b8aa;
      border: 2px solid #11b8aa;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.3s ease;
    "
    onmouseover="this.style.backgroundColor='#11b8aa'; this.style.color='white';"
    onmouseout="this.style.backgroundColor='white'; this.style.color='#11b8aa';"
  >
    Obtenir de l'aide
  </button>
</div>
</div>

<!-- Overlay -->
<div id="overlay" class="modal-overlay" onclick="closeModal()"></div>
<!-- Bouton flottant de fermeture -->
<div id="closeBtn" class="floating-close" onclick="closeModal()">×</div>


        
            @if(env('APP_CONSTRUCTION',false))
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"><i class="fas fa-info-circle"></i>
                        {{env('APP_CONSTRUCTION','') }}</a>
                </li>
            @endif
            @can('favorites.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('favorites*') ? 'active' : '' }}" href="{{route('favorites.index')}}"><i class="fas fa-heart"></i></a>
                </li>
            @endcan
            @can('notifications.index')
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('notifications*') ? 'active' : '' }}" href="{!! route('notifications.index') !!}"><i class="fas fa-bell"></i></a>
                </li>
            @endcan
  
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <img src="{{auth()->user()->getFirstMediaUrl('avatar','icon')}}" class="brand-image mx-2 img-circle elevation-2" alt="User Image">
@php
    $name = auth()->user()->name;
    $decodedName = json_decode($name);

    if (json_last_error() === JSON_ERROR_NONE && is_object($decodedName) && isset($decodedName->fr)) {
        $displayName = $decodedName->fr;
    } else {
        $displayName = $name;
    }
@endphp

<i class="fa fas fa-angle-down"></i> {{ $displayName }}

                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="{{route('users.profile')}}" class="dropdown-item"> <i class="fas fa-user mr-2"></i> {{trans('lang.user_profile')}} </a>
                    <div class="dropdown-divider"></div>
                    <a href="{!! url('/logout') !!}" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-envelope mr-2"></i> {{__('auth.logout')}}
                    </a>
                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Left side column. contains the logo and sidebar -->
@include('layouts.sidebar')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    <!-- Main Footer -->
<!--     <footer class="main-footer border-0 shadow-sm">
        <div class="float-sm-right d-none d-sm-block">
            <b>Version</b> {{implode('.',str_split(substr(config('installer.currentVersion','v100'),1,3)))}}
        </div>
        <strong>Copyright © {{date('Y')}} <a href="{{url('/')}}">{{setting('app_name')}}</a>.</strong> All rights reserved.
    </footer> -->

</div>

<!-- jQuery -->
<script src="{{asset('vendor/jquery/jquery.min.js')}}"></script>

<script src="{{asset('vendor/bootstrap-v4-rtl/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>

<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="{{asset('https://www.gstatic.com/firebasejs/7.2.0/firebase-app.js')}}"></script>

<script src="{{asset('https://www.gstatic.com/firebasejs/7.2.0/firebase-messaging.js')}}"></script>

<script type="text/javascript">@include('vendor.notifications.init_firebase')</script>

<script type="text/javascript">
    const messaging = firebase.messaging();
    navigator.serviceWorker.register("{{url('firebase/sw-js')}}")
        .then((registration) => {
            messaging.useServiceWorker(registration);
            messaging.requestPermission()
                .then(function () {
                    console.log('Notification permission granted.');
                    getRegToken();

                })
                .catch(function (err) {
                    console.log('Unable to get permission to notify.', err);
                });
            messaging.onMessage(function (payload) {
                console.log("Message received. ", payload);
                notificationTitle = payload.data.title;
                notificationOptions = {
                    body: payload.data.body,
                    icon: payload.data.icon,
                    image: payload.data.image
                };
                var notification = new Notification(notificationTitle, notificationOptions);
            });
        });

    function getRegToken(argument) {
        messaging.getToken().then(function (currentToken) {
            if (currentToken) {
                saveToken(currentToken);
                console.log(currentToken);
            } else {
                console.log('No Instance ID token available. Request permission to generate one.');
            }
        })
            .catch(function (err) {
                console.log('An error occurred while retrieving token. ', err);
            });
    }


    function saveToken(currentToken) {
        $.ajax({
            type: "POST",
            data: {'device_token': currentToken, 'api_token': '{!! auth()->user()->api_token !!}'},
            url: '{!! url('api/users',['id'=>auth()->id()]) !!}',
            success: function (data) {

            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function changeLanguage(locale) {
        event.preventDefault();
        document.getElementById('current-language').value = locale;
        document.getElementById('languages-form').submit();
    }
</script>

@stack('scripts_lib')
<script src="{{asset('dist/js/adminlte.min.js')}}"></script>
<script src="{{asset('js/scripts.min.js')}}"></script>
<script src="{{ asset('js/services/doctorService.js') }}"></script>
@stack('scripts')
</body>
</html>
<script>
function openModal() {
    document.getElementById("iaModal").style.display = "flex";
    document.getElementById("overlay").style.display = "block";
    document.getElementById("closeBtn").style.display = "block";
}

function closeModal() {
    document.getElementById("iaModal").style.display = "none";
    document.getElementById("overlay").style.display = "none";
    document.getElementById("closeBtn").style.display = "none";
}
</script>
