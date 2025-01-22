<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckMembership
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $today = now()->toDateString();

            // Allow admins to bypass membership checks
            if ($user->hasRole('admin')) {
                return $next($request);
            }

            // Check active membership for non-admin users (e.g., doctors)
            $activeMembership = DB::table('membership')
                ->where('user_id', $userId)
                ->where('end_date', '>=', $today)
                ->orderBy('end_date', 'desc')
                ->first();

            if (!$activeMembership) {
                \Log::info("No active membership found for User ID: {$userId}");
                // Redirect to the 'no membership' page
                return response()->view('membership.no_membership', [], 403);
            }
        }

        return $next($request);
    }

}
