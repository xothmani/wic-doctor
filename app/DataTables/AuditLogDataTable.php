<?php

namespace App\DataTables;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Str;


class AuditLogDataTable extends DataTable
{
    /**
     * Build DataTable class.
     */
    public function dataTable($query)
    {
        \Log::info('[AUDIT DATATABLE] dataTable triggered');

        $locale = app()->getLocale();
        $translations = json_decode(file_get_contents(base_path("resources/lang/{$locale}/audit.json")), true);

        return datatables()
            ->eloquent($query)
            ->addColumn('user', function (AuditLog $auditLog) {
                return $auditLog->user ? $auditLog->user->name . " (#{$auditLog->user_id})" : __('System');
            })
            ->addColumn('doctor', function (AuditLog $auditLog) {
                return $auditLog->doctor ? $auditLog->doctor->name . " (#{$auditLog->doctor_id})" : __('N/A');
            })
            ->addColumn('created_at', function (AuditLog $auditLog) {
                return $auditLog->created_at->format('Y-m-d H:i:s');
            })
            ->editColumn('action', function (AuditLog $auditLog) use ($translations) {
                return $translations['actions'][$auditLog->action] ?? $auditLog->action;
            })
            ->editColumn('entity_type', function (AuditLog $auditLog) use ($translations) {
                return $translations['entities'][$auditLog->entity_type] ?? $auditLog->entity_type;
            })
            ->editColumn('old_values', function (AuditLog $auditLog) {
                $oldValues = $auditLog->old_values;

                // Replace the user and doctor IDs with names
                if (isset($newValues['creator_id'])) {
                    $user = $auditLog->user ? $auditLog->user->name . ' ' : 'System';
                    $newValues['creator_id'] = $user;  // Replace ID with name
                }
                if (isset($oldValues['doctor_id'])) {
                    $doctor = $auditLog->doctor ? $auditLog->doctor->name . ' ' . $auditLog->doctor->name : 'N/A';
                    $oldValues['doctor_id'] = $doctor;  // Replace ID with name
                }
                if (isset($newValues['patient_id'])) {
                    // Log the current values
                    \Log::info('newvalues:', ['new_values' => $newValues]);
                    \Log::info('auditlog:', ['auditlog' => $auditLog]);

                    // Get the patient ID from new_values
                    $patientId = $newValues['patient_id'];

                    // Directly fetch the patient from the database using the ID
                    $patientModel = \App\Models\Patient::find($patientId);

                    if ($patientModel) {
                        $patient = $patientModel->first_name . ' ' . $patientModel->last_name;
                    } else {
                        $patient = 'N/A (ID: ' . $patientId . ')';
                    }

                    \Log::info('patient:', ['patient' => $patient]);
                    $newValues['patient_id'] = $patient;  // Replace ID with name
                }
                return '<button type="button" class="btn btn-sm btn-primary view-values" data-toggle="modal" data-target="#valuesModal" data-values=\'' . htmlspecialchars(json_encode($oldValues), ENT_QUOTES, 'UTF-8') . '\' data-title="' . __('Old Values') . '"><i class="fa fa-eye"></i> ' . __('View') . '</button>';
            })
            ->editColumn('new_values', function (AuditLog $auditLog) {
                $newValues = $auditLog->new_values;
                \Log::info('newvalues:', ['new_values' => $newValues]);
                // Replace the user and doctor IDs with names
                if (isset($newValues['creator_id'])) {
                    $user = $auditLog->user ? $auditLog->user->name . ' ' : 'System';
                    $newValues['creator_id'] = $user;
                }
                if (isset($newValues['doctor_id'])) {
                    $doctor = $auditLog->doctor ? $auditLog->doctor->name . ' ' : 'N/A';
                    $newValues['doctor_id'] = $doctor;  // Replace ID with name
    
                }
                if (isset($newValues['patient_id'])) {
                    // Log the current values
                    \Log::info('newvalues:', ['new_values' => $newValues]);
                    \Log::info('auditlog:', ['auditlog' => $auditLog]);

                    // Get the patient ID from new_values
                    $patientId = $newValues['patient_id'];

                    // Directly fetch the patient from the database using the ID
                    $patientModel = \App\Models\Patient::find($patientId);

                    if ($patientModel) {
                        $patient = $patientModel->first_name . ' ' . $patientModel->last_name;
                    } else {
                        $patient = 'N/A (ID: ' . $patientId . ')';
                    }

                    \Log::info('patient:', ['patient' => $patient]);
                    $newValues['patient_id'] = $patient;  // Replace ID with name
                }
                return '<button type="button" class="btn btn-sm btn-primary view-values" data-toggle="modal" data-target="#valuesModal" data-values=\'' . htmlspecialchars(json_encode($newValues), ENT_QUOTES, 'UTF-8') . '\' data-title="' . __('New Values') . '"><i class="fa fa-eye"></i> ' . __('View') . '</button>';
            })


            ->filterColumn('action', function ($query, $keyword) {
                \Log::info('[AUDIT DATATABLE] Filtering action', ['keyword' => $keyword]);
                $query->where('action', 'like', "%{$keyword}%");
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                \Log::info('[AUDIT DATATABLE] Filtering date', ['keyword' => $keyword]);
                $query->whereDate('created_at', $keyword);
            })
            ->rawColumns(['action', 'old_values', 'new_values', 'see_all_info'])
            ->setRowId('id')
            ->setRowClass(function (AuditLog $auditLog) {
                return $auditLog->read_at ? '' : 'fw-bold';
            })
            ->setRowAttr([
                'data-read-at' => function (AuditLog $auditLog) {
                    return $auditLog->read_at;
                },
            ]);
    }


