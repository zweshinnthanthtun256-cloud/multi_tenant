<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscription
{
    public function handle(Request $r, Closure $next)
    {
        $company = $r->user()->company;
        $paid = $company?->paid_until?->isFuture();
        $trial = $company?->subscription_status === 'trial' && $company?->trial_ends_at?->isFuture();
        if (! $paid && ! $trial) {
            return redirect()->route('billing.index')->withErrors(['plan' => 'Your access period has ended. Contact your workspace owner to renew.']);
        }

        return $next($r);
    }
}
