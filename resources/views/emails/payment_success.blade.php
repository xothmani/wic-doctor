<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi</title>
</head>

<body>
    <h1>Paiement effectué avec succès !</h1>
    <p>Cher/Chère {{ $paymentDetails['patient_name'] }},</p>
    <p>Merci pour votre paiement. Votre transaction a été réalisée avec succès.</p>
    <p>Détails :</p>
    <ul>
        <li>Montant : {{ $paymentDetails['amount'] }} {{ $paymentDetails['currency'] }}</li>
        <li>Lien de téléconsultation : {{ $paymentDetails['description'] }}</li>
        <li>Date : {{ $paymentDetails['start_at'] }}</li>
    </ul>
    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
</body>

</html>
