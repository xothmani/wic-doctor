<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
         @page {
            size: A5 portrait;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            width: 14.8cm;
            height: 21cm;
            margin: auto;
            display: flex;
            flex-direction: column;
            margin-top: 30%;
   
        }
        .patient-info, .prescription, .instructions, .footer {
            width: 90%;
            margin-bottom: 20px;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <main>
        <section class="patient-info">
            <p>Mr/Mme {{ $patient_name }}</p>
        </section>

        <section class="prescription">
            @if ($type === 'Médicament')
                <h3>Médicaments</h3>
                @foreach ($medicaments as $medicament)
                    <p>{{ $medicament['nom_commercial'] }} - {{ $medicament['dosage'] }}, {{ $medicament['nb_de_fois'] }} fois, {{ $medicament['horaire'] }} pendant {{ $medicament['nb_de_jours'] }} jours.</p>
                @endforeach
            @elseif($type === 'Analyse')
                <h3>Analyses</h3>
                @foreach ($analyses as $analyse)
                    <p>{{ $analyse['Code_Analyse'] }}</p>
                @endforeach
            @elseif($type === 'Radio')
                <h3>Radios</h3>
                @foreach ($radios as $radio)
                    <p>{{ $radio['Nom'] }}</p>
                @endforeach
            @else
                <h3>{{ $type }}</h3>
                @foreach ($other_treatments as $treatment)
                    <p>{{ $treatment }}</p>
                @endforeach
            @endif
        </section>

        <section class="instructions">
            <p><strong>{{ $observation }}</strong></p>
        </section>
    </main>

    <footer class="footer">
        <div>
            @if ($type === 'Médicament')
                <strong>Total Médicaments:</strong> {{ $nombre_medicaments }}
            @elseif ($type === 'Analyse')
                <strong>Total Analyses:</strong> {{ $nombre_analyses }}
            @elseif ($type === 'Radio')
                <strong>Total Radios:</strong> {{ $nombre_radios }}
            @else
                <strong>Total {{ $type }}:</strong> {{ count($other_treatments) }}
            @endif
        </div>
        <div class="signature">
            <p>Signature</p>
            <p>____________________</p>
        </div>
    </footer>
</body>
</html>