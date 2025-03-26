<?php
/*
 * File name: AddressDataTable.php
 * Last modified: 2024.05.03 at 12:22:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\DataTables;

use App\Models\Address;
use App\Models\CustomField;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Log;

class AddressDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static array $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable(mixed $query): DataTableAbstract
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
		->editColumn('description', function ($address) {
		            Log::info('Raw Description:', ['description' => $address->description]);
            return $this->parseJson($address->description);
        })
        ->editColumn('address', function ($address) {
            return $this->parseJson($address->address);
        })
        ->editColumn('pays', function ($address) {
            return $this->parseJson($address->pays);
        })
        ->editColumn('ville', function ($address) {
            return $this->parseJson($address->ville);
        })
            ->editColumn('updated_at', function ($address) {
                return getDateColumn($address, 'updated_at');
            })
            ->addColumn('action', 'addresses.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

private function parseJson($json)
{
    // Decode the JSON string to an array
    $data = json_decode($json, true);

    // Check if it's an array and return the 'fr' value, or the first value if available
    if (is_array($data)) {
        return $data['fr'] ?? reset($data); // Return the 'fr' key if available, or the first value
    }

    return $json; // Return the original string if decoding fails
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
                'data' => 'description',
                'title' => trans('lang.address_description'),
            ],
            [
                'data' => 'address',
                'title' => trans('lang.address_address'),
            ],
            [
                'data' => 'latitude',
                'title' => trans('lang.address_latitude'),
            ],
            [
                'data' => 'longitude',
                'title' => trans('lang.address_longitude'),
            ],
            [
                'data' => 'pays',
                'title' => trans('lang.address_pays'),
            ],
            [
                'data' => 'ville',
                'title' => trans('lang.address_ville'),
            ],
            (auth()->check() && auth()->user()->hasRole('admin')) ?
                [
                    'data' => 'user.name',
                    'title' => trans('lang.address_user_id'),

                ] : null,
            [
                'data' => 'updated_at',
                'title' => trans('lang.address_updated_at'),
                'searchable' => false,
            ]
        ];
        $columns = array_filter($columns);
        $hasCustomField = in_array(Address::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Address::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.address_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param Address $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Address $model): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->user()->hasRole('admin')) {
            return $model->newQuery()->with("user");
        } else {
            return $model->newQuery()->with("user")->where('addresses.user_id', auth()->id());
        }
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
                        ), true)
                ]
            ));
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

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'addressesdatatable_' . time();
    }
}
