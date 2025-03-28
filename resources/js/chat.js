// Initialiser Echo
import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('doctor_token')
        }
    }
});

// Écouter les messages pour le médecin connecté
const doctorId = 1; // Récupérer dynamiquement (ex: depuis l'authentification)
window.Echo.private(`doctor-chat.${doctorId}`)
    .listen('.new-doctor-message', (data) => {
        console.log('Nouveau message:', data);
        // Ajouter le message à l'interface utilisateur
    });