<!DOCTYPE html>
<html lang="fr">
<head>
    <style>
        body {
            margin: 0;
            padding: 50px;
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .email-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #11B8AA 0%, #0ea5a5 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .logo {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            color: #11B8AA;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
            background-color : #f1f5f9;
        }
        .greeting {
            font-size: 18px;
            color: #053178;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 40px;
        }
        .download-section {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }
        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .download-button {
            display: inline-block;
            background: linear-gradient(135deg, #053178 0%, #0641a1 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(5, 49, 120, 0.3);
        }
        .download-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 49, 120, 0.4);
        }
        .alternative-text {
            font-size: 14px;
            color: #666;
            margin-top: 15px;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #11B8AA, transparent);
            margin: 0 30px 0 0px;
        }
        .footer {
            background-color: #053178;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .footer-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .footer-text {
            font-size: 14px;
            opacity: 0.8;
            line-height: 1.5;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            color: #11B8AA;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
            .download-section {
                padding: 20px;
            }
            .qr-code {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <img src="{{ $logoSquare }}" width="100px" height="auto"/>
            </div>
        </div>
        
        <div class="content">
            <div class="greeting">
                Bonjour {{ $toUserName }},
            </div>
            
            <div class="message">
                Votre fichier médical est maintenant prêt et sécurisé. Vous pouvez le télécharger facilement en utilisant le QR code ci-dessous ou en cliquant sur le bouton de téléchargement.
            </div>
            
            <div class="download-section">
                <div class="qr-code">
                    <div><img src= "{{ $qrImageUrl }}" width="300px" height="300px"/></div>
                </div>
                
                <a href="{{ $downloadUrl }}" class="download-button">
                    📥 Télécharger le fichier
                </a>
                
                <div class="alternative-text">
                    Ou scannez le QR code avec votre smartphone
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-logo">
                <img src="{{ $logoWhite }}" width="100px" height="auto"/>
            </div>
            <div class="footer-text">
                Votre plateforme médicale sécurisée<br>
                Pour toute assistance : contact@wic-doctor.com<br>
                © 2025 WIC DOCTOR - Tous droits réservés
            </div>
            <div class="social-links">
                <a href="https://wic-doctor.com/contact.html">Contact</a> | 
                <a href="https://wic-doctor.com/confidentialite.html">Confidentialité</a> | 
                <a href="https://wic-doctor.com/CGU.pdf">Conditions</a>
            </div>
        </div>
    </div>
</body>
</html>
