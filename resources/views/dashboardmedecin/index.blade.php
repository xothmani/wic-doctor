@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Content Header (Page header) -->
<section class="content-header content-header{{setting('fixed_header')}}">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-bold">{{trans('lang.dashboard')}}<small class="mx-3">|</small><small>{{trans('lang.dashboard_overview')}}</small></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item"><a href="#"><i class="fas fa-tachometer-alt"></i> {{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item active">{{trans('lang.dashboard')}}</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<div class="container py-4">
    <div class="row align-items-stretch">
        <!-- Partie gauche avec le message de bienvenue -->
        <div class="col-md-6 d-flex">
            <div class="position-relative w-100" style="min-height: 200px;">
                <div class="card p-4 shadow-sm h-100" style="position: relative; background-color: #fff; border: none; z-index: 1;">
                    <h2 class="mb-1">
                        👨‍⚕️ Bonjour, <strong>Dr. {{ Auth::user()->name }}</strong>
                    </h2>
                    <p class="text-muted mb-3 text-left ml-2" style="font-size: 1.25rem; font-weight: 500;">Ravi de vous revoir aujourd'hui !</p>            
                    <!-- Conteneur pour les deux cartes alignées horizontalement -->
                    <div class="d-flex" style="gap: 15px; width: 60%; margin-top: 20px">
                        <!-- Première carte agrandie -->
                        <div class="card shadow-sm" style="border-left: 4px solid #4e73df; width: 200px; height: 80px;">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h6 class="mb-1 text-primary"><b>Derniere connexion</b></h6>
                                <h6 class="mb-0">{{ $user->last_login_at->format('Y-m-d H:i') }}</h6>
                                </div>
                        </div>
                        
                        <!-- Deuxième carte agrandie -->
                        <div class="card shadow-sm" style="border-left: 4px solid #1cc88a; width: 200px; height: 80px;">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h6 class="mb-1 text-success"><b>Activation abonnement</b></h6>
                                <h6 class="mb-0">{{ $doctor->created_at->format('Y-m-d H:i') }}</h6>
                                </div>
                        </div>
                    </div>
                    
                    <!-- Image mise en avant -->
                    <img src="{{ asset('images/dashboard-icon.png')}}" 
                         alt="Welcome Image" 
                         class="position-absolute" 
                         style="left: 55%; height: 320px; z-index: 10; top: -45px;">
                </div>
            </div>
        </div>
        
        <!-- Partie droite avec les 4 cartes en grille -->
        <div class="col-md-6 mt-2">
            <div class="row h-100">
                <!-- Consultations ce mois -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-calendar-check fa-2x text-primary" style="margin-right: 15px;"></i>
                            <div>
                                <h6 class="mb-1"><b>Consultations ce mois</b></h6>
                                <h2 class="mb-0">{{ $consultationCount }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous cette semaine -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-user-clock fa-2x text-info" style="margin-right: 15px;"></i>
                            <div>
                                <h6 class="mb-1"><b>Rendez-vous cette semaine</b></h6>
                                <h2 class="mb-0"> {{ $appointmentsThisWeek }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rendez-vous aujourd'hui -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-calendar-day fa-2x text-warning" style="margin-right: 15px;"></i>
                            <div>
                                <h6 class="mb-1"><b>Rendez-vous du jour</b></h6>
                                <h2 class="mb-0">{{ $appointmentsToday }}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total patients -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <i class="fas fa-users fa-2x text-success" style="margin-right: 15px;"></i>
                            <div>
                                <h6 class="mb-1"><b>Total patients</b></h6>
                                <h2 class="mb-0"> {{ $totalPatients }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Nouvelle ligne pour les cartes Patients par âge et genre -->
<div class="row mt-4">
    <!-- Carte Patients par âge (1/3) -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie mr-2 text-info"></i> Patients par âge
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Donut chart à gauche -->
                    <div class="col-6 text-center">
                        <canvas id="ageDonutChart" width="75" height="75" style="max-width: 100%; height: auto;"></canvas>
                    </div>

                    <!-- Légendes et pourcentages à droite -->
                    <div class="col-6">
                        <div class="text-center mb-2">
                            <div style="color: #7fb3d5; font-size: 1.2rem;"><strong>{{ $agePercentages['0-11 mois'] ?? 0 }}%</strong></div>
                            <div>0-11 mois</div>
                        </div>

                        <div class="text-center mb-2">
                            <div style="color: #36b9cc; font-size: 1.2rem;"><strong>{{ $agePercentages['1-17'] ?? 0 }}%</strong></div>
                            <div>1-17</div>
                        </div>
                        <div class="text-center mb-2">
                            <div style="color: #4e73df; font-size: 1.2rem;"><strong>{{ $agePercentages['18-39'] ?? 0 }}%</strong></div>
                            <div>18-39</div>
                        </div>
                        <div class="text-center mb-2">
                            <div style="color: #f6c23e; font-size: 1.2rem;"><strong>{{ $agePercentages['40-59'] ?? 0 }}%</strong></div>
                            <div>40-59</div>
                        </div>
                        <div class="text-center">
                            <div style="color: #e74a3b; font-size: 1.2rem;"><strong>{{ $agePercentages['60-99'] ?? 0 }}%</strong></div>
                            <div>60-99</div>
                        </div>
                        <div class="text-center">
                            <div style="color: #858796; font-size: 1.2rem;"><strong>{{ $agePercentages['Non spécifié'] ?? 0 }}%</strong></div>
                            <div>Non spécifié</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte Patients par genre (1/3) -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-venus-mars mr-2 text-purple"></i> Patients par genre
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Donut chart à gauche -->
                    <div class="col-6 text-center">
                        <canvas id="genderDonutChart" width="75" height="75" style="max-width: 100%; height: auto;"></canvas>
                    </div>

                    <!-- Pourcentages à droite -->
                    <div class="col-6">
                    <div class="text-center mb-3">
    <div style="color: #4e73df; font-size: 1.5rem;"><strong>{{ $genderPercentages['Homme'] }}%</strong></div>
    <div><b>Homme</b></div>
</div>
<div class="text-center">
    <div style="color: #e83e8c; font-size: 1.5rem;"><strong>{{ $genderPercentages['Femme'] }}%</strong></div>
    <div><b>Femme</b></div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Carte RDV du jour par statut (1/3) -->
<div class="col-md-4 mb-4">
    <div class="card shadow-sm h-100">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-calendar-check mr-2 text-success"></i> RDV du jour par statut
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <!-- Donut chart à gauche -->
                <div class="col-6 text-center">
                    <canvas id="rdvStatusDonutChart" width="75" height="75" style="max-width: 100%; height: auto;"></canvas>
                </div>

                <!-- Pourcentages à droite -->
                <div class="text-center mb-2">
                <div style="color: #17a2b8; font-size: 1.3rem;"><strong>{{ $statusDataToday['Reçu']['percent'] }}%</strong></div>
                <div><b>Reçu</b></div>
                </div>
                <div class="text-center mb-2">
                    <div style="color: #28a745; font-size: 1.3rem;"><strong>{{ $statusDataToday['Prêt']['percent'] }}%</strong></div>

                    <div><b>Prêt</b></div>
                </div>
                <div class="text-center mb-2">
                    <div style="color: #dc3545; font-size: 1.3rem;"><strong>{{ $statusDataToday['Annulé']['percent'] }}%</strong></div>

                    <div><b>Annulé</b></div>
                </div>
                <div class="text-center">
                    <div style="color: #6c757d; font-size: 1.3rem;"><strong>{{ $statusDataToday['Terminé']['percent'] }}%</strong></div>

                    <div><b>Terminé</b></div>
                </div>


            </div>
        </div>
    </div>
</div>
    
    

    <!-- Pour remplir toute la ligne avec 3 cartes de même taille -->
    <div class="col-md-4 mb-4">
        <!-- Tu peux ajouter ici une autre carte -->
    </div>
</div>


</div>
<script>
    const ctx = document.getElementById('genderDonutChart').getContext('2d');
    const genderDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Homme', 'Femme'],
            datasets: [{
                data: [{{ $genderCounts['Homme'] }}, {{ $genderCounts['Femme'] }}], // Utilisez les counts directement
                backgroundColor: ['#4e73df', '#e83e8c'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            // Affiche directement la valeur (count) au lieu du pourcentage
                            return context.label + ': ' + context.raw;
                        }
                    }
                }
            }
        }
    });
</script>

<script>
    const ageData = @json(array_values($ageCounts)); // <-- on envoie les NOMBRES pour le graphe
    const ctxAge = document.getElementById('ageDonutChart').getContext('2d');

    new Chart(ctxAge, {
        type: 'pie',
        data: {
            labels: ['0-11 mois', '1-17', '18-39', '40-59', '60-99', 'Non spécifié'],
            datasets: [{
                data: ageData,
                backgroundColor: ['#7fb3d5', '#36b9cc', '#4e73df', '#f6c23e', '#e74a3b', '#858796'],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return ` ${value} patient${value > 1 ? 's' : ''}`;
                        }
                    }
                }
            }
        }
    });
</script>

<script>
    window.onload = function () {
        const ctx = document.getElementById('rdvStatusDonutChart').getContext('2d');
        const rdvStatusDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Reçu', 'Prêt', 'Annulé', 'Terminé'],
                datasets: [{
                    data: [
    {{ $statusDataToday['Reçu']['count'] }},
    {{ $statusDataToday['Prêt']['count'] }},
    {{ $statusDataToday['Annulé']['count'] }},
    {{ $statusDataToday['Terminé']['count'] }}
],


                    backgroundColor: ['#17a2b8', '#28a745', '#dc3545', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
    callbacks: {
        label: function (context) {
            const label = context.label || '';
            const value = context.raw;
            return `${label}: ${value} RDV`;
        }
    }
},

                }
            }
        });
    };
</script>



@endsection

