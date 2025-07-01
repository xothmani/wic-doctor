<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrugInteractionBackup extends Model
{
    protected $table = 'drug_interactions_backup';
    
    protected $fillable = [
        'drug_combination_hash',
        'drug_ids',
        'formatted_drug_list',
        'api_response',
        'total_results',
        'is_european',
        'first_fetched_at',
        'last_updated_at'
    ];

    protected $casts = [
        'drug_ids' => 'array',
        'api_response' => 'array',
        'is_european' => 'boolean',
        'first_fetched_at' => 'datetime',
        'last_updated_at' => 'datetime'
    ];

    /**
     * Generate a hash for drug combination
     */
    public static function generateCombinationHash(array $drugIds): string
    {
        // Sort drug IDs to ensure consistent hash regardless of order
        sort($drugIds);
        return md5(implode(',', $drugIds));
    }

    /**
     * Get formatted drug names from the backup
     */
    public function getDrugNamesAttribute(): array
    {
        $drugNames = [];
        if (isset($this->api_response['items'])) {
            foreach ($this->api_response['items'] as $item) {
                if (isset($item['leftBoxes'])) {
                    foreach ($item['leftBoxes'] as $box) {
                        if (isset($box['drug']['name']) && !in_array($box['drug']['name'], $drugNames)) {
                            $drugNames[] = $box['drug']['name'];
                        }
                    }
                }
            }
        }
        return $drugNames;
    }
}
