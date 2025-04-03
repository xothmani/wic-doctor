<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un Message</title>
</head>
<body>
    <h1>Envoyer un Message via Firebase</h1>

    <form action="{{ route('send-message2') }}" method="POST">
        @csrf
        <div>
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="4" required></textarea>
        </div>

        <div>
            <label for="device_token">Token de l'appareil</label>
            <input type="text" id="device_token" name="device_token" required />
        </div>

        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
