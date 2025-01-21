<?php

namespace App\DataTables;

use App\Models\Telesecretariat;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class TelesecretariatDataTable extends DataTable
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
            ->editColumn('nomCentre', function ($telesecretariat) {
                return $telesecretariat->nomCentre;
            })
      
   
            ->addColumn('responsable', function ($telesecretariat) {
                return $telesecretariat->user->name . ' ' . $telesecretariat->user->lastname;
            })
            ->addColumn('mail', function ($telesecretariat) {
                return $telesecretariat->user->email;
            })
      /*       ->editColumn('adresse', function ($telesecretariat) {
                return $telesecretariat->adresse;
            }) */
            ->editColumn('etat', function ($telesecretariat) {
                return $telesecretariat->etat ? trans('lang.active') : trans('lang.inactive');
            })
            ->addColumn('action', 'telesecretariats.datatables_actions')
            ->rawColumns(['action']);
    }
    

    /**
     * Get query source of dataTable.
     *
     * @param Telesecretariat $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Telesecretariat $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->select('telesecretariat.*');
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
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')),
                        true
                    ),
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
                'data' => 'nomCentre',
                'title' => trans('lang.telesecretariat_nom_centre'),
            ],
 
            [
                'data' => 'responsable',
                'title' => trans('lang.responsable'),
            ],
            [
                'data' => 'mail',
                'title' => trans('lang.telesecretariat_email'),
            ],
         /*    [
                'data' => 'adresse',
                'title' => trans('lang.telesecretariat_adresse'),
            ], */
            [
                'data' => 'etat',
                'title' => trans('lang.telesecretariat_etat'),
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
        return 'telesecretariatdatatable_' . time();
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
