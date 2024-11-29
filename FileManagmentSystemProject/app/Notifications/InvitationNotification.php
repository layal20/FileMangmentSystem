<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;

    use Queueable;

    protected $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
        //Log::info('Group data:', ['groupId' => $group->id, 'groupName' => $group->name]);
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation to join a new group')
            ->greeting('Hello')
            ->line('You have received an invitation to join the group: ' . $this->group->name)
            ->action('Joining the group', url('api/group/' . $this->group->id))
            ->line('We hope you accept the invitation and join us');
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
