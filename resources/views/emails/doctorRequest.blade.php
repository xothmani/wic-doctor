<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue chez WICDR</title>
</head>
<body>
<h1>Bonjour Dr {{ $doctor->name }},</h1>

<p>Bienvenue chez WIC Doctor ! Nous sommes heureux de vous accueillir parmi nous.</p>

<p>Voici vos informations de connexion :</p>

<h2>Compte Docteur :</h2>
<p><strong>Login :</strong> {{ $doctor->user->email }}</p> <!-- Affiche l'email du docteur -->
<p><strong>Mot de passe :</strong> {{ $doctorPassword }}</p> <!-- Affiche le mot de passe du docteur -->

<h2>Compte Patient :</h2>
<p><strong>Login :</strong> {{ $doctor->user->email }}</p> <!-- Réutilise le même email -->
<p><strong>Mot de passe :</strong> {{ $patientPassword }}</p> <!-- Affiche le mot de passe pour le compte patient -->

<p>Pour vous connecter à votre compte, cliquez sur les liens suivants :</p>
<ul>
    <li><strong>Compte Docteur :</strong> <a href="https://dashboard.wic-doctor.com/login">Se connecter en tant que Docteur</a></li>
    <li><strong>Compte Patient :</strong> <a href="https://wic-doctor.com/login.html">Se connecter en tant que Patient</a></li>
</ul>

<p>Vous avez un accès de 10 jours gratuits pour tester notre système. Nous espérons que vous apprécierez l'expérience !</p>

<p>Si vous avez des questions ou besoin d'aide, n'hésitez pas à nous contacter.</p>

<p>Bien cordialement,</p>
<p>L'équipe WIC Doctor</p>
</body>
</html>
