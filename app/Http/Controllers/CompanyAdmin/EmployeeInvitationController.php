<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeInvitation;
use App\Models\User;
use App\Notifications\WorkspaceInvitation;
use App\Support\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class EmployeeInvitationController extends Controller
{
    public function create()
    {
        $invitations = EmployeeInvitation::where('company_id', Workspace::id())->latest()->paginate(15);

        return view('company_admin.invitations.create', compact('invitations'));
    }

    public function store(Request $r)
    {
        $data = $r->validate(['email' => 'required|email|max:255|unique:users,email', 'role' => 'required|in:Manager,Staff']);
        $company = Company::findOrFail(Workspace::id());
        DB::transaction(function () use ($company, $data) {
            Workspace::reserveSeat($company);
            $email = strtolower($data['email']);
            abort_if(EmployeeInvitation::where('company_id', $company->id)->where('email', $email)->where('status', 'pending')->where('expires_at', '>', now())->exists(), 422, 'An active invitation already exists.');
            $token = Str::random(64);
            EmployeeInvitation::create(['company_id' => $company->id, 'email' => $email, 'role' => $data['role'],
                'token' => hash('sha256', $token), 'expires_at' => now()->addDays(3), 'status' => 'pending']);
            Notification::route('mail', $email)->notify(new WorkspaceInvitation(route('invitations.accept', $token), $company->name));
            ActivityLogger::log('Invitation queued', 'Invitation for '.$data['role'], $company->id);
        });

        return back()->with('success', 'Invitation queued for delivery.');
    }

    public function revoke(EmployeeInvitation $invitation)
    {
        Workspace::authorize($invitation);
        $invitation->update(['status' => 'expired', 'expires_at' => now()]);

        return back()->with('success', 'Invitation revoked.');
    }

    private function invitation(string $token)
    {
        return EmployeeInvitation::where('token', hash('sha256', $token))->where('status', 'pending')
            ->where('expires_at', '>', now())->firstOrFail();
    }

    public function accept(string $token)
    {
        $invitation = $this->invitation($token);
        abort_unless($invitation->company && (int) $invitation->company->status === 1, 404);

        return view('auth.invitation', compact('invitation', 'token'));
    }

    public function complete(Request $r, string $token)
    {
        $data = $r->validate(['name' => 'required|string|max:120', 'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()]]);
        DB::transaction(function () use ($token, $data) {
            $candidate = $this->invitation($token);
            $company = Company::whereKey($candidate->company_id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $company->status === 1, 403);
            $invitation = EmployeeInvitation::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
            abort_unless($invitation->status === 'pending' && $invitation->expires_at->isFuture(), 410);
            abort_if(User::where('email', $invitation->email)->exists(), 422, 'An account already exists for this address.');
            $limit = (int) config('saas.plans.'.$company->plan.'.seats', 5);
            abort_if(User::where('company_id', $company->id)->count() >= $limit, 422, 'Workspace seat limit reached.');
            $user = User::create(['name' => $data['name'], 'email' => $invitation->email, 'password' => $data['password'], 'company_id' => $company->id, 'status' => 'active']);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole(Role::findOrCreate($invitation->role, 'web'));
            Employee::create(['company_id' => $company->id, 'user_id' => $user->id, 'employee_code' => 'EMP-'.Str::uuid(), 'joining_date' => now(), 'status' => 'active']);
            $invitation->update(['status' => 'accepted']);
        });

        return redirect()->route('admin.login')->with('success', 'Your account is ready. Sign in to your workspace.');
    }
}
