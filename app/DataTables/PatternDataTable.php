<?php

namespace App\DataTables;

use App\Models\Pattern;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PatternDataTable extends DataTable
{
    public function dataTable($query)
    {
        $dataTable = datatables()->eloquent($query);

        return $dataTable->addColumn('action', 'patterns.datatables_actions')
            ->editColumn('nom', function ($pattern) {
                $nomJson = $pattern->nom;
                $nomArray = json_decode($nomJson, true);
                $locale = app()->getLocale(); // Get the current language setting
                return $nomArray[$locale] ?? ''; // Return the localized value or an empty string if not found
            })
            ->editColumn('type', function ($pattern) {
                // Map integer type values to their corresponding localized strings
                $typeMapping = [
                    1 => trans('lang.cabinet'),
                    2 => trans('lang.clinique'),
                    3 => trans('lang.adomicile'),
                ];
                return $typeMapping[$pattern->type] ?? ''; // Return the localized type or an empty string if not found
            })
            ->editColumn('speciality.name', function ($pattern) {
                return $pattern->speciality ? $pattern->speciality->name : '';
            })
            ->editColumn('clinic.name', function ($pattern) {
                return $pattern->clinic
                    ? $pattern->clinic->name
                    : trans('lang.not_associated_to_clinic'); // Show "Non associé à une clinique"
            })
            ->editColumn('color', function ($pattern) {
                return "<span style='display: inline-block; padding: 0.2em 0.6em; color: #fff; background-color: {$pattern->color}; border-radius: 0.25rem;'>{$pattern->color}</span>";
            })
            ->rawColumns(['color', 'action']);
    }


    public function query()
    {
        $query = Pattern::query();

        if (auth()->user()->hasRole('admin')) {
            return $query; // Admin sees all patterns
        }

        $doctor = Doctor::where('user_id', auth()->id())->first();
        if ($doctor) {
            return $query->where('doctor_id', $doctor->id) // Filter patterns by the logged-in doctor
                ->whereHas('speciality', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id); // Ensure the specialty is associated with the doctor
                });
        }

        return $query->where('doctor_id', -1); // No patterns for unauthorized users
    }


    public function html()
    {
        return $this->builder()
            ->columns([
                'nom' => ['title' => trans('lang.pattern_name')], // Localized title for "Name"
                'type' => ['title' => trans('lang.pattern_type')], // Localized title for "Type"
                'speciality.name' => ['title' => trans('lang.speciality')], // Localized title for "Speciality"
                'price' => ['title' => trans('lang.pattern_price')], // Localized title for "Price"
                'clinic.name' => ['title' => trans('lang.clinic')], // Localized title for "Clinic"
                'color' => ['title' => trans('lang.pattern_color')], // Localized title for "Color"
            ])
            ->minifiedAjax()
            ->addAction(['width' => '80px', 'printable' => false])
            ->parameters(config('datatables-buttons.parameters'));
    }


    protected function filename(): string
    {
        return 'Patterns_' . time();
    }
}
