<?php

namespace App\DataTables;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\DataTableAbstract;
use App\Models\CustomField;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class PhotosCabinetDataTable extends DataTable
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
            ->editColumn('name', function ($doctor) {
                return $doctor->name;
            })
            ->editColumn('speciality_name', function ($doctor) {
                $speciality = json_decode($doctor->speciality_name, true);
                return $speciality['fr'] ?? 'N/A';
            })
            ->editColumn('email', function ($doctor) {
                return $doctor->email ?? 'Non renseigné';
            })
            ->editColumn('phone_number', function ($doctor) {
                return $doctor->phone_number ?? 'Non renseigné';
            })
            ->editColumn('created_at', function ($doctor) {
                return $doctor->created_at ? $doctor->created_at->format('d/m/Y') : 'N/A';
            })
            
            ->addColumn('action', 'photos_cabinet.datatables_actions')
            ->rawColumns(['action']);
    }
    
    
    private function getDoctorsWithCabinetPhotos(): array
    {
        $doctorIds = [];
    
        // Récupérer les dossiers des docteurs
        $directories = Storage::disk('public')->directories('doctors');
    
        foreach ($directories as $directory) {
            // Extraire l'ID du docteur depuis le dossier
            $doctorId = basename($directory);
    
            // Vérifier si le dossier en_attente contient des images
            $path = "doctors/{$doctorId}/cabinet/en_attente";
            $files = Storage::disk('public')->files($path);
    
            if (!empty($files)) {
                $doctorIds[] = (int) $doctorId; // Stocker l'ID si des images existent
            }
        }
    
        return $doctorIds;
    }
        
    public function query(Doctor $model): QueryBuilder
    {
        $doctorIds = $this->getDoctorsWithCabinetPhotos();
    
        $query = $model->newQuery()
            ->select(
                "doctors.*",
                "specialities.name as speciality_name",
                "users.email",
                "users.phone_number"
            )
            ->leftJoin("doctor_specialities", "doctors.id", "=", "doctor_specialities.doctor_id")
            ->leftJoin("specialities", "doctor_specialities.speciality_id", "=", "specialities.id")
            ->leftJoin("users", "doctors.user_id", "=", "users.id")
            ->whereIn('doctors.id', $doctorIds); // Filtrer les docteurs qui ont des images
    
        return $query;
    }
    


    
    
    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */

     public function html(): HtmlBuilder{
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
        $columns = [
            [
                'data' => 'name',
                'title' => trans('lang.nomComplet'),
            ],
            [
                'data' => 'speciality_name',
                'title' => trans('lang.speciality'),
            ],
            [
                'data' => 'email',
                'title' => trans('lang.email'),
            ],
            [
                'data' => 'phone_number',
                'title' => trans('lang.phone_number'),
            ],
            [
                'data' => 'created_at',
                'title' => trans('lang.user_created_at'),
            ],
        ];
    
        return $columns;
    }
    
    
    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'doctorsdatatable_' . time();
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
