<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            // Check for 'doctor' role
            if ($user->hasRole('doctor') || $user->hasRole('Telesecretary')) {
                $activeMembership = DB::table('membership')
                    ->where('user_id', $userId)
                    ->where('end_date', '>=', $today)
                    ->orderBy('end_date', 'desc')
                    ->first();

                if (!$activeMembership) {
                    \Log::info("No active membership found for Doctor (User ID: {$userId})");
                    session()->flash('error', __('Votre abonnement a expiré ou est inactif.'));
                    abort(403, __('L’abonnement est inactif ou expiré.'));
                    //return view('membership.no_membership');
                }

                return $next($request);
            }

            // Check for 'Secretary' role
            if ($user->hasRole('Secretary')) {
                $this->checkAccess($userId, $today);
                return $next($request);
            }

            // Check for 'Replacement' role
            if ($user->hasRole('Replacement')) {
                $this->checkAccess($userId, $today);
                return $next($request);
            }

            // Default behavior if no roles match
            abort(403, __('Accès interdit.'));
        }

        return $next($request);
    }

    /**
     * Check membership and access for secretary-like roles.
     *
     * @param int $userId
     * @param string $today
     * @return void
     */
    private function checkAccess($userId, $today)
    {
        $associatedDoctorMembership = DB::table('profile_management')
            ->join('doctors', 'profile_management.doctor_id', '=', 'doctors.id') // Join to get the doctor details
            ->join('membership', 'doctors.user_id', '=', 'membership.user_id') // Match the membership with the doctor's user_id
            ->where('profile_management.user_id', $userId) // Check the secretary's user_id in profile_management
            ->where('profile_management.is_active', 1) // Ensure the profile is active
            ->where('profile_management.end_date', '>=', $today) // Ensure the profile end date is valid
            ->where('membership.end_date', '>=', $today) // Ensure the doctor's membership is still valid
            ->orderBy('membership.end_date', 'desc') // Order by the closest expiration date
            ->first();

        if (!$associatedDoctorMembership) {
            \Log::info("No active membership found for Secretary-like role (User ID: {$userId})");
            session()->flash('error', __('L’abonnement du médecin associé est inactif ou expiré.'));
            abort(403, __('Accès interdit ou L’abonnement du médecin associé est inactif ou expiré.'));
        }

        $isAccessActive = DB::table('profile_management')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('end_date', '>=', $today)
            ->exists();

        if (!$isAccessActive) {
            \Log::info("Access not active for Secretary-like role (User ID: {$userId})");
            abort(403, __('Votre accès est inactif ou expiré.'));
        }
    }
}
