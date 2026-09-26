<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('DEMO_USER_EMAIL', 'demo@coreflow.test');
        $password = env('DEMO_USER_PASSWORD');
        if (! $password || strlen($password) < 12) {
            throw new \RuntimeException('DEMO_USER_PASSWORD must contain at least 12 characters.');
        }

        $company = Company::withTrashed()->updateOrCreate(['email' => 'hello@northstar-demo.test'], [
            'name' => 'Northstar Demo', 'db_name' => 'shared_northstar_demo', 'phone' => '+1 555 0100',
            'address' => '100 Portfolio Avenue', 'description' => 'Fictional workspace for the public CoreFlow CRM demonstration.',
            'status' => 1, 'plan' => 'growth', 'subscription_status' => 'active', 'trial_ends_at' => null,
            'paid_until' => now()->addYear(), 'cancel_at_period_end' => false, 'ai_enabled' => false, 'deleted_at' => null,
        ]);
        $user = User::updateOrCreate(['email' => strtolower($email)], [
            'name' => 'Demo Workspace Owner', 'password' => Hash::make($password), 'company_id' => $company->id,
            'status' => 'active', 'email_verified_at' => now(),
        ]);
        $user->syncRoles([Role::findOrCreate('Company Admin', 'web')]);
        CompanyOwner::updateOrCreate(['user_id' => $user->id], [
            'company_id' => $company->id, 'name' => $user->name, 'email' => $user->email,
            'phone' => '+1 555 0100', 'address' => '100 Portfolio Avenue',
        ]);

        $contact = Contact::updateOrCreate(['company_id' => $company->id, 'email' => 'maya@atlas-demo.test'], [
            'name' => 'Maya Chen', 'phone' => '+1 555 0111', 'organization' => 'Atlas Studio',
            'status' => 'qualified', 'notes' => 'Fictional prospect interested in the Growth plan.',
        ]);
        Contact::updateOrCreate(['company_id' => $company->id, 'email' => 'noah@harbor-demo.test'], [
            'name' => 'Noah Williams', 'phone' => '+1 555 0122', 'organization' => 'Harbor Works',
            'status' => 'customer', 'notes' => 'Fictional customer used for the portfolio demonstration.',
        ]);
        Deal::updateOrCreate(['company_id' => $company->id, 'title' => 'Atlas CRM rollout'], [
            'contact_id' => $contact->id, 'assigned_to' => $user->id, 'value' => 12500,
            'stage' => 'proposal', 'expected_close' => now()->addWeeks(3)->toDateString(),
            'notes' => 'Review the fictional implementation proposal.',
        ]);
        CrmTask::updateOrCreate(['company_id' => $company->id, 'title' => 'Follow up with Atlas Studio'], [
            'assigned_to' => $user->id, 'contact_id' => $contact->id, 'notes' => 'Confirm the fictional onboarding timeline.',
            'due_date' => now()->addDays(2)->toDateString(), 'status' => 'open', 'reminded_at' => null,
        ]);
        Invoice::updateOrCreate(['number' => 'DEMO-INV-001'], [
            'company_id' => $company->id, 'plan' => 'growth', 'amount_cents' => 4900, 'currency' => 'USD',
            'status' => 'paid', 'due_date' => now()->subMonth()->toDateString(), 'paid_at' => now()->subMonth(),
            'period_end' => now()->addYear()->toDateString(), 'payment_reference' => 'DEMO-PAYMENT-001',
        ]);
    }
}
