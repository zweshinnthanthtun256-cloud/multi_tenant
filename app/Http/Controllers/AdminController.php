<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Services\Onboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        return view('Admin.dashboard', [
            'totalCompanies' => Company::count(), 'activeCompanies' => Company::where('status', 1)->count(),
            'companyAdmins' => User::role('Company Admin')->count(), 'employees' => User::role(['Manager', 'Staff'])->count(),
            'pendingRequests' => RegistrationRequest::where('status', 'pending')->count(),
            'approvedRequests' => RegistrationRequest::where('status', 'approved')->count(),
            'rejectedRequests' => RegistrationRequest::where('status', 'rejected')->count(),
        ]);
    }

    public function registerList()
    {
        $registrations = RegistrationRequest::latest()->paginate(15);

        return view('Admin.registration', compact('registrations'));
    }

    public function approve(int $id)
    {
        $user = app(Onboarding::class)->approve($id);
        $status = Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Workspace approved. Password setup: '.__($status));
    }

    public function reject(int $id)
    {
        DB::transaction(function () use ($id) {
            $r = RegistrationRequest::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($r->status === 'pending', 422, 'Only pending requests can be rejected.');
            $r->update(['status' => 'rejected']);
        });

        return back()->with('success', 'Registration request rejected.');
    }
}
