<?php

namespace App\DataTables;

use App\Models\Pattern;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PatternDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', 'patterns.datatables_actions')
            ->editColumn('nom', function ($pattern) {
                $nomArray = json_decode($pattern->nom, true);
                $locale = app()->getLocale();
                return $nomArray[$locale] ?? ''; // Return localized name or empty string
            })
            ->editColumn('type', function ($pattern) {
                $typeMapping = [
                    1 => trans('lang.cabinet'),
                    2 => trans('lang.clinique'),
                    3 => trans('lang.adomicile'),
                ];
                return $typeMapping[$pattern->type] ?? '';
            })
            ->editColumn('speciality.name', function ($pattern) {
                return $pattern->speciality->name ?? '';
            })
            ->editColumn('clinic.name', function ($pattern) {
                return $pattern->clinic
                    ? $pattern->clinic->name
                    : trans('lang.not_associated_to_clinic');
            })
            ->editColumn('color', function ($pattern) {
                return "<span style='display: inline-block; padding: 0.2em 0.6em; color: #fff; background-color: {$pattern->color}; border-radius: 0.25rem;'>{$pattern->color}</span>";
            })
            ->rawColumns(['color', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Pattern::query();

        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        $doctorId = auth()->user()->getDoctorId();

        if ($doctorId) {
            return $query->where('doctor_id', $doctorId);
        }

        return $query->where('doctor_id', -1); // Return empty if unauthorized
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
                        file_get_contents(
                            base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ),
                        true
                    )
                ]
            ));
    }
    /**
     * Get the columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('nom')->title(trans('lang.pattern_name')),
            Column::make('type')->title(trans('lang.pattern_type')),
            Column::make('speciality.name')->title(trans('lang.speciality')),
            Column::make('price')->title(trans('lang.pattern_price')),
            Column::make('clinic.name')->title(trans('lang.clinic')),
            Column::make('color')->title(trans('lang.pattern_color')),
        ];
    }

    /**
     * Get the filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Patterns_' . time();
    }
}
