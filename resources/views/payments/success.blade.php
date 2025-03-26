<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Paiement Réussi') }}</title>
</head>
<body>
    <div style="text-align: center; margin-top: 50px;">
        <h1>{{ __('Paiement Réussi') }}</h1>
        <p>{{ __('Votre paiement a été traité avec succès. Merci de votre confiance !') }}</p>
        <a href="{{ url('/') }}">{{ __('Retourner à la page d\'accueil') }}</a>
    </div>
</body>
</html>

