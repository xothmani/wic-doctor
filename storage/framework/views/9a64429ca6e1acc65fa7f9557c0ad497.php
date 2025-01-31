var firebaseConfig = {
  apiKey: "<?php echo e(env('FIREBASE_API_KEY')); ?>",
  authDomain: "<?php echo e(env('FIREBASE_AUTH_DOMAIN')); ?>",
  databaseURL: "<?php echo e(env('FIREBASE_DATABASE_URL')); ?>",
  projectId: "<?php echo e(env('FIREBASE_PROJECT_ID')); ?>",
  storageBucket: "<?php echo e(env('FIREBASE_STORAGE_BUCKET')); ?>",
  messagingSenderId: "<?php echo e(env('FIREBASE_MESSAGING_SENDER_ID')); ?>",
  appId: "<?php echo e(env('FIREBASE_APP_ID')); ?>",
  measurementId: "<?php echo e(env('FIREBASE_MEASUREMENT_ID')); ?>"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/vendor/notifications/init_firebase.blade.php ENDPATH**/ ?>