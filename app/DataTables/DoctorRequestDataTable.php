<?php

namespace App\DataTables;

use App\Models\DoctorRequest;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DoctorRequestDataTable extends DataTable
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
            ->editColumn('speciality_name', function ($row) {
                $name = json_decode($row->speciality_name, true);
                return $name['fr'] ?? $row->speciality_name; // Affiche la clé 'fr'
            })
            ->addColumn('action', 'doctor_requests.datatables_actions')
            ->rawColumns(['action']);
    }
    
    
    /**
     * Get query source of dataTable.
     *
     * @param DoctorRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DoctorRequest $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()
            ->select('doctor_requests_b2b.*', 'specialities.name as speciality_name')
            ->leftJoin('specialities', 'doctor_requests_b2b.speciality_id', '=', 'specialities.id');
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
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true
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
            ['data' => 'lastname', 'title' => trans('lang.doctor_request_lastname')],
            ['data' => 'name', 'title' => trans('lang.doctor_request_name')],
            ['data' => 'email', 'title' => trans('lang.doctor_request_email')],
            ['data' => 'Phone', 'title' => trans('lang.doctor_request_phone_number')],
            ['data' => 'type', 'title' => trans('lang.doctor_request_type')],
            ['data' => 'speciality_name', 'title' => trans('lang.doctor_request_specialities')],
        ];
    }
    

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'doctor_requests_' . time();
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
