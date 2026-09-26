<?php

namespace App\Services;

use App\Helpers\ActivityLogger;
use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class Onboarding
{
    public function approve(int $id): User
    {
        return DB::transaction(function () use ($id) {
            $registration = RegistrationRequest::lockForUpdate()->findOrFail($id);
            if ($registration->status === 'approved') {
                return User::where('email', $registration->email)->firstOrFail();
            }
            if ($registration->status !== 'pending' || User::where('email', $registration->email)->exists()) {
                throw ValidationException::withMessages(['registration' => 'This request cannot be approved.']);
            }
            $companyName = $registration->company_name ?: $registration->username.' workspace';
            if (Company::withTrashed()->where('name', $companyName)->exists()) {
                throw ValidationException::withMessages(['company_name' => 'A workspace with this company name already exists.']);
            }
            try {
                $company = Company::create(['name' => $companyName,
                    'db_name' => 'shared_'.Str::uuid(), 'email' => $registration->email,
                    'phone' => $registration->phone ?? '', 'address' => $registration->address ?? '', 'description' => '',
                    'status' => 1, 'trial_ends_at' => now()->addDays(14)]);
            } catch (QueryException $exception) {
                if (in_array($exception->getCode(), ['23000', '23505'], true)) {
                    throw ValidationException::withMessages(['company_name' => 'A workspace with this company name already exists.']);
                }

                throw $exception;
            }
            $user = User::create(['name' => $registration->username, 'email' => $registration->email,
                'password' => Str::random(64), 'company_id' => $company->id, 'status' => 'active']);
            $user->assignRole(Role::findOrCreate('Company Admin', 'web'));
            CompanyOwner::create(['name' => $user->name, 'email' => $user->email, 'phone' => $registration->phone ?? '',
                'address' => $registration->address, 'company_id' => $company->id, 'user_id' => $user->id]);
            $registration->update(['status' => 'approved']);
            ActivityLogger::log('Workspace approved', 'Registration #'.$id, $company->id);

            return $user;
        });
    }
}
