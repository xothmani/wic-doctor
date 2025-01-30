<?php $__env->startSection('content'); ?>

<div class="card-body login-card-body">
    <p class="login-box-msg"><?php echo e(__('auth.login_title')); ?></p>

    <form action="<?php echo e(url('/login')); ?>" method="post">
        <?php echo csrf_field(); ?>


        <div class="input-group mb-3">
            <input value="<?php echo e(old('email')); ?>" type="email"
                class="form-control <?php echo e($errors->has('email') ? ' is-invalid' : ''); ?>" name="email"
                placeholder="<?php echo e(__('auth.email')); ?>" aria-label="<?php echo e(__('auth.email')); ?>">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            </div>
            <?php if($errors->has('email')): ?>
                <div class="invalid-feedback">
                    <?php echo e($errors->first('email')); ?>

                </div>
            <?php endif; ?>
        </div>

        <div class="input-group mb-3">
            <input value="<?php echo e(old('password')); ?>" type="password"
                class="form-control  <?php echo e($errors->has('password') ? ' is-invalid' : ''); ?>" name="password"
                placeholder="<?php echo e(__('auth.password')); ?>" aria-label="<?php echo e(__('auth.password')); ?>">
            <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
            </div>
            <?php if($errors->has('password')): ?>
                <div class="invalid-feedback">
                    <?php echo e($errors->first('password')); ?>

                </div>
            <?php endif; ?>
        </div>

        <div class="row justify-content-center mb-2">
            <div class="col-6 text-center">
                <button type="submit" class="btn btn-<?php echo e(setting('theme_color')); ?> btn-block">
                    <?php echo e(__('auth.login')); ?>

                </button>
            </div>
        </div>




    </form>


</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth.default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/auth/login.blade.php ENDPATH**/ ?>