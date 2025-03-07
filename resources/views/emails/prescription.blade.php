<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Médicale</title>
</head>

<body style="background-color: #f9f9f9; padding: 20px; font-family: Arial, Helvetica, sans-serif; color: #555;">
    <table width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; padding: 20px;">
        <!-- Logo -->
        <tr>
            <td style="text-align: center;">
                <img src="https://wic-doctor.com/assets/images/wic-dr-logo.png" alt="Logo WIC Doctor" style="max-width: 150px;">
            </td>
        </tr>

        <!-- Titre -->
        <tr>
            <td style="text-align: center; font-size: 24px; font-weight: bold; color: #2d6dfd; padding-top: 10px;">
                📝 Votre Prescription Médicale 📜
            </td>
        </tr>

        <!-- Message principal -->
        <tr>
            <td style="font-size: 18px; margin-top: 20px;">
                <p>Bonjour {{ $patientName }},</p>
                <p>Nous avons le plaisir de vous envoyer votre prescription médicale du <strong>{{ $prescriptionDate }}</strong>. 💊</p>
                <p>Vous pouvez la consulter en pièce jointe. 📎</p>
            </td>
        </tr>

        <!-- Rappel important -->
        <tr>
            <td style="font-size: 16px; margin-top: 20px; color: #565a5c;">
                <p>Si vous avez des questions concernant votre prescription ou si vous avez besoin d'informations supplémentaires, n'hésitez pas à contacter votre médecin. 📞</p>
            </td>
        </tr>

        <!-- Conclusion -->
        <tr>
            <td style="font-size: 16px; margin-top: 20px; color: #565a5c; text-align: center;">
                <p>Nous vous souhaitons une bonne santé et un prompt rétablissement ! 🌱</p>
                <p><strong>🌿 L'équipe WIC Doctor 🌿👨‍⚕</strong></p>
            </td>
        </tr>
    </table>
</body>

</html>