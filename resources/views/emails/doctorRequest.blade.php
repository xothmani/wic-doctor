<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue chez WICDR</title>
</head>
<body style="background-color:#ffffff; padding: 20px; font-family: Helvetica, Arial, sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; max-width: 600px; margin: 0 auto;">
        <!-- Logo -->
        <tr>
            <td style="text-align: center;">
                <img src="https://wic-doctor.com/assets/images/wic-dr-logo.png" alt="Logo WIC Doctor" style="max-width: 150px;">
            </td>
        </tr>

        <!-- Message d'accueil -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p>🌟 Bonjour Dr {{ $doctor->name }},</p>
                <p>Bienvenue chez Wic-Doctor ! Nous sommes ravis de vous compter parmi nous.</p>
                <p>Voici les informations nécessaires pour vous connecter à vos comptes :</p>
            </td>
        </tr>

        <!-- Compte Docteur -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <h2>Compte Docteur :</h2>
                <p><strong>Identifiant :</strong> {{ $doctor->user->email }}</p>
                <p><strong>Mot de passe :</strong> {{ $doctorPassword }}</p>
                <p><a href="https://dashboard.wic-doctor.com/login" style="color: #2d6dfd;">🔑 Se connecter en tant que Docteur</a></p>
            </td>
        </tr>

        <!-- Compte Patient -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <h2>Compte Patient :</h2>
                <p><strong>Identifiant :</strong> {{ $doctor->user->email }}</p>
                <p><strong>Mot de passe :</strong> {{ $patientPassword }}</p>
                <p><a href="https://wic-doctor.com/login.html" style="color: #2d6dfd;">🔑 Se connecter en tant que Patient</a></p>
            </td>
        </tr>

        <!-- Accès Gratuit -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p>Vous bénéficiez de 10 jours d'accès gratuits pour découvrir notre plateforme. Nous espérons que vous apprécierez l'expérience.</p>
            </td>
        </tr>

        <!-- Guide et Assistance -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p>Vous trouverez également le <strong>Guide pour personnaliser et compléter votre profil</strong> sur WIC Doctor en pièce jointe.</p>
                <p>Si vous avez la moindre question ou besoin d'assistance, n'hésitez pas à nous contacter.</p>
            </td>
        </tr>

        <!-- Remerciements -->
        <tr>
            <td style="color: #565a5c; text-align: center;">
                <br> ✨ Merci de votre confiance, à très bientôt !  
                🌿 L'équipe WIC Doctor 🌿👨‍⚕
            </td>
        </tr>
    </table>
</body>
</html>
