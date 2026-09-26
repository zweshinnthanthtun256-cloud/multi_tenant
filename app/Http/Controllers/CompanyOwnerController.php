<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class CompanyOwnerController extends Controller
{
    public function index()
    {
        $owners = CompanyOwner::with('company')->latest()->paginate(15);

        return view('owners.index', compact('owners'));
    }

    public function create()
    {
        $companies = Company::where('status', 1)->get();

        return view('owners.create', compact('companies'));
    }

    public function show(CompanyOwner $owner)
    {
        return view('owners.show', compact('owner'));
    }

    public function edit(CompanyOwner $owner)
    {
        $companies = Company::all();

        return view('owners.edit', compact('owner', 'companies'));
    }

    public function store(Request $r)
    {
        $data = $r->validate(['company_id' => 'required|exists:companies,id', 'name' => 'required|string|max:120',
            'email' => 'required|email|max:255|unique:users,email', 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500']);
        $user = DB::transaction(function () use ($data) {
            $company = Company::findOrFail($data['company_id']);
            Workspace::reserveSeat($company);
            $user = User::create(['name' => $data['name'], 'email' => strtolower($data['email']), 'company_id' => $company->id, 'password' => Str::random(64), 'status' => 'active']);
            $user->assignRole(Role::findOrCreate('Company Admin', 'web'));
            CompanyOwner::create(array_merge($data, ['email' => $user->email, 'phone' => $data['phone'] ?? '', 'user_id' => $user->id]));
            ActivityLogger::log('Owner created', 'User #'.$user->id, $company->id);

            return $user;
        });
        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('admin.owners.index')->with('success', 'Owner created. A password setup link was requested.');
    }

    public function update(Request $r, CompanyOwner $owner)
    {
        $data = $r->validate(['company_id' => ['required', Rule::in([$owner->company_id])], 'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($owner->user_id)], 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500']);
        DB::transaction(function () use ($owner, $data) {
            $owner->update(array_merge($data, ['phone' => $data['phone'] ?? '']));
            $user = $owner->user;
            if ($user->email !== $data['email']) {
                $user->email_verified_at = null;
            }
            $user->fill(['name' => $data['name'], 'email' => strtolower($data['email'])])->save();
        });

        return redirect()->route('admin.owners.index')->with('success', 'Owner updated.');
    }

    public function destroy(CompanyOwner $owner)
    {
        DB::transaction(function () use ($owner) {
            Company::whereKey($owner->company_id)->lockForUpdate()->firstOrFail();
            abort_if(CompanyOwner::where('company_id', $owner->company_id)->count() <= 1, 422, 'Keep at least one workspace owner.');
            abort_if($owner->user_id === auth()->id(), 422, 'You cannot remove your own account.');
            ActivityLogger::log('Owner removed', 'User #'.$owner->user_id, $owner->company_id);
            $owner->user?->delete();
            $owner->delete();
        });

        return back()->with('success', 'Owner removed and login access revoked.');
    }
}
