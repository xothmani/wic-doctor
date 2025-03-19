<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $details["title"] ?? "Wic-Doctor Paiement Téléconsultation" }}</title>
</head>
<body style="background-color:#ffffff; padding: 20px; font-family: Helvetica, Arial, sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; max-width: 600px; margin: 0 auto;">
        <!-- Logo -->
        <tr>
            <td style="text-align: center;">
                <img src="https://wic-doctor.com/assets/images/wic-dr-logo.png" alt="Logo WIC Doctor" style="max-width: 150px;">
            </td>
        </tr>

        <!-- Message principal -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p>🌟 Cher patient, <strong>{{ $details["patient_name"] ?? "Patient" }}</strong>,</p>
                <p>Nous vous prions de trouver ci-dessous les informations relatives à votre téléconsultation :</p>
            </td>
        </tr>

        <!-- Détails consultation -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <h2>📅 Détails de votre téléconsultation :</h2>
                <p><strong>🕒 Date et Heure :</strong> {{ $details["date"] ?? "Non spécifié" }} à {{ $details["time"] ?? "Non spécifié" }}</p>
                <p><strong>👨‍⚕️ Docteur :</strong> Dr. {{ $details["doctor_name"] ?? "Non spécifié" }}</p>
            </td>
        </tr>

        <!-- Paiement TND -->
        @if ($details['tele_price_tnd'])
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p><strong>💵 Montant (TND) :</strong> <span style="color: #2d6dfd;">{{ $details['tele_price_tnd'] }} TND</span></p>
                <p><strong>🔗 Paiement Konnect :</strong> <a href="{{ $details['apiPaymentLink'] }}" style="color: #2d6dfd;" target="_blank">{{ $details['apiPaymentLink'] }}</a></p>
            </td>
        </tr>
        @endif

        <!-- Paiement EUR -->
        @if ($details['tele_price_eur'])
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p><strong>💶 Montant (EUR) :</strong> <span style="color: #2d6dfd;">{{ $details['tele_price_eur'] }} EUR</span></p>
                <p><strong>💳 PayPal :</strong> <a href="{{ $details['paypalLink'] }}" style="color: #2d6dfd;" target="_blank">{{ $details['paypalLink'] }}</a></p>
            </td>
        </tr>
        @endif

        <!-- Remerciements -->
        <tr>
            <td style="color: #565a5c; text-align: center;">
                <br> ✨ Merci d'utiliser Wic-Doctor !  
                🌿 L'équipe WIC Doctor 🌿👨‍⚕
            </td>
        </tr>
    </table>
</body>
</html>
