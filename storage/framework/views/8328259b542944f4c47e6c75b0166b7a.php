<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(setting('app_name')); ?> | <?php echo e(setting('app_short_description')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e($app_logo ?? ''); ?>"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,600&display=fallback">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.min.css')); ?>">
    <?php echo $__env->yieldPushContent('js_lib'); ?>
</head>
<body class="hold-transition login-page">
<div class="login-box" <?php if(isset($width)): ?> style="width:<?php echo e($width); ?>" <?php endif; ?>>
    <div class="login-logo">
        <a href="<?php echo e(url('/')); ?>"><img src="<?php echo e($app_logo); ?>" alt="<?php echo e(setting('app_name')); ?>"></a>
    </div>
    <!-- /.login-logo -->
    <div class="card shadow-sm">
        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>
<!-- /.login-box -->
<script src="<?php echo e(asset('vendor/jquery/jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('dist/js/adminlte.min.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/layouts/auth/default.blade.php ENDPATH**/ ?>