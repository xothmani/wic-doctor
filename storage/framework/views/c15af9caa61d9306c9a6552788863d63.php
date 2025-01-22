<div class='btn-group btn-group-sm'>
<!--     <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('consultations.create')): ?>
    <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.add_consultation')); ?>" href="<?php echo e(route('consultations.create', ['patient_id' => $id])); ?>" class='btn btn-link'>
        <i class="fas fa-plus"></i> 
    </a>
    <?php endif; ?> -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('fiche.show')): ?>
    <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.view_fiche')); ?>" href="<?php echo e(route('fiche.show', $id)); ?>" class='btn btn-link'>
        <i class="fas fa-file-alt"></i> <!-- Icône pour la fiche -->
    </a>
<?php endif; ?>


    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.show')): ?>
    <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.view_details')); ?>" href="<?php echo e(route('patients.show', $id)); ?>" class='btn btn-link'>
        <i class="fas fa-eye"></i> 
    </a> 
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.edit')): ?>
    <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.patient_edit')); ?>" href="<?php echo e(route('patients.edit', $id)); ?>" class='btn btn-link'>
        <i class="fas fa-edit"></i> 
    </a> 
    <?php endif; ?>


    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.email')): ?> <!-- Assurez-vous que l'autorisation est correctement définie -->
    <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.send_email')); ?>" href="<?php echo e(route('patients.email', $id)); ?>" class='btn btn-link'>
        <i class="fas fa-envelope"></i> 
    </a>
    <?php endif; ?>


    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.whatsapp')): ?>
<a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.send_whatsapp')); ?>" href="<?php echo e(route('patients.whatsapp', $id)); ?>" class='btn btn-link'>
    <i class="fab fa-whatsapp"></i> 
</a>
<?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('patients.destroy')): ?> 
    <?php echo Form::open(['route' => ['patients.destroy', $id], 'method' => 'delete']); ?> 
    <?php echo Form::button('<i class="fas fa-trash"></i>', [ 'type' => 'submit', 'class' => 'btn btn-link text-danger', 'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer ce patient ?')" ]); ?> 
    <?php echo Form::close(); ?> 
    <?php endif; ?>


</div>
<?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/patients/datatables_actions.blade.php ENDPATH**/ ?>