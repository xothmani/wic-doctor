<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue chez Wic-Doctor</title>
</head>
<body style="background-color:#ffffff; padding: 20px; font-family: Helvetica, Arial, sans-serif;">
    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; max-width: 600px; margin: 0 auto;">
        <tr>
            <td style="text-align: center;">
                <img src="https://wic-doctor.com/assets/images/wic-dr-logo.png" alt="Logo" style="max-width: 150px;">
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #565a5c; text-align: left;">
                <p>🌟 Bonjour {{ $userName }} {{ $userLastname }} !</p>
                <p>Nous sommes heureux de vous accueillir chez Wic-Doctor. Voici vos informations :</p>
                <p><strong>Votre email :</strong> {{ $email }}</p>
                <p><strong>Votre mot de passe :</strong> {{ $generatedPassword }}</p>
                <p>Vous pouvez utiliser ce mot de passe pour accéder à votre compte patient via ce lien : <a href="https://wic-doctor.com/login.html">Se connecter</a></p>
            </td>
        </tr>
        
        <tr>
            <td style="font-weight: bold; text-align: left;">
                <br> 📌 Préparez-vous en amont : <br>
                ✅ Pensez à votre Carnet de CNAM et documents médicaux <br>
                ✅ Ordonnances, examens ou déclarations médicales si nécessaires <br>
                ✅ Liste de vos symptômes et questions pour une consultation efficace <br>
                🔔 Un rappel vous sera envoyé avant votre rendez-vous. <br>
            </td>
        </tr>
        
        <tr>
            <td style="color: #565a5c; text-align: center;">
                <p><strong>Lien pour prendre rendez-vous avec votre médecin:</strong> <a href="{{ $shortUrl }}">{{ $shortUrl }}</a></p> <!-- Ajout du lien court -->
                <br> ✨ Merci de votre confiance, à très bientôt !  
                🌿 L'équipe WIC Docteur 🌿👨‍⚕
            </td>
        </tr>
    </table>
</body>
</html>
