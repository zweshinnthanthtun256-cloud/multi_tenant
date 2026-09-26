<?php

namespace App\Jobs;

use App\Models\CrmTask;
use App\Notifications\TaskReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTaskReminder implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $taskId) {}

    public function handle(): void
    {
        $task = CrmTask::with('assignee')->find($this->taskId);
        if (! $task || ! $task->assignee || ! $task->assignee->canAccessWorkspace()) {
            return;
        }

        $task->assignee->notifyNow(new TaskReminder($task->id));
    }

    public function failed(?\Throwable $exception): void
    {
        CrmTask::whereKey($this->taskId)->update(['reminded_at' => null]);
    }
}
