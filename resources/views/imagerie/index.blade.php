@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Spécialités Médicales</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: white;
                min-height: 100vh;
                padding: 0.5rem;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
            }

            .header {
                text-align: center;
                margin-bottom: 3rem;
                color: #2c3e50;
            }

            .header h1 {
                font-size: 2.5rem;
                font-weight: 300;
                margin-bottom: 0.5rem;
                text-shadow: none;
            }

            .header p {
                font-size: 1.1rem;
                opacity: 0.9;
            }

            .specialties-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 2rem;
                padding: 1rem 0;
            }

            .specialty-card {
                background: white;
                border-radius: 20px;
                padding: 2rem;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
                cursor: pointer;
                border: 3px solid transparent;
                position: relative;
                overflow: hidden;
            }

            .specialty-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 5px;
                transform: scaleX(0);
                transition: transform 0.3s ease;
            }

            .specialty-card:hover::before {
                transform: scaleX(1);
            }

            .specialty-card:hover {
                transform: translateY(-10px);
            }

            .icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 1.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.2rem;
                color: white;
                transition: all 0.3s ease;
            }

            .specialty-card:hover .icon {
                transform: scale(1.1) rotate(5deg);
            }

            .specialty-card:hover .icon i {
                animation: pulse 1.5s infinite;
            }

            .specialty-title {
                font-size: 1.4rem;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 0.5rem;
            }

            .specialty-description {
                color: #7f8c8d;
                font-size: 0.95rem;
                line-height: 1.4;
            }

            .pulse {
                display: inline-block;
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.1);
                }

                100% {
                    transform: scale(1);
                }
            }

            @media (max-width: 768px) {
                .header h1 {
                    font-size: 2rem;
                }

                .specialties-grid {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }

                .specialty-card {
                    padding: 1.5rem;
                }
            }

            /* Palette de couleurs médicales */
            .specialty-card:nth-child(1) .icon {
                background: #ce7e52;
            }

            /* Dermatologie */
            .specialty-card:nth-child(2) .icon {
                background: #c0392b;
            }

            /* Cardiologie (rouge) */
            .specialty-card:nth-child(3) .icon {
                background: #2980b9;
            }

            /* Radiologie (bleu) */
            .specialty-card:nth-child(4) .icon {
                background: #27ae60;
            }

            /* Gastro (vert) */
            .specialty-card:nth-child(5) .icon {
                background: #8e44ad;
            }

            /* Gynéco (violet) */
            .specialty-card:nth-child(6) .icon {
                background: #16a085;
            }

            /* Orthopédie (turquoise) */
            .specialty-card:nth-child(7) .icon {
                background: #f39c12;
            }

            /* Ophtalmo (orange) */
            .specialty-card:nth-child(8) .icon {
                background: #d35400;
            }

            /* Dentiste (orange foncé) */
            .specialty-card:nth-child(9) .icon {
                background: #e84393;
            }

            /* Oncologie (rose) */

            /* Effets hover correspondants */
            .specialty-card:nth-child(1):hover {
                border-color: #ce7e52;
                box-shadow: 0 20px 40px rgba(206, 126, 82, 0.3);
            }

            .specialty-card:nth-child(2):hover {
                border-color: #c0392b;
                box-shadow: 0 20px 40px rgba(192, 57, 43, 0.3);
            }

            .specialty-card:nth-child(3):hover {
                border-color: #2980b9;
                box-shadow: 0 20px 40px rgba(41, 128, 185, 0.3);
            }

            .specialty-card:nth-child(4):hover {
                border-color: #27ae60;
                box-shadow: 0 20px 40px rgba(39, 174, 96, 0.3);
            }

            .specialty-card:nth-child(5):hover {
                border-color: #8e44ad;
                box-shadow: 0 20px 40px rgba(142, 68, 173, 0.3);
            }

            .specialty-card:nth-child(6):hover {
                border-color: #16a085;
                box-shadow: 0 20px 40px rgba(22, 160, 133, 0.3);
            }

            .specialty-card:nth-child(7):hover {
                border-color: #f39c12;
                box-shadow: 0 20px 40px rgba(243, 156, 18, 0.3);
            }

            .specialty-card:nth-child(8):hover {
                border-color: #d35400;
                box-shadow: 0 20px 40px rgba(211, 84, 0, 0.3);
            }

            .specialty-card:nth-child(9):hover {
                border-color: #e84393;
                box-shadow: 0 20px 40px rgba(232, 67, 147, 0.3);
            }

            /* Barres du haut au hover */
            .specialty-card:nth-child(1):hover::before {
                background: #ce7e52;
            }

            .specialty-card:nth-child(2):hover::before {
                background: #c0392b;
            }

            .specialty-card:nth-child(3):hover::before {
                background: #2980b9;
            }

            .specialty-card:nth-child(4):hover::before {
                background: #27ae60;
            }

            .specialty-card:nth-child(5):hover::before {
                background: #8e44ad;
            }

            .specialty-card:nth-child(6):hover::before {
                background: #16a085;
            }

            .specialty-card:nth-child(7):hover::before {
                background: #f39c12;
            }

            .specialty-card:nth-child(8):hover::before {
                background: #d35400;
            }

            .specialty-card:nth-child(9):hover::before {
                background: #e84393;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="header">
                <h1
                    style="text-align: center; color: #001f3f;font-weight: bold; font-size: 2.5em; background: #001f3f; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Spécialités Médicales</h1>
                <p>Choisissez une spécialité pour accéder aux services</p>
            </div>

            <div class="specialties-grid">
                <a href="{{ route('imagerie.specialties.show', 'dermato') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <h3 class="specialty-title">Dermatologie</h3>
                    <p class="specialty-description">Analyse intelligente des images cutanées...</p>
                </a>

                <a href="{{ route('imagerie.specialties.show', 'cardio') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3 class="specialty-title">Cardiologie</h3>
                    <p class="specialty-description">Interprétation automatisée des examens cardiaques pour une évaluation
                        rapide</p>
                </a>



                <a href="{{ route('imagerie.specialties.show', 'Radiologue') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-x-ray"></i>
                    </div>
                    <h3 class="specialty-title">Radiologie</h3>
                    <p class="specialty-description">Analyse automatique et intelligente de tous vos types d’images
                        médicales : IRM, scanner, rayons X, échographies.</p>
                </a>



                <a href="{{ route('imagerie.specialties.show', 'gastro') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-diagnoses"></i>
                    </div>
                    <h3 class="specialty-title">Gastro-entérologie</h3>
                    <p class="specialty-description">Détection assistée des anomalies digestives à partir d’images
                        endoscopiques et médicales.</p>
                </a>






                <a href="{{ route('imagerie.specialties.show', 'gyneco') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-female"></i>
                    </div>
                    <h3 class="specialty-title">Gynécologie</h3>
                    <p class="specialty-description">Analyse d’imagerie gynécologique pour un suivi précis de la santé
                        féminine.</p>
                </a>



                <a href="{{ route('imagerie.specialties.show', 'ortho') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-bone"></i>
                    </div>
                    <h3 class="specialty-title">Orthopédie</h3>
                    <p class="specialty-description">Interprétation d’imageries osseuses et articulaires pour guider les
                        diagnostics.</p>
                </a>


                <a href="{{ route('imagerie.specialties.show', 'ophta') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="specialty-title">Ophtalmologie</h3>
                    <p class="specialty-description">Analyse automatisée du fond d’œil et des images rétiniennes pour une
                        détection précoce.</p>
                </a>




                 <a href="{{ route('imagerie.specialties.show', 'dentist') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-tooth"></i>
                    </div>
                    <h3 class="specialty-title">Dentisterie</h3>
                    <p class="specialty-description">Lecture intelligente des radiographies dentaires pour un diagnostic
                        précis.</p>
                </a>

     <a href="{{ route('imagerie.specialties.show', 'oncology') }}" class="specialty-card">
                    <div class="icon">
                        <i class="fas fa-ribbon"></i>
                    </div>
                    <h3 class="specialty-title">Oncologie</h3>
                    <p class="specialty-description">Détection assistée des tumeurs et lésions à partir d’imageries
                        médicales complexes.</p>
                </a>


               
            </div>
        </div>

        <script>
            function redirectTo(page) {
                // Animation avant redirection
                event.target.closest('.specialty-card').style.transform = 'scale(0.95)';

                setTimeout(() => {
                    window.location.href = page;
                }, 200);
            }

            // Animation d'entrée pour les cartes
            document.addEventListener('DOMContentLoaded', function () {
                const cards = document.querySelectorAll('.specialty-card');
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        card.style.transition = 'all 0.6s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            });
        </script>
    </body>

    </html>
@endsection