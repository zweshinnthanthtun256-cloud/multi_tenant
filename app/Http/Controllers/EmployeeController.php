<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    private function platform(): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    private function prefix(): string
    {
        return $this->platform() ? 'admin' : 'company_admin';
    }

    private function check(Employee $employee): void
    {
        if (! $this->platform()) {
            Workspace::authorize($employee);
        }
        abort_unless($employee->user && $employee->user->hasAnyRole(['Manager', 'Staff'])
            && ! $employee->user->hasAnyRole(['Company Admin', 'Super Admin']), 403);
    }

    public function index(Request $r)
    {
        $employees = Employee::with('user', 'company')->when(! $this->platform(), fn ($q) => $q->where('company_id', Workspace::id()))
            ->when($r->filled('q'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$r->string('q').'%')))
            ->latest()->paginate(15)->withQueryString();
        $prefix = $this->prefix();

        return view('company_admin.employees.index', compact('employees', 'prefix'));
    }

    public function create()
    {
        return $this->form(new Employee);
    }

    private function form(Employee $employee)
    {
        $prefix = $this->prefix();
        $companies = $this->platform() ? Company::where('status', 1)->get() : collect();

        return view('company_admin.employees.form', compact('employee', 'prefix', 'companies'));
    }

    public function edit(Employee $employee)
    {
        $this->check($employee);

        return $this->form($employee);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
            'role' => 'required|in:Manager,Staff', 'phone' => 'nullable|string|max:30', 'position' => 'nullable|string|max:120',
            'company_id' => $this->platform() ? 'required|exists:companies,id' : 'prohibited']);
        $company = Company::findOrFail($this->platform() ? $data['company_id'] : Workspace::id());
        abort_unless((int) $company->status === 1, 422);
        $user = DB::transaction(function () use ($data, $company) {
            Workspace::reserveSeat($company);
            $user = User::create(['name' => $data['name'], 'email' => strtolower($data['email']), 'password' => $data['password'], 'company_id' => $company->id, 'status' => 'active']);
            $user->assignRole(Role::findOrCreate($data['role'], 'web'));
            $employee = Employee::create(['company_id' => $company->id, 'user_id' => $user->id, 'employee_code' => 'EMP-'.Str::uuid(),
                'phone' => $data['phone'] ?? null, 'position' => $data['position'] ?? null, 'joining_date' => now(), 'status' => 'active']);
            ActivityLogger::log('Employee created', 'Employee #'.$employee->id, $company->id);

            return $user;
        });
        $user->sendEmailVerificationNotification();

        return redirect()->route($this->prefix().'.employees.index')->with('success', 'Team member created. Verification email sent.');
    }

    public function update(Request $r, Employee $employee)
    {
        $this->check($employee);
        $data = $r->validate(['name' => 'required|string|max:120', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($employee->user_id)],
            'role' => 'required|in:Manager,Staff', 'phone' => 'nullable|string|max:30', 'position' => 'nullable|string|max:120', 'status' => 'required|in:active,inactive,suspended']);
        DB::transaction(function () use ($employee, $data) {
            $user = $employee->user;
            $email = strtolower($data['email']);
            if ($user->email !== $email) {
                $user->email_verified_at = null;
            }
            $user->fill(['name' => $data['name'], 'email' => $email, 'status' => $data['status']])->save();
            $user->syncRoles([$data['role']]);
            $employee->update(['phone' => $data['phone'] ?? null, 'position' => $data['position'] ?? null, 'status' => $data['status']]);
            ActivityLogger::log('Employee updated', 'Employee #'.$employee->id, $employee->company_id);
        });

        return redirect()->route($this->prefix().'.employees.index')->with('success', 'Team member updated.');
    }

    public function destroy(Employee $employee)
    {
        $this->check($employee);
        DB::transaction(function () use ($employee) {
            ActivityLogger::log('Employee removed', 'Employee #'.$employee->id, $employee->company_id);
            $employee->user->delete();
        });

        return back()->with('success', 'Team member removed and access revoked.');
    }

    public function changeStatus(Employee $employee)
    {
        $this->check($employee);
        DB::transaction(function () use ($employee) {
            $status = $employee->status === 'active' ? 'suspended' : 'active';
            $employee->update(['status' => $status]);
            $employee->user->update(['status' => $status]);
        });

        return back()->with('success', 'Access updated.');
    }
}
