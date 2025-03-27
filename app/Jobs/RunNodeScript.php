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
        // Escaping the path to your Node.js script and parameters
        $nodeScript = escapeshellarg(base_path('public/script-spec/nodejs.js'));
        $specialityId = escapeshellarg($this->specialityId);
        $specialityName = escapeshellarg($this->specialityName);
    
        // Command to execute Node.js script
        $command = "node $nodeScript $specialityId $specialityName 2>&1";
    
        // Execute the command and capture the output and return status
        exec($command, $output, $return_var);
    
        // Log the command being executed for debugging purposes
        Log::info("Executing command: $command");
    
        // Log the output of the Node.js script
        Log::info("Node.js Output: " . implode("\n", $output));
    
        // Log the exit code of the Node.js process
        Log::info("Node.js Exit Code: $return_var");
    
        // Check if the Node.js script failed
        if ($return_var !== 0) {
            // Log the error message with more details
            Log::error("Erreur lors de l'exécution du script Node.js. Exit Code: $return_var");
            Log::error("Output: " . implode("\n", $output)); // This will show detailed output/errors
        }
    }
    
}
