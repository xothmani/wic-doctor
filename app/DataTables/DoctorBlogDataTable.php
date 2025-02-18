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
         // Récupérer le statut passé par le contrôleur
         $status = $this->request->input('status', $this->status);
         Log::info('Statut récupéré dans la requête : ' . $status); // Ajouter un log
     
         $user = auth()->user();
     
         if (!$user) {
             return $model->newQuery()->whereRaw('1 = 0');
         }
     
         $query = $model->newQuery();
     
         // Si l'utilisateur est commercial, afficher les blogs en fonction du statut
         if ($user->hasRole('commercial')) {
             $query->select('doctor_blogs.*', 'doctors.name as doctor_name')
                   ->leftJoin('doctors', 'doctors.id', '=', 'doctor_blogs.doctor_id');
     
             if ($status) {
                 $query->where('doctor_blogs.status', $status);
             } else {
                 $query->whereIn('doctor_blogs.status', ['en cours', 'accepté']);
             }
     
             // Si le statut est 'en cours', trier par date de création (created_at)
             if ($status == 'en cours') {
                 $query->orderBy('doctor_blogs.created_at', 'asc'); // Ou 'desc' selon l'ordre voulu
             }
     
             return $query;
         }
     
         // Si l'utilisateur est médecin, afficher uniquement les blogs qui lui sont associés
         $doctor = Doctor::where('user_id', $user->id)->first();
     
         if (!$doctor) {
             return $model->newQuery()->whereRaw('1 = 0');
         }
     
         $query->select('doctor_blogs.*')
               ->where('doctor_blogs.doctor_id', '=', $doctor->id);
     
         if (!empty($status)) {
             $query->where('doctor_blogs.status', $status);
         } else {
             $query->whereIn('doctor_blogs.status', ['en cours', 'accepté']);
         }
     
            // Si le statut est 'en cours', trier par date de création (created_at)
            if ($status == 'en cours') {
                $query->orderBy('doctor_blogs.created_at', 'desc'); // Ordre décroissant pour les plus récents
            }

            // Si le statut est 'accepté', trier par date de mise à jour (updated_at)
            if ($status == 'accepté') {
                $query->orderBy('doctor_blogs.updated_at', 'desc'); // Ordre décroissant pour les plus récents
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
        ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
        ->parameters(array_merge(
            config('datatables-buttons.parameters'), [
                'language' => json_decode(
                    file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                    ), true),
                'fixedColumns' => [],
                'ajax' => [
    'url' => route('doctor_blog.index'),
    'data' => 'function(d) { d.status = "' . $this->status . '"; }'
],

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
