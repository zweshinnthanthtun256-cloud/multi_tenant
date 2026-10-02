<?php

namespace Tests\Feature;

use App\Jobs\SendTaskReminder;
use App\Models\Company;
use App\Models\CompanyOwner;
use App\Models\Contact;
use App\Models\CrmTask;
use App\Models\Deal;
use App\Models\Employee;
use App\Models\EmployeeInvitation;
use App\Models\Invoice;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Notifications\TaskReminder;
use App\Notifications\WorkspaceInvitation;
use App\Services\Onboarding;
use App\Support\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Mail::fake();
        foreach (['Super Admin', 'Company Admin', 'Manager', 'Staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function company(string $name = 'Acme'): Company
    {
        return Company::create(['name' => $name, 'email' => strtolower($name).'@example.test', 'db_name' => 'shared_'.$name,
            'phone' => '', 'address' => '', 'description' => '', 'status' => 1, 'trial_ends_at' => now()->addDays(14)]);
    }

    private function user(?Company $company, string $role = 'Company Admin'): User
    {
        $u = User::factory()->create(['company_id' => $company?->id, 'status' => 'active']);
        $u->assignRole($role);

        return $u;
    }

    private function employee(Company $c): Employee
    {
        $u = $this->user($c, 'Staff');

        return Employee::create(['company_id' => $c->id, 'user_id' => $u->id, 'employee_code' => 'EMP-'.$u->id, 'joining_date' => today(), 'status' => 'active']);
    }

    public function test_public_registration_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Request workspace')
            ->assertSee('Company name');
    }

    public function test_company_admin_cannot_read_update_or_delete_another_company_employee(): void
    {
        $a = $this->company();
        $b = $this->company('Other');
        $u = $this->user($a);
        $e = $this->employee($b);
        $this->actingAs($u)->get(route('company_admin.employees.index'))->assertOk()->assertDontSee($e->user->email);
        $this->get(route('company_admin.employees.edit', $e))->assertNotFound();
        $this->put(route('company_admin.employees.update', $e), ['name' => 'Stolen'])->assertNotFound();
        $this->delete(route('company_admin.employees.destroy', $e))->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $e->user_id]);
    }

    public function test_created_employee_inherits_authenticated_company(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $this->actingAs($u)->post(route('company_admin.employees.store'), [
            'name' => 'New colleague', 'email' => 'colleague@example.test', 'password' => 'SecurePass12345', 'password_confirmation' => 'SecurePass12345', 'role' => 'Staff',
        ])->assertRedirect(route('company_admin.employees.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'colleague@example.test', 'company_id' => $c->id]);
        $this->assertDatabaseHas('employees', ['company_id' => $c->id, 'status' => 'active']);
    }

    public function test_all_workspace_screens_render_and_cross_tenant_crm_requests_are_denied(): void
    {
        $a = $this->company();
        $b = $this->company('Other');
        $u = $this->user($a);
        $contact = Contact::create(['company_id' => $b->id, 'name' => 'Private contact', 'email' => 'private@example.test', 'status' => 'lead']);
        $deal = Deal::create(['company_id' => $b->id, 'title' => 'Private deal']);
        $task = CrmTask::create(['company_id' => $b->id, 'title' => 'Private task', 'due_date' => today()]);
        $this->actingAs($u);
        foreach (['crm.dashboard', 'crm.contacts', 'crm.contacts.create', 'crm.deals', 'crm.deals.create', 'crm.tasks', 'crm.ai', 'billing.index', 'workspace.settings', 'company_admin.employees.index', 'company_admin.employees.create', 'company_admin.invitations.create'] as $route) {
            $this->get(route($route))->assertOk()->assertDontSee('Private contact')->assertDontSee('private@example.test');
        }
        $this->get(route('crm.contacts.edit', $contact))->assertNotFound();
        $this->put(route('crm.contacts.update', $contact), [])->assertNotFound();
        $this->delete(route('crm.contacts.destroy', $contact))->assertNotFound();
        $this->get(route('crm.deals.edit', $deal))->assertNotFound();
        $this->put(route('crm.deals.update', $deal), [])->assertNotFound();
        $this->delete(route('crm.deals.destroy', $deal))->assertNotFound();
        $this->patch(route('crm.tasks.update', $task), ['status' => 'completed'])->assertNotFound();
        $this->post(route('crm.ai.generate'), ['contact_id' => $contact->id, 'kind' => 'summary'])->assertNotFound();
        $this->get(route('crm.contacts.export'))->assertOk()->assertDontSee('private@example.test');
        $this->get(route('workspace.export'))->assertOk();
    }

    public function test_crm_rejects_foreign_contact_and_assignee(): void
    {
        $a = $this->company();
        $b = $this->company('Other');
        $u = $this->user($a);
        $other = $this->user($b);
        $contact = Contact::create(['company_id' => $b->id, 'name' => 'Private', 'status' => 'lead']);
        $this->actingAs($u)->post(route('crm.deals.store'), ['title' => 'Test', 'value' => 20, 'stage' => 'new', 'contact_id' => $contact->id, 'assigned_to' => $other->id])
            ->assertSessionHasErrors(['contact_id', 'assigned_to']);
        $this->assertDatabaseCount('deals', 0);
    }

    public function test_registration_approval_is_idempotent_and_password_is_not_predictable(): void
    {
        $r = RegistrationRequest::create(['role' => 'Super Admin', 'username' => 'Owner', 'email' => 'owner@example.test', 'company_name' => 'NewCo', 'status' => 'pending']);
        $u = app(Onboarding::class)->approve($r->id);
        $again = app(Onboarding::class)->approve($r->id);
        $this->assertEquals($u->id, $again->id);
        $this->assertDatabaseCount('companies', 1);
        $this->assertTrue($u->hasRole('Company Admin'));
        $this->assertFalse($u->hasRole('Super Admin'));
        $this->assertFalse(Hash::check('password123', $u->password));
    }

    public function test_duplicate_company_name_is_rejected_during_registration_and_approval(): void
    {
        $this->company('ExistingCo');

        $this->post(route('register.submit'), [
            'username' => 'Owner', 'email' => 'new-owner@example.test', 'company_name' => 'ExistingCo',
        ])->assertSessionHasErrors('company_name');

        $registration = RegistrationRequest::create([
            'role' => 'Company Admin', 'username' => 'Owner', 'email' => 'other-owner@example.test',
            'company_name' => 'ExistingCo', 'status' => 'pending',
        ]);
        try {
            app(Onboarding::class)->approve($registration->id);
            $this->fail('Approval should reject a duplicate company name.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('company_name', $exception->errors());
        }
        $this->assertSame('pending', $registration->fresh()->status);
    }

    public function test_invitation_is_hashed_single_use_and_creates_correct_membership(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $this->actingAs($u)->post(route('company_admin.invitations.store'), ['email' => 'invite@example.test', 'role' => 'Staff'])->assertSessionHasNoErrors();
        $token = null;
        Notification::assertSentOnDemand(WorkspaceInvitation::class, function ($notification) use (&$token) {
            $token = basename($notification->url);

            return true;
        });
        $i = EmployeeInvitation::firstOrFail();
        $this->assertSame(hash('sha256', $token), $i->token);
        auth()->logout();
        $this->post(route('invitations.complete', $token), ['name' => 'Invited member', 'password' => 'SecurePass12345', 'password_confirmation' => 'SecurePass12345'])->assertRedirect(route('admin.login'));
        $this->assertDatabaseHas('users', ['email' => 'invite@example.test', 'company_id' => $c->id]);
        $this->get(route('invitations.accept', $token))->assertNotFound();
    }

    public function test_suspended_user_and_company_are_denied_on_existing_sessions(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $c->update(['status' => 0]);
        $this->actingAs($u)->get(route('crm.contacts'))->assertRedirect(route('admin.login'));
        $c->update(['status' => 1]);
        $u->update(['status' => 'suspended']);
        $this->actingAs($u->fresh())->get(route('crm.contacts'))->assertRedirect(route('admin.login'));
    }

    public function test_expired_plan_preserves_billing_and_export_access(): void
    {
        $c = $this->company();
        $c->update(['trial_ends_at' => now()->subDay()]);
        $u = $this->user($c);
        $this->actingAs($u)->get(route('crm.contacts'))->assertRedirect(route('billing.index'));
        $this->get(route('billing.index'))->assertOk();
        $this->get(route('workspace.export'))->assertOk();
    }

    public function test_paid_invoice_is_idempotent_and_tenant_cannot_mark_paid(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $admin = $this->user(null, 'Super Admin');
        $i = Invoice::create(['company_id' => $c->id, 'number' => 'INV-TEST', 'plan' => 'growth', 'amount_cents' => 4900, 'currency' => 'USD', 'due_date' => today(), 'period_end' => today()->addMonth()]);
        $this->actingAs($u)->post(route('admin.billing.paid', $i), ['payment_reference' => 'BANK-1'])->assertForbidden();
        $this->actingAs($admin)->post(route('admin.billing.paid', $i), ['payment_reference' => 'BANK-1'])->assertRedirect()->assertSessionHasNoErrors();
        $until = $c->fresh()->paid_until->toISOString();
        $this->post(route('admin.billing.paid', $i), ['payment_reference' => 'BANK-1'])->assertRedirect();
        $this->assertSame($until, $c->fresh()->paid_until->toISOString());
        $this->assertSame('growth', $c->fresh()->plan);
    }

    public function test_paying_an_older_invoice_does_not_replace_newer_plan_entitlements(): void
    {
        $company = $this->company();
        $admin = $this->user(null, 'Super Admin');
        $older = Invoice::create(['company_id' => $company->id, 'number' => 'INV-OLDER', 'plan' => 'starter',
            'amount_cents' => 1900, 'currency' => 'USD', 'due_date' => today(), 'period_end' => today()->addMonth()]);
        $newer = Invoice::create(['company_id' => $company->id, 'number' => 'INV-NEWER', 'plan' => 'growth',
            'amount_cents' => 4900, 'currency' => 'USD', 'due_date' => today(), 'period_end' => today()->addMonths(2)]);

        $this->actingAs($admin)->post(route('admin.billing.paid', $newer), ['payment_reference' => 'BANK-NEW'])->assertSessionHasNoErrors();
        $this->post(route('admin.billing.paid', $older), ['payment_reference' => 'BANK-OLD'])->assertSessionHasNoErrors();

        $company->refresh();
        $this->assertSame('growth', $company->plan);
        $this->assertSame($newer->period_end->endOfDay()->format('Y-m-d H:i:s'), $company->paid_until->format('Y-m-d H:i:s'));
    }

    public function test_company_can_submit_a_manual_payment_reference_for_admin_verification(): void
    {
        $company = $this->company();
        $owner = $this->user($company);
        $invoice = Invoice::create(['company_id' => $company->id, 'number' => 'INV-MANUAL', 'plan' => 'growth',
            'amount_cents' => 4900, 'currency' => 'USD', 'due_date' => today(), 'period_end' => today()->addMonth()]);

        $this->actingAs($owner)->post(route('billing.submit-payment', $invoice), ['payment_reference' => 'TRANSFER-100'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending_verification', 'payment_reference' => 'TRANSFER-100']);
        $this->get(route('billing.index'))->assertOk()->assertSee('TRANSFER-100');

        $otherOwner = $this->user($this->company('Other'));
        $this->actingAs($otherOwner)->post(route('billing.submit-payment', $invoice), ['payment_reference' => 'TRANSFER-OTHER'])->assertNotFound();

        $admin = $this->user(null, 'Super Admin');
        $this->actingAs($admin)->get(route('admin.billing'))->assertOk()->assertSee('TRANSFER-100');
        $this->actingAs($admin)->post(route('admin.billing.paid', $invoice), [])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid', 'payment_reference' => 'TRANSFER-100']);
        $this->assertSame('growth', $company->fresh()->plan);
    }

    public function test_ai_scopes_context_and_enforces_monthly_limit_without_sending_messages(): void
    {
        config(['saas.ai_enabled' => true, 'saas.openai_key' => 'test-key', 'saas.openai_model' => 'test-model', 'saas.plans.starter.ai_requests' => 1]);
        Http::preventStrayRequests();
        Http::fake(['api.openai.com/*' => Http::response(['output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Review this draft.']]]], 'usage' => ['total_tokens' => 20]])]);
        $c = $this->company();
        $c->update(['ai_enabled' => true]);
        $u = $this->user($c);
        $contact = Contact::create(['company_id' => $c->id, 'name' => 'A person', 'email' => 'secret@example.test', 'organization' => 'Acme', 'notes' => 'Call next week', 'status' => 'lead']);
        $this->actingAs($u)->post(route('crm.ai.generate'), ['contact_id' => $contact->id, 'kind' => 'follow_up'])->assertSessionHasNoErrors();
        Http::assertSent(fn ($req) => $req['store'] === false && ! str_contains($req['input'], 'secret@example.test') && ! isset($req['tools']));
        $this->assertDatabaseHas('ai_generations', ['company_id' => $c->id, 'status' => 'completed']);
        $this->post(route('crm.ai.generate'), ['contact_id' => $contact->id, 'kind' => 'summary'])->assertStatus(429);
        Http::assertSentCount(1);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->user(null, 'Super Admin'));
        foreach (['admin.dashboard', 'admin.companies.index', 'admin.companies.create', 'admin.owners.index', 'admin.owners.create', 'admin.registrations.index', 'admin.roles.index', 'admin.billing', 'admin.activity_logs.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_staff_cannot_manage_team_or_export_contacts(): void
    {
        $u = $this->user($this->company(), 'Staff');
        $this->actingAs($u)->get(route('company_admin.employees.index'))->assertForbidden();
        $this->get(route('crm.contacts.export'))->assertForbidden();
        $this->get(route('crm.dashboard'))->assertOk();
    }

    public function test_crm_happy_path_and_csv_validation(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $this->actingAs($u)->post(route('crm.contacts.store'), ['name' => 'Alice', 'email' => 'alice@example.test', 'status' => 'lead'])->assertSessionHasNoErrors();
        $contact = Contact::firstOrFail();
        $this->assertSame($c->id, $contact->company_id);
        $this->post(route('crm.deals.store'), ['title' => 'First sale', 'value' => '1200.50', 'stage' => 'proposal', 'contact_id' => $contact->id, 'assigned_to' => $u->id])->assertSessionHasNoErrors();
        $this->post(route('crm.tasks.store'), ['title' => 'Call Alice', 'due_date' => today()->toDateString(), 'contact_id' => $contact->id, 'assigned_to' => $u->id])->assertSessionHasNoErrors();
        $this->patch(route('crm.tasks.update', CrmTask::first()), ['status' => 'completed'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('crm_tasks', ['status' => 'completed', 'company_id' => $c->id]);
        $csv = UploadedFile::fake()->createWithContent('contacts.csv', "name,email,phone,organization,status\nBob,bob@example.test,,Example,lead\nBad,invalid-email,,Example,lead\n");
        $this->post(route('crm.contacts.import'), ['file' => $csv])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('contacts', 1);
        $csv = UploadedFile::fake()->createWithContent('contacts.csv', "name,email,phone,organization,status\nBob,bob@example.test,,Example,lead\n");
        $this->post(route('crm.contacts.import'), ['file' => $csv])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('contacts', 2);
        Contact::create(['company_id' => $c->id, 'name' => '=HYPERLINK(1)', 'status' => 'lead']);
        $csv = $this->get(route('crm.contacts.export'))->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK(1)", $csv);
    }

    public function test_seat_limits_and_expired_invites(): void
    {
        config(['saas.plans.starter.seats' => 1]);
        $c = $this->company();
        $u = $this->user($c);
        $this->actingAs($u)->post(route('company_admin.invitations.store'), ['email' => 'new@example.test', 'role' => 'Staff'])->assertSessionHasErrors('seats');
        $this->assertDatabaseCount('employee_invitations', 0);
        EmployeeInvitation::create(['company_id' => $c->id, 'email' => 'new@example.test', 'role' => 'Staff', 'token' => hash('sha256', 'expired-token'), 'expires_at' => now()->subDay(), 'status' => 'pending']);
        auth()->logout();
        $this->get(route('invitations.accept', 'expired-token'))->assertNotFound();
    }

    public function test_seat_reservation_uses_the_locked_current_plan(): void
    {
        config(['saas.plans.starter.seats' => 5, 'saas.plans.growth.seats' => 20]);
        $staleCompany = $this->company();
        $staleCompany->update(['plan' => 'growth']);
        for ($i = 0; $i < 5; $i++) {
            $this->user($staleCompany, 'Staff');
        }
        Company::whereKey($staleCompany->id)->update(['plan' => 'starter']);

        $this->expectException(ValidationException::class);
        Workspace::reserveSeat($staleCompany);
    }

    public function test_archiving_is_reversible_and_never_drops_company_data(): void
    {
        $c = $this->company();
        $admin = $this->user(null, 'Super Admin');
        Contact::create(['company_id' => $c->id, 'name' => 'Retained', 'status' => 'customer']);
        $this->actingAs($admin)->delete(route('admin.companies.destroy', $c))->assertRedirect();
        $this->assertSoftDeleted('companies', ['id' => $c->id]);
        $this->assertDatabaseHas('contacts', ['company_id' => $c->id, 'name' => 'Retained']);
        $this->post(route('admin.companies.restore', $c->id))->assertRedirect();
        $this->assertSame(1, (int) $c->fresh()->status);
        $this->assertFalse($c->fresh()->trashed());
    }

    public function test_password_reset_requires_valid_token_and_unverified_accounts_are_gated(): void
    {
        $c = $this->company();
        $u = $this->user($c);
        $token = Password::createToken($u);
        $this->post(route('password.update'), ['email' => $u->email, 'token' => 'wrong', 'password' => 'AnotherSecure123', 'password_confirmation' => 'AnotherSecure123'])->assertSessionHasErrors('email');
        $this->post(route('password.update'), ['email' => $u->email, 'token' => $token, 'password' => 'AnotherSecure123', 'password_confirmation' => 'AnotherSecure123'])->assertRedirect(route('admin.login'));
        $this->assertTrue(Hash::check('AnotherSecure123', $u->fresh()->password));
        $this->post(route('password.update'), ['email' => $u->email, 'token' => $token, 'password' => 'AnotherSecure123', 'password_confirmation' => 'AnotherSecure123'])->assertSessionHasErrors('email');
        $u->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($u)->get(route('crm.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_failed_login_has_feedback_and_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), ['email' => 'absent@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post(route('login.submit'), ['email' => 'absent@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_owner_removal_revokes_central_account_and_protects_last_owner(): void
    {
        $c = $this->company();
        $admin = $this->user(null, 'Super Admin');
        $first = $this->user($c);
        $second = $this->user($c);
        $a = CompanyOwner::create(['company_id' => $c->id, 'user_id' => $first->id, 'name' => $first->name, 'email' => $first->email, 'phone' => '']);
        $b = CompanyOwner::create(['company_id' => $c->id, 'user_id' => $second->id, 'name' => $second->name, 'email' => $second->email, 'phone' => '']);
        $this->actingAs($admin)->delete(route('admin.owners.destroy', $a))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $first->id]);
        $this->delete(route('admin.owners.destroy', $b))->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $second->id]);
    }

    public function test_builtin_roles_cannot_be_renamed_or_deleted(): void
    {
        $this->actingAs($this->user(null, 'Super Admin'));
        $role = Role::findByName('Company Admin', 'web');
        $this->put(route('admin.roles.update', $role), ['name' => 'Anything'])->assertStatus(422);
        $this->delete(route('admin.roles.destroy', $role))->assertStatus(422);
    }

    public function test_reminders_recheck_membership_before_delivery(): void
    {
        $c = $this->company();
        $u = $this->user($c, 'Staff');
        $task = CrmTask::create(['company_id' => $c->id, 'title' => 'Due', 'assigned_to' => $u->id, 'due_date' => today()]);
        $notification = new TaskReminder($task->id);
        $this->assertTrue($notification->shouldSend($u, 'mail'));
        $u->update(['status' => 'suspended']);
        $this->assertFalse($notification->shouldSend($u, 'mail'));
    }

    public function test_failed_reminder_job_releases_the_task_for_retry(): void
    {
        Queue::fake();
        $company = $this->company();
        $user = $this->user($company, 'Staff');
        $task = CrmTask::create(['company_id' => $company->id, 'title' => 'Due', 'assigned_to' => $user->id, 'due_date' => today()]);

        $this->artisan('crm:reminders')->assertSuccessful();
        Queue::assertPushed(SendTaskReminder::class, fn ($job) => $job->taskId === $task->id);
        $this->assertNotNull($task->fresh()->reminded_at);

        (new SendTaskReminder($task->id))->failed(new \RuntimeException('Mail provider unavailable'));
        $this->assertNull($task->fresh()->reminded_at);
    }
}
