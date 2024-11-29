<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileApprovalNotification extends Notification
{
    use Queueable;
    protected $fileName;
    protected $path;
    protected $groupId;

    public function __construct($fileName, $path, $groupId)
    {
        $this->fileName = $fileName;
        $this->path = $path;
        $this->groupId = $groupId;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('File Approval Request')
            ->greeting('Hello,')
            ->line("User has requested to upload a file: {$this->fileName}.")
            ->action('Approve File', url("api/groups/{$this->groupId}/approve-file?file={$this->path}"))
            ->line('Please review the request and take appropriate action.');
    }
}
