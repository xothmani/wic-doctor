<?php $__env->startPush('css_lib'); ?>
    <!-- icheck-bootstrap -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css')); ?>">
    <!-- select2 -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/select2/css/select2.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css')); ?>">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="<?php echo e(asset('vendor/summernote/summernote-lite.min.css')); ?>">
    
    <link rel="stylesheet" href="<?php echo e(asset('vendor/dropzone/min/dropzone.min.css')); ?>">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo trans('lang.user_profile'); ?> <small><?php echo e(trans('lang.media_desc')); ?></small></h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                        <li class="breadcrumb-item"><a href="<?php echo e(url('/dashboard')); ?>"><i class="fas fa-tachometer-alt"></i> <?php echo e(trans('lang.dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo e(trans('lang.user_profile')); ?></li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i> <?php echo e(trans('lang.user_about_me')); ?></h3>
                        </div>
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img src="<?php echo e(auth()->user()->getFirstMediaUrl('avatar','icon')); ?>" class="profile-user-img img-fluid img-circle" alt="<?php echo e(auth()->user()->name); ?>">
                            </div>
                            <h3 class="profile-username text-center"><?php echo e(auth()->user()->name); ?></h3>
                            <p class="text-muted text-center"><?php echo e(implode(', ',$rolesSelected)); ?></p>
                            <a class="btn btn-outline-<?php echo e(setting('theme_color')); ?> btn-block" href="mailto:<?php echo e(auth()->user()->email); ?>"><i class="fas fa-envelope mr-2"></i><?php echo e(auth()->user()->email); ?>

                            </a>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <?php echo $__env->make('flash::message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php echo $__env->make('adminlte-templates::common.errors', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="clearfix"></div>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <ul class="nav nav-tabs d-flex flex-row align-items-start card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="<?php echo url()->current(); ?>"><i class="fas fa-cog mr-2"></i><?php echo e(trans('lang.app_setting')); ?></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo e(route('fieldsDoctor')); ?>">
                                        <i class="fas fa-cog mr-2"></i><?php echo e(trans('lang.profile')); ?>

                                    </a>
                                </li>
                                <?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'customer')): ?>
                                <div class="ml-auto d-inline-flex">
                                    <li class="nav-item">
                                        <a class="nav-link pt-1" href="<?php echo e(route('clinics.create')); ?>"><i class="fas fa-check-o"></i> <?php echo e(trans('lang.app_setting_become_servicclinic')); ?>

                                        </a>
                                    </li>
                                </div>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <div class="card-body">
                            <?php echo Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']); ?>

                            <div class="row">
                                <?php echo $__env->make('settings.users.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            </div>
                            <?php echo Form::close(); ?>

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php echo $__env->make('layouts.media_modal',['collection'=>null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts_lib'); ?>
    <!-- select2 -->
    <script src="<?php echo e(asset('vendor/select2/js/select2.full.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendor/summernote/summernote-lite.min.js')); ?>"></script>
    
    <script src="<?php echo e(asset('vendor/dropzone/min/dropzone.min.js')); ?>"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;
        var dropzoneFields = [];
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/settings/users/profile.blade.php ENDPATH**/ ?>