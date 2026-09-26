# First paid customer launch review

Reviewed 2026-09-23 against the current working tree, including existing uncommitted work. Scope: application routes, controllers, models, migrations, views, dependencies and tests. This is a static code review with a frontend build check, not a deployed-system penetration test or infrastructure audit.

**Recommendation: do not onboard paying customers yet.** The application has company, owner, employee, role and activity-log administration, but tenant isolation and onboarding are incomplete. No implemented CRM contact/deal workflow, subscription lifecycle or AI workflow was found in the inspected application.

## Launch blockers

### 1. Critical: company administrators can access other companies' employees

Evidence: `routes/web.php` imports the shared `App\Http\Controllers\EmployeeController` and exposes its resource routes to both Super Admin and Company Admin. `app/Http/Controllers/EmployeeController.php:20` lists all employees; `:147`, `:170` and `:241` accept unscoped employee bindings for edit, update and delete. No ownership policy or global tenant scope was found. The separate CompanyAdmin controller is not the routed controller.

Impact: a company administrator can list other companies' employees and submit requests against their IDs to change account details/roles or delete accounts. Broken UI links do not protect those endpoints.

Required: derive company context from authenticated membership; enforce ownership in queries, route binding and policies; make platform-wide access explicit. Add two-company tests covering list/edit/update/delete and forged IDs. Company A must never read or mutate Company B data.

### 2. High: tenant database architecture is not connected to ordinary requests

Evidence: CompanyController creates a database and CompanyOwnerController copies credentials into it. However, the active controllers extend Controller, not TenantBaseController; tenant middleware is absent from active route groups. Models use the default connection. Tenant migrations contain users, roles and cache, but no employee tables. `config/multitenancy.php` references a package absent from composer.json.

Required decision: choose one coherent design before adding CRM tables. For this first launch, a shared database with enforced company ownership is the simpler option unless separate databases are a business requirement. If keeping separate databases, explicitly separate central identity from tenant data, complete tenant schemas, resolve context before tenant operations, and reset context after requests/jobs. Merely enabling the existing middleware would not fix the architecture.

Acceptance: authentication, authorization, background work and all tenant-owned reads/writes operate in the intended tenant; missing or suspended tenants are denied. Test context reuse as well as individual requests.

### 3. High: approval and invitations do not complete onboarding

Evidence: `app/Http/Controllers/AdminController.php:126` marks registration approved and emails a fixed password and localhost URL, but creates no user or company. `app/Http/Controllers/CompanyAdmin/EmployeeInvitationController.php:35` stores an invitation and reports it sent, but sends no mail. No acceptance route exists.

Required: approval provisions the company and owner through an idempotent workflow; send an expiring, single-use account setup link rather than a password. Implement invitation delivery, acceptance, revocation, resend and expiry with validated roles and tenant membership. Queue delivery after committed state and report failures accurately.

Acceptance: a new owner can register, be approved, set a password and sign in; an invited employee joins only the intended company. Replaying approval or acceptance creates no duplicate accounts.

### 4. High: account removal and suspension do not reliably revoke access

Evidence: `CompanyOwnerController.php:202` removes the tenant user and owner profile but leaves the central login user and its role. Login checks credentials and roles without company/employee status checks. Company-admin route middleware checks auth and role only.

Required: centralize account and membership lifecycle; block suspended/deleted companies and users on every protected request, and revoke sessions when removing access. Protect the last company administrator and platform administrator.

Acceptance: a removed owner or suspended employee cannot sign in or continue using an existing session.

### 5. High: company deletion permanently destroys data immediately

Evidence: `app/Http/Controllers/CompanyController.php:215` drops the tenant database, then soft-deletes the company row. Restoring the row cannot restore the database.

Required: suspend access first, apply a documented retention window, allow recovery/export, then perform audited permanent deletion with explicit authorization. Maintain encrypted backups and verify restoration before launch.

Acceptance: accidental company deletion is recoverable within the promised window; a restore rehearsal recovers both company metadata and tenant records consistently.

### 6. High: provisioning can leave partial accounts and databases

Evidence: company rows and logs are created before CREATE DATABASE and tenant migration. Owner creation writes central records before tenant operations, without a recoverable provisioning state. Cross-database errors leave partial state. Employee creation omits the user's company_id and explicitly stores a null employee company_id.

Required: transactions for related writes on a single database; idempotent provisioning states and compensating recovery for cross-database work. Do not assume a transaction makes database DDL or two connections atomic. Assign employee company ownership on the server. Replace count-based employee codes with concurrency-safe identifiers.

Acceptance: inject failure at each provisioning stage; retry safely without orphaned records, duplicate identities or incorrect company assignment.

### 7. High: authentication and navigation paths are unfinished

