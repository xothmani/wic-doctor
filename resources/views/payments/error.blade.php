<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Paiement Échoué') }}</title>
</head>
<body>
    <div style="text-align: center; margin-top: 50px;">
        <h1>{{ __('Paiement Échoué') }}</h1>
        <p>{{ __('Nous avons rencontré un problème lors du traitement de votre paiement. Veuillez réessayer.') }}</p>
        <a href="{{ url('/') }}">{{ __('Retourner à la page d\'accueil') }}</a>
    </div>
</body>
</html>

