<?php

namespace App\Support;

use App\Models\Company;
use App\Models\EmployeeInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Workspace
{
    public static function id(): int
    {
        $user = auth()->user();
        abort_unless($user && $user->canAccessWorkspace() && $user->company_id, 403);

        return (int) $user->company_id;
    }

    public static function authorize(Model $record): void
    {
        abort_unless((int) $record->company_id === static::id(), 404);
    }

    public static function manager(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Company Admin', 'Manager']), 403);
    }

    // Call inside a transaction; the company lock serializes seat reservations.
    public static function reserveSeat(Company $company): void
    {
        $lockedCompany = Company::whereKey($company->id)->lockForUpdate()->firstOrFail();
        $used = User::where('company_id', $lockedCompany->id)->count();
        $pending = EmployeeInvitation::where('company_id', $lockedCompany->id)
            ->where('status', 'pending')->where('expires_at', '>', now())->count();
        $limit = (int) config('saas.plans.'.$lockedCompany->plan.'.seats', 5);
        Validator::make(['seats' => $used + $pending], ['seats' => 'integer|max:'.($limit - 1)],
            ['seats.max' => 'Your plan seat limit has been reached.'])->validate();
    }
}
