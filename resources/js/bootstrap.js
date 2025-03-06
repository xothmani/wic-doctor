const doctorId = /* Récupérer l'ID du médecin connecté */

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
