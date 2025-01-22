<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo e(setting('app_name')); ?> | <?php echo e(setting('app_short_description')); ?></title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/png" href="<?php echo e($app_logo ?? ''); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome-free/css/all.min.css')); ?>">

    <?php echo $__env->yieldPushContent('css_lib'); ?>
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,600" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/styles.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/'.setting("theme_color","primary").'.min.css')); ?>">
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo $__env->yieldContent('css_custom'); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
	<?php echo $__env->yieldContent('styles'); ?>
</head>

<body class="<?php if(in_array(app()->getLocale(), ['ar','ku','fa','ur','he','ha','ks'])): ?> rtl <?php else: ?> ltr <?php endif; ?> layout-fixed <?php echo e(setting('fixed_header',false) ? "layout-navbar-fixed" : ""); ?> <?php echo e(setting('fixed_footer',false) ? "layout-footer-fixed" : ""); ?> sidebar-mini <?php echo e(setting('theme_color')); ?> <?php echo e(setting('theme_contrast','')); ?>-mode" data-scrollbar-auto-hide="r" data-scrollbar-theme="os-theme-dark">
<?php echo $__env->yieldContent('scripts'); ?>
<div class="wrapper">

    <nav class="main-header navbar navbar-expand <?php echo e(setting('nav_color','navbar-light navbar-white')); ?> border-bottom-0">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?php echo e(url('dashboard')); ?>" class="nav-link"><?php echo e(trans('lang.dashboard')); ?></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php if(env('APP_CONSTRUCTION',false)): ?>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"><i class="fas fa-info-circle"></i>
                        <?php echo e(env('APP_CONSTRUCTION','')); ?></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('favorites.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('favorites*') ? 'active' : ''); ?>" href="<?php echo e(route('favorites.index')); ?>"><i class="fas fa-heart"></i></a>
                </li>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notifications.index')): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(Request::is('notifications*') ? 'active' : ''); ?>" href="<?php echo route('notifications.index'); ?>"><i class="fas fa-bell"></i></a>
                </li>
            <?php endif; ?>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#"> <i class="fa fas fa-angle-down"></i> <?php echo Str::upper(app()->getLocale()); ?>

                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php echo Form::open(['url' => ['settings/updateLanguage'], 'method' => 'patch','id'=>'languages-form']); ?>

                    <?php echo Form::hidden('locale',app()->getLocale(),['id'=>'current-language']); ?>

                    <?php $__currentLoopData = getAvailableLanguages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale => $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="#" class="dropdown-item <?php if(app()->getLocale() == $locale): ?> active <?php endif; ?>" onclick="changeLanguage('<?php echo e($locale); ?>')">
                            <i class="fas fa-circle mr-2"></i> <?php echo __($lang); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php echo Form::close(); ?>

                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <img src="<?php echo e(auth()->user()->getFirstMediaUrl('avatar','icon')); ?>" class="brand-image mx-2 img-circle elevation-2" alt="User Image">
                    <i class="fa fas fa-angle-down"></i> <?php echo auth()->user()->name; ?>


                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?php echo e(route('users.profile')); ?>" class="dropdown-item"> <i class="fas fa-user mr-2"></i> <?php echo e(trans('lang.user_profile')); ?> </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo url('/logout'); ?>" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-envelope mr-2"></i> <?php echo e(__('auth.logout')); ?>

                    </a>
                    <form id="logout-form" action="<?php echo e(url('/logout')); ?>" method="POST" style="display: none;">
                        <?php echo e(csrf_field()); ?>

                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Left side column. contains the logo and sidebar -->
<?php echo $__env->make('layouts.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- Main Footer -->
    <footer class="main-footer border-0 shadow-sm">
        <div class="float-sm-right d-none d-sm-block">
            <b>Version</b> <?php echo e(implode('.',str_split(substr(config('installer.currentVersion','v100'),1,3)))); ?>

        </div>
        <strong>Copyright © <?php echo e(date('Y')); ?> <a href="<?php echo e(url('/')); ?>"><?php echo e(setting('app_name')); ?></a>.</strong> All rights reserved.
    </footer>

</div>

<!-- jQuery -->
<script src="<?php echo e(asset('vendor/jquery/jquery.min.js')); ?>"></script>

<script src="<?php echo e(asset('vendor/bootstrap-v4-rtl/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js')); ?>"></script>

<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="<?php echo e(asset('https://www.gstatic.com/firebasejs/7.2.0/firebase-app.js')); ?>"></script>

<script src="<?php echo e(asset('https://www.gstatic.com/firebasejs/7.2.0/firebase-messaging.js')); ?>"></script>

<script type="text/javascript"><?php echo $__env->make('vendor.notifications.init_firebase', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?></script>

<script type="text/javascript">
    const messaging = firebase.messaging();
    navigator.serviceWorker.register("<?php echo e(url('firebase/sw-js')); ?>")
        .then((registration) => {
            messaging.useServiceWorker(registration);
            messaging.requestPermission()
                .then(function () {
                    console.log('Notification permission granted.');
                    getRegToken();

                })
                .catch(function (err) {
                    console.log('Unable to get permission to notify.', err);
                });
            messaging.onMessage(function (payload) {
                console.log("Message received. ", payload);
                notificationTitle = payload.data.title;
                notificationOptions = {
                    body: payload.data.body,
                    icon: payload.data.icon,
                    image: payload.data.image
                };
                var notification = new Notification(notificationTitle, notificationOptions);
            });
        });

    function getRegToken(argument) {
        messaging.getToken().then(function (currentToken) {
            if (currentToken) {
                saveToken(currentToken);
                console.log(currentToken);
            } else {
                console.log('No Instance ID token available. Request permission to generate one.');
            }
        })
            .catch(function (err) {
                console.log('An error occurred while retrieving token. ', err);
            });
    }


    function saveToken(currentToken) {
        $.ajax({
            type: "POST",
            data: {'device_token': currentToken, 'api_token': '<?php echo auth()->user()->api_token; ?>'},
            url: '<?php echo url('api/users',['id'=>auth()->id()]); ?>',
            success: function (data) {

            },
            error: function (err) {
                console.log(err);
            }
        });
    }

    function changeLanguage(locale) {
        event.preventDefault();
        document.getElementById('current-language').value = locale;
        document.getElementById('languages-form').submit();
    }
</script>

<?php echo $__env->yieldPushContent('scripts_lib'); ?>
<script src="<?php echo e(asset('dist/js/adminlte.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/scripts.min.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/layouts/app.blade.php ENDPATH**/ ?>