<?php

namespace App\Notifications;

use App\Models\FamilyConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FamilyConnectionRequest extends Notification
{
    use Queueable;

    public function __construct(
        public FamilyConnection $connection,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Family Connection Request from ' . $this->connection->requesterFamily->name)
            ->line('The "' . $this->connection->requesterFamily->name . '" family would like to connect with your family on MediaVault.')
            ->line('Connecting allows you to browse each other\'s collections and request to borrow media.')
            ->action('View Request', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Family Connection Request',
            'body' => 'The "' . $this->connection->requesterFamily->name . '" family wants to connect.',
            'connection_id' => $this->connection->id,
        ];
    }
}
