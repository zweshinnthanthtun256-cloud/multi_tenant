@extends('layouts.app')
@section('title', 'Billing administration')
@section('content')
<div class="page-heading"><div><div class="eyebrow">PLATFORM REVENUE</div><h1>Invoices & payments</h1><p>Issue invoices and verify manual transfers. No paid gateway integration is required.</p></div></div>
<details class="panel mb-4"><summary class="fw-semibold">Issue an invoice</summary>
    <form method="POST" action="{{ route('admin.billing.store') }}" class="mt-4">@csrf<div class="form-grid">
        <div class="field"><label for="company_id" class="form-label">Company</label><select name="company_id" id="company_id" class="form-select" required>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}</option>@endforeach</select></div>
        <div class="field"><label for="plan" class="form-label">Plan</label><select name="plan" id="plan" class="form-select">@foreach(config('saas.plans') as $key => $plan)<option value="{{ $key }}">{{ $plan['name'] }}</option>@endforeach</select></div>
        @foreach(['amount_cents' => 'Amount in minor units (e.g. 4900 = 49.00)', 'currency' => 'Currency (3 letters)', 'due_date' => 'Payment due date', 'period_end' => 'Access period end'] as $name => $label)
        <div class="field"><label class="form-label" for="{{ $name }}">{{ $label }}</label><input class="form-control" id="{{ $name }}" name="{{ $name }}" required type="{{ in_array($name, ['due_date', 'period_end']) ? 'date' : ($name === 'amount_cents' ? 'number' : 'text') }}" value="{{ old($name, $name === 'currency' ? config('saas.currency') : '') }}"></div>
        @endforeach
    </div><button class="btn btn-primary">Issue invoice</button></form>
</details>
<section class="panel"><div class="table-responsive"><table class="table"><thead><tr><th>Invoice / company</th><th>Amount</th><th>Plan / through</th><th>Status</th><th>Verification</th></tr></thead><tbody>
@forelse($invoices as $invoice)
<tr>
    <td><strong>{{ $invoice->number }}</strong><small>{{ $invoice->company?->name ?? 'Archived company' }}</small></td>
    <td>{{ $invoice->currency }} {{ number_format($invoice->amount_cents / 100, 2) }}</td>
    <td>{{ ucfirst($invoice->plan) }}<small>{{ $invoice->period_end->format('M j, Y') }}</small></td>
    <td><span class="badge">{{ str_replace('_', ' ', $invoice->status) }}</span></td>
    <td>
        @if($invoice->status === 'open')
        <form class="d-flex gap-2" method="POST" action="{{ route('admin.billing.paid', $invoice) }}" data-confirm="Have you independently verified this payment?">@csrf
            <input name="payment_reference" class="form-control" required maxlength="120" placeholder="Verified reference" aria-label="Payment reference"><button class="btn btn-sm btn-primary">Record paid</button>
        </form>
        @elseif($invoice->status === 'pending_verification')
        <div><small>Customer reference: {{ $invoice->payment_reference }}</small><div class="d-flex gap-2 mt-2">
            <form method="POST" action="{{ route('admin.billing.paid', $invoice) }}" data-confirm="Have you independently verified this payment?">@csrf<button class="btn btn-sm btn-primary">Verify & activate</button></form>
            <form method="POST" action="{{ route('admin.billing.reject', $invoice) }}" data-confirm="Reject this payment reference?">@csrf<button class="btn btn-sm btn-outline-secondary">Reject</button></form>
        </div></div>
        @else
        {{ $invoice->payment_reference }}
        @endif
    </td>
</tr>
@empty
<tr><td colspan="5"><div class="empty">No invoices issued yet.</div></td></tr>
@endforelse
</tbody></table></div>{{ $invoices->links() }}</section>
@endsection
