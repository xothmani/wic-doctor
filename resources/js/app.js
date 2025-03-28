
// Écoute globale
Echo.private('doctors.chat.global')
    .listen('.new.message', (data) => {
        console.log('Message global:', data);
    });

// Écoute privée
Echo.private(`doctors.chat.private.${doctorId}`)
    .listen('.new.message', (data) => {
        console.log('Message privé:', data);
    });




    // Écouter l'événement 'message.sent'
window.Echo.private(`chat.${userId}`)
.listen('.message.sent', (data) => {
    console.log('Nouveau message reçu :', data);

    // Ajouter le message à l'interface utilisateur
    appendMessage(data);

    // Afficher une notification toast
    showToast(`Nouveau message de ${data.sender_name}: ${data.content}`);

    // Mettre à jour le badge de notification
    updateNotificationBadge();
});