<?php

namespace App\Http\Controllers\API;

use App\Repositories\RoomRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\Consultation;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;



class PrescriptionsApiController extends Controller
{


    /**
     * Liste de toutes les rooms avec leurs appointments
     */
    public function index(): JsonResponse
    {
        $prescriptions = Prescription::all();
        return response()->json($prescriptions);
    }

    /**
     * Afficher tous les prescriptions d'un patient
     */
    public function show(int $id): JsonResponse
    {
        
        if (!$id) {
            return response()->json(['message' => 'Prescriptions not found'], 404);
        }

        $results = Consultation::join('prescriptions', 'consultations.id', '=', 'prescriptions.consultation_id')
            ->where('consultations.patient_id', $id)
            ->select(
                'prescriptions.id',
                'prescriptions.date',
                'consultations.patient_id',
                'consultations.user_id',
                'prescriptions.type',
                'prescriptions.consultation_id',
                'prescriptions.pdfForMail'
            )
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }




}
