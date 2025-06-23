# WIC Doctor - Guide d'Installation et Configuration

_WIC Doctor - Installation and Setup Guide_

## 🏥 Vue d'ensemble / Overview

WIC Doctor est une plateforme complète de gestion médicale avec système de messagerie en temps réel utilisant Laravel, Vue.js, et Firebase.

_WIC Doctor is a comprehensive medical management platform with real-time messaging system using Laravel, Vue.js, and Firebase._

---

## 📋 Prérequis / Prerequisites

### Logiciels requis / Required Software:

- **PHP** ≥ 8.1
- **Composer** ≥ 2.0
- **Node.js** ≥ 16.0
- **NPM** ou **Yarn**
- **MySQL** ≥ 8.0
- **Git**

### Extensions PHP requises / Required PHP Extensions:

```bash
php-curl
php-dom
php-fileinfo
php-filter
php-hash
php-mbstring
php-openssl
php-pcre
php-pdo
php-session
php-tokenizer
php-xml
php-zip
php-mysql
php-gd
php-imagick
```

---

## 🚀 Installation

### 1. Cloner le projet / Clone the project

```bash
git clone https://github.com/your-repo/wic-doctor.git
cd wic-doctor
```

### 2. Installation des dépendances PHP / Install PHP dependencies

```bash
composer install
```

### 3. Installation des dépendances JavaScript / Install JavaScript dependencies

```bash
npm install
# ou / or
yarn install
```

### 4. Configuration de l'environnement / Environment setup

```bash
# Copier le fichier d'environnement / Copy environment file
cp .env.example .env

# Générer la clé d'application / Generate application key
php artisan key:generate
```

### 5. Configuration de la base de données / Database configuration

Éditer le fichier `.env` / Edit the `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wic_doctor
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Créer la base de données / Create database

```sql
CREATE DATABASE wic_doctor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Exécuter les migrations / Run migrations

```bash
php artisan migrate
```

### 8. Exécuter les scripts du système de messagerie / Run messenger system scripts

```bash
mysql -u your_username -p wic_doctor < SQL_Scripts_Messenger.sql
```

### 9. Seeders (optionnel) / Seeders (optional)

```bash
php artisan db:seed
```

---

## 🔥 Configuration Firebase

### 1. Créer un projet Firebase / Create Firebase project

1. Aller sur https://console.firebase.google.com/
2. Créer un nouveau projet / Create new project
3. Activer **Realtime Database** et **Storage**

### 2. Configuration dans `.env`

```env
# Firebase Configuration
FIREBASE_API_KEY=AIzaSyCONylt3t8MDw_02k5H9ceXTEmdtxmQtu8
FIREBASE_AUTH_DOMAIN=wic-doctor-b83e0.firebaseapp.com
FIREBASE_DATABASE_URL=https://wic-doctor-b83e0-default-rtdb.europe-west1.firebasedatabase.app
FIREBASE_PROJECT_ID=wic-doctor-b83e0
FIREBASE_STORAGE_BUCKET=wic-doctor-b83e0.firebasestorage.app
FIREBASE_MESSAGING_SENDER_ID=895957208558
FIREBASE_APP_ID=1:895957208558:web:322c25347af966f5f512ff
```

### 3. Règles Firebase Realtime Database / Firebase Realtime Database Rules

```json
{
  "rules": {
    ".read": "auth != null",
    ".write": "auth != null",
    "messages": {
      "$conversationId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    },
    "conversations": {
      "$conversationId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    },
    "userPresence": {
      "$userId": {
        ".read": "auth != null",
        ".write": "auth != null && auth.uid == $userId"
      }
    },
    "typing": {
      "$conversationId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    },
    "sharedMedia": {
      "$conversationId": {
        ".read": "auth != null",
        ".write": "auth != null"
      }
    }
  }
}
```

### 4. Règles Firebase Storage / Firebase Storage Rules

```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /conversations/{conversationId}/{allPaths=**} {
      allow read, write: if request.auth != null;
    }
  }
}
```

---

## ⚙️ Configuration de l'application / Application Configuration

### 1. Permissions et liens symboliques / Permissions and symbolic links

```bash
# Définir les permissions / Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Créer le lien symbolique pour le stockage / Create storage symbolic link
php artisan storage:link
```

### 2. Configuration du cache / Cache configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Configuration de l'authentification API / API Authentication setup

```bash
php artisan passport:install
# ou / or
php artisan sanctum:publish
```

---

## 🎨 Compilation des assets / Asset compilation

### Développement / Development

