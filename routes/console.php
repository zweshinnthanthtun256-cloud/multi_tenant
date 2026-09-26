<?php

use App\Jobs\SendTaskReminder;
use App\Models\CrmTask;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('crm:reminders', function () {
    CrmTask::where('status', 'open')->whereDate('due_date', '<=', today())->whereNull('reminded_at')
        ->with('assignee')->chunkById(100, function ($tasks) {
            foreach ($tasks as $task) {
                if (! $task->assignee || ! $task->assignee->canAccessWorkspace()) {
                    continue;
                }
                $claimed = CrmTask::whereKey($task->id)->whereNull('reminded_at')->update(['reminded_at' => now()]);
                if ($claimed) {
                    SendTaskReminder::dispatch($task->id);
                }
            }
        });
    $this->info('Due reminders queued.');
})->purpose('Queue follow-up reminders without sending CRM content in email');
Schedule::command('crm:reminders')->hourly()->withoutOverlapping();
