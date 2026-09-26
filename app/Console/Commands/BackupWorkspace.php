<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupWorkspace extends Command
{
    protected $signature = 'workspace:backup';

    protected $description = 'Create a private database snapshot for offsite backup';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $dir = storage_path('app/private/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $file = $dir.'/'.now()->format('Ymd-His').'-'.Str::random(8);

        if ($driver === 'sqlite') {
            $file .= '.sqlite';
            $connection->statement("VACUUM INTO '".str_replace("'", "''", $file)."'");
            $check = new \PDO('sqlite:'.$file);
            if ($check->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
                throw new \RuntimeException('Backup integrity check failed.');
            }
        } elseif ($driver === 'mysql') {
            $file .= '.sql';
            $config = $connection->getConfig();
            $credentials = $dir.'/.mysql-'.Str::random(16).'.cnf';
            $escape = fn ($value) => '"'.str_replace(
                ['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $value
            ).'"';
            file_put_contents($credentials, "[client]\nhost=".$escape($config['host'])
                ."\nport=".(int) ($config['port'] ?? 3306)."\nuser=".$escape($config['username'])
                ."\npassword=".$escape($config['password'])."\n");
            chmod($credentials, 0600);
            try {
                $process = new Process(['mysqldump', '--defaults-extra-file='.$credentials,
                    '--single-transaction', '--quick', '--no-tablespaces', '--result-file='.$file, $config['database']]);
                $process->setTimeout(1800);
                $process->run();
                if (! $process->isSuccessful()) {
                    throw new \RuntimeException('Backup failed. Check mysqldump and backup-account privileges.');
                }
            } finally {
                if (is_file($credentials)) {
                    unlink($credentials);
                }
            }
        } else {
            $this->error('Supported backup drivers: SQLite and MySQL.');

            return self::FAILURE;
        }

        chmod($file, 0600);
        $this->info('Private database snapshot: '.$file);
        $this->warn('Encrypt and copy offsite. Back up uploaded files separately. Rehearse restoration.');

        return self::SUCCESS;
    }
}
