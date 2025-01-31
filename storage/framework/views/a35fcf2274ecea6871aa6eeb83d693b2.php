
<?php if(session('alert')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?php echo e(session('alert')); ?>

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
<?php $__env->startPush('css_lib'); ?>
<?php echo $__env->make('layouts.datatables_css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $dataTable->table(['width' => '100%']); ?>


<?php $__env->startPush('scripts_lib'); ?>
<?php echo $__env->make('layouts.datatables_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $dataTable->scripts(); ?>

<?php $__env->stopPush(); ?>
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/patients/table.blade.php ENDPATH**/ ?>