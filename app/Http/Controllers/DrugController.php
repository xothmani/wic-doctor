<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DrugInteractionBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DrugController extends Controller
{
    /**
     * Get all drugs for the drug interaction checker
     * Cached for performance since drug data is relatively static
     */
    public function index(Request $request)
    {
        try {
            // Cached for 24 hours (1440 minutes)
            $drugs = Cache::remember('medicaments_france_all', 60 * 24, function () {
                return DB::table('medicaments_france')
                    ->select([
                        'medicament_id as id',
                        'medicament_name as name'
                    ])
                    ->whereNotNull('medicament_id')
                    ->whereNotNull('medicament_name')
                    ->get()
                    ->map(function ($drug) {
                        // Split name on first comma for display
                        $nameParts = explode(',', $drug->name, 2);
                        $drug->display_name = trim($nameParts[0]);
                        $drug->subtext = isset($nameParts[1]) ? trim($nameParts[1]) : '';
                        return $drug;
                    });
            });

            // If the request expects JSON (API call), return JSON
            if ($request->wantsJson()) {
                return response()->json($drugs);
            }

            // Otherwise, load the Blade view and pass data to it
            return view('drug_drug_interactions.index', ['drugs' => $drugs]);

        } catch (\Exception $e) {
            $error = [
                'error' => 'Failed to fetch drugs',
                'message' => $e->getMessage()
            ];

            if ($request->wantsJson()) {
                return response()->json($error, 500);
            }

            return view('drug_drug_interactions.index', ['drugs' => collect(), 'error' => $error]);
        }
    }

    /**
     * Search drugs by name
     * Server-side search
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $drugs = DB::table('medicaments_france')
                ->select([
                    'medicament_id as id',
                    'medicament_name as name'
                ])
                ->where('medicament_name', 'LIKE', '%' . $query . '%')
                ->whereNotNull('medicament_id')
                ->whereNotNull('medicament_name')
                ->limit($limit)
                ->get()
                ->map(function ($drug) {
                    // Split name on first comma for display
                    $nameParts = explode(',', $drug->name, 2);
                    $drug->display_name = trim($nameParts[0]);
                    $drug->subtext = isset($nameParts[1]) ? trim($nameParts[1]) : '';
                    return $drug;
                });

            return response()->json($drugs);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific drug by ID
     */
    public function show($medicament_id)
    {
        try {
            $drug = DB::table('medicaments_france')
                ->select([
                    'medicament_id as id',
                    'medicament_name as name'
                ])
                ->where('medicament_id', $medicament_id)
                ->first();

            if (!$drug) {
                return response()->json([
                    'error' => 'Drug not found'
                ], 404);
            }

            // Split name on first comma for display
            $nameParts = explode(',', $drug->name, 2);
            $drug->display_name = trim($nameParts[0]);
            $drug->subtext = isset($nameParts[1]) ? trim($nameParts[1]) : '';

            return response()->json($drug);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch drug',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check drug interactions with permanent backup storage
     */
    public function checkInteractions(Request $request)
    {
        $drugIds = $request->get('drug_ids', []);
        
        if (empty($drugIds) || count($drugIds) < 2) {
            return response()->json([
                'error' => 'At least 2 drugs are required for interaction checking'
            ], 400);
        }

        try {
            // Generate hash for this drug combination
            $combinationHash = DrugInteractionBackup::generateCombinationHash($drugIds);
            
            // Check if we're in backup mode or if API should be bypassed
            $backupMode = config('drug_interactions.backup_mode', false);
            
            if (!$backupMode) {
                // Try to fetch from API first
                try {
                    Log::info('Attempting to fetch from API', [
                        'combination_hash' => $combinationHash,
                        'drug_ids' => $drugIds
                    ]);
                    
                    $apiResponse = $this->fetchFromUnivadisAPI($drugIds);
                    
                    // Store in backup database (create or update)
                    $this->storeInBackup($combinationHash, $drugIds, $apiResponse);
                    
                    return $this->formatResponse($apiResponse, 'api');
                    
                } catch (\Exception $e) {
                    Log::warning('API fetch failed, falling back to backup', [
                        'error' => $e->getMessage(),
                        'combination_hash' => $combinationHash
                    ]);
                    // Continue to backup lookup below
                }
            }
            
            // Look for data in backup database
            $backupData = DrugInteractionBackup::where('drug_combination_hash', $combinationHash)->first();
            
            if ($backupData) {
                Log::info('Using backup database', [
                    'combination_hash' => $combinationHash,
                    'first_fetched_at' => $backupData->first_fetched_at
                ]);
                
                return $this->formatResponse($backupData->api_response, 'backup');
            }
            
            // No data available anywhere
            return response()->json([
                'error' => 'No interaction data available',
                'message' => 'API is unavailable and no backup data exists for this drug combination',
                'drug_ids' => $drugIds
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Drug interaction check completely failed', [
                'error' => $e->getMessage(),
                'drug_ids' => $drugIds
            ]);
            
            return response()->json([
                'error' => 'System error',
                'message' => 'Unable to process request'
            ], 500);
        }
    }

    /**
     * Store API response in backup database
     */
    private function storeInBackup(string $combinationHash, array $drugIds, array $apiResponse): void
    {
        $formattedDrugList = implode(',', array_map(function ($id) {
            return 'drug_' . $id . '_fr';
        }, $drugIds));

        DrugInteractionBackup::updateOrCreate(
            ['drug_combination_hash' => $combinationHash],
            [
                'drug_ids' => $drugIds,
                'formatted_drug_list' => $formattedDrugList,
                'api_response' => $apiResponse,
                'total_results' => $apiResponse['count'] ?? 0,
                'is_european' => $apiResponse['isEuropean'] ?? 1,
                'last_updated_at' => now(),
                'first_fetched_at' => now() // This will only be set on create, not update
            ]
        );

        Log::info('Stored interaction data in backup', [
            'combination_hash' => $combinationHash,
            'total_results' => $apiResponse['count'] ?? 0
        ]);
    }

    /**
     * Fetch data from Univadis API
     */
    private function fetchFromUnivadisAPI(array $drugIds): array
    {
        // Format drug IDs for Univadis API
        $formattedDrugIds = array_map(function ($id) {
            return 'drug_' . $id . '_fr';
        }, $drugIds);

        $drugList = implode(',', $formattedDrugIds);
        
        // Get cookie from config
        $cookieHeader = config('drug_interactions.univadis_cookie') ?: 
                       config('services.univadis_cookie') ?: 
                       env('UNIVADIS_COOKIE');

        if (empty($cookieHeader)) {
            throw new \Exception('Univadis cookie not configured');
        }

        $response = Http::withHeaders([
            'Cookie' => $cookieHeader,
        ])->timeout(config('drug_interactions.api_timeout', 30))
          ->get('https://www.univadis.fr/ajax/interactions', [
            'drugList' => $drugList
        ]);

        if (!$response->successful()) {
            throw new \Exception('External API error: ' . $response->status());
        }

        return $response->json();
    }

    /**
     * Format response data
     */
    private function formatResponse(array $data, string $source): \Illuminate\Http\JsonResponse
    {
        $interactions = [];
        
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $interactions[] = [
                    'interaction_id' => $item['interactionId'] ?? '',
                    'severity' => $this->mapSeverity($item['severity'] ?? '1'),
                    'effect' => $item['effect'] ?? '',
                    'recommendation' => $item['recommendation'] ?? '',
                    'substances_line' => $item['substancesLine'] ?? '',
                    'substances' => $item['substances'] ?? [],
                    'left_boxes' => $item['leftBoxes'] ?? []
                ];
            }
        }

        $response = [
            'interactions' => $interactions,
            'total_results' => $data['count'] ?? count($interactions),
            'is_european' => $data['isEuropean'] ?? 1,
            'data_source' => $source
        ];
        
        if ($source === 'backup') {
            $response['notice'] = 'Data retrieved from backup database';
        }
        
        return response()->json($response);
    }

    /**
     * Map severity levels
     */
    private function mapSeverity($severity): string
    {
        $severityMap = [
            '1' => 'low',
            '2' => 'minor',
            '3' => 'moderate',
            '4' => 'major',
            '5' => 'high',
            '6' => 'critical'
        ];
        
        return $severityMap[$severity] ?? 'Unknown';
    }

    /**
     * Get backup database statistics
     */
    public function getBackupStats()
    {
        $totalCombinations = DrugInteractionBackup::count();
        $totalInteractions = DrugInteractionBackup::sum('total_results');
        
        $recentBackups = DrugInteractionBackup::where('first_fetched_at', '>', now()->subDays(30))->count();
        
        $severityStats = [];
        $allBackups = DrugInteractionBackup::all();
        
        foreach ($allBackups as $backup) {
            if (isset($backup->api_response['items'])) {
                foreach ($backup->api_response['items'] as $item) {
                    $severity = $this->mapSeverity($item['severity'] ?? '1');
                    $severityStats[$severity] = ($severityStats[$severity] ?? 0) + 1;
                }
            }
        }
        
        return response()->json([
            'backup_stats' => [
                'total_drug_combinations' => $totalCombinations,
                'total_interactions_stored' => $totalInteractions,
                'recent_additions' => $recentBackups,
                'interactions_by_severity' => $severityStats,
                'oldest_backup' => DrugInteractionBackup::orderBy('first_fetched_at')->first()?->first_fetched_at,
                'newest_backup' => DrugInteractionBackup::orderBy('first_fetched_at', 'desc')->first()?->first_fetched_at,
                'backup_mode_active' => config('drug_interactions.backup_mode', false)
            ]
        ]);
    }

    /**
     * Toggle backup mode (useful when API is permanently unavailable)
     */
    public function toggleBackupMode(Request $request)
    {
        $backupMode = $request->get('backup_mode', true);
        
        // Note: This would require updating config dynamically or using a database setting
        // For now, it just returns the current status
        return response()->json([
            'message' => 'Backup mode toggle requested',
            'requested_mode' => $backupMode,
            'current_mode' => config('drug_interactions.backup_mode', false),
            'note' => 'Update DRUG_INTERACTIONS_BACKUP_MODE in .env file to persist this change'
        ]);
    }

    /**
     * Search through backup data for specific drugs or interactions
     */
    public function searchBackup(Request $request)
    {
        $searchTerm = $request->get('search', '');
        $limit = $request->get('limit', 50);
        
        if (empty($searchTerm)) {
            return response()->json(['error' => 'Search term is required'], 400);
        }
        
        $results = DrugInteractionBackup::whereRaw(
            'JSON_SEARCH(api_response, "all", ?) IS NOT NULL', 
            ['%' . $searchTerm . '%']
        )->limit($limit)->get();
        
        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'drug_combination_hash' => $result->drug_combination_hash,
                'drug_ids' => $result->drug_ids,
                'drug_names' => $result->drug_names,
                'total_results' => $result->total_results,
                'first_fetched_at' => $result->first_fetched_at,
                'last_updated_at' => $result->last_updated_at
            ];
        }
        
        return response()->json([
            'search_term' => $searchTerm,
            'results_count' => count($formattedResults),
            'results' => $formattedResults
        ]);
    }
}