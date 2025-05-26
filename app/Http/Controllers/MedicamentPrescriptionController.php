<?php

namespace App\Http\Controllers;

use App\DataTables\MedicamentPrescriptionDataTable;
use App\Models\MedicamentPrescription;
use Illuminate\Http\Request;

class MedicamentPrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param MedicamentPrescriptionDataTable $dataTable
     * @return mixed
     */
    public function index(MedicamentPrescriptionDataTable $dataTable)
    {
        return $dataTable->render('medicament_prescriptions.index');
    }

    public function markAsTreated($id)
    {
        $medicament = MedicamentPrescription::findOrFail($id);
    
        if ($medicament->status_medicament === 'en cours') {
            $medicament->status_medicament = 'traité';
            $medicament->save();
    
            return redirect()->back()->with('success', 'Le statut du médicament a été mis à jour.');
        }
    
        return redirect()->back()->with('alert', 'Ce médicament est déjà traité.');
    }
    }