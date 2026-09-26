# CoreFlow pilot operations

## Runtime and local development

Use PHP **8.4+**, including mbstring, fileinfo, openssl, PDO MySQL and PDO SQLite, and Node **22.12+**. The checked-in dependency lock requires PHP 8.4 even though the original composer.json minimum is broader. Do not bypass Composer platform checks.

On this Windows machine, `scripts/php.ps1` selects the installed PHP 8.4.10 and enables SQLite for tests. Set `PROJECT_PHP` to override it. It does not change the global PATH.

```powershell
./scripts/php.ps1 artisan route:list
./scripts/php.ps1 vendor/bin/phpunit
```

Install dependencies with Composer under the same PHP runtime and `npm ci`, then run `npm run build`. Configure `.env` from `.env.example`, set the database and application URL, and generate the application key for a **new installation only**. Never regenerate the key of an existing deployment.

## Shared-database tenancy

All active accounts, companies and CRM records live in the configured primary database. Tenant-owned HTTP operations use the authenticated company, explicit ownership checks and company-scoped relation validation. Platform administrators have separate routes. Company administrators can manage their team; managers and company administrators can delete CRM records and export CSV; staff can work on contacts, opportunities and tasks inside their own company. AI drafts are shared within that company.

Existing separate tenant databases are **not dropped or automatically imported**. Their `db_name` remains as legacy metadata. Before production migration, inventory any tenant-only records and reconcile them with the primary database. New company records use a `shared_` identifier and no database DDL.

The membership reconciliation migration only assigns an employee to a company when its existing user already has a company. Ambiguous null ownership must be resolved by an administrator after checking the correct company; it is not guessed. Users with no valid company are denied tenant access.

## Upgrade an existing installation

1. Back up the primary database, legacy tenant databases, uploaded company logos and the existing application key. Verify restoration to a separate staging database.
2. Install PHP 8.4 and locked dependencies in staging. Deploy the new assets.
3. Review legacy users with missing `company_id`, duplicate company names/emails and disabled accounts. Check that current administrators can access their email for verification.
4. Run `php artisan migrate --force` on the intended database. The two new migrations add CRM/billing tables and bounded 14-day migration trials. They do not drop legacy tenant databases.
5. Test login, verification, two-company isolation, invitation acceptance, contact import and invoice confirmation in staging.
6. Deploy an immutable release, run migrations, cache configuration/routes/views, then restart queue workers. Keep the previous release available. Review migration compatibility before rolling application code back; do not blindly roll back migrations containing customer data.

Do not run `migrate:fresh` on a real customer database. It deletes tables.

## Initial administrator and email

The initial administrator seeder requires `INITIAL_ADMIN_EMAIL` and a strong `INITIAL_ADMIN_PASSWORD`; it no longer creates a known default password. Run `php artisan db:seed` once, remove these bootstrap secrets afterward, then sign in and verify the email. Existing user passwords are not overwritten by reseeding.

Set a real mail transport, sender domain and `APP_URL`. Approval creates an owner with an unknowable random password and sends a password setup link. Password recovery and verification use Laravel notifications. Invitations and reminders use the queue. An invitation email reporting “queued” is not a delivery guarantee.

Run a supervised worker:

```sh
php artisan queue:work --tries=3 --backoff=60 --timeout=90 --max-time=3600
```

Use `QUEUE_CONNECTION=database` or a managed queue, not sync, in production. Run `php artisan schedule:run` each minute under a scheduler. The hourly `crm:reminders` command queues due follow-ups. Inspect `queue:failed`; alert and retry after resolving failures. Notifications are reauthorized at delivery for task reminders. Monitor the queue worker, scheduler and mail provider delivery events.

## Billing pilot

This version uses **manual invoicing**, not automated card charging. Platform administrators issue invoices with explicit amount/currency and service-period end. A company administrator enters the bank or transfer reference, which changes the invoice to `pending_verification` without granting access. A platform administrator independently verifies or rejects the reference. Verification activates access; rejection reopens the invoice for a corrected submission. Repeated confirmation is idempotent, references are unique, and paying an older invoice cannot replace entitlements from a newer service period. Configure customer-facing transfer directions with `BILLING_PAYMENT_INSTRUCTIONS`.