```bash
npm run dev
# ou / or
npm run watch
```

### Production

```bash
npm run production
```

---

## 🏃‍♂️ Lancement du projet / Running the project

### 1. Serveur de développement / Development server

```bash
php artisan serve
```

L'application sera accessible sur / The application will be available at: http://localhost:8000

### 2. Serveur de développement Vite (pour Vue.js) / Vite development server (for Vue.js)

```bash
npm run dev
```

### 3. Démarrage en arrière-plan / Background services

```bash
# Queues (si utilisées) / Queues (if used)
php artisan queue:work

# Scheduler (pour les tâches programmées) / Scheduler (for scheduled tasks)
php artisan schedule:run
```

---

## 📧 Configuration Email (optionnel) / Email Configuration (optional)

Dans `.env` / In `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@wic-doctor.com
MAIL_FROM_NAME="WIC Doctor"
```

---

## 🔒 Configuration de sécurité / Security Configuration

### 1. HTTPS (Production)

```env
APP_URL=https://yourdomain.com
FORCE_HTTPS=true
```

### 2. CORS Configuration

Dans `config/cors.php` / In `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'messenger/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000', 'https://yourdomain.com'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

---

## 🗂️ Structure du projet / Project Structure

```
wic-doctor/
├── app/
│   ├── Http/Controllers/API/
│   │   └── MessengerController.php
│   └── Models/
│       ├── Friend.php
│       ├── Group.php
│       ├── Conversation.php
│       └── ...
├── resources/
│   ├── js/
│   │   ├── components/Messenger/
│   │   │   ├── MessengerApp.vue
│   │   │   ├── ChatArea.vue
│   │   │   └── ...
│   │   └── composables/
│   │       ├── useFirebase.js
│   │       └── useMessengerAPI.js
│   └── views/
│       └── messenger/
│           ├── index.blade.php
│           ├── embed.blade.php
│           └── nav-link.blade.php
├── routes/
│   ├── api.php (API routes)
│   └── web.php (Web routes)
└── SQL_Scripts_Messenger.sql
```

---

## 🚨 Dépannage / Troubleshooting

### Problèmes courants / Common Issues:

#### 1. Erreur de permissions / Permission errors

```bash
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

#### 2. Erreur de clé d'application / Application key error

```bash
php artisan key:generate
```

#### 3. Problèmes de cache / Cache issues

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 4. Problèmes Node.js / Node.js issues

```bash
rm -rf node_modules
rm package-lock.json
npm install
```

#### 5. Erreurs de base de données / Database errors

```bash
php artisan migrate:fresh --seed
```

---

## 📞 Utilisation du système de messagerie / Using the Messenger System

### Accès / Access:

- **URL principale / Main URL:** `/messenger`
- **Widget intégré / Embedded widget:** `/messenger/embed`
- **API Endpoints:** `/api/messenger/*`

### Fonctionnalités / Features:

- ✅ Messages en temps réel / Real-time messaging
- ✅ Conversations de groupe / Group conversations
- ✅ Partage de fichiers / File sharing
- ✅ Indicateurs de frappe / Typing indicators
- ✅ Statut en ligne / Online status
- ✅ Système d'invitations / Invitation system
- ✅ Interface responsive / Responsive interface
- ✅ Support multilingue (FR/EN) / Multi-language support

---

## 🔄 Mise à jour / Updates

### Mise à jour du code / Code updates:

```bash
git pull origin main
composer install
npm install
php artisan migrate
npm run production
php artisan cache:clear
```

---

## 📊 Surveillance / Monitoring

### Logs / Logs:

```bash
# Logs Laravel / Laravel logs
tail -f storage/logs/laravel.log

# Logs serveur web / Web server logs
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log
```

### Performance:

- Utiliser **Redis** pour les sessions et le cache / Use **Redis** for sessions and cache
- Configurer **Queue workers** pour les tâches lourdes / Configure **Queue workers** for heavy tasks
- Optimiser les images avec **ImageMagick** / Optimize images with **ImageMagick**

---

## 🎯 Déploiement en production / Production Deployment

### 1. Serveur web / Web server

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/wic-doctor/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 2. Variables d'environnement production / Production environment variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Optimisations
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 📞 Support / Support

- **Documentation Laravel:** https://laravel.com/docs
- **Documentation Vue.js:** https://vuejs.org/guide/
- **Documentation Firebase:** https://firebase.google.com/docs

---

**Version:** 1.0.0  
**Dernière mise à jour / Last updated:** 2024  
**Auteur / Author:** WIC Doctor Team
