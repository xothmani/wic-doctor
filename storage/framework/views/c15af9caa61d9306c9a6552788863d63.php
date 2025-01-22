<div class='btn-group btn-group-sm'>
    <?php
        $doctorId = auth()->user()->getDoctorId(); // Fetch the associated doctor ID for the logged-in user
    ?>

    <?php if(auth()->user()->hasPermissionInContext('consultations.create', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.add_consultation')); ?>"
            href="<?php echo e(route('consultations.create', ['patient_id' => $id])); ?>" class='btn btn-link'>
            <i class="fas fa-plus"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('fiche.show', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.view_fiche')); ?>"
            href="<?php echo e(route('fiche.show', $id)); ?>" class='btn btn-link'>
            <i class="fas fa-file-alt"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('patients.show', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.view_details')); ?>"
            href="<?php echo e(route('patients.show', $id)); ?>" class='btn btn-link'>
            <i class="fas fa-eye"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('patients.edit', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.patient_edit')); ?>"
            href="<?php echo e(route('patients.edit', $id)); ?>" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('patients.email', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.send_email')); ?>"
            href="<?php echo e(route('patients.email', $id)); ?>" class='btn btn-link'>
            <i class="fas fa-envelope"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('patients.whatsapp', $doctorId)): ?>
        <a data-toggle="tooltip" data-placement="left" title="<?php echo e(trans('lang.send_whatsapp')); ?>"
            href="<?php echo e(route('patients.whatsapp', $id)); ?>" class='btn btn-link'>
            <i class="fab fa-whatsapp"></i>
        </a>
    <?php endif; ?>

    <?php if(auth()->user()->hasPermissionInContext('patients.destroy', $doctorId)): ?>
        <?php echo Form::open(['route' => ['patients.destroy', $id], 'method' => 'delete']); ?>

        <?php echo Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer ce patient ?')"
        ]); ?>

        <?php echo Form::close(); ?>

    <?php endif; ?>

</div><?php /**PATH /home/support-03/Dev/wic-doctor/resources/views/patients/datatables_actions.blade.php ENDPATH**/ ?>