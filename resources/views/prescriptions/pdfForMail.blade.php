<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /* Définir la taille de la page en A5 */
        @page {
            size: A5;
            margin: 1cm; /* Marges de 1 cm */
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            max-width: 148mm; /* Largeur A5 */
            height: 210mm; /* Hauteur A5 */
            margin: 0 auto;
            font-size: 12px; /* Taille de police réduite */
        }

        .header-left h1, .header-left p, .header-right p {
            margin: 0;
            font-size: 1.2em; /* Taille de police ajustée */
        }

        .patient-info {
            margin-bottom: 20px; /* Espacement réduit */
        }

        .medications, .instructions {
            margin-top: 10px; /* Espacement réduit */
        }

        .instructions {
            font-style: italic;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px; /* Espacement réduit */
            font-size: 1em; /* Taille de police ajustée */
        }

        .signature {
            text-align: right;
            margin-top: 20px; /* Espacement réduit */
        }

        h1 {
            font-size: 1.5em; /* Taille de police ajustée */
        }

        h2 {
            font-size: 1.3em; /* Taille de police ajustée */
        }

        p {
            margin: 5px 0; /* Espacement réduit */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px 0; /* Espacement réduit */
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <h1>Dr. {{ $user_name }}</h1>
            <table>
                <tr>
                    <td style="text-align: left;">Médecin {{ json_decode($doctor_speciality)->{app()->getLocale()} ?? '' }}</td>
                    <td style="text-align: right;">{{ json_decode($doctor_address)->{app()->getLocale()} ?? '' }}</td>
                </tr>
            </table>
            <div style="border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px;">
                <table>
                    <tr>
                        <td style="text-align: left;">{{ $diplome }}</td>
                        <td style="text-align: right;"><strong>{{ trans('lang.tel') }}</strong>{{ $doctor_phone }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">{{ trans('lang.numOrdre') }}: {{ $numOrdre }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </header>

    @if(!empty($matricule_cnam))
    <div style="text-align: center; margin-top: 15px;">
        <p><strong>{{ trans('lang.matricule_CNAM') }}: </strong>{{ $matricule_cnam }}</p>
    </div>
    @endif

    <div style="text-align: right; margin-top: 20px;">
        <p><strong>Le </strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
    </div>

    <main>
        <section class="patient-info">
            <p>Mr/Mme {{ $patient_name }}</p>
        </section>

        <section class="prescription">
            @if ($type === 'Médicament')
                <h2>Médicaments</h2>
                <div class="medications">
                    @foreach ($medicaments as $medicament)
                        <p>{{ $medicament['nom_commercial'] }} - {{ $medicament['dosage'] }}, {{ $medicament['nb_de_fois'] }} fois, {{ $medicament['horaire'] }} pendant {{ $medicament['nb_de_jours'] }} jours.</p>
                    @endforeach
                </div>
            @elseif($type === 'Analyse')
                <h2>Analyses</h2>
                <div class="medications">
                    @foreach ($analyses as $analyse)
                        <p>{{ $analyse['Code_Analyse'] }}</p>
                    @endforeach
                </div>
            @elseif($type === 'Radio')
                <h2>Radios</h2>
                <div class="medications">
                    @foreach ($radios as $radio)
                        <p>{{ $radio['Nom'] }}</p>
                    @endforeach
                </div>
            @else
                <h2>{{ $type }}</h2>
                <div class="other-treatments">
                    @foreach ($other_treatments as $treatment)
                        <p>{{ $treatment }}</p>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="instructions">
            <p><strong>{{ $observation }}</strong></p>
        </section>
    </main>

    <footer class="footer">
        <div>
            @if ($type === 'Médicament')
                <strong>{{ trans('lang.total_medicaments') }}</strong> {{ $nombre_medicaments }}
            @elseif ($type === 'Analyse')
                <strong>{{ trans('lang.total') }} {{ $type }}</strong> {{ $nombre_analyses }}
            @elseif ($type === 'Radio')
                <strong>{{ trans('lang.total') }} {{ $type }}</strong> {{ $nombre_radios }}
            @else
                <strong>{{ trans('lang.total') }} {{ $type }}:</strong> {{ count($other_treatments) }}
            @endif
        </div>
        <div class="signature">
            <p>{{ trans('lang.signature') }}</p>
            <p>____________________</p>
        </div>
    </footer>
</body>
</html>