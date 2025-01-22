<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Flash;
use App\Models\RoleOwnership;
use App\Models\UserOwnership;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\ProfileManagement;

use App\Models\Doctor; // Assuming you have a Doctor model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\DataTables\DoctorUserDataTable;
use Illuminate\Support\Facades\DB;




class DoctorUserController extends Controller
{
    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var RoleRepository */
    private RoleRepository $roleRepository;



    public function __construct(UserRepository $userRepo, RoleRepository $roleRepo)
    {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;

    }

    /**
     * Display a listing of the users created by the doctor from `user_ownership` table.
     *
     * @return \Illuminate\View\View
     */
    /*public function index()
    {
        $userId = Auth::id();
        //Log::info("Retrieving doctor for user ID: {$userId}");

        // Retrieve the doctor associated with the logged-in user
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::warning("Doctor not found for user ID: {$userId}");
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }
        $doctorId = $doctor->id;
        // Get user IDs from the user_ownership table
        $userIds = UserOwnership::where('created_by', $doctorId)->pluck('user_id');

        // Fetch users based on the retrieved IDs
        $users = User::whereIn('id', $userIds)->get();

        return view('profile_management.users.index', compact('users'));
    }*/
    public function index(DoctorUserDataTable $dataTable): mixed
    {
        return $dataTable->render('profile_management.users.index');
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $userId = Auth::id();

        // Retrieve the doctor associated with the logged-in user
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::warning("Doctor not found for user ID: {$userId}");
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }

        $doctorId = $doctor->id;

        // Fetch roles created by any user (can refine this later)
        $roles = DB::table('role_for_doctors')
            ->join('roles', 'roles.id', '=', 'role_for_doctors.role_id') // Join with the roles table
            ->pluck('roles.name', 'roles.name'); // Retrieve role names
        $rolesSelected = []; // No roles selected by default for a new user

        // Pass the logged-in doctor's ID to the view
        $doctors = [$doctor->id => $doctor->name]; // Format the doctor as key-value pair

        return view('profile_management.users.create', compact('roles', 'rolesSelected', 'doctors'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'email' => 'required|email|unique:users,email',
        ]);


        // Check if start_date is before the current date
        if ($request->start_date && $request->start_date < now()->toDateString()) {
            Flash::error(__('La date de début ne peut pas être dans le passé.'));
            return redirect()->back()->withInput();
        }

        // Check if end_date is before start_date
        if ($request->end_date && $request->start_date && $request->end_date < $request->start_date) {
            Flash::error(__('La date de fin ne peut pas être avant la date de début.'));
            return redirect()->back()->withInput();
        }

