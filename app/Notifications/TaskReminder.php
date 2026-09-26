<?php

namespace App\Notifications;

use App\Models\CrmTask;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminder extends Notification
{
    public function __construct(public int $taskId) {}

    public function via(object $user): array
    {
        return ['mail'];
    }

    public function shouldSend(object $user, string $channel): bool
    {
        $task = CrmTask::find($this->taskId);

        return $task && $task->status === 'open' && $task->assigned_to === $user->id
            && $task->company_id === $user->company_id && $user->fresh()?->canAccessWorkspace();
    }

    public function toMail(object $user): MailMessage
    {
        return (new MailMessage)->subject('A follow-up is due')->line('You have a CRM follow-up due. Open your workspace to review it.')->action('View tasks', route('crm.tasks'));
    }
}
