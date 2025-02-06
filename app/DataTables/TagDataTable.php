<?php

namespace App\DataTables;

use App\Models\Tag;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class TagDataTable extends DataTable
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
            ->editColumn('name', function ($tag) {
                $name = json_decode($tag->name, true); // Décoder le JSON
                return $name['fr'] ?? 'Non défini'; // Récupérer 'fr' ou afficher un texte par défaut
            })
            ->editColumn('country', function ($tag) {
                return $tag->country;
            })
            ->addColumn('speciality', function ($tag) {
                return $tag->speciality ? $tag->speciality->name : 'N/A';
            })
            ->addColumn('action', 'tags.datatables_actions')
            ->rawColumns(['name', 'action']);
    }
    
    

    /**
     * Get query source of dataTable.
     *
     * @param Tag $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Tag $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->with('speciality'); // Charge la relation speciality
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
                'data' => 'name',
                'title' => trans('lang.tag_name'), // Nom du tag
            ],
            [
                'data' => 'speciality',
                'title' => trans('lang.speciality'), // Nom de la spécialité
            ],
            [
                'data' => 'country',
                'title' => trans('lang.country'), // country de la spécialité
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
        return 'tagdatatable_' . time();
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
