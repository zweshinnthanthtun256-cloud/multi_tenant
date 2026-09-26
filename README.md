# CoreFlow CRM

A Laravel 13 / Blade CRM for company workspaces, with a responsive Bootstrap-based interface.

## Included

- Shared-database company isolation; separate platform and company administration.
- Active-account checks, login throttling, password reset, email verification and session authentication.
- Registration approval, secure single-use team invitations, seat limits and role-based actions.
- Contacts and leads, opportunity stages, assignments, notes, follow-up tasks and reminders.
- Validated CSV import/export and workspace CRM export.
- Manual invoices, customer-submitted transfer references, administrator verification, bounded trial/paid access and renewal cancellation. This path requires no payment gateway subscription; banks may charge transfer fees.
- Optional OpenAI summaries and follow-up drafts with company consent, quotas and human review.
- Reversible company archiving, audit activity, private database snapshots and Linux CI.

## Run locally

Use PHP 8.4+ and Node 22.12+. Install locked dependencies with Composer and npm. Copy .env.example for a new installation, configure the database and mail, and generate an application key only for a new installation. Then migrate, seed roles/your configured administrator, and build assets.

On the provided Windows machine:

    ./scripts/php.ps1 artisan migrate
    ./scripts/php.ps1 artisan db:seed
    ./scripts/php.ps1 vendor/bin/phpunit
    npm run build

The helper uses the installed PHP 8.4 runtime without changing system PATH. For the development web server, ensure SQLite extensions are enabled in php.ini if using SQLite: Artisan's child server does not inherit command-line extension flags. Use MySQL for a production deployment and validate the CI MySQL job before release.

Initial admin creation requires INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD; there is no default password. Configure a working mail transport to verify the account. A log mailer is for local development only.

## Upgrade and deployment

Read [operations and migration instructions](docs/OPERATIONS.md) before applying migrations to an existing database. Existing tenant databases are retained, not imported or deleted. There is no automated card charging, external deployment, live API configuration or managed offsite backup implied by this repository.

AI remains disabled until configured globally and enabled by each company owner. Tests use a fake API and do not spend API credits.

## Verification

Run the feature suite with PHP 8.4 and SQLite extensions. CI additionally defines a MySQL 8.4 test job. Browser verification uses a separate local database containing fictional records; it does not alter the configured customer database.
