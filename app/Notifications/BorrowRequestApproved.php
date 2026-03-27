<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestApproved extends Notification
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
        $mail = (new MailMessage)
            ->subject('Borrow Request Approved - ' . $this->borrowRequest->mediaItem->title)
            ->line('Your request to borrow "' . $this->borrowRequest->mediaItem->title . '" has been approved!');

        if ($this->borrowRequest->due_at) {
            $mail->line('Due date: ' . $this->borrowRequest->due_at->format('M j, Y'));
        }

        if ($this->borrowRequest->response_message) {
            $mail->line('Note: ' . $this->borrowRequest->response_message);
        }

        return $mail->action('View Details', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Borrow Request Approved',
            'body' => 'Your request to borrow "' . $this->borrowRequest->mediaItem->title . '" was approved!',
            'borrow_request_id' => $this->borrowRequest->id,
        ];
    }
}
