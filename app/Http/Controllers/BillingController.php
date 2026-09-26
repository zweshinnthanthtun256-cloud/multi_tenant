<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\Company;
use App\Models\EmployeeInvitation;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index()
    {
        $company = Company::findOrFail(Workspace::id());
        $invoices = Invoice::forCompany($company->id)->when(! auth()->user()->hasRole('Company Admin'), fn ($q) => $q->whereRaw('1 = 0'))->latest()->paginate(15);
        $seats = User::where('company_id', $company->id)->count() + EmployeeInvitation::where('company_id', $company->id)->where('status', 'pending')->where('expires_at', '>', now())->count();

        return view('billing.index', compact('company', 'invoices', 'seats'));
    }

    public function cancel()
    {
        abort_unless(auth()->user()->hasRole('Company Admin'), 403);
        $company = Company::findOrFail(Workspace::id());
        $company->update(['cancel_at_period_end' => true]);
        ActivityLogger::log('Renewal cancelled', 'Access retained until current period ends', $company->id);

        return back()->with('success', 'Renewal cancelled. Access continues until your paid period ends.');
    }

    public function admin()
    {
        $invoices = Invoice::with('company')->latest()->paginate(20);
        $companies = Company::orderBy('name')->get();

        return view('billing.admin', compact('invoices', 'companies'));
    }

    public function store(Request $r)
    {
        $data = $r->validate(['company_id' => 'required|exists:companies,id', 'plan' => ['required', Rule::in(array_keys(config('saas.plans')))],
            'amount_cents' => 'required|integer|min:1|max:999999999', 'currency' => 'required|string|size:3|alpha',
            'due_date' => 'required|date', 'period_end' => 'required|date|after_or_equal:due_date']);
        abort_unless(Company::find($data['company_id']), 422);
        $data['currency'] = strtoupper($data['currency']);
        Invoice::create($data + ['number' => 'INV-'.strtoupper(Str::random(12))]);

        return back()->with('success', 'Invoice issued. Confirm payment only after verifying funds.');
    }

    public function submitPayment(Request $r, Invoice $invoice)
    {
        abort_unless(auth()->user()->hasRole('Company Admin'), 403);
        Workspace::authorize($invoice);
        abort_unless($invoice->status === 'open', 422, 'Only open invoices can receive a payment reference.');
        $data = $r->validate(['payment_reference' => [
            'required', 'string', 'max:120',
            Rule::unique('invoices', 'payment_reference')->ignore($invoice->id),
        ]]);
        $invoice->update(['status' => 'pending_verification', 'payment_reference' => trim($data['payment_reference'])]);
        ActivityLogger::log('Payment submitted', 'Invoice '.$invoice->number.' awaiting verification', $invoice->company_id);

        return back()->with('success', 'Payment reference submitted. Access will update after verification.');
    }

    public function rejectPayment(Invoice $invoice)
    {
        abort_unless($invoice->status === 'pending_verification', 422, 'Only pending payments can be rejected.');
        $invoice->update(['status' => 'open', 'payment_reference' => null]);
        ActivityLogger::log('Payment rejected', 'Invoice '.$invoice->number.' returned for correction', $invoice->company_id);

        return back()->with('success', 'Payment reference rejected. The customer can submit a corrected reference.');
    }

    public function paid(Request $r, Invoice $invoice)
    {
        $data = $r->validate(['payment_reference' => 'nullable|string|max:120']);
        DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->status === 'paid') {
                return;
            }
            $company = Company::whereKey($invoice->company_id)->lockForUpdate()->firstOrFail();
            $paymentReference = trim($data['payment_reference'] ?? $invoice->payment_reference ?? '');
            abort_if($paymentReference === '', 422, 'A verified payment reference is required.');
            abort_if($invoice->period_end->endOfDay()->isPast(), 422, 'Invoice service period has already ended.');
            abort_if(Invoice::where('payment_reference', $paymentReference)->whereKeyNot($invoice->id)->exists(), 422, 'Payment reference already recorded.');
            $invoice->update(['status' => 'paid', 'paid_at' => now(), 'payment_reference' => $paymentReference]);
            $entitlement = Invoice::where('company_id', $company->id)->where('status', 'paid')
                ->orderByDesc('period_end')->orderByDesc('id')->firstOrFail();
            $seats = User::where('company_id', $company->id)->count() + EmployeeInvitation::where('company_id', $company->id)->where('status', 'pending')->where('expires_at', '>', now())->count();
            abort_if($seats > config('saas.plans.'.$entitlement->plan.'.seats'), 422, 'Remove excess seats before changing this plan.');
            $until = $entitlement->period_end->copy()->endOfDay();
            $company->update(['plan' => $entitlement->plan, 'subscription_status' => 'active', 'cancel_at_period_end' => false,
                'paid_until' => $company->paid_until && $company->paid_until->gt($until) ? $company->paid_until : $until]);
            ActivityLogger::log('Invoice paid', 'Invoice '.$invoice->number, $company->id);
        });

        return back()->with('success', 'Payment recorded and workspace access updated.');
    }
}
