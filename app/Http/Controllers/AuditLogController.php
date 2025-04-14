<?php

namespace App\Http\Controllers;

use App\DataTables\AuditLogDataTable;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request, AuditLogDataTable $auditLogDataTable)
    {
        \Log::info('[AUDIT CONTROLLER] Request params', [
            'action' => $request->action,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        return $auditLogDataTable->render('audit_logs.index', [
            'actions' => AuditLog::distinct('action')->pluck('action')
        ]);
    }

    public function filter(Request $request, AuditLogDataTable $auditLogDataTable)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'action' => 'nullable|string',
        ]);

        return $auditLogDataTable->render('audit_logs.index', [
            'actions' => AuditLog::distinct('action')->pluck('action'),
            'selected_action' => $request->action,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        if (!$auditLog->read_at) {
            $auditLog->update(['read_at' => now()]);
        }

        return response()->json([
            'log' => $auditLog->load('user', 'doctor'),
            'html' => view('audit_logs.partials.detail', compact('auditLog'))->render()
        ]);
    }

    public function userHistory(Request $request, AuditLogDataTable $auditLogDataTable)
    {
        $userId = Auth::id();

        return $auditLogDataTable
            ->with([
                'userId' => $userId,
                'action' => $request->get('action'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date')
            ])
            ->render('audit_logs.user_history', [
                'actions' => AuditLog::distinct('action')->pluck('action'),
                'selected_action' => $request->get('action'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date')
            ]);
    }


    public function unreadCount(Request $request)
    {
        try {
            $doctorId = auth()->user()->getDoctorId();

            $unreadCount = AuditLog::where('doctor_id', $doctorId)
                ->whereNull('read_at')
                ->count();

            return response()->json(['unread_count' => $unreadCount]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error'], 500);
        }
    }


    /* public function getDetails($id)
    {
        \Log::info('[AUDIT CONTROLLER] Fetching audit log details', ['id' => $id]);
        try {
            $auditLog = AuditLog::with(['user', 'doctor'])->findOrFail($id);

            // Prepare old and new values with names instead of IDs
            $oldValues = $auditLog->old_values ?? [];
            $newValues = $auditLog->new_values ?? [];

            // Helper function to resolve IDs to names
            $resolveNames = function ($values) {
                if (isset($values['creator_id'])) {
                    $user = User::find($values['creator_id']);
                    $values['creator_id'] = $user ? $user->name : __('lang.na');
                }
                if (isset($values['doctor_id'])) {
                    $doctor = Doctor::find($values['doctor_id']);
                    $values['doctor_id'] = $doctor ? $doctor->name : __('lang.na');
                }
                if (isset($values['patient_id'])) {
                    $patient = Patient::find($values['patient_id']);
                    $values['patient_id'] = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : __('lang.na');
                }
                return $values;
            };

            $oldValues = $resolveNames($oldValues);
            $newValues = $resolveNames($newValues);

            $response = [
                'action' => $auditLog->action,
                'entity_type' => $auditLog->entity_type,
                'description' => $auditLog->description,
                'user_name' => $auditLog->user ? $auditLog->user->name : __('lang.system'),
                'created_at' => $auditLog->created_at->format('Y-m-d H:i:s'),
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ];

            \Log::info('[AUDIT CONTROLLER] Audit log details fetched', ['id' => $id]);
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Error fetching audit log details: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'Audit log not found'], 404);
        }
    } */
    public function getDetails($id)
    {
        \Log::info('[AUDIT CONTROLLER] Fetching audit log details', ['id' => $id]);
        try {
            $auditLog = AuditLog::with(['user', 'doctor'])->findOrFail($id);

            // Prepare old and new values with names and translations
            $oldValues = $auditLog->old_values ?? [];
            $newValues = $auditLog->new_values ?? [];

            // Helper function to resolve IDs to full names and translate values
            $resolveNamesAndTranslate = function ($values) {
                // Resolve names for IDs
                if (isset($values['creator_id'])) {
                    $user = User::find($values['creator_id']);
                    $values['creator_id'] = $user ? $user->name : __('lang.na');
                }
                if (isset($values['doctor_id'])) {
                    $doctor = Doctor::find($values['doctor_id']);
                    $values['doctor_id'] = $doctor ? $doctor->name : __('lang.na');
                }
                if (isset($values['patient_id'])) {
                    $patient = Patient::find($values['patient_id']);
                    $values['patient_id'] = $patient ? trim($patient->first_name . ' ' . $patient->last_name) : __('lang.na');
                }

                // Translate statuses
                if (isset($values['old_status'])) {
                    $values['old_status'] = __('lang.statuses.' . strtolower($values['old_status']), [], $values['old_status']);
                }
                if (isset($values['new_status'])) {
                    $values['new_status'] = __('lang.statuses.' . strtolower($values['new_status']), [], $values['new_status']);
                }
                if (isset($values['old_status_id'])) {
                    $values['old_status_id'] = __('lang.statuses.' . $values['old_status_id'], [], $values['old_status_id']);
                }
                if (isset($values['new_status_id'])) {
                    $values['new_status_id'] = __('lang.statuses.' . $values['new_status_id'], [], $values['new_status_id']);
                }

                // Translate appointment type
                if (isset($values['appointment_type'])) {
                    $typeKey = str_replace('audit.appointment_type.', '', $values['appointment_type']);
                    $values['appointment_type'] = __('lang.appointment_types.' . $typeKey, [], $values['appointment_type']);
                }

                // Replace 'N/A' with translated value
                foreach ($values as $key => $value) {
                    if ($value === 'N/A') {
                        $values[$key] = __('lang.na');
                    }
                }

                return $values;
            };

            $oldValues = $resolveNamesAndTranslate($oldValues);
            $newValues = $resolveNamesAndTranslate($newValues);

            $response = [
                'action' => __('lang.actions.' . $auditLog->action, [], $auditLog->action),
                'entity_type' => __('lang.entities.' . $auditLog->entity_type, [], $auditLog->entity_type),
                'description' => __('lang.descriptions.' . $auditLog->description, [], $auditLog->description),
                'user_name' => $auditLog->user ? $auditLog->user->name : __('lang.system'),
                'created_at' => $auditLog->created_at->format('Y-m-d H:i:s'),
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ];

            \Log::info('[AUDIT CONTROLLER] Audit log details fetched', ['id' => $id]);
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Error fetching audit log details: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'Audit log not found'], 404);
        }
    }
    public function markAsRead(Request $request, $id)
    {
        \Log::info('markAsRead called for ID: ' . $id);
        try {
            $auditLog = AuditLog::findOrFail($id);
            $user = Auth::user();
            $doctorId = auth()->user()->getDoctorId();

            \Log::info('User attempting mark as read', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames()->toArray(),
                'doctor_id' => $user->doctor_id,
                'audit_log_doctor_id' => $auditLog->doctor_id
            ]);

            // Allow doctors to mark as read (temporary fix from previous)
            if ($user->hasRole('doctor')) {
                $readAt = now()->format('Y-m-d H:i:s'); // Ensure proper format
                $auditLog->update(['read_at' => $readAt]);
                \Log::info('Marked as read by doctor', [
                    'audit_log_id' => $id,
                    'read_at' => $readAt
                ]);
                return response()->json(['success' => true]);
            } elseif (
                $user->hasRole(['secretary', 'telesecretary']) &&
                ($auditLog->doctor_id === $doctorId || $auditLog->user_id === $user->id)
            ) {
                $readAt = now()->format('Y-m-d H:i:s');
                $auditLog->update(['read_at' => $readAt]);
                \Log::info('Marked as read by secretary/telesecretary', [
                    'audit_log_id' => $id,
                    'read_at' => $readAt
                ]);
                return response()->json(['success' => true]);
            }

            \Log::warning('Unauthorized attempt to mark as read', [
                'user_id' => $user->id,
                'audit_log_id' => $id,
                'reason' => 'User does not have required role or matching doctor_id'
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Audit log not found for mark as read: ID ' . $id);
            return response()->json(['error' => 'Audit log not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Error marking audit log as read: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function recent(Request $request)
    {
        \Log::info('[AUDIT CONTROLLER] Fetching recent audit logs');
        $doctorId = auth()->user()->getDoctorId();

        try {
            $user = Auth::user();
            $query = AuditLog::with(['user', 'doctor'])
                ->where('read_at', null); // Only unread logs

            if ($user->hasRole('doctor') && $doctorId) {
                $query->where('doctor_id', $doctorId);
            } elseif ($user->hasRole(['secretary', 'telesecretary'])) {
                $query->where(function ($q) use ($user) {
                    $q->where('doctor_id', $user()->getDoctorId())
                        ->orWhere('user_id', $user->id);
                });
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => __($log->action),
                        'entity_type' => __($log->entity_type),
                        'description' => $log->description,
                        'user_name' => $log->user ? $log->user->name : __('System'),
                        'created_at' => $log->created_at->format('Y-m-d H:i:s')
                    ];
                });

            \Log::info('[AUDIT CONTROLLER] Recent logs fetched', ['count' => $logs->count()]);
            return response()->json($logs);
        } catch (\Exception $e) {
            \Log::error('Error fetching recent audit logs: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}