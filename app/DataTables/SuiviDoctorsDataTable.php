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
     
            ->editColumn('phone_number', function ($doctor) {
                return $doctor->phone_number ?? 'Non renseigné';
            })
            ->editColumn('verif_chart', function ($doctor) {
                $icon = '';
                if (is_null($doctor->verif_chart) || $doctor->verif_chart == 0) {
                    // Icône petite taille pour "non vérifié"
                    $icon = '<i class="fa fa-times" style="color: red; font-size: 16px; display: block; text-align: center;"></i>'; // Icône "x" rouge
                } else {
                    // Icône petite taille pour "vérifié"
                    $icon = '<i class="fa fa-check" style="color: green; font-size: 16px; display: block; text-align: center;"></i>'; // Icône "check" verte
                }
            
                // Centrer l'icône
                return '<div style="text-align: center;">' . $icon . '</div>';
            })
            
            
            ->editColumn('created_at', function ($doctor) {
                return $doctor->created_at ? $doctor->created_at->format('d-m-Y') : 'N/A';
            })
            ->editColumn('last_login_at', function ($doctor) {
                return $doctor->user && $doctor->user->last_login_at 
                    ? \Carbon\Carbon::parse($doctor->user->last_login_at)->format('d-m-Y H:i:s') 
                    : 'Non renseigné';
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
            
                // Centrer le badge
                return '<div style="text-align: center;"><span class="'.$badgeClass.'">'.$total.'%</span></div>';
            })
            
            
            
            ->addColumn('action', 'suivi_doctors.datatables_actions')
            ->rawColumns(['action','pourcentage','verif_chart']);
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
            'data' => 'phone_number',
            'title' => trans('lang.phone_number'),
        ],
        
        [
            'data' => 'created_at',
            'title' => trans('lang.user_created_at'),
        ],
        [
            'data' => 'last_login_at',
            'title' => trans('lang.last_login_at'),
        ],
        [
            'data' => 'pourcentage',
            'title' => trans('lang.pourcentage'),
            'orderable' => true, // Permettre le tri sur cette colonne
        ],
        [
            'data' => 'verif_chart',
            'title' => trans('lang.verif_chart'),
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
