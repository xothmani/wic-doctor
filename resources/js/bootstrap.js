import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY, // Ensure these match your .env settings
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});
// Listen for the AppointmentCreated event
window.Echo.channel('appointments')
    .listen('AppointmentCreated', (e) => {
        console.log('New appointment received:', e.appointment);
        // Update the UI dynamically with the new appointment
    });