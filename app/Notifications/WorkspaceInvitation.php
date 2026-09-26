<?php

namespace App\Notifications;

use App\Models\EmployeeInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspaceInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $url, public string $companyName)
    {
        $this->afterCommit();
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        $token = basename(parse_url($this->url, PHP_URL_PATH));

        return EmployeeInvitation::where('token', hash('sha256', $token))
            ->where('status', 'pending')->where('expires_at', '>', now())
            ->whereHas('company', fn ($q) => $q->where('status', 1))->exists();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Join '.$this->companyName)
            ->line('You have been invited to join '.$this->companyName.'.')
            ->action('Accept invitation', $this->url)->line('This single-use invitation expires in three days.');
    }
}
