<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorAssociate;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DoctorPermissionController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUserId = auth()->id();

        $doctor = Doctor::where('user_id', $loggedInUserId)->first();

        if (!$doctor) {
            abort(403, __('Unauthorized: You are not associated with any doctor.'));
        }

        $associatedUsers = DoctorAssociate::where('doctor_id', $doctor->id)
            ->whereHas('user') // assure que 'user' existe
            ->with('user.roles.permissions')
            ->get();

        $selectedUserId = $request->input('selected_user', null);

        /* // Fetch all permissions with readable names
        $permissions = DB::table('permissions')
            ->leftJoin('readable_permissions', 'permissions.id', '=', 'readable_permissions.permission_id')
            ->select(
                'permissions.id as permission_id',
                DB::raw('COALESCE(readable_permissions.readable_name, permissions.name) as display_name')
            )
            ->get(); */

        return view('profile_management.permissions.index', [
            'associatedUsers' => $associatedUsers,
            /* 'permissions' => $permissions, */
            'doctorId' => $doctor->id,
            'selectedUserId' => $selectedUserId,
        ]);
    }

    public function fetchUserRoles(Request $request)
    {
        $userId = $request->input('user_id');
        $doctorId = $request->input('doctor_id');

        // Fetch the user and their roles
        $user = DoctorAssociate::where('user_id', $userId)
            ->with('user.roles.permissions')
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Fetch existing permissions for the user in role_profile_permission
        $existingPermissions = DB::table('role_profile_permission')
            ->where('user_id', $userId)
            ->where('doctor_id', $doctorId)
            ->pluck('permission_id')
            ->toArray();

        // Fetch the current locale
        $locale = app()->getLocale();

        // Fetch all permissions with readable names
        $permissions = DB::table('permissions')
            // Only include permissions that have a corresponding record in permission_for_sub_profile
            ->join('permission_for_sub_profile', 'permissions.id', '=', 'permission_for_sub_profile.permission_readable_name_id')
            // Get the readable permission name (if available) from readable_permissions
            ->leftJoin('readable_permissions', 'permissions.id', '=', 'readable_permissions.permission_id')
            ->select(
                'permissions.id as permission_id',
                DB::raw('COALESCE(readable_permissions.readable_name, permissions.name) as display_name')
            )
            ->get()
            ->map(function ($permission) use ($existingPermissions, $locale) {
                // Attempt to decode the display name as JSON; if it fails, use the value directly.
                $decodedName = json_decode($permission->display_name, true);
                $localizedName = $decodedName[$locale] ?? $decodedName['fr'] ?? $permission->display_name;
                return [
                    'id' => $permission->permission_id,
                    'name' => $localizedName,
                    'has_permission' => in_array($permission->permission_id, $existingPermissions),
                ];
            });

        return response()->json([
            'roles' => $user->user->roles,
            'permissions' => $permissions,
            'existingPermissions' => $existingPermissions, // Optional, but kept for backward compatibility
        ]);
    }





    public function update(Request $request)
    {
        // Log the incoming request data
        \Log::info('Update Permission Request Data:', $request->all());

        // Validate the request data
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'is_checked' => 'required|boolean',
        ]);

        $doctorId = $validated['doctor_id'];
        $userId = $validated['user_id'];
        $permissionId = $validated['permission_id'];
        $isChecked = $validated['is_checked'];
        try {
            $role = DB::table('model_has_roles')
                ->where('model_id', $userId)
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->select('roles.*')
                ->first();

            if (!$role) {
                return response()->json(['success' => false, 'error' => 'Role not found for the selected user.'], 404);
            }
            if ($isChecked) {
                // Insert the permission if checked
                \Log::info("Inserting permission ID: $permissionId for user ID: $userId and doctor ID: $doctorId");

                DB::table('role_profile_permission')->insertOrIgnore([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'doctor_id' => $doctorId,
                    'user_id' => $userId,
                    'readable_name' => Permission::find($permissionId)->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \Log::info("Permission successfully inserted.");
            } else {
                // Remove the permission if unchecked
                \Log::info("Deleting permission ID: $permissionId for user ID: $userId and doctor ID: $doctorId");

                DB::table('role_profile_permission')
                    ->where('permission_id', $permissionId)
                    ->where('doctor_id', $doctorId)
                    ->where('user_id', $userId)
                    ->delete();

                \Log::info("Permission successfully deleted.");
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Log the exception message
            \Log::error('Error updating permission:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'error' => 'An error occurred while updating permissions.'], 500);
        }
    }

}
