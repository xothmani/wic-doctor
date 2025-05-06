<?php

return [
    'credentials' => base_path('firebase-credentials.json'),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://wic-doctor-b83e0-default-rtdb.firebaseio.com'),
    'project_id' => env('FIREBASE_PROJECT_ID', 'wic-doctor-b83e0'),
];