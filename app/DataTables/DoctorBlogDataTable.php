<?php

namespace App\DataTables;

use App\Models\DoctorBlog;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Log;  // Import the Log facade
use App\Models\Doctor;

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
            $title = strip_tags($blog->titre); // Supprime les balises HTML
            if (strlen($title) > 50) {
                $shortTitle = substr($title, 0, strrpos(substr($title, 0, 50), ' '));
                return $shortTitle . ' <a href="#" onclick="showModal(`' . addslashes($title) . '`)" style="color: blue; text-decoration: none;">...lire la suite</a>';
            }
            return $title;
        })
        ->editColumn('contenu', function ($blog) {
            $content = strip_tags($blog->contenu, '<p><br><strong><em><ul><li><ol>'); // Conserver certaines balises HTML            
            if (strlen($content) > 50) {
                $shortContent = substr($content, 0, 50);
                return $shortContent . ' <a href="#" onclick="showModal(`' . addslashes($content) . '`)" style="color: blue; text-decoration: none;">...lire la suite</a>';
            }
            return $content;
        })
        
        
        ->editColumn('status', function ($blog) {
            return $blog->status;
        })
        ->addColumn('doctor', function ($blog) {
            return optional($blog->doctor)->name; // Affiche le nom du médecin
        })
        ->addColumn('action', 'doctor_blog.datatables_actions')
        ->rawColumns(['contenu', 'titre', 'action']);
}

    
    
    
    /**
     * Get query source of dataTable.
     *
     * @param DoctorBlog $model
     * @return \Illuminate\Database\Eloquent\Builder
     */

     public function query(DoctorBlog $model): \Illuminate\Database\Eloquent\Builder
     {
         $user = auth()->user();
     
         if (!$user) {
             return $model->newQuery()->whereRaw('1 = 0');
         }
     
         if ($user->hasRole('commercial')) { 
             return $model->newQuery()
                 ->select('doctor_blogs.*', 'doctors.name as doctor_name')
                 ->leftJoin('doctors', 'doctors.id', '=', 'doctor_blogs.doctor_id')
                 ->where('doctor_blogs.status', '=', 'accepté'); // Filtre pour les blogs acceptés
         }
     
         $doctor = Doctor::where('user_id', $user->id)->first();
     
         if (!$doctor) {
             return $model->newQuery()->whereRaw('1 = 0');
         }
     
         return $model->newQuery()
             ->select('doctor_blogs.*')
             ->where('doctor_blogs.doctor_id', '=', $doctor->id);
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
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true),
                    'fixedColumns' => [],
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
    
        // Ajouter la colonne "doctor" uniquement si l'utilisateur est un commercial
        if (auth()->user()->hasRole('commercial')) {
            $columns[] = [
                'data' => 'doctor',
                'title' => trans('lang.blog_doctor'),
            ];
        }
    
        return $columns;
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
