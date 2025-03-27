<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $details["title"] ?? "Wic-Doctor - Création de comptes Télésécrétariat" }}</title>
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
                <p>🌟 Bonjour {{ $details["name"] ?? "Utilisateur" }},</p>
                <p>Nous sommes ravis de vous accueillir sur Wic-Doctor ! Voici les informations pour accéder à vos comptes :</p>
            </td>
        </tr>

        <!-- Compte Wic-Doctor -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <h2>📧 Compte Wic-Doctor :</h2>
                <p><strong>Email :</strong> {{ $details["email"] ?? "Non spécifié" }}</p>
                <p><strong>Mot de passe :</strong> {{ $details["password"] ?? "Non spécifié" }}</p>
                <p>Pour accéder à votre compte Wic-Doctor, veuillez cliquer sur le lien ci-dessous :</p>
                <p style="text-align: center;">
                    <a href="https://dashboard.wic-doctor.com/login" style="color: #2d6dfd;">Accéder à mon compte Wic-Doctor</a>
                </p>
            </td>
        </tr>

        <!-- Compte Fusion -->
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <h2>🔑 Compte Fusion :</h2>
                <p><strong>Nom d'utilisateur :</strong> {{ $details["username"] ?? "Non spécifié" }}</p>
                <p><strong>Mot de passe :</strong> {{ $details["passwordFusion"] ?? "Non spécifié" }}</p>
                <p><strong>Hôte :</strong> {{ $details["host"] ?? "Non spécifié" }}</p>
                <p>Pour vous connecter à votre compte Fusion, veuillez cliquer sur le lien ci-dessous :</p>
                <p style="text-align: center;">
                    <a href="{{ $details["host"] }}" style="color: #2d6dfd;">Accéder à mon compte Fusion</a>
                </p>
            </td>
        </tr>

        <!-- Remerciements -->
        <tr>
            <td style="color: #565a5c; text-align: center;">
                <br>✨ Merci d'utiliser Wic-Doctor !  
                🌿 L'équipe WIC Doctor 🌿👨‍⚕
            </td>
        </tr>
    </table>
</body>
</html>
