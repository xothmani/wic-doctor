<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use App\Events\AuditLogCreatedEvent;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log an activity in the system.
     *
     * @param string $action The type of action performed
     * @param string $entityType The type of entity affected
     * @param int $entityId The ID of the affected entity
     * @param string $description A description of the action
     * @param array $oldValues Previous values before the change
     * @param array $newValues New values after the change
     * @param int|null $doctorId Associated doctor ID
     * @return AuditLog
     */
    public function log(
        string $action,
        string $entityType,
        int $entityId,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        ?int $doctorId = null
    ): AuditLog {
        $user = Auth::user();

        // Create the audit log entry
        $auditLog = AuditLog::create([
            'user_id' => $user?->id,
            'doctor_id' => $doctorId ?? $user?->doctor_id ?? null,
            'user_role' => $user?->roles->first()?->name ?? 'system',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'read_at' => null, // Initially unread
        ]);

        // Trigger the AuditLogCreatedEvent if a doctor ID is available
        if ($auditLog->doctor_id) {
            Log::info('Audit log created:', ['auditLog' => $auditLog]);
            event(new AuditLogCreatedEvent($auditLog));
        }

        return $auditLog;
    }

    /**
     * Log a login activity.
     *
     * @param int $userId
     * @param string $description
     * @return AuditLog
     */
    public function logLogin(int $userId, string $description): AuditLog
    {
        return $this->log(
            'login',
            'user',
            $userId,
            $description
        );
    }

    /**
     * Log a logout activity.
     *
     * @param int $userId
     * @param string $description
     * @return AuditLog
     */
    public function logLogout(int $userId, string $description): AuditLog
    {
        return $this->log(
            'logout',
            'user',
            $userId,
            $description
        );
    }

    /**
     * Log patient creation.
     *
     * @param int $patientId
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logPatientCreation(
        int $patientId,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'Création de patient',
            'patient',
            $patientId,
            'Nouveau patient créé',
            [],
            $newValues,
            $doctorId
        );
    }

    /**
     * Log patient update.
     *
     * @param int $patientId
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logPatientUpdate(
        int $patientId,
        array $oldValues,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'Mise à jour de patient',
            'patient',
            $patientId,
            'Informations du patient mises à jour',
            $oldValues,
            $newValues,
            $doctorId
        );
    }

    /**
     * Log patient deletion.
     *
     * @param int $patientId
     * @param array $oldValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logPatientDeletion(
        int $patientId,
        array $oldValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'Suppression de patient',
            'patient',
            $patientId,
            'Patient supprimé',
            $oldValues,
            [],
            $doctorId
        );
    }

    /**
     * @deprecated Use logPatientCreation, logPatientUpdate, or logPatientDeletion instead
     */
    public function logPatientModification(
        int $patientId,
        string $description,
        array $oldValues,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'patient_modification',
            'patient',
            $patientId,
            $description,
            $oldValues,
            $newValues,
            $doctorId
        );
    }

    /**
     * Log an appointment related activity.
     *
     * @param int $appointmentId
     * @param string $action
     * @param string $description
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logAppointment(
        int $appointmentId,
        string $action,
        string $description,
        array $oldValues = [],
        array $newValues = [],
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            $action,
            'appointment',
            $appointmentId,
            $description,
            $oldValues,
            $newValues,
            $doctorId
        );
    }

    /**
     * Log a document upload activity.
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $description
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logDocumentUpload(
        string $entityType,
        int $entityId,
        string $description,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'document_upload',
            $entityType,
            $entityId,
            $description,
            [],
            [],
            $doctorId
        );
    }

    /**
     * Log a profile update activity.
     *
     * @param string $entityType
     * @param int $entityId
     * @param string $description
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logProfileUpdate(
        string $entityType,
        int $entityId,
        string $description,
        array $oldValues,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'profile_update',
            $entityType,
            $entityId,
            $description,
            $oldValues,
            $newValues,
            $doctorId
        );
    }

    /**
     * Log availability creation.
     *
     * @param int $availabilityId
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logAvailabilityCreation(
        int $availabilityId,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'availability_create',
            'availability_hour',
            $availabilityId,
            'Nouvelle disponibilité créée',
            [],
            $newValues,
            $doctorId
        );
    }

    /**
     * Log availability update.
     *
     * @param int $availabilityId
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logAvailabilityUpdate(
        int $availabilityId,
        array $oldValues,
        array $newValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'availability_update',
            'availability_hour',
            $availabilityId,
            'Disponibilité mise à jour',
            $oldValues,
            $newValues,
            $doctorId
        );
    }

    /**
     * Log availability deletion.
     *
     * @param int $availabilityId
     * @param array $oldValues
     * @param int|null $doctorId
     * @return AuditLog
     */
    public function logAvailabilityDeletion(
        int $availabilityId,
        array $oldValues,
        ?int $doctorId = null
    ): AuditLog {
        return $this->log(
            'availability_delete',
            'availability_hour',
            $availabilityId,
            'Disponibilité supprimée',
            $oldValues,
            [],
            $doctorId
        );
    }
}