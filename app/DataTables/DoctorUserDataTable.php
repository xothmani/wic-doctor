<?php

namespace App\DataTables;

use App\Models\User;
use App\Models\RoleOwnership;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\DataTableAbstract;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\Html\Builder;



class DoctorUserDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static array $customFields = [];

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
            ->editColumn('roles', function ($user) {
                $roles = $user->getRoleNames();
                return view('profile_management.users.role_badges', compact('roles'))->render();
            })
            ->addColumn('is_active', function ($user) {
                return $user->is_active
                    ? '<span class="badge badge-success">' . trans('lang.active') . '</span>'
                    : '<span class="badge badge-danger">' . trans('lang.inactive') . '</span>';
            })
            ->addColumn('start_date', function ($user) {
                return $user->start_date ?? '-';
            })
            ->addColumn('end_date', function ($user) {
                return $user->end_date ?? '-';
            })
            ->addColumn('action', 'profile_management.users.datatables_actions')
            ->rawColumns(['roles', 'is_active', 'action']);
    }


    /**
     * Get query source of dataTable.
     *
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model): \Illuminate\Database\Eloquent\Builder
    {
        $userId = auth()->id();
        $doctor = \App\Models\Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }

        $doctorId = $doctor->id;

        return $model->newQuery()
            ->join('doctor_associate', 'users.id', '=', 'doctor_associate.user_id')
            ->leftJoin('profile_management', function ($join) use ($doctorId) {
                $join->on('users.id', '=', 'profile_management.user_id')
                    ->where('profile_management.doctor_id', '=', $doctorId);
            })
            ->where('doctor_associate.doctor_id', $doctorId)
            ->select('users.*', 'profile_management.is_active', 'profile_management.start_date', 'profile_management.end_date');
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
            ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'),
                [
                    'language' => json_decode(
                        file_get_contents(
                            base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ),
                        true
                    )
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
        return [
            [
                'data' => 'name',
                'title' => trans('lang.user_name'),
            ],
            [
                'data' => 'email',
                'title' => trans('lang.user_email'),
            ],
            [
                'data' => 'roles',
                'title' => trans('lang.user_roles'),
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'start_date',
                'title' => trans('lang.start_date'),
            ],
            [
                'data' => 'end_date',
                'title' => trans('lang.end_date'),
            ],
            [
                'data' => 'is_active',
                'title' => trans('lang.is_active'),
                'orderable' => false,
                'searchable' => false,
            ],
        ];
    }



    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DoctorUser_' . date('YmdHis');
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf(): mixed
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }
}
