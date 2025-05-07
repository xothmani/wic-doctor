<?php

namespace App\DataTables;

use App\Models\Patient;
use App\Models\CustomField;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Appointment;
class PatientDataTable extends DataTable
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
        return $dataTable
           
            ->editColumn('first_name', function ($patient) {
                return $patient->first_name;
            })
            ->editColumn('last_name', function ($patient) {
                return $patient->last_name;
            })
            ->editColumn('updated_at', function ($patient) {
                return getDateColumn($patient, 'updated_at');
            })
            ->addColumn('last_rdv', function ($patient) {
                $doctorId = auth()->user()->getDoctorId();
                $lastAppointment = Appointment::where('patient_id', $patient->id)
                    ->where('doctor_id', $doctorId)
                    ->where('start_at', '<', now())
                    ->orderBy('start_at', 'desc')
                    ->first();
            
                return $lastAppointment ? $lastAppointment->start_at->format('d/m/Y H:i') : 'Aucun RDV';
            })
            ->addColumn('next_rdv', function ($patient) {
                $doctorId = auth()->user()->getDoctorId();
                $nextAppointment = Appointment::where('patient_id', $patient->id)
                    ->where('doctor_id', $doctorId)
                    ->where('start_at', '>', now())
                    ->orderBy('start_at', 'asc')
                    ->first();
            
                if ($nextAppointment) {
                    return $nextAppointment->start_at->format('d/m/Y H:i');
                }
            
                // Lien "Ajouter RDV" quand aucun rendez-vous n'est trouvé
                $url = route('appointment-events.index', ['patient_id' => $patient->id]);
                return '<a href="'.$url.'" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar-plus mr-1"></i> Ajouter RDV
                        </a>';
            })   
            ->addColumn('action', 'patients.datatables_actions')
            ->rawColumns(array_merge($columns, ['action', 'last_rdv', 'next_rdv']));
        }

    /**
     * Get query source of dataTable.
     *
     * @param Patient $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Patient $model): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $model->newQuery()->select("patients.*");
        }

        if ($user->hasRole('clinic_owner')) {
            return $model->newQuery()
                ->join("doctor_patients", "patient_id", "=", "patients.id")
                ->join("doctors", "doctors.id", "=", "doctor_patients.doctor_id")
                ->join("clinic_users", "clinic_users.clinic_id", "=", "doctors.clinic_id")
                ->where('clinic_users.user_id', auth()->id())
                ->groupBy("patients.id")
                ->select("patients.*");
        }

        if ($user->hasRole('doctor')) {
            return $model->newQuery()
                ->join("doctor_patients", "patient_id", "=", "patients.id")
                ->join("doctors", "doctors.id", "=", "doctor_patients.doctor_id")
                ->where('doctors.user_id', auth()->id())
                ->groupBy("patients.id")
                ->select("patients.*");
        }

        if ($user->hasRole('Secretary')) {
            $associatedDoctorIds = $user->associatedDoctors->pluck('doctor_id')->toArray();

            if (empty($associatedDoctorIds)) {
                abort(403, __('Vous n\'êtes associé à aucun médecin.'));
            }

            return $model->newQuery()
                ->join("doctor_patients", "patient_id", "=", "patients.id")
                ->join("doctors", "doctors.id", "=", "doctor_patients.doctor_id")
                ->whereIn('doctors.id', $associatedDoctorIds)
                ->groupBy("patients.id")
                ->select("patients.*");
        }

        if ($user->hasRole('Telesecretary')) {
            $doctorId = auth()->user()->getDoctorId();
            \Log::info('Telésécrétariat using doctor id in the datatable:', ['doctorId' => $doctorId]);
            $doctorIds = is_array($doctorId) ? $doctorId : [$doctorId];
            return $model->newQuery()
                ->join("doctor_patients", "patient_id", "=", "patients.id")
                ->join("doctors", "doctors.id", "=", "doctor_patients.doctor_id")
                ->where('doctors.id', $doctorId)
                ->groupBy("patients.id")
                ->select("patients.*");
        }

        if ($user->hasRole('customer')) {
            return $model->newQuery()
                ->join("doctor_patients", "patient_id", "=", "patients.id")
                ->where('patients.user_id', auth()->id())
                ->groupBy("patients.id")
                ->select("patients.*");
        }

        abort(403, __('Vous n\'avez pas la permission d\'accéder à cette page.'));
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
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $columns = [
            [
                'data' => 'first_name',
                'title' => trans('lang.patient_first_name'),

            ],
            [
                'data' => 'last_name',
                'title' => trans('lang.patient_last_name'),

            ],
            [
                'data' => 'phone_number',
                'title' => trans('lang.patient_phone_number'),

            ],
            [
                'data' => 'last_rdv',
                'title' => 'Dernier RDV',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'next_rdv',
                'title' => 'Prochain RDV',
                'orderable' => false,
                'searchable' => false,
            ],
            
        
       
           
        ];

        $hasCustomField = in_array(Patient::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Patient::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [
                    [
                        'data' => 'custom_fields.' . $field->name . '.view',
                        'title' => trans('lang.patient_' . $field->name),
                        'orderable' => false,
                        'searchable' => false,
                    ]
                ]);
            }
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
        return 'patientsdatatable_' . time();
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
