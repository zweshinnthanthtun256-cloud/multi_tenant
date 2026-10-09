<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
        $credentials = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $credentials['email'])->first();
        $passwordInfo = password_get_info((string) $user?->password);

        if (! $user || ($passwordInfo['algoName'] ?? 'unknown') !== 'bcrypt'
            || ! Auth::attempt($credentials, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
        }
        if (! Auth::user()->canAccessWorkspace()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your account or workspace is inactive.']);
        }
        $r->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function dashboard()
    {
        return redirect()->route(auth()->user()->hasRole('Super Admin') ? 'admin.dashboard' : 'crm.dashboard');
    }

    public function forgot(Request $r)
    {
        $r->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($r->only('email'));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'If that account exists, a password setup link has been sent.');
    }

    public function reset(Request $r)
    {
        $data = $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()]]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PasswordReset
            ? redirect()->route('admin.login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function verify(EmailVerificationRequest $r)
    {
        $r->fulfill();

        return redirect()->route('dashboard');
    }

    public function resend(Request $r)
    {
        if (! $r->user()->hasVerifiedEmail()) {
            $r->user()->sendEmailVerificationNotification();
        }

        return back()->with('success', 'Verification link sent. Check your inbox.');
    }
}
