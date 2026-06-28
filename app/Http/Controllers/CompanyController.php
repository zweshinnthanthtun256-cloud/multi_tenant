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
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|email',
    //         'logo' => 'required|image',
    //     ]);

    //     $logoName = null;

    //     if ($request->hasFile('logo')) {

    //         $logoName = time().'.'.$request->logo->extension();

    //         $request->logo->move(
    //             public_path('uploads/company'),
    //             $logoName
    //         );
    //     }

    //     Company::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'website' => $request->website,
    //         'logo' => $logoName,
    //         'address' => $request->address,
    //         'description' => $request->description,
    //         'status' => $request->status ?? 1,
    //     ]);

    //     return redirect()
    //         ->route('companies.index')
    //         ->with('success', 'Company Created Successfully');
    // }
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
    $dbName = 'company_' . Str::slug($request->name, '_') . '_' . rand(1000,9999);;

    
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

    // 3. CREATE DATABASE
    DB::statement("CREATE DATABASE {$dbName}");

    // 4. SWITCH TO TENANT DB
    config([
        'database.connections.tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'database' => $dbName,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]
    ]);

    DB::purge('tenant');
    DB::reconnect('tenant');

    // 5. RUN TENANT MIGRATIONS
    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);

    return redirect()
        ->route('companies.index')
        ->with('success', 'Company Created with Database Successfully');
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

        // delete old logo safely
        if ($company->logo && file_exists(public_path('uploads/company/' . $company->logo))) {
            unlink(public_path('uploads/company/' . $company->logo));
        }

        $logoName = time().'.'.$request->logo->extension();

        $request->logo->move(
            public_path('uploads/company'),
            $logoName
        );
    }

    // ❗ IMPORTANT: DO NOT TOUCH db_name HERE
    // (this is your tenant database identity)

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
        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company Deleted Successfully');
    }
}
