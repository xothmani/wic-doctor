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
        $requestId = uniqid('univadis_', true);

        Log::info('[Univadis API] Starting request', [
            'request_id' => $requestId,
            'drug_ids' => $drugIds,
            'drug_count' => count($drugIds)
        ]);

        // Format drug IDs for Univadis API
        $formattedDrugIds = array_map(function ($id) {
            return 'drug_' . $id . '_fr';
        }, $drugIds);

        $drugList = implode(',', $formattedDrugIds);

        Log::debug('[Univadis API] Formatted drug list', [
            'request_id' => $requestId,
            'formatted_drug_list' => $drugList
        ]);

        // First attempt - try with existing cookie or no cookie
        $cookieHeader = config('drug_interactions.univadis_cookie') ?:
            config('services.univadis_cookie') ?:
            env('UNIVADIS_COOKIE');

        $hasCookie = !empty($cookieHeader);
        Log::info('[Univadis API] First attempt configuration', [
            'request_id' => $requestId,
            'has_existing_cookie' => $hasCookie,
            'cookie_source' => $hasCookie ? $this->determineCookieSource() : 'none'
        ]);

        $startTime = microtime(true);
        $response = $this->makeUnivadisRequest($drugList, $cookieHeader, $requestId, 1);
        $firstAttemptDuration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info('[Univadis API] First attempt completed', [
            'request_id' => $requestId,
            'status_code' => $response->status(),
            'duration_ms' => $firstAttemptDuration,
            'response_size_bytes' => strlen($response->body())
        ]);

        // If first attempt fails with 502, try to extract cookie and retry
        if ($response->status() === 502) {
            Log::warning('[Univadis API] Received 502 Bad Gateway, attempting cookie extraction', [
                'request_id' => $requestId,
                'response_headers_count' => count($response->headers())
            ]);

            $newCookie = $this->extractCookieFromResponse($response, $requestId);

            if ($newCookie) {
                Log::info('[Univadis API] Cookie extracted successfully, retrying request', [
                    'request_id' => $requestId,
                    'new_cookie_length' => strlen($newCookie)
                ]);

                // Retry with the new cookie
                $retryStartTime = microtime(true);
                $response = $this->makeUnivadisRequest($drugList, $newCookie, $requestId, 2);
                $retryDuration = round((microtime(true) - $retryStartTime) * 1000, 2);

                Log::info('[Univadis API] Retry attempt completed', [
                    'request_id' => $requestId,
                    'status_code' => $response->status(),
                    'duration_ms' => $retryDuration,
                    'response_size_bytes' => strlen($response->body())
                ]);
            } else {
                Log::error('[Univadis API] Failed to extract cookie from 502 response', [
                    'request_id' => $requestId,
                    'response_body_preview' => substr($response->body(), 0, 200)
                ]);
            }
        }

        // If still not successful after retry, throw exception
        if (!$response->successful()) {
            $totalDuration = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('[Univadis API] Request failed after all attempts', [
                'request_id' => $requestId,
                'final_status_code' => $response->status(),
                'total_duration_ms' => $totalDuration,
                'response_body_preview' => substr($response->body(), 0, 500)
            ]);

            throw new \Exception('External API error: ' . $response->status());
        }

        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
        $responseData = $response->json();

        Log::info('[Univadis API] Request completed successfully', [
            'request_id' => $requestId,
            'total_duration_ms' => $totalDuration,
            'response_data_count' => is_array($responseData) ? count($responseData) : 'non-array',
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        return $responseData;
    }

    private function makeUnivadisRequest(string $drugList, ?string $cookieHeader = null, string $requestId = '', int $attemptNumber = 1): \Illuminate\Http\Client\Response
    {
        $headers = [];

        if (!empty($cookieHeader)) {
            $headers['Cookie'] = $cookieHeader;
            $cookiePreview = substr($cookieHeader, 0, 50) . (strlen($cookieHeader) > 50 ? '...' : '');
        }

        $timeout = config('drug_interactions.api_timeout', 30);
        $url = 'https://www.univadis.fr/ajax/interactions';

        Log::debug('[Univadis API] Making HTTP request', [
            'request_id' => $requestId,
            'attempt' => $attemptNumber,
            'url' => $url,
            'method' => 'GET',
            'timeout_seconds' => $timeout,
            'has_cookie' => !empty($cookieHeader),
            'cookie_preview' => $cookiePreview ?? 'none',
            'drug_list_length' => strlen($drugList),
            'headers_count' => count($headers)
        ]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout($timeout)
                ->get($url, [
                    'drugList' => $drugList
                ]);

            Log::debug('[Univadis API] HTTP response received', [
                'request_id' => $requestId,
                'attempt' => $attemptNumber,
                'status_code' => $response->status(),
                'response_headers' => $response->headers(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => $response->header('Content-Length') ?: strlen($response->body())
            ]);

            return $response;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Univadis API] Connection error', [
                'request_id' => $requestId,
                'attempt' => $attemptNumber,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            throw $e;

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('[Univadis API] Request error', [
                'request_id' => $requestId,
                'attempt' => $attemptNumber,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('[Univadis API] Unexpected error during request', [
                'request_id' => $requestId,
                'attempt' => $attemptNumber,
                'error_class' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function extractCookieFromResponse(\Illuminate\Http\Client\Response $response, string $requestId = ''): ?string
    {
        Log::debug('[Univadis API] Starting cookie extraction', [
            'request_id' => $requestId,
            'response_status' => $response->status()
        ]);

        $setCookieHeaders = $response->header('Set-Cookie');

        if (!$setCookieHeaders) {
            Log::warning('[Univadis API] No Set-Cookie headers found in response', [
                'request_id' => $requestId,
                'available_headers' => array_keys($response->headers())
            ]);
            return null;
        }

        Log::debug('[Univadis API] Set-Cookie headers found', [
            'request_id' => $requestId,
            'header_type' => gettype($setCookieHeaders),
            'header_count' => is_array($setCookieHeaders) ? count($setCookieHeaders) : 1
        ]);

        // If multiple Set-Cookie headers, they might be returned as array or string
        if (is_array($setCookieHeaders)) {
            $setCookieHeaders = implode('; ', $setCookieHeaders);
            Log::debug('[Univadis API] Merged multiple Set-Cookie headers', [
                'request_id' => $requestId,
                'merged_length' => strlen($setCookieHeaders)
            ]);
        }

        Log::debug('[Univadis API] Raw cookie headers content', [
            'request_id' => $requestId,
            'headers_preview' => substr($setCookieHeaders, 0, 200) . (strlen($setCookieHeaders) > 200 ? '...' : ''),
            'full_headers_length' => strlen($setCookieHeaders)
        ]);

        // Extract med_session cookie specifically
        if (preg_match('/med_session=([^;]+)/', $setCookieHeaders, $matches)) {
            $extractedCookie = 'med_session=' . $matches[1];

            Log::info('[Univadis API] Successfully extracted med_session cookie', [
                'request_id' => $requestId,
                'cookie_value_length' => strlen($matches[1]),
                'cookie_preview' => substr($matches[1], 0, 20) . '...',
                'full_cookie_length' => strlen($extractedCookie)
            ]);

            return $extractedCookie;
        }

        // Log all cookies found for debugging
        $allCookieMatches = [];
        if (preg_match_all('/([^=]+)=([^;]+)/', $setCookieHeaders, $allMatches, PREG_SET_ORDER)) {
            foreach ($allMatches as $match) {
                $allCookieMatches[] = [
                    'name' => trim($match[1]),
                    'value_length' => strlen($match[2]),
                    'value_preview' => substr($match[2], 0, 20) . '...'
                ];
            }
        }

        Log::warning('[Univadis API] med_session cookie not found in response', [
            'request_id' => $requestId,
            'all_cookies_found' => $allCookieMatches,
            'cookies_count' => count($allCookieMatches)
        ]);

        return null;
    }

    private function determineCookieSource(): string
    {
        if (config('drug_interactions.univadis_cookie')) {
            return 'drug_interactions.univadis_cookie config';
        } elseif (config('services.univadis_cookie')) {
            return 'services.univadis_cookie config';
        } elseif (env('UNIVADIS_COOKIE')) {
            return 'UNIVADIS_COOKIE environment variable';
        }
        return 'unknown';
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