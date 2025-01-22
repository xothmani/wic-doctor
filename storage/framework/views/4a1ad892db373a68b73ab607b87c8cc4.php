<?php $__env->startSection('content'); ?>
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-md-6">
                <h1 class="m-0 text-bold"><?php echo e(trans('lang.patient_plural')); ?> <small class="mx-3">|</small><small><?php echo e(trans('lang.patient_desc')); ?></small></h1>
            </div><!-- /.col -->
            <div class="col-md-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item"><a href="<?php echo e(url('/dashboard')); ?>"><i class="fas fa-tachometer-alt mx-1"></i> <?php echo e(trans('lang.dashboard')); ?></a></li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo route('patients.index'); ?>"><?php echo e(trans('lang.patient_plural')); ?></a>
                    </li>
                    <li class="breadcrumb-item active"><?php echo e(trans('lang.patient_table')); ?></li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<div class="content">
  <div class="clearfix"></div>
  <?php echo $__env->make('flash::message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-tabs d-flex flex-md-row flex-column-reverse align-items-start card-header-tabs">
        <div class="d-flex flex-row">
            <li class="nav-item">
              <a class="nav-link active" href="<?php echo url()->current(); ?>"><i class="fa fa-list mr-2"></i><?php echo e(trans('lang.patient_table')); ?></a>
            </li>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.create')): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo route('patients.create'); ?>"><i class="fa fa-plus mr-2"></i><?php echo e(trans('lang.patient_create')); ?></a>
            </li>
        
            <?php endif; ?>
        </div>
        <?php echo $__env->make('layouts.right_toolbar', compact('dataTable'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </ul>
    </div>
    <div class="card-body">
      <?php echo $__env->make('patients.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="clearfix"></div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/patients/index.blade.php ENDPATH**/ ?>