<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

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

        $company = Company::findOrFail($request->company_id);
        $tenantDbName = $company->db_name;

        // ----------------------------------------------------
        // အဆင့် (၁) - LANDLORD (MAIN) DATABASE ထဲသို့ ထည့်ခြင်း
        // ----------------------------------------------------
        
        // 1. Create USER (login account)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $request->company_id,
        ]);
        
        $role = Role::findByName('Company Admin', 'web');
        $user->assignRole($role);

        // 2. Create COMPANY OWNER
        CompanyOwner::create([
            'company_id' => $request->company_id,
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // ----------------------------------------------------
        // အဆင့် (၂) - TENANT DATABASE ထဲသို့ တပြိုင်တည်း ထည့်ခြင်း
        // ----------------------------------------------------
        try {
            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database' => $tenantDbName,
                    'username' => config('database.connections.mysql.username'),
                    'password' => config('database.connections.mysql.password'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ]
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            // ၁။ CompanyController က ဆောက်ပေးထားခဲ့ပြီးသား Role ID ကို လှမ်းရှာမယ်
            $tenantRole = DB::connection('tenant')->table('roles')
                ->where('name', 'Company Admin')
                ->where('guard_name', 'web')
                ->first();

            if ($tenantRole) {
                // ၂။ Tenant DB ရဲ့ users table မှာ သွားဆောက်မယ်
                $tenantUserId = DB::connection('tenant')->table('users')->insertGetId([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'company_id' => $request->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ၃။ Role ချိတ်ဆက်ပေးမယ်
                DB::connection('tenant')->table('model_has_roles')->insert([
                    'role_id' => $tenantRole->id,
                    'model_type' => 'App\Models\User',
                    'model_id' => $tenantUserId,
                ]);
            }

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Tenant DB Error: ' . $e->getMessage()]);
        }

        return redirect()->route('owners.index')
            ->with('success', 'Owner + User created successfully in both databases');
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

        $oldEmail = $owner->getOriginal('email'); // ပြောင်းလဲခြင်းမပြုမီ Email အဟောင်းကို မှတ်ထားမယ်

        // 1. Update owner (Main DB)
        $owner->update([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // 2. Update user too (Main DB)
        if ($owner->user) {
            $owner->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        }

        // 3. Update User (Tenant DB)
        $company = Company::findOrFail($request->company_id);
        if ($company && $company->db_name) {
            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database' => $company->db_name,
                    'username' => config('database.connections.mysql.username'),
                    'password' => config('database.connections.mysql.password'),
                ]
            ]);
            DB::purge('tenant');

            // Email အဟောင်းကို အခြေခံပြီး ရှာဖွေပြီး Update လုပ်ပေးခြင်း
            DB::connection('tenant')->table('users')
                ->where('email', $oldEmail)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'updated_at' => now()
                ]);
        }

        return redirect()->route('owners.index')
            ->with('success', 'Owner updated successfully in both databases');
    }

    public function show(CompanyOwner $owner)
    {
        return view('owners.show', compact('owner'));
    }

    public function destroy(CompanyOwner $owner)
    {
        // Tenant DB ထဲကပါ တစ်ပါတည်း လိုက်ဖျက်ပေးခြင်း
        if ($owner->company && $owner->company->db_name) {
            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'database' => $owner->company->db_name,
                    'username' => config('database.connections.mysql.username'),
                    'password' => config('database.connections.mysql.password'),
                ]
            ]);
            DB::purge('tenant');

            // User ရဲ့ ID ကို အရင်ရှာမယ် (Spatie Relation တွေပါ ရှင်းထုတ်ဖို့)
            $tenantUser = DB::connection('tenant')->table('users')->where('email', $owner->email)->first();
            
            if ($tenantUser) {
                // Role connection ဖြုတ်မယ်
                DB::connection('tenant')->table('model_has_roles')
                    ->where('model_id', $tenantUser->id)
                    ->where('model_type', 'App\Models\User')
                    ->delete();

                // User ကို ဖျက်မယ်
                DB::connection('tenant')->table('users')->where('id', $tenantUser->id)->delete();
            }
        }

        // Main DB ကနေ ဖျက်မယ်
        $owner->delete();

        return redirect()->route('owners.index')->with('success', 'Owner deleted from both databases');
    }
}