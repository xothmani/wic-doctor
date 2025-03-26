<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunNodeScript implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $specialityId;
    protected $specialityName;

    public function __construct($specialityId, $specialityName)
    {
        $this->specialityId = $specialityId;
        $this->specialityName = $specialityName;
    }

    public function handle()
    {
        $nodeScript = escapeshellarg(base_path('public/script-spec/nodejs.js'));
        $command = "node $nodeScript " . escapeshellarg($this->specialityId) . " " . escapeshellarg($this->specialityName) . " 2>&1";
        
        exec($command, $output, $return_var);
        
       // Log::info("Execution Node.js Output: " . implode("\n", $output));
       // Log::info("Node.js Exit Code: $return_var");

        if ($return_var !== 0) {
            Log::error("Erreur lors de l'exécution du script Node.js !");
        }
    }
}
