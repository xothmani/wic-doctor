<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi</title>
</head>

<body style="background-color: #f9f9f9; padding: 20px; font-family: Arial, Helvetica, sans-serif; color: #555;">
    <table width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 20px;">
        <!-- Titre -->
        <tr>
            <td style="text-align: center; font-size: 24px; font-weight: bold; color: #2d6dfd;">
                🎉 Paiement effectué avec succès ! 🎉
            </td>
        </tr>

        <!-- Message -->
        <tr>
            <td style="font-size: 18px; margin-top: 20px;">
                <p>Cher(e) {{ $paymentDetails['patient_name'] }},</p>
                <p>Merci pour votre paiement. Nous avons bien reçu votre transaction et celle-ci a été réalisée avec succès. 🏦💳</p>
            </td>
        </tr>

        <!-- Détails de la transaction -->
        <tr>
            <td style="font-size: 16px; margin-top: 20px; color: #565a5c;">
                <p><strong>📋 Détails de votre paiement :</strong></p>
                <ul style="list-style-type: none; padding-left: 0;">
                    <li>💰 <strong>Montant :</strong> {{ $paymentDetails['amount'] }} {{ $paymentDetails['currency'] }}</li>
                    <li>🔗 <strong>Lien de téléconsultation :</strong> <a href="{{ $paymentDetails['description'] }}" style="color: #2d6dfd;">Cliquez ici pour accéder à votre consultation</a></li>
                    <li>📅 <strong>Date :</strong> {{ $paymentDetails['start_at'] }}</li>
                </ul>
            </td>
        </tr>

        <!-- Conclusion -->
        <tr>
            <td style="font-size: 16px; color: #565a5c;">
                <p>Si vous avez des questions ou besoin d’assistance, n’hésitez pas à nous contacter. Nous sommes là pour vous aider ! 🤝</p>
                <p>Nous vous remercions de votre confiance. 🙏</p>
            </td>
        </tr>

        <!-- Signature -->
        <tr>
            <td style="text-align: center; font-size: 16px; font-weight: bold; margin-top: 30px; color: #2d6dfd;">
                🌟 L'équipe WIC Doctor 🌟
            </td>
        </tr>
    </table>
</body>

</html>
