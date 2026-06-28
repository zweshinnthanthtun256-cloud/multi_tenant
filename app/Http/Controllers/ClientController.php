<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    public function index()
    {
        return view ('userpage');
    }

    public function register()
    {
        return view('register');
    }
    public function registerSubmit(Request $request)
{
    $request->validate([
        'role' => 'required',
        'username' => 'required',
        'email' => 'required|email',
        'phone' => 'nullable',
        'address' => 'nullable',
        'company_name' => 'nullable',
    ]);

    $registration = RegistrationRequest::create([
        'role' => $request->role,
        'username' => $request->username,
        'email' => $request->email,
        'phone' => $request->phone,
        'address' => $request->address,
        'company_name' => $request->company_name,
        'status' => 'pending'
    ]);

    Mail::raw(
        "New Registration Request:\n\n" .
        "Role: {$registration->role}\n" .
        "Username: {$registration->username}\n" .
        "Email: {$registration->email}\n",
        function ($message) {
            $message->to('gtisrvc@gmail.com')
                    ->subject('New SaaS Registration Request');
        }
    );

    return back()->with(
        'success',
        'Registration submitted successfully. Waiting for approval.'
    );
}
    public function ownerDashboard()
{
    return view('owners.dashboard', [
        'totalCompanies' => Company::count(),
        'activeCompanies' => Company::where('status', 'active')->count(),
        'pendingCompanies' => Company::where('status', 'pending')->count(),
        'companies' => Company::latest()->get(),
    ]);
}
}

