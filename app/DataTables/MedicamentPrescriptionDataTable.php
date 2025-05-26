<?php

namespace App\DataTables;

use App\Models\MedicamentPrescription;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MedicamentPrescriptionDataTable extends DataTable
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
            ->editColumn('nom_medicament', function ($medicament) {
                return $medicament->nom_medicament;
            })
          
            ->editColumn('status_medicament', function ($medicament) {
                return '<span class="badge badge-'.($medicament->status_medicament == 'en cours' ? 'primary' : 'success').'">'
                    .ucfirst($medicament->status_medicament).
                    '</span>';
            })
            ->editColumn('created_at', function ($medicament) {
                return $medicament->created_at->format('d/m/Y H:i');
            })
            ->addColumn('action', function ($medicament) {
                return view('medicament_prescriptions.datatables_actions', [
                    'id' => $medicament->id,
                    'status_medicament' => $medicament->status_medicament,
                ]);
            })
                        ->rawColumns(['status_medicament', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param MedicamentPrescription $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MedicamentPrescription $model): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        
        $query = $model->newQuery()
            ->enCours()
            ->with('prescription');

        // Filtre selon le rôle de l'utilisateur
        if ($user->hasRole('doctor')) {
            $query->whereHas('prescription', function($q) use ($user) {
                $q->where('doctor_id', $user->doctor->id);
            });
        }

        return $query;
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
            ->addAction(['width' => '120px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters([
               'language' => json_decode(
                        file_get_contents(
                            base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ),
                        true
                    ),
                'order' => [[0, 'desc']],
              
                
            ]);
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
                'data' => 'nom_medicament',
                'title' => trans('lang.medicament_prescription_nom'),
                'searchable' => true,
                'orderable' => true,
            ],
           
            [
                'data' => 'status_medicament',
                'title' => trans('lang.medicament_prescription_status'),
            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.medicament_prescription_created_at'),
                'searchable' => false,
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
        return 'medicament_prescriptions_datatable_' . time();
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