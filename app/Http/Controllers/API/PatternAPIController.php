<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pattern;
use App\Repositories\PatternRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Class PatternAPIController
 * @package App\Http\Controllers\API
 */
class PatternAPIController extends Controller
{
    /** @var PatternRepository */
    private PatternRepository $patternRepository;

    public function __construct(PatternRepository $patternRepo)
    {
        $this->patternRepository = $patternRepo;
        parent::__construct();
    }

    /**
     * Display a listing of the Pattern.
     * GET|HEAD /patterns
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Apply criteria for filtering, sorting, and pagination
            $this->patternRepository->pushCriteria(new RequestCriteria($request));
            $this->patternRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        // Fetch all patterns with applied criteria
        $patterns = $this->patternRepository->all();

        return $this->sendResponse($patterns->toArray(), 'Patterns retrieved successfully');
    }

    /**
     * Display the specified Pattern.
     * GET|HEAD /patterns/{id}
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Fetch a specific pattern by ID
        $pattern = $this->patternRepository->find($id);

        if (empty($pattern)) {
            return $this->sendError('Pattern not found');
        }

        return $this->sendResponse($pattern->toArray(), 'Pattern retrieved successfully');
    }
 
public function getPatternsByDoctor($doctorId): JsonResponse
{
    try {

        // Validate doctor ID
        if (empty($doctorId)) {
            return response()->json([
                'status' => 400,
                'message' => 'The doctor ID field is required.',
            ], 400);
        }

        // Fetch patterns by doctor ID
        $patterns = DB::table('pattern')
	->select('id', 'nom', 'type','specialite_id', 'price', 'doctor_id', 'clinic_id') // Exclude 'color'
            ->where('doctor_id', $doctorId)
            ->get();

        if ($patterns->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No patterns found for the given doctor.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Patterns retrieved successfully.',
            'data' => $patterns,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error retrieving patterns:', ['message' => $e->getMessage()]);
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve patterns: ' . $e->getMessage(),
        ], 500);
    }
}



public function getPatternsByDoctorAndType(Request $request): JsonResponse
{

    try {
        
        $doctorId = $request->input('doctor_id');
        $type = $request->input('type');


        // Validate type ID
        if ($type === null) {
            return response()->json([
                'status' => 400,
                'message' => 'The type field is required.',
            ], 400);
        }


        // Validate doctor ID
        if (empty($doctorId)) {
            return response()->json([
                'status' => 400,
                'message' => 'The doctor ID field is required.',
            ], 400);
        }

        // Fetch patterns by doctor ID
        $patterns = DB::table('pattern')
	->select('id', 'nom', 'type','specialite_id', 'price', 'doctor_id', 'clinic_id') // Exclude 'color'
            ->where('doctor_id', $doctorId)
            ->where('type', $type)
            ->get();

        if ($patterns->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No patterns found for the given doctor.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Patterns retrieved successfully.',
            'data' => $patterns,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error retrieving patterns:', ['message' => $e->getMessage()]);
        return response()->json([
            'status' => 500,
            'message' => 'Failed to retrieve patterns: ' . $e->getMessage(),
        ], 500);
    }
}

}
