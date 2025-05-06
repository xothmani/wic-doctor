<?php

namespace App\DataTables;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AuditLogDataTable extends DataTable
{
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
            ->addColumn('changes', function (AuditLog $auditLog) {
                return '<button type="button" class="btn btn-xs btn-primary view-changes" data-id="' . $auditLog->id . '"><i class="fa fa-eye"></i> ' . __('lang.view_changes') . '</button>';
            })
            ->addColumn('mark_as_read', function (AuditLog $auditLog) {
                if (!$auditLog->read_at) {
                    return '<button type="button" class="btn btn-xs btn-info mark-read" data-id="' . $auditLog->id . '"><i class="fa fa-check"></i> ' . __('lang.mark_as_read') . '</button>';
                }
                return '<span class="badge bg-success">' . __('lang.read') . '</span>';
            })
            ->editColumn('action', function (AuditLog $auditLog) use ($translations) {
                return $translations['actions'][$auditLog->action] ?? $auditLog->action;
            })
            ->editColumn('entity_type', function (AuditLog $auditLog) use ($translations) {
                return $translations['entities'][$auditLog->entity_type] ?? $auditLog->entity_type;
            })
            ->filterColumn('action', function ($query, $keyword) {
                \Log::info('[AUDIT DATATABLE] Filtering action', ['keyword' => $keyword]);
                $query->where('action', 'like', "%{$keyword}%");
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                \Log::info('[AUDIT DATATABLE] Filtering date', ['keyword' => $keyword]);
                $query->whereDate('created_at', $keyword);
            })
            ->rawColumns(['changes', 'mark_as_read', 'action'])
            ->setRowId('id')
            ->setRowClass(function (AuditLog $auditLog) {
                return $auditLog->read_at ? '' : 'fw-bold';
            })
            ->setRowAttr([
                'data-read-at' => function (AuditLog $auditLog) {
                    return $auditLog->read_at;
                },
            ])
            ->orderColumn('created_at', 'created_at $1')
            ->order(function ($query) {
                $query->orderBy('created_at', 'desc');
            });
    }

    public function query(AuditLog $model)
    {
        \Log::info('[AUDIT DATATABLE] Query triggered');

        $query = $model->newQuery();
        $user = Auth::user();
        $doctorId = auth()->user()->getDoctorId();

        \Log::info('[AUDIT DATATABLE] User info', [
            'user_id' => $user->id,
            'roles' => $user->getRoleNames(),
            'doctor_id' => $doctorId
        ]);

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('doctor') && $doctorId) {
            $query->where('doctor_id', $doctorId);
        } elseif ($user->hasRole(['secretary', 'telesecretary']) && $doctorId) {
            $query->where(function ($q) use ($doctorId, $user) {
                $q->where('doctor_id', $doctorId)
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($action = $this->request()->get('action')) {
            $query->where('action', $action);
        }

        if ($start = $this->request()->get('start_date')) {
            $query->whereDate('created_at', '>=', $start);
        }

        if ($end = $this->request()->get('end_date')) {
            $query->whereDate('created_at', '<=', $end);
        }

        if ($id = $this->request()->get('id')) {
            $query->where('id', $id);
        }

        \Log::info('[AUDIT DATATABLE] Final query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('audit-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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

    protected function getColumns()
    {
        $locale = app()->getLocale();
        $translations = json_decode(file_get_contents(base_path("resources/lang/{$locale}/audit.json")), true);
        $columnTranslations = $translations['columns'] ?? [];

        return [
            Column::make('id')->hidden(),
            Column::make('user')->title($columnTranslations['user'] ?? 'User'),
            Column::make('doctor')->title($columnTranslations['doctor'] ?? 'Doctor'),
            Column::make('user_role')->title($columnTranslations['user_role'] ?? 'Role'),
            Column::make('action')->title($columnTranslations['action'] ?? 'Action'),
            Column::make('entity_type')->title($columnTranslations['entity_type'] ?? 'Entity Type'),
            Column::make('description')->title($columnTranslations['description'] ?? 'Description'),
            Column::make('changes')->title($columnTranslations['changes'] ?? 'Changes'),
            Column::make('created_at')->title($columnTranslations['created_at'] ?? 'Date'),
            Column::make('mark_as_read')->title($columnTranslations['mark_as_read'] ?? 'Status'),
        ];
    }

    protected function filename(): string
    {
        return 'AuditLogs_' . date('YmdHis');
    }
}