<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * LIST EMPLOYEES
     */
    public function index()
    {
        $employees = User::where('company_id', auth()->user()->company_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Manager', 'Staff']);
            })
            ->latest()
            ->paginate(10);

        return view('company_admin.employees.index', compact('employees'));
    }

    /**
     * SHOW CREATE FORM
     */
    public function create()
    {
        $roles = Role::whereIn('name', ['Manager', 'Staff'])->get();

        return view('company_admin.employees.create', compact('roles'));
    }

    /**
     * STORE EMPLOYEE
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:Manager,Staff',
            'phone'    => 'nullable',
            'position' => 'nullable',
        ]);

        $employee = User::create([
            'company_id' => auth()->user()->company_id,
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'phone'      => $request->phone,
            'position'   => $request->position,
            'status'     => 'active',
        ]);

        // assign role (Spatie)
        $employee->assignRole($request->role);

        return redirect()
            ->route('company_admin.employees.index')
            ->with('success', 'Employee created successfully');
    }

    /**
     * EDIT FORM
     */
    public function edit(User $employee)
    {
        abort_if(
            $employee->company_id !== auth()->user()->company_id,
            403
        );

        $roles = Role::whereIn('name', ['Manager', 'Staff'])->get();

        return view('company_admin.employees.edit', compact('employee', 'roles'));
    }

    /**
     * UPDATE EMPLOYEE
     */
    public function update(Request $request, User $employee)
    {
        abort_if(
            $employee->company_id !== auth()->user()->company_id,
            403
        );

        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email,' . $employee->id,
            'role'     => 'required|in:Manager,Staff',
            'phone'    => 'nullable',
            'position' => 'nullable',
            'status'   => 'required|in:active,inactive,suspended',
        ]);

        $employee->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'position' => $request->position,
            'status'   => $request->status,
        ]);

        // sync role
        $employee->syncRoles([$request->role]);

        return redirect()
            ->route('company_admin.employees.index')
            ->with('success', 'Employee updated successfully');
    }

    /**
     * DELETE EMPLOYEE
     */
    public function destroy(User $employee)
    {
        abort_if(
            $employee->company_id !== auth()->user()->company_id,
            403
        );

        $employee->delete();

        return redirect()
            ->route('company_admin.employees.index')
            ->with('success', 'Employee deleted successfully');
    }

    /**
     * CHANGE STATUS (AJAX OR BUTTON)
     */
    public function changeStatus(User $employee)
    {
        abort_if(
            $employee->company_id !== auth()->user()->company_id,
            403
        );

        $employee->status = match ($employee->status) {
            'active'   => 'inactive',
            'inactive' => 'suspended',
            'suspended'=> 'active',
        };

        $employee->save();

        return back()->with('success', 'Employee status updated');
    }
}