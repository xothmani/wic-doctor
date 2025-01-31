<?php $__env->startSection('content'); ?>
    <div class="card-body login-card-body">
        <p class="login-box-msg"><?php echo e(__('auth.login_title')); ?></p>

        <form id="login-form" action="<?php echo e(url('/login')); ?>" method="post" onsubmit="return validateRecaptcha()">
            <?php echo csrf_field(); ?>


            <div class="input-group mb-3">
                <input value="<?php echo e(old('email')); ?>" type="email" class="form-control <?php echo e($errors->has('email') ? ' is-invalid' : ''); ?>" name="email" placeholder="<?php echo e(__('auth.email')); ?>" aria-label="<?php echo e(__('auth.email')); ?>">
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
                <input value="<?php echo e(old('password')); ?>" type="password" class="form-control  <?php echo e($errors->has('password') ? ' is-invalid' : ''); ?>" name="password" placeholder="<?php echo e(__('auth.password')); ?>" aria-label="<?php echo e(__('auth.password')); ?>">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                </div>
                <?php if($errors->has('password')): ?>
                    <div class="invalid-feedback">
                        <?php echo e($errors->first('password')); ?>

                    </div>
                <?php endif; ?>
            </div>

            
            <div class="form-group">
                <?php echo NoCaptcha::display(); ?>

                <?php if($errors->has('g-recaptcha-response')): ?>
                    <div class="invalid-feedback d-block">
                        <?php echo e($errors->first('g-recaptcha-response')); ?>

                    </div>
                <?php endif; ?>
            </div>

            
            <div id="recaptcha-error" class="text-danger" style="display:none;">
                Veuillez valider le reCAPTCHA avant de soumettre le formulaire.
            </div>

            <div class="row mb-2">
                <div class="col-8"></div>
                <div class="col-4">
                    <button type="submit" class="btn btn-<?php echo e(setting("theme_color")); ?> btn-block"><?php echo e(__('auth.login')); ?></button>
                </div>
            </div>

            <?php if(config('installer.demo_app')): ?>
                <div class="my-4">
                    <div class="col-12 card card-outline card-primary">
                        <div class="card-body">
                            <div class="text-bold">Admin</div>
                            <small>User: admin@demo.com | Password: 123456</small>
                            <div class="text-bold mt-3">Clinic Owner</div>
                            <small>User: clinic@demo.com | Password: 123456</small>
                            <div class="text-bold mt-3">Customer</div>
                            <small>User: customer@demo.com | Password: 123456</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>

<script src="https://www.google.com/recaptcha/api.js?ver=3.0"></script>

    </div>

    <script>
        function validateRecaptcha() {
            var recaptchaResponse = grecaptcha.getResponse();
            var errorDiv = document.getElementById("recaptcha-error");
            
            // Si le reCAPTCHA n'est pas validé
            if (recaptchaResponse.length === 0) {
                errorDiv.style.display = "block";  // Afficher le message d'erreur
                return false; // Ne pas soumettre le formulaire
            }
            
            // Si le reCAPTCHA est validé, cacher le message d'erreur
            errorDiv.style.display = "none";
            return true; // Soumettre le formulaire
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth.default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/auth/login.blade.php ENDPATH**/ ?>