    public function query(AuditLog $model)
    {
        \Log::info('[AUDIT DATATABLE] Query triggered');

        $query = $model->newQuery();
        $user = Auth::user();
        $doctorId = $user->doctor_id;

        \Log::info('[AUDIT DATATABLE] User info', [
            'user_id' => $user->id,
            'roles' => $user->getRoleNames(),
            'doctor_id' => $doctorId
        ]);
        if ($user->hasRole('admin')) {
            return $query; // admin → accès total
        }

        // 🧑‍⚕️ Cas médecin
        if ($user->hasRole('doctor') && $doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        // 🧑‍💼 Cas secrétaire ou télésécrétaire
        elseif ($user->hasRole(['secretary', 'telesecretary']) && $doctorId) {
            $query->where(function ($q) use ($doctorId, $user) {
                $q->where('doctor_id', $doctorId)
                    ->orWhere('user_id', $user->id);
            });
        }

        // ✅ Filtres dynamiques (action/date)
        if ($action = $this->request()->get('action')) {
            $query->where('action', $action);
        }

        if ($start = $this->request()->get('start_date')) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end = $this->request()->get('end_date')) {
            $query->whereDate('created_at', '<=', $end);
        }

        \Log::info('[AUDIT DATATABLE] Final query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query;
    }




    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('audit-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('frtip') // No buttons
            ->orderBy(1)
            ->parameters([
                'language' => json_decode(
                    file_get_contents(
                        base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                    ),
                    true
                ),
            ]);
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        $locale = app()->getLocale();
        $translations = json_decode(file_get_contents(base_path("resources/lang/{$locale}/audit.json")), true);
        $columnTranslations = $translations['columns'] ?? [];

        return [
            Column::make('id'),
            Column::make('user')->title($columnTranslations['user'] ?? 'User'),
            Column::make('doctor')->title($columnTranslations['doctor'] ?? 'Doctor'),
            Column::make('user_role')->title($columnTranslations['user_role'] ?? 'Role'),
            Column::make('action')->title($columnTranslations['action'] ?? 'Action'),
            Column::make('entity_type')->title($columnTranslations['entity_type'] ?? 'Entity Type'),
            Column::make('entity_id')->title($columnTranslations['entity_id'] ?? 'Entity ID'),
            Column::make('description')->title($columnTranslations['description'] ?? 'Description'),
            Column::make('old_values')->title($columnTranslations['old_values'] ?? 'Old Values'),
            Column::make('new_values')->title($columnTranslations['new_values'] ?? 'New Values'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'AuditLogs_' . date('YmdHis');
    }
}