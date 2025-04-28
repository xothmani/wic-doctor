@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Content Header (Page header) -->
<section class="content-header content-header{{setting('fixed_header')}}">
    <div class="container-fluid">
        <div class="row">
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
</div> <!-- Fermeture du container py-4 -->

<!-- Nouvelle ligne pour les cartes Patients par âge et genre -->
<div class="container">
    <div class="row">
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
                            <div class="row">
                                <div class="col-6 text-center mb-4">
                                    <div style="color: #7fb3d5; font-size: 1.2rem;"><strong>{{ $agePercentages['0-11 mois'] ?? 0 }}%</strong></div>
                                    <div>0-11 mois</div>
                                </div>
                                <div class="col-6 text-center mb-4">
                                    <div style="color: #36b9cc; font-size: 1.2rem;"><strong>{{ $agePercentages['1-17'] ?? 0 }}%</strong></div>
                                    <div>1-17</div>
                                </div>

                                <div class="col-6 text-center mb-4">
                                    <div style="color: #4e73df; font-size: 1.2rem;"><strong>{{ $agePercentages['18-39'] ?? 0 }}%</strong></div>
                                    <div>18-39</div>
                                </div>
                                <div class="col-6 text-center mb-4">
                                    <div style="color: #f6c23e; font-size: 1.2rem;"><strong>{{ $agePercentages['40-59'] ?? 0 }}%</strong></div>
                                    <div>40-59</div>
                                </div>

                                <div class="col-6 text-center">
                                    <div style="color: #e74a3b; font-size: 1.2rem;"><strong>{{ $agePercentages['60-99'] ?? 0 }}%</strong></div>
                                    <div>60-99</div>
                                </div>
                                <div class="col-6 text-center">
                                    <div style="color: #858796; font-size: 1.2rem;"><strong>{{ $agePercentages['Non spécifié'] ?? 0 }}%</strong></div>
                                    <div>Non spécifié</div>
                                </div>
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
                        <div class="col-6">
                            <div class="row">
                                <div class="col-6 text-center mb-4">
                                    <div style="color: #17a2b8; font-size: 1.3rem;"><strong>{{ $statusDataToday['Reçu']['percent'] }}%</strong></div>
                                    <div><b>Reçu</b></div>
                                </div>
                                <div class="col-6 text-center mb-4">
                                    <div style="color: #28a745; font-size: 1.3rem;"><strong>{{ $statusDataToday['Prêt']['percent'] }}%</strong></div>
                                    <div><b>Prêt</b></div>
                                </div>
                                <div class="col-6 text-center">
                                    <div style="color: #dc3545; font-size: 1.3rem;"><strong>{{ $statusDataToday['Annulé']['percent'] }}%</strong></div>
                                    <div><b>Annulé</b></div>
                                </div>
                                <div class="col-6 text-center">
                                    <div style="color: #6c757d; font-size: 1.3rem;"><strong>{{ $statusDataToday['Terminé']['percent'] }}%</strong></div>
                                    <div><b>Terminé</b></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section inférieures -->
<div class="container">
    <div class="row g-0">
        <!-- Colonne gauche -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-notes-medical mr-2 text-danger"></i> Motifs les plus fréquents (Top 5)
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($topMotifs as $motif)
                        @php
                            $percentage = ($motif['total_rendezvous'] / $topMotifs[0]['total_rendezvous']) * 100;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
                            <div>{{ $motif['motif_nom'] }}</div>
                            <div>{{ $motif['total_rendezvous'] }}</div>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-danger" style="width: {{ $percentage }}%;"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Colonne droite -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-info mr-2"></i> Évolution des rendez-vous par mois
                    </h5>
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="appointmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .progress {
        background-color: #e9ecef;
        border-radius: 0.25rem;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
    canvas {
        width: 100% !important;
        height: auto !important;
    }
</style>

<script>
    // Chart for appointments evolution
const ctx = document.getElementById('appointmentsChart').getContext('2d');

