<?php

namespace App\DataTables;

use App\Models\DoctorRequest;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Auth;
class ParrainerDataTable extends DataTable
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
            ->addColumn('action', 'parrainers.datatables_actions') // Vue pour les actions
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
            ->select('doctor_requests_b2b.*')
            ->join('doctors', 'doctor_requests_b2b.code_parent', '=', 'doctors.code_doctor')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->where('users.id', '=', Auth::id());  // Filter by the authenticated user
    }
    

    // public function query(DoctorRequest $model): \Illuminate\Database\Eloquent\Builder
    // {
    //     if (Auth::check() && Auth::user()->doctor) {
    //         $parrainCode = Auth::user()->doctor->getAttribute('code_parent');
    
    //         if (!$parrainCode) {
    //             return $model->newQuery()->whereRaw('1 = 0'); // Aucun résultat si code_parent est null ou vide
    //         }
    
    //         return $model->newQuery()
    //                      ->where('code_parent', $parrainCode)
    //                      ->select('doctor_requests_b2b.*');
    //     }
    
    //     return $model->newQuery()->whereRaw('1 = 0');
    // }
    

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
        //    ['data' => 'id', 'title' => trans('lang.doctor_request_id')],
            ['data' => 'lastname', 'title' => trans('Last Name')],
            ['data' => 'name', 'title' => trans('lang.doctor_request_name')],
            ['data' => 'email', 'title' => trans('lang.doctor_request_email')],
            ['data' => 'Phone', 'title' => trans('lang.doctor_request_phone_number')],
            ['data' => 'code_parent', 'title' => trans('Code Parent')],
            ['data' => 'status', 'title' => trans('Statut')],


            //['data' => 'type', 'title' => trans('lang.doctor_request_type')],
           // ['data' => 'speciality_id', 'title' => trans('lang.doctor_request_specialities')],
           // ['data' => 'description', 'title' => trans('lang.doctor_request_description')],
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
