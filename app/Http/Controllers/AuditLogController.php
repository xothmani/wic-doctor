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

    public function show($id)
    {
        $auditLog = AuditLog::findOrFail($id);
        return view('audit_logs.show', compact('auditLog'));
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





}