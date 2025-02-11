<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Création de votre compte Télésécrétariat - Wic-Doctor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .content {
            margin-top: 20px;
            font-size: 16px;
            color: #555;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
            text-align: center;
        }
        .icon {
            margin-right: 8px;
        }
        .email-info {
            color: #333;
        }
        .icon-inline {
            display: inline-flex;
            align-items: center;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <p class="header">Bienvenue sur Wic-Doctor ! <span class="icon-inline">🚀</span></p>
        <p class="content">Bonjour {{ $details['name'] }},</p>
        <p class="content">Nous sommes ravis de vous accueillir sur Wic-Doctor ! Profitez dès à présent de votre expérience en télésécrétariat et de tous nos services.</p>
        <p class="content"><span class="icon-inline">🔗</span>Voici vos identifiants de connexion :</p>
        
        <div class="credentials">
            <p><span class="icon-inline">📧</span><strong>Email :</strong> <span class="email-info">{{ $details['email'] }}</span></p>
            <p><span class="icon-inline">🔑</span><strong>Mot de passe :</strong> {{ $details['password'] }}</p>
        </div>

        <p class="content">Cliquez sur le bouton ci-dessous pour accéder à votre compte :</p>
        <p style="text-align: center;">
            <a href="https://dashboard.wic-doctor.com/login" class="btn" style="color: white;">Accéder à mon compte</a>
        </p>

        <p class="footer">Merci, et à bientôt sur Wic-Doctor !<br>L'équipe de Wic-Doctor.</p>
    </div>
</body>
</html>
