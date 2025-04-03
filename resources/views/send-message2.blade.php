<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoyer un message Firebase</title>
</head>
<body>

    <h1>Envoyer un message à Firebase</h1>

    <form action="{{ url('send-message2') }}" method="POST">
        @csrf
        <label for="message">Message :</label><br>
        <textarea name="message" id="message" required></textarea><br><br>

        <label for="device_token">Token du dispositif :</label><br>
        <input type="text" name="device_token" id="device_token" required><br><br>

        <button type="submit">Envoyer</button>
    </form>

</body>
</html>
