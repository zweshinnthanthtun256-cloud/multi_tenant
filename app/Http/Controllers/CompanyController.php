<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::latest()->paginate(10);
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'logo' => 'required|image',
        ]);

        $logoName = null;
        if ($request->hasFile('logo')) {
            $logoName = time().'.'.$request->logo->extension();
            $request->logo->move(
                public_path('uploads/company'),
                $logoName
            );
        }

        // 1. Create company DB name
        $dbName = 'company_' . Str::slug($request->name, '_') . '_' . rand(1000,9999);

        $company = Company::create([
            'name' => $request->name,
            'db_name' => $dbName,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'logo' => $logoName,
            'address' => $request->address,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        // 2. CREATE DATABASE
        DB::statement("CREATE DATABASE {$dbName}");

        // 3. SWITCH TO TENANT DB (Config များကို လက်ရှိ Main connection ကနေ ယူသုံးတာ ပိုစိတ်ချရပါတယ်)
        config([
            'database.connections.tenant' => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => $dbName,
                'username' => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        // 4. RUN TENANT MIGRATIONS
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        // 5. 💡 အသစ်ထည့်သွင်းချက် - Tenant DB ထဲမှာ Default Spatie Roles များကို တစ်ပါတည်း ဆောက်ပေးခြင်း
        // ဒါမှ နောက်တစ်ဆင့်မှာ အသုံးပြုသူ ဆောက်တဲ့အခါ Role ရှာမတွေ့တဲ့ Error လုံးဝ မတက်တော့မှာ ဖြစ်ပါတယ်။
        DB::connection('tenant')->table('roles')->insert([
            [
                'name' => 'Company Admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Manager',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Sales Staff',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company Created with Database and Default Roles Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'logo' => 'nullable|image',
        ]);

        $logoName = $company->logo;
        if ($request->hasFile('logo')) {
            if ($company->logo && file_exists(public_path('uploads/company/' . $company->logo))) {
                unlink(public_path('uploads/company/' . $company->logo));
            }

            $logoName = time().'.'.$request->logo->extension();
            $request->logo->move(
                public_path('uploads/company'),
                $logoName
            );
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'website' => $request->website,
            'logo' => $logoName,
            'address' => $request->address,
            'description' => $request->description,
            'status' => $request->status ?? 1,
        ]);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // 💡 အသစ်ထည့်သွင်းချက် - ကုမ္ပဏီကို ဖျက်ရင် ၎င်းရဲ့ Database ကိုပါ အပြီးဖျက်ချခြင်း
        if ($company->db_name) {
            // SQL Injection မဖြစ်အောင် သန့်စင်ပြီးမှ သုံးပါမယ်
            $safeDbName = preg_replace('/[^a-zA-Z0-9_]/', '', $company->db_name);
            DB::statement("DROP DATABASE IF EXISTS {$safeDbName}");
        }

        // Logo ပုံပါ ဖျက်ပေးမယ်
        if ($company->logo && file_exists(public_path('uploads/company/' . $company->logo))) {
            unlink(public_path('uploads/company/' . $company->logo));
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company and its Database Deleted Successfully');
    }
}