<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = Manager::latest()->paginate(10);
        return view('managers.index', compact('managers'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('managers.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // 1. Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->company_id,
        ]);

        // 2. Assign Role (Spatie)
        $user->assignRole('Manager');

        // 3. Create Manager
        Manager::create([
            'company_id' => $request->company_id,
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('managers.index')
            ->with('success', 'Manager created successfully');
    }

    public function edit(Manager $manager)
    {
        $companies = Company::all();
        return view('managers.edit', compact('manager', 'companies'));
    }

    public function update(Request $request, Manager $manager)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $manager->update([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        if ($manager->user) {
            $manager->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }

        return redirect()->route('managers.index')
            ->with('success', 'Manager updated successfully');
    }

    public function destroy(Manager $manager)
    {
        if ($manager->user) {
            $manager->user->delete();
        }

        $manager->delete();

        return redirect()->route('managers.index')
            ->with('success', 'Manager deleted successfully');
    }
}