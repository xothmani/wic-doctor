<!DOCTYPE html>
<html>
<head>
    <title>Création de votre compte Telesecretariat</title>
</head>
<body>
    <p>Bonjour {{ $details['name'] }},</p>
    <p>Votre compte Telesecretariat a été créé avec succès.</p>
    <p>Voici vos identifiants de connexion :</p>
    <ul>
        <li>Email : {{ $details['email'] }}</li>
        <li>Mot de passe : {{ $details['password'] }}</li>
    </ul>
    <p>Veuillez changer votre mot de passe après votre première connexion.</p>
    <p>Merci,</p>
    <p>L'équipe de Wic Dr.</p>
</body>
</html>
