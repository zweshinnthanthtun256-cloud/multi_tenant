@extends('layouts.app')
@section('title', 'Plan & billing')
@section('content')
<div class="page-heading">
    <div><div class="eyebrow">ROOM TO GROW</div><h1>Your workspace plan</h1><p>Transparent usage and verified manual payments.</p></div>
    <span class="badge">{{ str_replace('_', ' ', $company->subscription_status) }}</span>
</div>
<div class="metrics">
    <div class="metric highlight"><div class="metric-label">Current plan</div><div class="metric-value">{{ config('saas.plans.'.$company->plan.'.name') }}</div><div class="metric-note">Manual payment has no gateway fee</div></div>
    <div class="metric"><div class="metric-label">Seats reserved</div><div class="metric-value">{{ $seats }} / {{ config('saas.plans.'.$company->plan.'.seats') }}</div><div class="metric-note">Members + pending invitations</div></div>
    <div class="metric"><div class="metric-label">Access until</div><div class="metric-value" style="font-size:22px">{{ ($company->paid_until ?? $company->trial_ends_at)?->format('M j, Y') ?? 'Renewal needed' }}</div><div class="metric-note">Billing remains accessible after expiry</div></div>
    <div class="metric"><div class="metric-label">AI requests / month</div><div class="metric-value">{{ config('saas.plans.'.$company->plan.'.ai_requests') }}</div><div class="metric-note">Available when AI is enabled</div></div>
</div>
<section class="panel">
    <div class="panel-header">
        <h2>Invoice history</h2>
        @role('Company Admin')
        <form action="{{ route('billing.cancel') }}" method="POST" data-confirm="Cancel renewal at the end of the current access period?">@csrf
            <button class="btn btn-outline-secondary" @disabled($company->cancel_at_period_end)>{{ $company->cancel_at_period_end ? 'Renewal cancelled' : 'Cancel renewal' }}</button>
        </form>
        @endrole
    </div>
    <p class="muted">{{ config('saas.payment_instructions') }} CoreFlow adds no gateway fee; your bank may apply its own transfer fee.</p>
    <div class="table-responsive"><table class="table"><thead><tr><th>Invoice</th><th>Plan</th><th>Amount</th><th>Due</th><th>Status / action</th></tr></thead><tbody>
    @forelse($invoices as $invoice)
        <tr>
            <td>{{ $invoice->number }}</td><td>{{ ucfirst($invoice->plan) }}</td>
            <td>{{ $invoice->currency }} {{ number_format($invoice->amount_cents / 100, 2) }}</td><td>{{ $invoice->due_date->format('M j, Y') }}</td>
            <td>
                <span class="badge">{{ str_replace('_', ' ', $invoice->status) }}</span>
                @role('Company Admin')
                    @if($invoice->status === 'open')
                    <form class="d-flex gap-2 mt-2" method="POST" action="{{ route('billing.submit-payment', $invoice) }}">@csrf
                        <input name="payment_reference" class="form-control" required maxlength="120" placeholder="Bank or transfer reference" aria-label="Payment reference">
                        <button class="btn btn-sm btn-primary">Submit</button>
                    </form>
                    @elseif($invoice->status === 'pending_verification')
                    <small>Reference {{ $invoice->payment_reference }} is awaiting administrator verification.</small>
                    @endif
                @endrole
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="empty">Your invoices will appear here when issued.</div></td></tr>
    @endforelse
    </tbody></table></div>
    {{ $invoices->links() }}
</section>
@endsection
