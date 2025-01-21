<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue chez Wic-Doctor</title>
</head>
<body>
    <h1>Bienvenue, {{ $userName }} {{ $userLastname }} !</h1>
    <p>Nous sommes heureux de vous accueillir chez Wic-Doctor. Voici vos informations :</p>
    <p><strong>Votre email :</strong> {{ $email }}</p> <!-- Affichage de l'email -->
    <p><strong>Votre mot de passe :</strong> {{ $generatedPassword }}</p>
    <p>Vous pouvez utiliser ce mot de passe pour accéder à votre compte patient via ce lien: <a href="https://wic-doctor.com/login.html">Se connecter </a>
    </p>
    <p>Merci de votre confiance,</p>
    <p>L'équipe Wic-Doctor</p>
</body>
</html>