Evidence: no application login throttling or password-reset/verification routes found. Failed login has no explicit response. Manager and Staff login redirects name nonexistent routes. `resources/views/partials/sidebar.blade.php:27` calls nonexistent `dashboard` instead of `admin.dashboard`. Company-admin employee forms and links point to Super Admin routes. Employee status changes use GET. `app/Models/employee.php` casing disagrees with Employee references, risking autoload failure on case-sensitive hosts.

Required: complete error handling, recovery, email verification, rate limiting, role landing pages and route names; use a CSRF-protected mutation method for status changes; fix file casing. Add MFA for privileged accounts. Normalize employee status comparisons: the dashboard compares numeric 1/0 against string enum values.

Acceptance: each role can navigate its allowed workflow on Linux; guests receive the intended login redirect; invalid credentials show an error; repeated attempts are limited; GET requests do not mutate data.

## Feature delivery order

### Milestone A — safe account and company foundation

Resolve the blockers above. Finish owner signup, invitations, password recovery, email verification, role permissions, suspension, audit trails and company settings. Audit events should identify actor, company, target, action and time, with sensitive values redacted. Keep platform administration separate from company administration.

Exit gate: automated cross-company denial tests pass, each role's happy path works, and failed provisioning is recoverable.

### Milestone B — one useful CRM workflow

Build contacts/customer organizations, leads and deals, configurable pipeline stages, deal ownership/value/expected close date, notes, tasks and follow-up reminders. Include search, filtering, pagination, tenant-scoped CSV import/export, duplicate detection and validation feedback.

First end-to-end customer outcome: import contacts → create a deal → assign a teammate → schedule a follow-up → record won/lost → view an accurate pipeline report. Use real stored data for reporting.

Exit gate: a pilot customer can complete that sequence without developer intervention; unauthorized imports, exports and attachments cannot cross tenant boundaries.

### Milestone C — charging and support

Define plans, seat/usage limits and server-side entitlements. Implement a payment approach supported in the target market: hosted checkout with verified, idempotent webhooks, or an audited manual-invoice process for the pilot. Support invoice/payment history, renewals, cancellation, grace periods and failed payments. Never grant a paid plan solely from a browser redirect.

Add a support contact, onboarding checklist, account export/deletion process and clear product terms/privacy information. External legal and payment requirements depend on customer and operating jurisdictions and were not audited here.

Exit gate: duplicate payment notifications do not double-charge or double-grant access; unpaid and cancelled states follow the documented policy; seat/usage limits cannot be bypassed through direct requests.

### Milestone D — operate reliably

Add CI for tests, frontend builds, style checks, dependency checks and fresh-database migrations on the production database engine. Cover Linux deployment. Document runtime versions, staging, deployment/rollback, supervised queue workers, scheduler, mail delivery and secret management. Configure HTTPS, production debug settings, monitoring, error alerts and backup/restore procedures. Infrastructure controls may exist outside this repository; none were verified.

The existing /up route is useful but does not by itself demonstrate database, mail or queue health. Monitor actual dependencies, failed jobs and tenant-specific failures. Put limits on uploads, batch imports and expensive jobs.

Exit gate: deployment and restore rehearsals pass, an intentional job failure triggers an actionable alert, and a failed release can be rolled back safely.

### Milestone E — a bounded AI feature

After CRM data and isolation work, add one feature such as contact activity summaries or follow-up drafts. Keep retrieved context tenant-scoped, redact unnecessary personal data, enforce per-company usage budgets, handle timeout/failure, and record model/version and usage metadata. Require a user to approve any external send or business mutation. Test hallucinations and prompt injection before enabling actions.

Defer autonomous outreach, enterprise SSO/SCIM, custom domains, a general automation builder and advanced analytics until customer demand justifies them. They are not prerequisites for the first paid pilot.

## Verification and limits

- Vite production build completed using the bundled Node executable. npm was not on the shell PATH.
- PHP tests could not run: shell PHP is 8.2.30; PHPUnit requires at least 8.3, and installed Composer dependencies require at least 8.4. composer.lock contains Symfony packages requiring PHP >=8.4 although composer.json declares ^8.3. Align and pin the development/CI/production runtime or deliberately resolve compatible dependencies.
- Artisan route listing was blocked by the same platform check. Route findings above come from source inspection.
- Only the default homepage-response and assertTrue example tests were found. There is no demonstrated tenant, onboarding, billing or authorization coverage.
- No production database was migrated, no tenant was created/deleted, and no mail was sent during this review. Existing application edits were preserved.

## Reference guidance

- [OWASP multi-tenant security guidance](https://cheatsheetseries.owasp.org/cheatsheets/Multi_Tenant_Security_Cheat_Sheet.html): supports enforcing tenant boundaries across data, storage and asynchronous work and testing cross-tenant denial.
- [Laravel routing and rate limiting](https://laravel.com/framework/docs/routing): framework mechanisms for route middleware and request throttling.
- [Laravel deployment guidance](https://laravel.com/framework/docs/deployment): production configuration and deployment checks.

Recommended first implementation scope: repair employee tenant ownership and authorization, with two-company regression tests, before expanding the feature set.
