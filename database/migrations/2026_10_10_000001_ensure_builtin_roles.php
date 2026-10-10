<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table(config('permission.table_names.roles', 'roles'))->insertOrIgnore(
            collect(['Super Admin', 'Company Admin', 'Manager', 'Staff'])
                ->map(fn (string $name) => [
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all()
        );
    }

    public function down(): void
    {
        // Built-in roles may already be assigned, so rollback must not delete them.
    }
};