        $userId = Auth::id();
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            abort(403, __('Unauthorized: You are not associated with any doctor.'));
        }

        $input = $request->all();
        $input['password'] = Hash::make($request->password);
        $input['api_token'] = Str::random(60);

        try {
            // Create the user
            $user = $this->userRepository->create($input);

            $selectedRoleName = $request->input('role');

            $role = Role::where('name', $selectedRoleName)
                ->where('doctor_id', $doctor->id)
                ->first();

            // If the role doesn't exist, create a new one
            if (!$role) {
                $role = Role::create([
                    'name' => $selectedRoleName,
                    'guard_name' => 'web',
                    'doctor_id' => $doctor->id,
                ]);
            }

            // Assign the newly created role to the user
            $user->assignRole($role);

            // Insert into user_ownership table
            UserOwnership::create([
                'user_id' => $user->id,
                'created_by' => $doctor->id,
            ]);

            ProfileManagement::create([
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->has('is_active') ? $request->is_active : 0,
            ]);

            Flash::success(__('Utilisateur créé avec succès.'));
        } catch (\Exception $e) {
            Flash::error(__('Erreur lors de la création de l\'utilisateur : ') . $e->getMessage());
            return redirect()->route('Doctors_users.index')->with('error', __('Erreur lors de la création de l\'utilisateur.'));
        }

        return redirect()->route('Doctors_users.index');
    }




    /**
     * Show the form for editing the specified user.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $userId = Auth::id();

        // Retrieve the doctor associated with the logged-in user
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }

        $doctorId = $doctor->id;

        // Find the user being edited
        $user = $this->userRepository->find($id);

        // Verify if the user is owned by the logged-in doctor
        $ownership = \DB::table('user_ownership')
            ->where('user_id', $id)
            ->where('created_by', $doctorId)
            ->exists();

        if (!$ownership) {
            abort(403, __('Non autorisé : Vous n\'êtes pas autorisé.'));
        }

        // Fetch roles
        $roles = RoleOwnership::join('roles', 'roles.id', '=', 'role_ownership.role_id')
            ->pluck('roles.name', 'roles.name');

        $rolesSelected = $user->getRoleNames()->toArray();

        // Fetch ProfileManagement using both user_id and doctor_id
        $profileManagement = ProfileManagement::where('user_id', $id)
            ->where('doctor_id', $doctorId)
            ->first();

        // If no profileManagement exists, create an empty one to avoid errors in the view
        if (!$profileManagement) {
            $profileManagement = new ProfileManagement([
                'start_date' => null,
                'end_date' => null,
                'is_active' => false,
            ]);
        }

        return view('profile_management.users.edit', compact('user', 'roles', 'rolesSelected', 'profileManagement'));
    }




    /**
     * Update the specified user in storage.
     *
     * @param int $id
     * @param UpdateUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, UpdateUserRequest $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id, // Unique email, excluding current user
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);



        // Check if end_date is before start_date
        if ($request->end_date && $request->start_date && $request->end_date < $request->start_date) {
            Flash::error(__('La date de fin ne peut pas être avant la date de début.'));
            return redirect()->back()->withInput();
        }

        $userId = Auth::id();
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }

        $doctorId = $doctor->id;

        $input = $request->all();
        if (empty($input['password'])) {
            unset($input['password']);
        } else {
            $input['password'] = Hash::make($input['password']);
        }

        try {
            // Update user
            $user = $this->userRepository->update($input, $id);

            // Update roles
            if (isset($input['roles'])) {
                $user->syncRoles($input['roles']);
            }

            // Update profile management data
            ProfileManagement::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'doctor_id' => $doctor->id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'is_active' => $request->has('is_active') ? $request->is_active : 0,
                ]
            );

            Flash::success(__('Utilisateur mis à jour avec succès.'));
        } catch (\Exception $e) {
            Flash::error(__('Erreur lors de la mise à jour de l\'utilisateur : ') . $e->getMessage());
        }

        return redirect()->route('Doctors_users.index');
    }




    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        //Log::info("Retrieving doctor for user ID: {$userId}");

        // Retrieve the doctor associated with the logged-in user
        $doctor = Doctor::where('user_id', $userId)->first();

        if (!$doctor) {
            Log::warning("Doctor not found for user ID: {$userId}");
            abort(403, __('Non autorisé : Vous n\'êtes pas associé à un médecin.'));
        }
        $doctorId = $doctor->id;
        $user = $this->userRepository->find($id);

        // Verify if the user is owned by the logged-in doctor
        $ownership = \DB::table('user_ownership')
            ->where('user_id', $id)
            ->where('created_by', $doctorId)
            ->exists();

        if (!$ownership) {
            Flash::error(__('User not found or unauthorized.'));
            return redirect()->route('Doctors_users.index');
        }

        try {
            $this->userRepository->delete($id);

            // Remove ownership
            \DB::table('user_ownership')
                ->where('user_id', $id)
                ->delete();

            Flash::success(__('User deleted successfully.'));
        } catch (\Exception $e) {
            Flash::error(__('Error deleting user: ') . $e->getMessage());
        }

        return redirect()->route('Doctors_users.index');
    }


}
