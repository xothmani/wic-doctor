<?php

namespace App\DataTables;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class DoctorPermissionDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable(mixed $query): DataTableAbstract
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable
            ->editColumn('class', function ($permission) {
                return explode('.', $permission->name)[0];
            })
            ->editColumn('roles', function ($permission) {
                return json_encode([
                    'permission' => $permission->name,
                    'roles' => $permission->roles->pluck('name')->toArray(),
                ]);
            })
            ->editColumn('namerole', function ($permission) {
                return json_encode([
                    'permission' => $permission->name,
                    'roles' => $permission->roles->pluck('name')->toArray(),
                ]);
            })
            ->addColumn('action', 'doctor_permissions.datatables_actions'); // Update the action view path if needed
    }

    /**
     * Get query source of dataTable.
     *
     * @param Permission $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Permission $model): \Illuminate\Database\Eloquent\Builder
    {
        // Fetch permissions related to the 'doctor' role
        return $model->newQuery()->whereHas('roles', function ($query) {
            $query->where('name', 'doctor');
        })->with('roles');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */
    public function html(): Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['title' => trans('lang.actions'), 'width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'),
                [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')),
                        true
                    ),
                    'rowGroup' => [
                        'dataSrc' => 'class',
                    ],
                    'colReorder' => false,
                    'fixedColumns' => false,
                    "initComplete" => "function(settings){console.log('initComplete'); renderButtons(settings.sTableId); renderiCheck(settings.sTableId)}",
                    "stateSaveParams" => "function(settings){console.log('stateSaveParams'); renderiCheck(settings.sTableId);}"
                ]
            ));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $columns = [
            [
                'data' => 'name',
                'title' => trans('lang.permission_name'),
                'searchable' => true,
            ],
            [
                'data' => 'class',
                'title' => trans('lang.permission_class'),
                'visible' => false,
                'className' => "hide",
                'searchable' => false,
            ],
            [
                'data' => 'guard_name',
                'title' => trans('lang.permission_guard_name'),
                'searchable' => false,
            ],
            [
                'data' => 'roles',
                'title' => trans('lang.role_plural'),
                'visible' => false,
                'className' => "hide",
                'searchable' => false,
            ],
            [
                'data' => 'namerole',
                'title' => trans('lang.role_plural'),
                'visible' => false,
                'className' => "hide",
                'searchable' => false,
            ],
        ];

        // Add columns dynamically for each role
        $roles = Role::where('name', 'Secretary')->get();
        foreach ($roles as $role) {
            $newColumn = [
                'data' => 'roles',
                'title' => $role->name,
                'searchable' => 'false',
                'exportable' => 'false',
                'printable' => 'false',
                'render' => 'function(){return "<div class=\'icheck-default icheck-permission\'><input  type=\'checkbox\' name=\'namehere\' class=\'permission\' data-role-name=\'' . $role->name . '\' data-role-id=\'' . $role->id . '\' data-permission=\'"+data+"\'><label for=\'namehere\'></label></div>"}',
            ];
            $columns[] = $newColumn;
        }

        return $columns;
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'doctor_permissions_' . time();
    }
}
