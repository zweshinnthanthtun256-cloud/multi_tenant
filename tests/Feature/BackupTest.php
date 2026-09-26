<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupTest extends TestCase
{
    public function test_sqlite_snapshot_can_be_opened_and_restored_independently(): void
    {
        $dir = sys_get_temp_dir().'/coreflow-backup-'.bin2hex(random_bytes(6));
        mkdir($dir, 0700, true);
        $source = $dir.'/source.sqlite';
        touch($source);
        $oldStorage = storage_path();
        try {
            $this->app->useStoragePath($dir);
            config(['database.default' => 'backup_test', 'database.connections.backup_test' => ['driver' => 'sqlite', 'database' => $source, 'foreign_key_constraints' => true]]);
            DB::purge('backup_test');
            DB::statement('CREATE TABLE restore_probe (id INTEGER PRIMARY KEY, label TEXT)');
            DB::table('restore_probe')->insert(['label' => 'snapshot survives']);
            $this->artisan('workspace:backup')->assertSuccessful();
            $files = glob($dir.'/app/private/backups/*.sqlite');
            $this->assertCount(1, $files);
            DB::table('restore_probe')->delete();
            $restored = new \PDO('sqlite:'.$files[0]);
            $this->assertSame('ok', $restored->query('PRAGMA integrity_check')->fetchColumn());
            $this->assertSame('snapshot survives', $restored->query('SELECT label FROM restore_probe')->fetchColumn());
            $restored = null;
        } finally {
            DB::disconnect('backup_test');
            $this->app->useStoragePath($oldStorage);
            foreach (glob($dir.'/app/private/backups/*') ?: [] as $file) {
                unlink($file);
            }
            foreach ([$dir.'/app/private/backups', $dir.'/app/private', $dir.'/app'] as $folder) {
                if (is_dir($folder)) {
                    rmdir($folder);
                }
            }
            foreach (glob($source.'*') ?: [] as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
    }
}
