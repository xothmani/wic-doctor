<?php

namespace App\Http\Controllers;

use App\DataTables\AuditLogDataTable;
use App\Models\AuditLog;
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

    // In your AuditLogController.php
    public function show(AuditLog $auditLog)
    {
        // Mark as read when viewed
        if (!$auditLog->read_at) {
            $auditLog->update(['read_at' => now()]);
        }

        return response()->json([
            'log' => $auditLog->load('user', 'doctor'),
            'html' => view('audit-logs.partials.detail', compact('auditLog'))->render()
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
    public function getDetails($id)
    {
        // Fetch the audit log by ID
        $auditLog = AuditLog::findOrFail($id);

        // Retrieve the related user and doctor names
        $userName = $auditLog->user ? $auditLog->user->name . ' ' . $auditLog->user->name : 'System';
        $doctorName = $auditLog->doctor ? $auditLog->doctor->name . ' ' . $auditLog->doctor->name : 'N/A';

        // Prepare the response with the details
        return response()->json([
            'user_name' => $userName,
            'doctor_name' => $doctorName,
            'action' => $auditLog->action,
            'entity_type' => $auditLog->entity_type,
            'old_values' => json_decode($auditLog->old_values, true),
            'new_values' => json_decode($auditLog->new_values, true),
            'created_at' => $auditLog->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    // AuditLogController.php
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



    public function markAsRead(Request $request, $id)
    {
        $auditLog = AuditLog::findOrFail($id);
        $user = Auth::user();

        if ($user->hasRole('doctor') && $auditLog->doctor_id === $user->doctor_id) {
            $auditLog->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        } elseif (
            $user->hasRole(['secretary', 'telesecretary']) &&
            ($auditLog->doctor_id === $user->doctor_id || $auditLog->user_id === $user->id)
        ) {
            $auditLog->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
    // AuditLogController.php
    public function recent()
    {
        $logs = AuditLog::where('doctor_id', auth()->user()->getDoctorId())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $log->created_at->toISOString(),
                    // Add other needed fields
                ];
            });

        return response()->json($logs);
    }

}