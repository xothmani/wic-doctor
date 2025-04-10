<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation exclusive – Rejoignez WicDoctor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            max-width: 600px;
            background: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .button {
            display: block;
            width: fit-content;
            margin: 20px auto;
            background-color: #4da6ff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .button:hover {
            background-color: #3399ff;
        }
        ul {
            text-align: left;
            display: inline-block;
        }
        .qrcode {
    margin: 15px auto;
    padding: 10px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    display: inline-block;
    text-align: center;
}

        .link-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 20px auto;
            max-width: 80%;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="https://wic-doctor.com/inscription-professionnel/assets/images/wic-dr-logo.png" alt="WicDoctor Logo" class="logo">
        
        <h2>📢 Invitation exclusive – Rejoignez WicDoctor et profitez du parrainage !</h2>
        
        <p>Cher(e) collègue,</p>
        
        <p><strong>{{ $senderName }}</strong> vous invite à rejoindre <strong>Wic-Doctor</strong>, la plateforme innovante dédiée aux professionnels de santé.</p>
        
        <p>Avec Wic-Doctor, simplifiez la gestion de votre activité et améliorez votre communication avec vos patients grâce à des outils performants.</p>
        
        <h3>🎁 Offre spéciale : Profitez du programme de parrainage !</h3>
        
        <p>En rejoignant Wic-Doctor via ce lien, vous bénéficiez d'avantages exclusifs et permettez à votre parrain de recevoir également des avantages.</p>
        
        <div style="text-align: center;">
    <h4>Scannez ce QR Code pour accéder directement :</h4>
    <div class="qrcode">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($link) }}" alt="QR Code">
    </div>

    <a href="{{ $link }}" class="button" style="color: white; background-color: #3498db; text-decoration: none; font-weight: bold;">
        Inscrivez-vous dès maintenant
    </a>
</div>
 
        
        <h3>🚀 En vous inscrivant, vous aurez accès à :</h3>
        <ul>
            <li>✅ Un agenda en ligne pour une gestion optimisée de vos rendez-vous</li>
            <li>✅ Un espace de téléconsultation sécurisé et conforme aux normes de santé</li>
            <li>✅ Une visibilité accrue dans notre annuaire médical</li>
            <li>✅ Des outils de communication avancés (SMS, notifications, etc.)</li>
            <li>✅ Un système de parrainage avantageux</li>
        </ul>
        
        <p>Nous serions ravis de vous compter parmi notre communauté de médecins !</p>
        
        <p>Cordialement,</p>
        <p>L'équipe Wic-Doctor</p>
    </div>
</body>
</html>