// Calculer le maximum des données pour définir l'échelle
const appointmentsData = {!! $monthlyAppointmentsData !!};
const maxValue = Math.max(...appointmentsData);
const yMax = Math.ceil(maxValue * 1.1); // 10% de marge au-dessus du maximum
const stepSize = Math.ceil(yMax / 10); // Diviser l'axe en 10 segments

new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! $monthlyAppointmentsLabels !!},
        datasets: [{
            label: 'Rendez-vous par mois',
            data: appointmentsData,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            fill: true,
            tension: 0.3,
            pointRadius: 5,
            pointBackgroundColor: '#4e73df'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                precision: 0,
                suggestedMax: yMax,
                ticks: {
                    stepSize: stepSize,
                    callback: function(value) {
                        return value.toFixed(0); // Afficher les nombres entiers
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

    // Chart for gender distribution
    const hommeCount = {{ $genderCounts['Homme'] }};
    const femmeCount = {{ $genderCounts['Femme'] }};
    const total = hommeCount + femmeCount;

    let data, labels, backgroundColor;

    if (total === 0) {
        data = [1];
        labels = ['Aucun patient'];
        backgroundColor = ['#d3d3d3'];
    } else {
        data = [hommeCount, femmeCount];
        labels = ['Homme', 'Femme'];
        backgroundColor = ['#4e73df', '#e83e8c'];
    }

    const genderCtx = document.getElementById('genderDonutChart').getContext('2d');
    const genderDonutChart = new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor,
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
                            if (total === 0) {
                                return 'Aucun patient';
                            } else {
                                const count = context.raw;
                                const isPlural = count > 1;
                                let labelText = '';
                                
                                if (context.label === 'Homme') {
                                    labelText = count + ' patient' + (isPlural ? 's' : '');
                                } else if (context.label === 'Femme') {
                                    labelText = count + ' patiente' + (isPlural ? 's' : '');
                                }
                                
                                return labelText;
                            }
                        }
                    }
                }
            }
        }
    });

    // Chart for age distribution
    const ageData = @json(array_values($ageCounts));
    const ctxAge = document.getElementById('ageDonutChart').getContext('2d');
    const isEmpty = ageData.every(value => value === 0);

    new Chart(ctxAge, {
        type: 'pie',
        data: {
            labels: isEmpty ? ['Pas de patients'] : ['0-11 mois', '1-17', '18-39', '40-59', '60-99', 'Non spécifié'],
            datasets: [{
                data: isEmpty ? [1] : ageData,
                backgroundColor: isEmpty ? ['#d6d6d6'] : ['#7fb3d5', '#36b9cc', '#4e73df', '#f6c23e', '#e74a3b', '#858796'],
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
                            if (isEmpty) {
                                return ' Aucun patient';
                            } else {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return ` ${value} patient${value > 1 ? 's' : ''}`;
                            }
                        }
                    }
                }
            }
        }
    });

    // Chart for today's appointments status
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('rdvStatusDonutChart').getContext('2d');
        const rawData = {
            'Reçu': {{ $statusDataToday['Reçu']['count'] }},
            'Prêt': {{ $statusDataToday['Prêt']['count'] }},
            'Annulé': {{ $statusDataToday['Annulé']['count'] }},
            'Terminé': {{ $statusDataToday['Terminé']['count'] }}
        };

        const dataValues = Object.values(rawData);
        let chartData, chartLabels, chartColors;

        if (dataValues.every(value => value === 0)) {
            chartData = [1];
            chartLabels = ['Aucun RDV'];
            chartColors = ['#d6d6d6'];
        } else {
            chartData = dataValues;
            chartLabels = Object.keys(rawData);
            chartColors = ['#17a2b8', '#28a745', '#dc3545', '#6c757d'];
        }

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: chartColors,
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
                                const label = context.label || '';
                                const value = context.raw;
                                if (label === 'Aucun RDV') {
                                    return 'Aucun RDV aujourd\'hui';
                                }
                                return `${label}: ${value} RDV`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>

@endsection