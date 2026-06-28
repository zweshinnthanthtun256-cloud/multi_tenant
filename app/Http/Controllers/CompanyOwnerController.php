<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyOwnerController extends Controller
{
    public function index()
    {
        $owners = CompanyOwner::latest()->paginate(10);

        return view('owners.index', compact('owners'));
    }

    public function create()
{
    $companies = Company::all();

    return view('owners.create', compact('companies'));
}

    

public function store(Request $request)
{
    $request->validate([
        'company_id' => 'required|exists:companies,id',
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable',
        'address' => 'nullable',
        'password' => 'required|min:6',
    ]);

    // 1. Create USER (login account)
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'company_id' => $request->company_id,
    ]);

    // 2. Create COMPANY OWNER
    CompanyOwner::create([
        'company_id' => $request->company_id,
        'user_id' => $user->id,
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
    ]);

    return redirect()->route('owners.index')
        ->with('success', 'Owner + User created successfully');
}

    public function edit(CompanyOwner $owner)
    {
        $companies = Company::all();

        return view('owners.edit', compact('owner', 'companies'));
    }

    public function update(Request $request, CompanyOwner $owner)
{
    $request->validate([
        'company_id' => 'required|exists:companies,id',
        'name' => 'required',
        'email' => 'required|email',
        'phone' => 'nullable',
        'address' => 'nullable',
    ]);

    // update owner
    $owner->update([
        'company_id' => $request->company_id,
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
    ]);

    // update user too
    if ($owner->user) {
        $owner->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
    }

    return redirect()->route('owners.index')
        ->with('success', 'Owner updated successfully');
}
    public function show(CompanyOwner $owner)
    {
        return view('owners.show', compact('owner'));
    }

    public function destroy(CompanyOwner $owner)
    {
        $owner->delete();

        return redirect()->route('owners.index')->with('success', 'Owner deleted');
    }
}