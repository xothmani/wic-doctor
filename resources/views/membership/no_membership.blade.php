<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé</title>
    <style>
        /* Basic styles */
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
            background: #f9f9f9;
            color: #333;
            overflow: hidden;
        }
        h1 {
            color: #e74c3c;
            font-size: 36px;
            margin-top: 50px;
            opacity: 0;
            animation: fadeIn 1s forwards;
        }
        p {
            font-size: 18px;
            margin: 20px auto;
            max-width: 600px;
            opacity: 0;
            animation: fadeIn 1.5s forwards;
        }
        a {
            text-decoration: none;
            color: #ffffff;
            background-color: #3498db;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 18px;
            display: inline-block;
            margin-top: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 0;
            animation: fadeIn 2s forwards, pulse 1.5s infinite;
        }
        a:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }

        /* Keyframe Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.03);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Spinner animation */
        #spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #3498db;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <h1>Accès Refusé</h1>
    <p>Votre abonnement a expiré ou est inactif. Veuillez renouveler votre abonnement pour accéder à cette section.</p>

    <!-- Hidden logout form -->
    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Logout and Redirect Button -->
    <a href="#" onclick="logoutAndRedirect()">Renouveler l'Abonnement</a>

    <!-- Spinner -->
    <div id="spinner"></div>

    <!-- JavaScript for Logout and Redirect -->
    <script>
        function logoutAndRedirect() {
            const spinner = document.getElementById('spinner');
            const link = document.querySelector('a');
            link.style.display = 'none'; // Hide button
            spinner.style.display = 'block'; // Show spinner

            // Submit the logout form
            document.getElementById('logout-form').submit();

            // Redirect to external URL in new tab after slight delay
            
                window.open('https://wic-doctor.com/inscription-professionnel/#pricing', '_blank');
                spinner.style.display = 'none';
            
        }
    </script>
</body>
</html>

