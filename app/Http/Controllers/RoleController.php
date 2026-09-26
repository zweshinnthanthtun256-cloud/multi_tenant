<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private function editable(Role $role): void
    {
        abort_if(in_array($role->name, ['Super Admin', 'Company Admin', 'Manager', 'Staff']), 422, 'Built-in access roles cannot be changed.');
    }

    public function index()
    {
        $roles = Role::latest()->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function edit(Role $role)
    {
        $this->editable($role);

        return view('roles.edit', compact('role'));
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:100|unique:roles,name']);
        Role::create($data + ['guard_name' => 'web']);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function update(Request $r, Role $role)
    {
        $this->editable($role);
        $role->update($r->validate(['name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)]]));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $this->editable($role);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role removed.');
    }
}
