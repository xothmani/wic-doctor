@extends('layouts.app') {{-- Si vous utilisez un layout --}}

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Médecin - Rapports Médicaux</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #053178;
            font-weight: 700;
        }

        .header p {
            color: #636e72;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 3rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, #fd8e26, #ffc38d);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: #053178;
        }

        .card-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
        }

        .card:hover .card-icon::before {
            transform: scale(1);
        }
        .icon-symbol{
            color: white;
            font-size: 30px;
        }

       

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #053178;
            margin-bottom: 1rem;
        }

        .card-subtitle {
            color: #636e72;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        .features-list {
            list-style: none;
            margin-bottom: 2.5rem;
            text-align: left;
        }

        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #636e72;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .features-list li::before {
            content: '✓';
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fd8e26;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 12px;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .card-button {
            background: #053178;
            color: white;
            border: none;
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: none;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(255, 195, 141, 0.3);
        }

        .card-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .card-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 195, 141, 0.4);
        }

        .card-button:hover::before {
            left: 100%;
        }

        .card-button:active {
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .container {
                padding-top: 1rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .cards-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .card {
                padding: 2rem 1.5rem;
            }
            
            .card-icon {
                width: 80px;
                height: 80px;
            }
            
  }

        /* Animations d'entrée */
        .card {
            opacity: 0;
            transform: translateY(50px);
            animation: slideUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
        }

        .card:nth-child(2) {
            animation-delay: 0.2s;
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Formes flottantes en arrière-plan */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            opacity: 0.05;
            animation: float 8s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
            background: #4ecdc4;
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 15%;
            width: 150px;
            height: 150px;
            background: #fd79a8;
            border-radius: 30%;
            animation-delay: 3s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            width: 80px;
            height: 80px;
            background: #74b9ff;
            border-radius: 20%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Rapport IA</h1>
            <p>Choisissez le type de rapport médical que vous souhaitez générer</p>
        </div>

        <div class="cards-container">
            <div class="card patient-report">
                <div class="card-icon">
<div class="icon-symbol"><i class="fas fa-clipboard"></i></div>                </div>
                <h2 class="card-title">Rapport Médical Patient</h2>
                <p class="card-subtitle">
                  Générez des rapports médicaux personnalisés basés sur les données du patient, grâce à l’intelligence artificielle.
                </p>
                <ul class="features-list">
                    <li>Historique médical complet</li>
                    <li>Synthèse des consultations</li>
                    <li>Traitements et prescriptions</li>
                    <li>Allergies et contre-indications</li>
                    <li>Recommandations personnalisées</li>
                    <li>Export PDF sécurisé</li>
                </ul>
     <a href="{{ route('SpeechToText.renderRapportPatient') }}" class="card-button">Générer le Rapport</a>

            </div>

            <div class="card professional-report">
                <div class="card-icon">
<div class="icon-symbol"><i class="fas fa-chart-bar"></i></div>                </div>
                <h2 class="card-title">Rapport Médical Pro</h2>
                <p class="card-subtitle">
              Générez des rapports à partir de vos observations ou dictées, avec reformulation intelligente  via l’IA.
                </p>
                <ul class="features-list">
                    <li>Transcription intelligente</li>
                    <li>Résumé automatique</li>
                    <li>Modèles prédéfinis</li>
                    <li>Personnalisation avancée</li>
                    <li>Assistance IA contextuelle</li>
                    <li>Export multi-formats </li>
                </ul>
                <button class="card-button" >Générer le Rapport</button>
            </div>
        </div>
    </div>

</body>
</html>
@endsection