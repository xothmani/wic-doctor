<?php if($customFields): ?>
    <h5 class="col-12 pb-4"><?php echo trans('lang.main_fields'); ?></h5>
<?php endif; ?>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <?php echo Form::label('name', trans("lang.user_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

        <div class="col-md-9">
            <?php echo Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_name_placeholder")]); ?>

            <div class="form-text text-muted">
                <?php echo e(trans("lang.user_name_help")); ?>

            </div>
        </div>
    </div>

    <!-- Email Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <?php echo Form::label('email', trans("lang.user_email"), ['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

        <div class="col-md-9">
            <?php echo Form::text('email', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_email_placeholder")]); ?>

            <div class="form-text text-muted">
                <?php echo e(trans("lang.user_email_help")); ?>

            </div>
        </div>
    </div>

    <!-- Phone Number Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <?php echo Form::label('phone_number', trans("lang.user_phone_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

        <div class="col-md-9">
            <?php echo Form::text('phone_number', null,  ['class' => 'form-control','placeholder'=>  trans("lang.user_phone_number_placeholder")]); ?>

            <div class="form-text text-muted">
                <?php echo e(trans("lang.user_phone_number_help")); ?>

            </div>
        </div>
    </div>

    <!-- Password Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <?php echo Form::label('password', trans("lang.user_password"), ['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

        <div class="col-md-9">
            <?php echo Form::password('password', ['class' => 'form-control','placeholder'=>  trans("lang.user_password_placeholder")]); ?>

            <div class="form-text text-muted">
                <?php echo e(trans("lang.user_password_help")); ?>

            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- $FIELD_NAME_TITLE$ Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        <?php echo Form::label('avatar', trans("lang.user_avatar"), ['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

        <div class="col-md-9">
            <div style="width: 100%" class="dropzone avatar" id="avatar" data-field="avatar">
                <input type="hidden" name="avatar">
            </div>
            <a href="#loadMediaModal" data-dropzone="avatar" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-<?php echo e(setting('theme_color','primary')); ?> btn-sm float-right mt-1"><?php echo e(trans('lang.media_select')); ?></a>
            <div class="form-text text-muted w-50">
                <?php echo e(trans("lang.user_avatar_help")); ?>

            </div>
        </div>
    </div>
    <?php $__env->startPrepend('scripts'); ?>
    <script type="text/javascript">
        var user_avatar = '';
        <?php if(isset($user) && $user->hasMedia('avatar')): ?>
            user_avatar = {
            name: "<?php echo $user->getFirstMedia('avatar')->name; ?>",
            size: "<?php echo $user->getFirstMedia('avatar')->size; ?>",
            type: "<?php echo $user->getFirstMedia('avatar')->mime_type; ?>",
            collection_name: "<?php echo $user->getFirstMedia('avatar')->collection_name; ?>"
        };
                <?php endif; ?>
        var dz_user_avatar = $(".dropzone.avatar").dropzone({
                url: "<?php echo url('uploads/store'); ?>",
                addRemoveLinks: true,
                maxFiles: 1,
                init: function () {
                    <?php if(isset($user) && $user->hasMedia('avatar')): ?>
                    dzInit(this, user_avatar, '<?php echo url($user->getFirstMediaUrl('avatar','thumb')); ?>')
                    <?php endif; ?>
                },
                accept: function (file, done) {
                    dzAccept(file, done, this.element, "<?php echo config('media-library.icons_folder'); ?>");
                },
                sending: function (file, xhr, formData) {
                    dzSending(this, file, formData, '<?php echo csrf_token(); ?>');
                },
                maxfilesexceeded: function (file) {
                    dz_user_avatar[0].mockFile = '';
                    dzMaxfile(this, file);
                },
                complete: function (file) {
                    dzComplete(this, file, user_avatar, dz_user_avatar[0].mockFile);
                    dz_user_avatar[0].mockFile = file;
                },
                removedfile: function (file) {
                    dzRemoveFile(
                        file, user_avatar, '<?php echo url("users/remove-media"); ?>',
                        'avatar', '<?php echo isset($user) ? $user->id : 0; ?>', '<?php echo url("uplaods/clear"); ?>', '<?php echo csrf_token(); ?>'
                    );
                }
        });
        dz_user_avatar[0].mockFile = user_avatar;
        dropzoneFields['avatar'] = dz_user_avatar;
    </script>
    <?php $__env->stopPrepend(); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('permissions.index')): ?>
    <!-- Roles Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            <?php echo Form::label('roles[]', trans("lang.user_role_id"),['class' => 'col-md-3 control-label text-md-right mx-1']); ?>

            <div class="col-md-9">
                <?php echo Form::select('roles[]', $role, $rolesSelected, ['class' => 'select2 form-control' , 'multiple'=>'multiple']); ?>

                <div class="form-text text-muted"><?php echo e(trans("lang.user_role_id_help")); ?></div>
            </div>
        </div>
    <?php endif; ?>

</div>
<?php if($customFields): ?>
    
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4"><?php echo trans('lang.custom_field_plural'); ?></h5>
        <?php echo $customFields; ?>

    </div>
<?php endif; ?>
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-<?php echo e(setting('theme_color')); ?> mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> <?php echo e(trans('lang.save')); ?> <?php echo e(trans('lang.user')); ?></button>
    <a href="<?php echo route('users.index'); ?>" class="btn btn-default"><i class="fas fa-undo"></i> <?php echo e(trans('lang.cancel')); ?></a>
</div>
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/settings/users/fields.blade.php ENDPATH**/ ?>