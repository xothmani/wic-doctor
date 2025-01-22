<!DOCTYPE html>
<html>

<head>
  <title>{{ $details["title"] ?? "No Title" }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
    }

    h1 {
      color: #333;
    }

    p,
    li {
      font-size: 16px;
    }

    ul {
      padding-left: 20px;
    }
  </style>
</head>

<body>
  <h1>{{ $details["title"] ?? "Wic-Doctor Payment Téléconsultation" }}</h1>
  <p>
    Cher patient, <strong>{{ $details["patient_name"] ?? "Patient" }}</strong>,
  </p>
  <p>Voici les liens de paiement de votre téléconsultation:</p>
  <ul>
    <li>
      <strong>Date et heure :</strong>
      {{ $details["date"] ?? "Non spécifié" }} à
      {{ $details["time"] ?? "Non spécifié" }}
    </li>
    <li>
      <strong>Docteur :</strong> Dr.
      {{ $details["doctor_name"] ?? "Non spécifié" }}
    </li>
  </ul>
  @if ($details['tele_price_tnd'])
    <p>
    <strong>Montant (TND) :</strong>
    <span class="amount">{{ $details['tele_price_tnd'] }} TND</span>
    </p>
    <p>
    <strong>Konnect :</strong>
    <a href="{{ $details['apiPaymentLink'] }}" target="_blank">{{ $details['apiPaymentLink'] }}</a>
    </p>
  @endif

  @if ($details['tele_price_eur'])
    <p>
    <strong>Montant (EUR) :</strong>
    <span class="amount">{{ $details['tele_price_eur'] }} EUR</span>
    </p>
    <p>
    <strong>PayPal :</strong>
    <a href="{{ $details['paypalLink'] }}" target="_blank">{{ $details['paypalLink'] }}</a>
    </p>
  @endif
  <p>Merci d'utiliser <strong>Wic-Doctor</strong>.</p>
</body>

</html>
