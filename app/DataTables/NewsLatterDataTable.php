<?php

namespace App\DataTables;

use App\Models\NewsLatter;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class NewsLatterDataTable extends DataTable
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
            ->editColumn('email', function ($newsLatter) {
                return $newsLatter->email;
            })
            ->editColumn('created_at', function ($newsLatter) {
                return $newsLatter->created_at->format('d/m/Y'); // Format de date sans l'heure
            });
            // Pas d'ajout de colonne 'action'
    }

    /**
     * Get query source of dataTable.
     *
     * @param NewsLatter $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(NewsLatter $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()
                     ->select("News_latter.*")
                     ->orderBy('created_at', 'desc'); // Tri par 'created_at' du plus récent au plus ancien
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
            // Pas d'ajout de colonne 'action'
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true)
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
                'data' => 'email',
                'title' => trans('lang.news_latter_email'), // Traduction ou texte statique
            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.news_latter_created_at'), // Traduction ou texte statique
            ],
            // Pas de colonne 'action'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'news_latterdatatable_' . time();
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
