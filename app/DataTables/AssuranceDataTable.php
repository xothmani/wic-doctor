<?php

namespace App\DataTables;

use App\Models\Assurance;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Log;
class AssuranceDataTable extends DataTable
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
        
        $columns = array_column($this->getColumns(), 'data');
        Log::info('Données récupérées pour les assurances:', ['query' => $query]);
        $dataTable = $dataTable
            ->editColumn('nom', function ($assurance) {
                return $assurance->nom;
            })
            ->editColumn('description', function ($assurance) {
                return $assurance->description;
            })
            ->addColumn('action', 'assurances.datatables_actions') // Si vous avez une vue pour les actions
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
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
                'data' => 'nom',
                'title' => trans('lang.assurance_nom'), // Traduction du nom
            ],
            [
                'data' => 'description',
                'title' => trans('lang.assurance_description'), // Traduction de la description
            ],
/*             [
                'data' => 'created_at',
                'title' => trans('lang.assurance_created_at'),
                'searchable' => false,
            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.assurance_updated_at'),
                'searchable' => false,
            ], */
        ];
    }

    /**
     * Get query source of dataTable.
     *
     * @param Assurance $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Assurance $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use HTML builder.
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
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
                ]
            ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf(): mixed
    {
        $data = $this->getDataForPrint();
        $pdf = \Barryvdh\DomPDF\Facade::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'assurancesdatatable_' . time();
    }
}
