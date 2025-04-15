<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use App\Events\AuditLogCreatedEvent;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    public function createAuditLog($action, $entityType, $description, $oldValues = [], $newValues = [], $fieldTypes = [], $doctorId = null)
    {
        \Log::info('[AUDIT SERVICE] Creating audit log', ['action' => $action, 'entity_type' => $entityType]);

        // Preprocess old_values and new_values
        $processedOldValues = $this->preprocessValues($oldValues, $fieldTypes);
        $processedNewValues = $this->preprocessValues($newValues, $fieldTypes);

        // Create the audit log
        $auditLog = new AuditLog();
        $auditLog->action = $action;
        $auditLog->entity_type = $entityType;
        $auditLog->description = $description;
        $auditLog->old_values = $processedOldValues;
        $auditLog->new_values = $processedNewValues;
        $auditLog->user_id = Auth::id();
        $auditLog->doctor_id = $doctorId ?? (Auth::user()->hasRole('doctor') ? Auth::user()->doctor_id : null);
        $auditLog->save();

        \Log::info('[AUDIT SERVICE] Audit log created', ['id' => $auditLog->id]);
        return $auditLog;
    }

    /**
     * Preprocess the values based on their field types.
     *
     * @param array $values
     * @param array $fieldTypes
     * @return array
     */
    protected function preprocessValues($values, $fieldTypes)
    {
        if (empty($values)) {
            return [];
        }

        $processedValues = [];

        foreach ($values as $key => $value) {
            // Skip developer-specific fields
            $fieldsToHide = ['metadata'];
            if (in_array($key, $fieldsToHide)) {
                continue;
            }

            // Determine the field type
            $fieldType = $fieldTypes[$key] ?? 'text'; // Default to 'text' if type not specified

            // Resolve the value based on the field type
            $processedValue = $this->resolveValue($key, $value, $fieldType);

            // Translate the field name
            $translatedKey = __('audit.fields.' . $key, [], ucfirst(str_replace('_', ' ', $key)));

            // Add to processed values
            $processedValues[$translatedKey] = $processedValue;
        }

        return $processedValues;
    }

    /**
     * Resolve the value based on its field type.
     *
     * @param string $key
     * @param mixed $value
     * @param string $fieldType
     * @return mixed
     */
    protected function resolveValue($key, $value, $fieldType)
    {
        // Handle 'N/A' globally
        if ($value === 'N/A') {
            return __('audit.na');
        }

        switch ($fieldType) {
            case 'user':
                $user = User::find($value);
                return $user ? trim($user->name . ' ' . $user->last_name) : __('audit.na');

            case 'doctor':
                $doctor = Doctor::find($value);
                return $doctor ? trim($doctor->name) : __('audit.na');

            case 'patient':
                $patient = Patient::find($value);
                return $patient ? trim($patient->first_name . ' ' . $patient->last_name) : __('audit.na');

            case 'status':
                return __('audit.statuses.' . (is_numeric($value) ? $value : strtolower($value)), [], $value);

            case 'appointment_type':
                $typeKey = str_replace('audit.appointment_type.', '', $value);
                return __('audit.appointment_types.' . $typeKey, [], $value);
            case 'datetime':
                \Log::info('Formatting datetime in AuditLogService', ['value' => $value]);
                try {
                    $date = Carbon::parse($value);
                    $formatted = $date->format('Y-m-d H:i:s');
                    \Log::info('Formatted datetime in AuditLogService', ['value' => $value, 'formatted' => $formatted]);
                    return $formatted;
                } catch (\Exception $e) {
                    \Log::warning('Failed to parse datetime in AuditLogService', ['value' => $value, 'error' => $e->getMessage()]);
                    return $value;
                }
            case 'text':
            default:
                return $value;
        }
    }
}