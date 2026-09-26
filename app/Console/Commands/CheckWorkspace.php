<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckWorkspace extends Command
{
    protected $signature = 'workspace:check {--production : Enforce live-service configuration}';

    protected $description = 'Check runtime, database and workspace deployment prerequisites without exposing secrets';

    public function handle(): int
    {
        $checks = ['PHP 8.4+' => PHP_VERSION_ID >= 80400, 'Application key configured' => filled(config('app.key'))];
        try {
            DB::select('SELECT 1');
            $checks['Database connection'] = true;
            $checks['Required schema installed'] = DB::getSchemaBuilder()->hasTable('contacts')
                && DB::getSchemaBuilder()->hasColumn('companies', 'plan');
            $checks['Employee company ownership resolved'] = DB::table('employees')->whereNull('company_id')->count() === 0;
            $checks['Employee and login ownership consistent'] = DB::table('employees')
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->where(function ($query) {
                    $query->whereColumn('employees.company_id', '!=', 'users.company_id')
                        ->orWhereNull('users.company_id');
                })->count() === 0;
        } catch (\Throwable $exception) {
            $checks['Database and schema checks'] = false;
        }

        if ($this->option('production')) {
            $checks['Production environment'] = app()->isProduction();
            $checks['Debug disabled'] = ! config('app.debug');
            $checks['HTTPS application URL'] = str_starts_with((string) config('app.url'), 'https://');
            $checks['Secure session cookie'] = (bool) config('session.secure');
            $checks['Persistent queue configured'] = in_array(config('queue.default'), ['database', 'redis', 'sqs', 'beanstalkd']);
            $checks['Live email transport configured'] = ! in_array(config('mail.default'), ['log', 'array', null]);
            $checks['Support address configured'] = (bool) filter_var(config('saas.support_email'), FILTER_VALIDATE_EMAIL);
            if (config('saas.ai_enabled')) {
                $checks['AI key and model configured'] = filled(config('saas.openai_key')) && filled(config('saas.openai_model'));
            }
        }

        foreach ($checks as $label => $ok) {
            $this->line(($ok ? 'PASS ' : 'FAIL ').$label);
        }
        $this->warn('This does not verify mail delivery, worker uptime, offsite backups, external monitoring or legal readiness.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
