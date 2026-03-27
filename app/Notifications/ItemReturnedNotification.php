<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemReturnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public BorrowRequest $borrowRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Item Returned - ' . $this->borrowRequest->mediaItem->title)
            ->line('"' . $this->borrowRequest->mediaItem->title . '" has been returned.')
            ->action('View Details', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Item Returned',
            'body' => '"' . $this->borrowRequest->mediaItem->title . '" has been returned.',
            'borrow_request_id' => $this->borrowRequest->id,
        ];
    }
}
