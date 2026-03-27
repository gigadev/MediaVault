<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestReceived extends Notification
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
            ->subject('New Borrow Request - ' . $this->borrowRequest->mediaItem->title)
            ->line($this->borrowRequest->requestingUser->name . ' would like to borrow "' . $this->borrowRequest->mediaItem->title . '".')
            ->line($this->borrowRequest->message ? 'Message: ' . $this->borrowRequest->message : '')
            ->action('View Request', url('/app'))
            ->line('Please review and respond to this request.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Borrow Request',
            'body' => $this->borrowRequest->requestingUser->name . ' wants to borrow "' . $this->borrowRequest->mediaItem->title . '"',
            'borrow_request_id' => $this->borrowRequest->id,
        ];
    }
}
