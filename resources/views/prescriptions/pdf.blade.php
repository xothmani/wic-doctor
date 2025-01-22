<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            max-width: 21cm;
            margin: 0 auto;
        }
        
        .header-left h1, .header-left p, .header-right p {
            margin: 0;
        }
        .patient-info {
            margin-bottom: 40px;
        }
        .medications {
            margin-top: 20px;
        }
        .instructions {
            margin-top: 20px;
            font-style: italic;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
            font-size: 1.1em;
        }
        .signature {
            text-align: right;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="header-left">
        <h1>Dr. {{ $user_name }}</h1>
        <table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 0; text-align: left;">Médecin {{ json_decode($doctor_speciality)->{app()->getLocale()} ?? '' }}</td>
        <td style="padding: 0; text-align: right;">{{ json_decode($doctor_address)->{app()->getLocale()} ?? '' }}</td>
    </tr>
</table>


        <div style="border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px;">
        <table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td style="padding: 0; text-align: left;">{{ $diplome }}</td>
        <td style="padding: 0; text-align: right;"><strong>{{ trans('lang.tel') }}</strong>{{ $doctor_phone }}</td>
    </tr>
    <tr>
        <td style="padding: 0; text-align: left;">{{ trans('lang.numOrdre') }}: {{ $numOrdre }}</td>
    </tr>
</table>
        </div>
    </div>
</header>
@if(!empty($matricule_cnam))
<div style="text-align: center; margin-top: 30px;">
    <p><strong>{{ trans('lang.matricule_CNAM') }}: </strong>{{ $matricule_cnam }}</p>
</div>
@endif



    <div style="text-align: right; margin-top: 40px;">
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
