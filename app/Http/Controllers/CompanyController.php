<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withTrashed()->latest()->paginate(15);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.form', ['company' => new Company]);
    }

    public function edit(Company $company)
    {
        return view('companies.form', compact('company'));
    }

    private function data(Request $r, ?Company $company = null): array
    {
        return $r->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('companies')->ignore($company?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('companies')->ignore($company?->id)],
            'phone' => 'nullable|string|max:30', 'website' => 'nullable|url|max:255', 'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000', 'status' => 'required|in:0,1', 'logo' => 'nullable|image|max:2048',
        ]);
    }

    private function logo(Request $r): ?string
    {
        if (! $r->hasFile('logo')) {
            return null;
        }
        $name = Str::uuid().'.'.$r->file('logo')->extension();
        $r->file('logo')->move(public_path('uploads/company'), $name);

        return $name;
    }

    public function store(Request $r)
    {
        $data = $this->data($r);
        unset($data['logo']);
        $data['phone'] ??= '';
        $data['address'] ??= '';
        $data['description'] ??= '';
        $company = Company::create($data + ['logo' => $this->logo($r), 'db_name' => 'shared_'.Str::uuid(), 'trial_ends_at' => now()->addDays(14)]);
        ActivityLogger::log('Company created', 'Company #'.$company->id, $company->id);

        return redirect()->route('admin.companies.index')->with('success', 'Company created.');
    }

    public function update(Request $r, Company $company)
    {
        $data = $this->data($r, $company);
        unset($data['logo']);
        $data['phone'] ??= '';
        $data['address'] ??= '';
        $data['description'] ??= '';
        if ($r->hasFile('logo')) {
            $data['logo'] = $this->logo($r);
        }
        $company->update($data);
        ActivityLogger::log('Company updated', 'Company #'.$company->id, $company->id);

        return redirect()->route('admin.companies.index')->with('success', 'Company updated.');
    }

    public function show(Company $company)
    {
        $owners = User::where('company_id', $company->id)->role('Company Admin')->with('owner')->get();
        $managers = User::where('company_id', $company->id)->role('Manager')->get();
        $staffs = User::where('company_id', $company->id)->role('Staff')->get();
        $employeeCount = $owners->count() + $managers->count() + $staffs->count();

        return view('companies.show', compact('company', 'owners', 'managers', 'staffs', 'employeeCount'));
    }

    public function destroy(Company $company)
    {
        DB::transaction(function () use ($company) {
            $company->update(['status' => 0]);
            $company->delete();
            ActivityLogger::log('Company archived', 'Company #'.$company->id.'; data retained', $company->id);
        });

        return back()->with('success', 'Company archived. Data retained and access blocked.');
    }

    public function restore(int $id)
    {
        DB::transaction(function () use ($id) {
            $company = Company::onlyTrashed()->lockForUpdate()->findOrFail($id);
            $company->restore();
            $company->update(['status' => 1]);
            ActivityLogger::log('Company restored', 'Company #'.$company->id, $company->id);
        });

        return back()->with('success', 'Company restored.');
    }
}
