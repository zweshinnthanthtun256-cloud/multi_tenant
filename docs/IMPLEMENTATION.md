# Implementation handoff

## Delivered in this change

- PHP 8.4 project runner for Windows and a pinned runtime marker.
- Shared-database company ownership, protected employee access and server-assigned membership.
- Active-account checks, role redirects, login throttling, password setup/recovery, email verification and session password-hash checks.
- Transactional approval that creates a company and owner without emailing a password.
- Hashed, expiring, single-use invitation links, queued delivery, revocation and seat reservations.
- Contacts/leads, searchable lists, opportunity pipeline stages, assignments, notes, follow-up tasks and queued reminders.
- Atomic CSV import with validation, duplicate detection, tenant-scoped export and formula neutralization.
- Manual billing, invoices, tenant-scoped transfer-reference submission, administrator verification/rejection, unique references, chronological entitlement selection, idempotent confirmation, paid/trial access periods, plan limits and cancellation.
- Optional company-consented AI summaries/drafts, bounded context, quotas, usage metadata, provider failure handling and no automatic sending.
- Workspace settings, audit views, CRM data export, safe company archiving/restoration, private backup command and deployment prerequisite checks.
- New responsive navigation, dashboard, authentication, CRM, team, billing and AI screens.
- Linux CI definition for SQLite/MySQL tests, frontend builds and dependency audits.

## Verified locally

- 28 tests and 182 assertions passed using PHP 8.4.10 and SQLite.
- Frontend production build passed.
- PHP syntax checks passed for application, routes and tests.
- Blade compilation and route caching passed; temporary route cache was cleared afterward.
- Desktop/mobile browser checks found no JavaScript errors or horizontal page overflow; sign-in and contact creation succeeded using fictional data in an isolated database.
- SQLite backup was opened independently and retained a record after removal from the source database.

## Configuration / rollout still required

- Apply the two new migrations to your intended database after backup and staging validation. Only an isolated preview/test database was migrated during implementation.
- Audit and reconcile any data stored only in legacy separate tenant databases; these databases were preserved and are not automatically imported.
- Replace any previously seeded known passwords and verify existing administrator email addresses. The new seeder no longer creates a fixed password.
- Configure SMTP/mail delivery, a supervised queue worker, scheduler and a production URL.
- Set OpenAI credentials/model and enable AI only when you have approved that data processing. Provider tests used a fake API, not a live account.
- A no-gateway manual transfer flow is implemented. Automated card subscriptions/webhooks still require a selected payment provider and merchant account, and the provider will charge transaction fees.
- Validate the MySQL CI job and MySQL restoration on your deployment infrastructure. Remote CI was not run from this task.
- Connect encrypted offsite backups, monitoring/alerts and a documented retention/deletion process. A local snapshot command does not supply those external services.

See [OPERATIONS.md](OPERATIONS.md) for the upgrade and launch procedure. This implementation is not a declaration that the existing live deployment is ready to accept paying customers.
