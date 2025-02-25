<?php

namespace App\DataTables;

use App\Models\Doctor;  
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\DataTableAbstract;

class SuiviDoctorsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(mixed $query): DataTableAbstract
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable   
            ->editColumn('name', function ($doctor) {
                return $doctor->name;
            })
            ->editColumn('speciality_name', function ($doctor) {
                $speciality = json_decode($doctor->speciality_name, true);
                return $speciality['fr'] ?? 'N/A';
            })
            ->editColumn('email', function ($doctor) {
                return $doctor->email ?? 'Non renseigné';
            })
            ->editColumn('phone_number', function ($doctor) {
                return $doctor->phone_number ?? 'Non renseigné';
            })
            ->editColumn('created_at', function ($doctor) {
                return $doctor->created_at ? $doctor->created_at->format('d/m/Y') : 'N/A';
            })
            ->addColumn('pourcentage', function ($doctor) {
                $total = $doctor->getTotalPourcentage();
                
                // Choisir la couleur du badge selon le pourcentage
                $badgeClass = 'badge bg-secondary'; // Par défaut
            
                if ($total >= 80) {
                    $badgeClass = 'badge bg-success'; // Vert si >= 80%
                } elseif ($total >= 50) {
                    $badgeClass = 'badge bg-warning'; // Jaune si >= 50%
                } else {
                    $badgeClass = 'badge bg-danger'; // Rouge si < 50%
                }
            
                return '<span class="'.$badgeClass.'">'.$total.'%</span>';
            })
            
            
            ->addColumn('action', 'photos_cabinet.datatables_actions')
            ->rawColumns(['action','pourcentage']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Doctor $model): QueryBuilder
    {
        return $model->newQuery()
            ->select(
                "doctors.*",
                "specialities.name as speciality_name",
                "users.email",
                "users.phone_number",
                \DB::raw('(
                    COALESCE(doctors.pourcentage_avatar, 0) +
                    COALESCE(doctors.pourcentage_adresse, 0) +
                    COALESCE(doctors.pourcentage_cv, 0) +
                    COALESCE(doctors.pourcentage_cabinet, 0) +
                    COALESCE(doctors.pourcentage_tags, 0) +
                    COALESCE(doctors.pourcentage_profil, 0)
                ) AS total_pourcentage') // Calcul du pourcentage total
            )
            ->leftJoin("doctor_specialities", "doctors.id", "=", "doctor_specialities.doctor_id")
            ->leftJoin("specialities", "doctor_specialities.speciality_id", "=", "specialities.id")
            ->leftJoin("users", "doctors.user_id", "=", "users.id")
            ->orderBy('total_pourcentage', 'asc'); // Tri par pourcentage croissant
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'),
                [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')),
                        true
                    ),
                    'order' => [[5, 'asc']], // Tri par défaut sur la colonne 'pourcentage' (index 5)
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
            'title' => trans('lang.nomComplet'),
        ],
        [
            'data' => 'speciality_name',
            'title' => trans('lang.speciality'),
        ],
        [
            'data' => 'email',
            'title' => trans('lang.email'),
        ],
        [
            'data' => 'phone_number',
            'title' => trans('lang.phone_number'),
        ],
        [
            'data' => 'created_at',
            'title' => trans('lang.user_created_at'),
        ],
        [
            'data' => 'pourcentage',
            'title' => trans('lang.pourcentage'),
            'orderable' => true, // Permettre le tri sur cette colonne
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
        return 'doctorsdatatable_' . time();
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
