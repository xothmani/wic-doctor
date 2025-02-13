<?php

namespace App\DataTables;

use App\Models\DoctorBlog;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DoctorBlogDataTable extends DataTable
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
            ->editColumn('titre', function ($blog) {
                return $blog->titre;
            })
            ->editColumn('titre_court', function ($blog) {
                return $blog->titre_court;
            })
            ->editColumn('contenu', function ($blog) {
                return '<a href="#" onclick="showModal(`'. addslashes($blog->contenu) .'`)" style="color: black; text-decoration: none;">'
                    . substr($blog->contenu, 0, 100) . 'lire la suite</a>';
            })
            ->editColumn('status', function ($blog) {
                return $blog->status;
            })
            ->addColumn('action', 'doctor_blog.datatables_actions')
            ->rawColumns(['contenu', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param DoctorBlog $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DoctorBlog $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->select('doctor_blogs.*');
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
                'data' => 'titre_court',
                'title' => trans('lang.blog_short_title'),
            ],
            [
                'data' => 'titre',
                'title' => trans('lang.blog_title'),
            ],
            [
                'data' => 'contenu',
                'title' => trans('lang.blog_content'),
            ],
            [
                'data' => 'status',
                'title' => trans('lang.blog_status'),
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
        return 'doctorblogsdatatable_' . time();
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
