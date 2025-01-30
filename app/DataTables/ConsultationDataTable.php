<?php

namespace App\DataTables;

use App\Models\Consultation;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class ConsultationDataTable extends DataTable
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
            ->editColumn('dateConsultation', function ($consultation) {
                // Vérifiez si dateConsultation est une instance de Carbon
                if ($consultation->dateConsultation instanceof \Carbon\Carbon) {
                    return $consultation->dateConsultation;
                }
                // Sinon, retournez la date telle quelle ou un message approprié
                return $consultation->dateConsultation ?? 'N/A';
            })
            ->editColumn('raison', function ($consultation) {
                return $consultation->raison;
            })
            ->editColumn('motif', function ($consultation) {
                return $consultation->motif ?? 'N/A';
            })
            ->editColumn('patient_name', function ($consultation) {
                return ($consultation->patient->first_name ?? 'N/A') . ' ' . ($consultation->patient->last_name ?? 'N/A');
            })
            ->addColumn('action', 'consultations.datatables_actions') // Vue pour les actions
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
            'data' => 'dateConsultation',
            'title' => trans('lang.consultation_date'),
        ],
        [
            'data' => 'raison',
            'title' => trans('lang.consultation_reason'),
        ],
        [
            'data' => 'motif',
            'title' => trans('lang.consultation_motif'),
        ],
        [
            'data' => 'patient_name', // Nouvelle colonne pour le nom complet
            'title' => trans('lang.consultation_patient'), // Titre de la colonne
        ],
    ];
}



    /**
     * Get query source of dataTable.
     *
     * @param Consultation $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Consultation $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->select('consultations.*');
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
        return 'consultationsdatatable_' . time();
    }
}
