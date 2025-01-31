<?php

namespace App\Http\Controllers;

use App\DataTables\DoctorRoleDataTable;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\RoleRepository;
use App\Models\RoleOwnership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Flash;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Response;

class DoctorRoleController extends Controller
{
    /** @var RoleRepository */
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
    }

    /**
     * Display a listing of the roles created by the logged-in doctor.
     *
     * @param DoctorRoleDataTable $doctorRoleDataTable
     * @return mixed
     */
    public function index(DoctorRoleDataTable $doctorRoleDataTable): mixed
    {
        return $doctorRoleDataTable->render('doctors.roles.index');
    }

    /**
     * Show the form for creating a new Role.
     *
     * @return View
     */
    public function create(): View
    {
        return view('doctors.roles.create');
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param CreateRoleRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateRoleRequest $request): RedirectResponse
    {
        $input = $request->all();

        // Create the role
        $role = $this->roleRepository->create($input);
        $userId = Auth::id();

        // Track ownership in the `role_ownership` table
        RoleOwnership::create([
            'role_id' => $role->id,
            'created_by' => $userId, // Track the logged-in doctor
        ]);

        Flash::success('Role saved successfully.');

        return redirect(route('roles_for_doctors.index'));
    }

    /**
     * Display the specified Role.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function show(int $id): RedirectResponse|View
    {
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles_for_doctors.index'));
        }

        return view('doctors.roles.show')->with('role', $role);
    }

    /**
     * Show the form for editing the specified Role.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id): RedirectResponse|View
    {
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles_for_doctors.index'));
        }

        return view('doctors.roles.edit')->with('role', $role);
    }

    /**
     * Update the specified Role in storage.
     *
     * @param int $id
     * @param UpdateRoleRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdateRoleRequest $request): RedirectResponse
    {
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles_for_doctors.index'));
        }

        $role = $this->roleRepository->update($request->all(), $id);

        Flash::success('Role updated successfully.');

        return redirect(route('roles_for_doctors.index'));
    }

    /**
     * Remove the specified Role from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles_for_doctors.index'));
        }

        $this->roleRepository->delete($id);

        Flash::success('Role deleted successfully.');

        return redirect(route('roles_for_doctors.index'));
    }
}
