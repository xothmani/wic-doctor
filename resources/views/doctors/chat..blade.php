@extends('layouts.app') <!-- Si vous utilisez un layout -->

@section('content')
<div id="chat">
    <!-- Liste des messages -->
    <div v-for="message in messages" :key="message.id">
        <strong>@{{ message.doctorName }}:</strong> @{{ message.text }}
    </div>

    <!-- Champ de saisie -->
    <input 
        type="text" 
        v-model="newMessage" 
        @keyup.enter="sendMessage"
        placeholder="Écrire un message..."
    >
</div>

<script src="{{ asset('js/app.js') }}"></script> <!-- Inclure Laravel Echo -->
<script>
    // Initialisation de Vue.js après le chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        const app = new Vue({
            el: '#chat',
            data: {
                newMessage: '',
                messages: []
            },
            methods: {
                sendMessage() {
                    // Envoyer le message au contrôleur
                    axios.post('/send-doctor-message', {
                        message: this.newMessage,
                        recipient_doctor_id: 2 // À remplacer dynamiquement
                    });
                    this.newMessage = '';
                }
            },
            mounted() {
                // Écouter les messages Pusher (à ajouter ici)
            }
        });
    });
</script>
@endsection