# WIC Doctor Messenger - Guide de Lancement

_WIC Doctor Messenger - Setup Guide_

## 🚀 Lancement Rapide / Quick Start

### 1. Installation Dépendances / Install Dependencies

```bash
composer install
npm install
```

### 2. Configuration / Configuration

```bash
# Copier environnement
cp .env.example .env

# Générer clé
php artisan key:generate

# Configurer .env avec votre base de données
```

### 3. Base de Données / Database

```bash
# Créer BDD
CREATE DATABASE wic_doctor;

# Migrations
php artisan migrate

# Scripts Messenger
mysql -u username -p wic_doctor < SQL_Scripts_Messenger.sql
```

### 4. Firebase Configuration

Ajouter dans `.env`:

```env
FIREBASE_API_KEY=AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8
FIREBASE_AUTH_DOMAIN=wic-doctor-b83e0.firebaseapp.com
FIREBASE_DATABASE_URL=https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app
FIREBASE_PROJECT_ID=wic-doctor-b83e0
FIREBASE_STORAGE_BUCKET=wic-doctor-b83e0.firebasestorage.app
FIREBASE_MESSAGING_SENDER_ID=895957208558
FIREBASE_APP_ID=1:895957208558:web:322c25347af966f5f512ff
```

### 5. Compilation Assets / Compile Assets

```bash
npm run dev
```

### 6. Lancer Serveur / Start Server

```bash
php artisan serve
```

## 📱 Accès Messenger / Access Messenger

### Via Menu / Through Menu:

1. Se connecter / Login
2. Menu latéral / Sidebar menu
3. **"WIC Messenger"**

### Direct URL:

- **Messenger principal:** `http://localhost:8000/messenger`

## ✨ Fonctionnalités / Features

- ✅ Messages temps réel / Real-time messaging
- ✅ Conversations privées / Private conversations
- ✅ Groupes / Groups
- ✅ Partage fichiers / File sharing
- ✅ Interface Facebook Messenger / Facebook Messenger UI
- ✅ Bilingue FR/EN / Bilingual FR/EN
- ✅ Notifications / Notifications
- ✅ Recherche médecins / Doctor search

## 🛠️ Structure Fichiers / File Structure

```
├── app/Http/Controllers/API/MessengerController.php
├── app/Models/ (Friend, Group, Conversation...)
├── resources/js/components/Messenger/MessengerApp.vue
├── resources/js/composables/useFirebase.js
├── resources/views/messenger/index.blade.php
├── routes/web.php (messenger routes)
└── SQL_Scripts_Messenger.sql
```

## 🔧 Problèmes / Troubleshooting

### Cache Issues:

```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```

### Assets Issues:

```bash
npm run dev
# ou / or
npm run production
```

### Permissions:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 📞 Support

- Laravel: https://laravel.com/docs
- Vue.js: https://vuejs.org/
- Firebase: https://firebase.google.com/docs

---

**Version:** 1.0  
**Date:** 2024
