@extends('layouts.app')

@section('content')
   

    <div class="container py-4">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="wic-title">
                        <h2>
                            <i class="fas fa-file-medical medical-icon"></i>
                            Fichiers de consultation pour 
                            <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                        </h2>
                        <div class="d-flex align-items-center">
                            <span class="badge modern-badge">
                                {{ $speciality->name }}
                            </span>
                        </div>
                        
                    </div>
                    <a href="#" class="btn modern-back-btn" onclick="history.back()">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>

            <div class="card-body modern-body">
                @if ($pdfs->isEmpty())
                    <div class="card empty-state-card">
                        <div class="card-body text-center py-5">
                            <div class="empty-icon-wrapper">
                                <i class="fas fa-file-excel empty-icon"></i>
                            </div>
                            <h4 class="empty-title">Aucun fichier disponible</h4>
                            <p class="empty-subtitle">Aucun document PDF n'est disponible pour cette spécialité actuellement.</p>
                            <button class="btn modern-empty-btn" onclick="history.back()">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </button>
                        </div>
                    </div>
                @else
                    <div class="card modern-list-card">
                        <div class="card-header modern-list-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list-ul me-2 list-icon"></i>
                                Documents disponibles ({{ count($pdfs) }})
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group modern-list-group">
                                @foreach ($pdfs as $pdf)
                                    <div class="list-group-item modern-list-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="pdf-icon-wrapper">
                                                    <i class="fas fa-file-pdf pdf-icon"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 ml-2 pdf-title">{{ $pdf['name'] }}</h6>
                                                </div>
                                            </div>
                                            <div>
                                              <a href="{{ route('consultation_perso.open_pdf', ['patient' => $patient->id, 'pdf' => $pdf['name']]) }}" class="btn modern-start-btn">
    <i class="fas fa-play-circle me-1"></i> Commencer
</a>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer modern-footer">
                            <small class="footer-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Cliquez sur "Commencer" pour ouvrir le document dans un nouvel onglet
                            </small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Styles généraux améliorés */
        .modern-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 31, 63, 0.08);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-top: 50px;
        }

        .modern-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(0, 31, 63, 0.12);
        }

        .modern-header {
            background: linear-gradient(135deg, #f5f5f5 0%, #f5f5f5 100%);
            color: #001f3f;
            border: none;
            padding: 2rem;
        }

        .modern-body {
            padding: 2rem;
        }

        /* Titre principal */
        .wic-title h2 {
            color: #001f3f;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            margin-bottom: 15px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .medical-icon {
            color: #001f3f;
            margin-right: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Badge moderne */
        .modern-badge {
                        color: #365070ff;
            transition: all 0.3s ease;
            border: 1px solid #365070ff;
            background: #c4d1e1;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
        }

        /* Bouton retour */
        .modern-back-btn {
            background: white;
            color: #001f3f;
            border: 2px solid #001f3f;
            border-radius: 25px;
            padding: 10px 20px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .modern-back-btn:hover {
            background: white;
            color: #001f3f;
            transform: translateX(-3px);
        }

        /* État vide amélioré */
        .empty-state-card {
            border: none;
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(255, 152, 0, 0.1);
        }

        .empty-icon-wrapper {
            margin-bottom: 20px;
        }

        .empty-icon {
            color: #ff9800;
            font-size: 4rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .empty-title {
            color: #e65100;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .empty-subtitle {
            color: #bf360c;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .modern-empty-btn {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3);
            transition: all 0.3s ease;
        }

        .modern-empty-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
            color: white;
        }

        /* Liste des documents */
        .modern-list-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 31, 63, 0.06);
        }

        .modern-list-header {
background: linear-gradient(135deg, #f5f5f5 0%, #f5f5f5 100%);
            color: #001f3f;
            border: none;
            padding: 20px;
        }

        .list-icon {
            color: #001f3f;
        }

        .modern-list-group {
            border-radius: 0;
        }

        .modern-list-item {
            border: none;
            border-bottom: 1px solid #e3f2fd;
            padding: 20px;
            transition: all 0.3s ease;
            background: white;
        }

        .modern-list-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f8f9fa 100%);
            transform: translateX(5px);
            box-shadow: inset 5px 0 0 #577daa;
        }

        .modern-list-item:last-child {
            border-bottom: none;
        }

        /* Icône PDF */
        .pdf-icon-wrapper {
            margin-right: 15px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.2);
        }

        .pdf-icon {
            color: white;
            font-size: 1.5rem;
        }

        .pdf-title {
            color: #001f3f;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Bouton commencer */
        .modern-start-btn {
            background: linear-gradient(135deg, #577daa 0%, #577daa 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modern-start-btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        /* Footer moderne */
        .modern-footer {
            background: linear-gradient(135deg, #f5f5f5 0%, #f5f5f5 100%);
            text-align: center;
            padding: 15px;
            border: none;
        }

        .footer-text {
            color: #666;
            font-style: italic;
        }

        /* Styles existants conservés */
        .feature-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #c4d1e1;
            border-radius: 25px;
            color: #365070ff;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #365070ff;
        }

        .feature-item:hover {
            background: #c4d1e1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px #a9bed8ff;
        }

        .feature-item i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Responsive amélioré */
        @media (max-width: 768px) {
            .modern-header {
                padding: 1.5rem;
            }
            
            .modern-body {
                padding: 1.5rem;
            }
            
            .wic-title h2 {
                font-size: 1.5rem;
            }
            
            .modern-list-item {
                padding: 15px;
            }
            
            .pdf-icon-wrapper {
                width: 40px;
                height: 40px;
                margin-right: 10px;
            }
            
            .pdf-icon {
                font-size: 1.2rem;
            }
        }
    </style>

    <script>
        // Activer les tooltips Bootstrap
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection