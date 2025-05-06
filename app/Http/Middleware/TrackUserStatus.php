<?php
// app/Http/Middleware/TrackUserStatus.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;

class TrackUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->is_online = true;
            $user->last_activity = now();
            $user->save();


            app(FirebaseService::class)->updateUserPresence($user->id, true);
        }

        return $next($request);
    }
}