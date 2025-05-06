<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use App\Events\AuditLogCreatedEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    public function createAuditLog($action, $entityType, $description = null, $oldValues = [], $newValues = [], $doctorId = null)
    {
        \Log::info('[AUDIT SERVICE] Creating audit log', ['action' => $action, 'entity_type' => $entityType]);

        // Generate description if not provided
        if (is_null($description)) {
            $description = $this->generateDescription($action, $newValues);
        }

        // Get entity ID from new values or old values
        $entityId = $newValues['id'] ?? $oldValues['id'] ?? null;

        // Create the audit log
        $auditLog = AuditLog::create([
            'user_id' => Auth::id(),
            'doctor_id' => $doctorId ?? (Auth::user() && Auth::user()->hasRole('doctor') ? Auth::user()->getDoctorId() : null),
            'user_role' => Auth::user() ? Auth::user()->getRoleNames()->first() : null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);

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







    protected function formatValuesForDisplay($values)
    {
        if (empty($values)) {
            return $values;
        }

        $formatted = [];

        foreach ($values as $key => $value) {
            // Skip null values
            if (is_null($value)) {
                $formatted[$this->formatFieldName($key)] = null;
                continue;
            }

            // Format based on field name
            switch (true) {
                // Date and time fields
                case Str::endsWith($key, ['_at', '_date', '_time', 'date', 'time']):
                    if ($value && $this->isValidDate($value)) {
                        $date = is_string($value) ? new Carbon($value) : $value;
                        $formatted[$this->formatFieldName($key)] = $date->format('Y-m-d H:i:s');
                    } else {
                        $formatted[$this->formatFieldName($key)] = $value;
                    }
                    break;

                // Boolean fields
                case is_bool($value):
                    $formatted[$this->formatFieldName($key)] = $value ? 'Yes' : 'No';
                    break;

                // ID references - try to get meaningful names
                case Str::endsWith($key, '_id') && !in_array($key, ['id']):
                    $formatted[$this->formatFieldName($key)] = $this->getReferenceName($key, $value);
                    break;

                // Status fields might use translation keys
                case Str::contains($key, ['status', 'type', 'state']):
                    if (is_string($value) && Str::contains($value, '.')) {
                        // Keep translations as they are
                        $formatted[$this->formatFieldName($key)] = $value;
                    } else {
                        $formatted[$this->formatFieldName($key)] = $value;
                    }
                    break;

                // Arrays or objects should be displayed as JSON
                case is_array($value) || is_object($value):
                    $formatted[$this->formatFieldName($key)] = json_encode($value, JSON_PRETTY_PRINT);
                    break;

                // Default: just keep the value as is
                default:
                    $formatted[$this->formatFieldName($key)] = $value;
                    break;
            }
        }

        return $formatted;
    }

    /**
     * Format field names to be more readable
     * 
     * @param string $fieldName
     * @return string
     */
    protected function formatFieldName($fieldName)
    {
        // Convert snake_case to Title Case
        return ucfirst(str_replace('_', ' ', $fieldName));
    }

    /**
     * Check if a value is a valid date string
     * 
     * @param mixed $value
     * @return bool
     */
    protected function isValidDate($value)
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            new Carbon($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Try to get a meaningful name for an ID reference
     * 
     * @param string $field
     * @param mixed $id
     * @return string
     */
    protected function getReferenceName($field, $id)
    {
        if (empty($id)) {
            return $id;
        }

        // Try to determine the model class based on the field name
        $modelName = null;

        switch ($field) {
            case 'doctor_id':
                $modelName = 'App\\Models\\Doctor';
                break;
            case 'patient_id':
                $modelName = 'App\\Models\\Patient';
                break;
            case 'user_id':
                $modelName = 'App\\Models\\User';
                break;
            case 'appointment_status_id':
                $modelName = 'App\\Models\\AppointmentStatus';
                break;
            case 'motif_id':
                $modelName = 'App\\Models\\Motif';
                break;
            // Add more mappings as needed
        }

        // If we have a model class, try to get the name
        if ($modelName && class_exists($modelName)) {
            try {
                $model = $modelName::find($id);

                if ($model) {
                    // Try common name fields
                    foreach (['name', 'title', 'label', 'full_name'] as $nameField) {
                        if (isset($model->$nameField)) {
                            return $model->$nameField . " (#{$id})";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors and fall back to just the ID
                Log::error('[AUDIT SERVICE] Error getting reference name', [
                    'error' => $e->getMessage(),
                    'field' => $field,
                    'id' => $id
                ]);
            }
        }

        // Default: return the ID
        return $id;
    }

    /**
     * Generate a descriptive message for the audit log
     * 
     * @param string $action
     * @param array $values
     * @return string
     */
    protected function generateDescription($action, $values)
    {
        $entity = isset($values['entity_type']) ? strtolower(class_basename($values['entity_type'])) : '';

        // If entity is empty, try to guess from action
        if (empty($entity)) {
            // Split action by underscore and get the last part (e.g., create_appointment -> appointment)
            $parts = explode('_', $action);
            if (count($parts) > 1) {
                $entity = end($parts);
            }
        }

        // Get entity id or name if available
        $entityIdentifier = '';
        if (isset($values['id'])) {
            $entityIdentifier = "#{$values['id']}";
        }

        // Patient information if available
        $patientInfo = '';
        if (isset($values['patient_id'])) {
            $patientName = $this->getReferenceName('patient_id', $values['patient_id']);
            $patientInfo = " for patient {$patientName}";
        }

        // Doctor information if available
        $doctorInfo = '';
        if (isset($values['doctor_id'])) {
            $doctorName = $this->getReferenceName('doctor_id', $values['doctor_id']);
            $doctorInfo = " with doctor {$doctorName}";
        }

        // Date information for appointments
        $dateInfo = '';
        if (isset($values['start_at']) && $this->isValidDate($values['start_at'])) {
            $date = new Carbon($values['start_at']);
            $dateInfo = " on " . $date->format('Y-m-d') . " at " . $date->format('H:i');
        }

        // Build description based on action
        switch ($action) {
            case 'create_appointment':
                return "Created appointment{$entityIdentifier}{$patientInfo}{$doctorInfo}{$dateInfo}";

            case 'update_appointment':
                return "Updated appointment{$entityIdentifier}{$patientInfo}{$doctorInfo}{$dateInfo}";

            case 'cancel_appointment':
                return "Cancelled appointment{$entityIdentifier}{$patientInfo}{$doctorInfo}{$dateInfo}";

            case 'create_patient':
                return "Created patient record{$entityIdentifier}";

            case 'update_patient':
                return "Updated patient information{$entityIdentifier}";

            case 'delete_patient':
                return "Deleted patient record{$entityIdentifier}";

            // Add more action types as needed

            default:
                // Generic description
                $actionVerb = Str::startsWith($action, 'create') ? 'Created' :
                    (Str::startsWith($action, 'update') ? 'Updated' :
                        (Str::startsWith($action, 'delete') ? 'Deleted' : 'Modified'));

                return "{$actionVerb} {$entity}{$entityIdentifier}";
        }
    }

    /**
     * Mark an audit log as read
     * 
     * @param int $id
     * @return bool
     */
    public function markAsRead($id)
    {
        try {
            $auditLog = AuditLog::findOrFail($id);
            $auditLog->read_at = now();
            return $auditLog->save();
        } catch (\Exception $e) {
            \Log::error('[AUDIT SERVICE] Error marking log as read', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}


