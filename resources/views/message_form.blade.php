<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Form</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div id="message-form">
        <h2>Write a Message</h2>

        <form id="messageForm" method="POST" onsubmit="return false;">
            @csrf
            <div>
                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div>
                <label for="sender">Sender:</label>
                <input type="text" id="sender" name="sender" required>
            </div>
            <div>
                <label for="timestamp">Timestamp:</label>
                <input type="datetime-local" id="timestamp" name="timestamp" required>
            </div>
            <button type="button" onclick="postMessage()">Post Message</button>
        </form>

        <div id="response" style="margin-top: 20px;"></div>
    </div>

    <script>
        function postMessage() {
            // Collect form data
            const message = document.getElementById('message').value;
            const sender = document.getElementById('sender').value;
            const timestamp = document.getElementById('timestamp').value;

            // Prepare data to send to the API
            const data = {
                message: message,
                sender: sender,
                timestamp: timestamp,
            };

            // Make the AJAX request to save the message
            axios.post('/api/save-message', data)
                .then(function(response) {
                    document.getElementById('response').innerText = 'Message saved successfully! Document ID: ' + response.data.id;
                })
                .catch(function(error) {
                    document.getElementById('response').innerText = 'Error: ' + error.response.data.message;
                });
        }
    </script>
</body>
</html>
