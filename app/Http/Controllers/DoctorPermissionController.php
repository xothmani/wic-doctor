<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;



class DoctorPermissionController extends Controller
{
    /**
     * Display the permissions management interface for doctor-associated users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the currently authenticated user's ID
        $loggedInUserId = auth()->id();

        // Fetch the doctor record associated with the logged-in user
        $doctor = Doctor::where('user_id', $loggedInUserId)->first();

        if (!$doctor) {
            abort(403, __('Unauthorized: You are not associated with any doctor.'));
        }

        // Fetch the 'doctor' role
        $doctorRole = Role::where('name', 'doctor')->first();

        if (!$doctorRole) {
            abort(403, __('Unauthorized: No doctor role found.'));
        }

        // Fetch the permissions assigned to the 'doctor' role
        $doctorPermissions = $doctorRole->permissions;

        // Fetch all role-profile-permission entries for the current doctor
        $roleProfilePermissions = DB::table('role_profile_permission')
            ->where('doctor_id', $doctor->id)
            ->get()
            ->keyBy('permission_id'); // Index by permission_id for quick lookup

        // Fetch the 'secretary' role
        $secretaryRole = Role::where('name', 'secretary')->first();

        return view('profile_management.permissions.index', [
            'roles' => [$secretaryRole],
            'profilePermissions' => $doctorPermissions,
            'roleProfilePermissions' => $roleProfilePermissions,
        ]);
    }









    /**
     * Update the permissions for a specific user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $roleId)
    {
        // Validate the request
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Validate the role
        $role = Role::findOrFail($roleId);

        // Sync the permissions for the role
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->back()->with('success', __('Permissions updated successfully.'));
    }

    public function store(Request $request)
    {
        // Log the request data for debugging
        \Log::info('Request Data:', $request->all());

        // Get the currently authenticated user's ID
        $loggedInUserId = auth()->id();

        // Fetch the doctor record associated with the logged-in user
        $doctor = Doctor::where('user_id', $loggedInUserId)->first();

        if (!$doctor) {
            abort(403, __('Unauthorized: You are not associated with any doctor.'));
        }

        // Validate the request
        $roleId = $request->input('role_id'); // Role being updated
        $submittedPermissions = $request->input('permissions', []); // Permissions from the form (checked)
        $doctorId = $doctor->id;

        // Fetch the role
        $role = Role::findOrFail($roleId);

        // Fetch all existing permissions related to the role and doctor_id
        $existingPermissions = DB::table('role_profile_permission')
            ->where('role_id', $roleId)
            ->when($doctorId, function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->pluck('permission_id')
            ->toArray();

        // Determine which permissions to add and which to remove
        $permissionsToAdd = array_diff($submittedPermissions, $existingPermissions); // New permissions
        $permissionsToRemove = array_diff($existingPermissions, $submittedPermissions); // Unchecked permissions

        // Add new permissions to the role_profile_permission table
        foreach ($permissionsToAdd as $permissionId) {
            DB::table('role_profile_permission')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'doctor_id' => $doctorId, // Optional: associate with the doctor
                'readable_name' => 'Permission for role ' . $role->name, // Optional readable name
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Remove unchecked permissions from the role_profile_permission table
        DB::table('role_profile_permission')
            ->where('role_id', $roleId)
            ->when($doctorId, function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->whereIn('permission_id', $permissionsToRemove)
            ->delete();

        // Update the role's permissions in the `role_has_permissions` table
        //$permissions = Permission::whereIn('id', $submittedPermissions)->get();
        //$role->syncPermissions($permissions);

        // Forget cached permissions to reflect changes
        //app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Redirect back with a success message
        return redirect()->back()->with('success', __('Permissions updated successfully.'));
    }







    public function storePermissions(Request $request)
    {
        // Fetch the selected permissions from the request
        $selectedPermissions = $request->input('permissions', []);

        // Insert selected permissions into the profile_permission table
        foreach ($selectedPermissions as $permissionId) {
            $permission = Permission::find($permissionId);

            if ($permission) {
                DB::table('role_profile_permission')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'readable_name' => ucfirst(str_replace('.', ' ', $permission->name)), // Convert "availabilityHours.index" to "Availability Hours Index"
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', __('Permissions saved successfully.'));
    }


}
