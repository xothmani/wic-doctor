<?php
// app/Console/Commands/UpdateUserStatus.php
namespace App\Console\Commands;

use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateUserStatus extends Command
{
    protected $signature = 'users:update-status';
    protected $description = 'Update offline status for inactive users';

    public function handle()
    {
        $threshold = Carbon::now()->subMinutes(5);

        $users = User::where('is_online', true)
            ->where('last_activity', '<', $threshold)->get();

        foreach ($users as $user) {
            $user->is_online = false;
            $user->save();

            // Update Firebase
            app(FirebaseService::class)->updateUserPresence($user->id, false);
        }

        $this->info($users->count() . ' users marked as offline.');
    }
}