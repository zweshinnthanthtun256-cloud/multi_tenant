<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\RegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    // Register Form Page ကို ပြသပေးသည့် Function
    public function index()
    {
        return view('Admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('dashboard')->with('success', 'Login Successful');
        }

        return back()->withErrors([
            'email' => 'Invalid Email or Password',

        ])->onlyInput('email');

    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $companyCount = Company::count();
        $ownerCount = CompanyOwner::count();
        $notificationCount = RegistrationRequest::where('status', 'pending')
            ->count();

        return view('Admin.dashboard', compact('companyCount', 'ownerCount', 'notificationCount'));
    }

    public function registerList()
    {
        $registrations = RegistrationRequest::latest()
            ->paginate(10);

        return view(
            'Admin.registration',
            compact('registrations')
        );
    }

    public function approve($id)
    {
        $registration = RegistrationRequest::findOrFail($id);

        // Generate default password
        $plainPassword = 'password123';

        // Update registration status
        $registration->update([
            'status' => 'approved',
        ]);

        // Send login details
        Mail::raw(
            "Dear {$registration->username},

Congratulations! Your registration has been approved.

Login Details:
--------------------------------
Email: {$registration->email}
Password: {$plainPassword}
--------------------------------

Login URL:
http://127.0.0.1:8000/login

For security, please change your password after your first login.

Thank you.",
            function ($message) use ($registration) {
                $message->to($registration->email)
                    ->subject('Registration Approved - Login Credentials');
            }
        );

        return back()->with(
            'success',
            'Registration approved, user account created, and email sent.'
        );
    }

    public function reject($id)
    {
        $registration = RegistrationRequest::findOrFail($id);

        $registration->update([
            'status' => 'rejected',
        ]);

        Mail::raw(
            "Dear {$registration->username},

            We regret to inform you that your registration request has been rejected.

            Please contact the administrator for further information.",
        function ($message) use ($registration) {
                $message->to($registration->email)
                    ->subject('Registration Rejected');
            }
        );

        return back()->with(
            'success',
            'Registration rejected and email sent successfully.'
        );
    }
}
