<?php

namespace App\DataTables;

use App\Models\Prescription;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class PrescriptionDataTable extends DataTable
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
            ->editColumn('date', function ($prescription) {
                // Vérifiez si date est une instance de Carbon
                return $prescription->date instanceof \Carbon\Carbon 
                    ? $prescription->date->format('Y-m-d') 
                    : $prescription->date ?? 'N/A';
            })
            ->editColumn('observation', function ($prescription) {
                return $prescription->observation ?? 'N/A';
            })
            ->addColumn('medicaments', function ($prescription) {
                return $prescription->medicaments->pluck('NOM_COMMERCIAL')->implode(', ') ?? 'N/A'; // Assurez-vous d'avoir une propriété 'nom' sur le modèle Medicament
            })
            ->addColumn('action', 'prescriptions.datatables_actions') // Vue pour les actions
            ->rawColumns(array_merge(array_column($this->getColumns(), 'data'), ['action']));
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
                'data' => 'date',
                'title' => trans('lang.prescription_date'),
            ],
            [
                'data' => 'observation',
                'title' => trans('lang.prescription_observation'),
            ],
            [
                'data' => 'medicaments',
                'title' => trans('lang.prescription_medicaments'),
            ],
        ];
    }

    /**
     * Get query source of dataTable.
     *
     * @param Prescription $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Prescription $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->with('medicaments'); // Charger les medicaments en eager loading
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
            ->parameters(config('datatables-buttons.parameters'));
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'prescriptionsdatatable_' . time();
    }
}