Plan seat limits include pending, unexpired invitations; reservations are serialized with a company lock. Monthly AI quotas count attempted requests, including failures, to bound repeated provider calls. Expired trials/paid periods block CRM use but retain billing and export access. Cancelling renewal retains access until the paid period ends; there are no automatic charges to cancel.

Plan limits are in `config/saas.php`. Prices are intentionally not invented; agree them with pilot customers when issuing invoices. The application charges no gateway fee for the manual flow, although the sending or receiving bank may charge a transfer fee. Review tax, invoice and privacy obligations for the jurisdictions you serve. A provider-backed checkout/webhook integration can replace the manual workflow after selecting a supported merchant provider; card processing is not free and requires a merchant account.

## Optional AI drafts

Set `AI_ENABLED=true`, `OPENAI_API_KEY` and an explicitly chosen `OPENAI_MODEL` supported by your API account. Each company owner must also enable data sharing on the AI screen. The server sends the selected contact's organization, status and bounded notes; contact name, email and phone fields are excluded, and email patterns in notes are redacted. Notes may still contain other personal data: disclose the processing and avoid entering sensitive data.

The implementation uses the [OpenAI Responses API](https://developers.openai.com/api/docs/guides/migrate-to-responses), `store=false`, bounded input/output and timeouts. That flag is not a claim of zero retention across all provider systems. No tools, email sending or automatic CRM mutations are enabled. Outputs are escaped plain text and require human review. Provider failures return a generic message without logging CRM text or credentials. Tests use a fake provider; live connectivity and account availability require your credentials.

## Backups, restoration and retention

`php artisan workspace:backup` writes a private snapshot under `storage/app/private/backups`. SQLite snapshots run an integrity check. MySQL uses `mysqldump --single-transaction --quick`; install the matching client and grant the backup account the needed privileges. MySQL credentials are kept in a temporary permission-restricted option file, not command-line password arguments. On Windows, apply appropriate directory ACLs as chmod alone does not enforce Unix permissions.

This is a snapshot primitive, not a complete managed backup service. Schedule it, alert on failure, encrypt and copy snapshots to offsite storage, restrict access, and back up uploads separately. Never expose `storage/app/private` through the web server. Do not make schema changes while the MySQL dump runs.

Choose and document recovery objectives with customers before launch. A reasonable pilot starting target is daily snapshots and a same-business-day restore, but only promise an objective that has been rehearsed. Restore a snapshot to an **isolated** database, point a staging app to it with outbound mail/AI disabled, and verify company counts, membership, contacts, deals and invoices. Keep a dated record of the restore result. The local test run validates the SQLite snapshot path; MySQL restoration and offsite storage must be validated on your infrastructure.

Archiving a company blocks access and retains records; an administrator can restore it. Permanent erasure and a timed retention purge are not automatic. Establish your retention policy and implement/review an audited purge before promising deletion deadlines. Workspace export currently includes contacts, deals and tasks, not every administrative/billing record.

## Deployment and monitoring

Serve only `public/` over HTTPS. Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, a production `APP_URL`, and a secrets-managed application key. Use a least-privileged runtime database account: the application no longer needs CREATE/DROP DATABASE privileges. Configure trusted proxies for your host rather than trusting arbitrary forwarding headers.

Run `config:cache`, `route:cache` and `view:cache` during release; use `queue:restart` after activation. Store application logs centrally with access controls and retention. Alert on HTTP 5xx, failed queues, absent scheduler heartbeat, backup failure, low disk, and sustained mail/AI failures. `/up` is an application liveness signal; pair it with database/queue probes and a synthetic login-to-dashboard check in your monitoring system.

Run `php artisan workspace:check --production` before release. It checks runtime, schema, employee ownership consistency and key production configuration without displaying secrets. It deliberately does not claim to verify actual email delivery, worker uptime, monitoring or offsite backup health.

CI runs tests on Linux using SQLite and MySQL, builds assets, and audits dependencies. A checked-in workflow is not evidence that remote CI has run. Check its results after pushing.

Before accepting payment, finish production configuration and run the customer pilot: request workspace → approve → set password → verify email → invite teammate → import contacts → create opportunity → schedule follow-up → issue invoice → verify payment → export data → restore a backup in staging.
