<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
     * Check drug interactions using Univadis API
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
            // Format drug IDs for Univadis API
            $formattedDrugIds = array_map(function ($id) {
                return 'drug_' . $id . '_fr';
            }, $drugIds);

            $drugList = implode(',', $formattedDrugIds);


            // Long cookie string from your browser/dev tools
            $cookieHeader = 'med_session=FA6MYyHekYcwW4S1Ktt1aMnSZm9sVJOimVCNhznnZKX6sAufO0Vi2Mh8CNRvq7yjUsAB8Lu4UqliV%2BVm%2FDvc%2B9D5oeUNWWDeQi%2Fy0IRH81Z19eaj%2Bf%2BbLWBX%2BV9dcBemgW0kn0lwvd9IsWVDpCse8Ky2LY9cc77YQTgD6Dt6YIUZjh2YtbpaKqss33EhgaX7M9VKIhpNq6zPXv5Pl%2FB%2BKxwclqvN0zW%2BMJV8UONHSiI1ph5RCzxHV9l5LRmVMADtxW0ZH3Xgkmkl4FdLx5ufQUSUA6xT0ETq707hFwznlap2ltZdv%2FeM8jDfdMZQ86X5F6EAZnvj60uPRBRX%2BSekKeMRbEs2aSOs7tp%2FXQH1h467icGH9y%2BXgDqMybcEPu2nM39X9FUH%2BRILkiypkDbd5xSVYua0894rw1xvY4WdzRjvaKcfp7eLTb48%2F0x0lxlV; Path=/; Domain=univadis.fr; Secure; HttpOnly; Expires=Tue, 02 Jul 2030 13:31:48 GMT;';

            $response = Http::withHeaders([
                'Cookie' => $cookieHeader,
            ])->timeout(30)->get('https://www.univadis.fr/ajax/interactions', [
                        'drugList' => $drugList
                    ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to check interactions',
                    'message' => 'External API error'
                ], 500);
            }

            $data = $response->json();

            // Transform the response to match our expected format
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

            return response()->json([
                'interactions' => $interactions,
                'total_results' => $data['count'] ?? count($interactions),
                'is_european' => $data['isEuropean'] ?? 1
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check interactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map numeric severity to text
     */
    private function mapSeverity($numericSeverity)
    {
        $severityMap = [
            '1' => 'low',
            '2' => 'low',
            '3' => 'moderate',
            '4' => 'moderate',
            '5' => 'high',
            '6' => 'critical'
        ];

        return $severityMap[$numericSeverity] ?? 'unknown';
    }
}