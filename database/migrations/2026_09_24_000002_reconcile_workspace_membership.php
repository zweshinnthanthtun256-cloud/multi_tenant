<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only repair ownership with an unambiguous existing user association.
        DB::table('employees')->whereNull('company_id')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $id = DB::table('users')->where('id', $row->user_id)->value('company_id');
                if ($id) {
                    DB::table('employees')->where('id', $row->id)->update(['company_id' => $id]);
                }
            }
        });
        // Existing companies get a bounded migration trial rather than unlimited access.
        DB::table('companies')->whereNull('trial_ends_at')->where('subscription_status', 'trial')
            ->update(['trial_ends_at' => now()->addDays(14)]);
    }

    public function down(): void
    { /* Data reconciliation is intentionally not reversed. */
    }
};
