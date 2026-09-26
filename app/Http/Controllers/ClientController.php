<?php

namespace App\Http\Controllers;

use App\Models\RegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index()
    {
        return view('userpage');
    }

    public function register()
    {
        return view('register');
    }

    public function registerSubmit(Request $r)
    {
        $data = $r->validate(['username' => 'required|string|max:120', 'email' => 'required|email|max:255|unique:users,email',
            'company_name' => ['required', 'string', 'max:120', Rule::unique('companies', 'name')],
            'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500']);
        $data['email'] = strtolower($data['email']);
        RegistrationRequest::firstOrCreate(['email' => $data['email'], 'status' => 'pending'], $data + ['role' => 'Company Admin']);

        return back()->with('success', 'Your workspace request is ready for review. We will email your setup link after approval.');
    }
}
