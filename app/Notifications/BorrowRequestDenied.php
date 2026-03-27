<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestDenied extends Notification
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
            ->subject('Borrow Request Declined - ' . $this->borrowRequest->mediaItem->title)
            ->line('Your request to borrow "' . $this->borrowRequest->mediaItem->title . '" was declined.');

        if ($this->borrowRequest->response_message) {
            $mail->line('Reason: ' . $this->borrowRequest->response_message);
        }

        return $mail->action('View Details', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Borrow Request Declined',
            'body' => 'Your request to borrow "' . $this->borrowRequest->mediaItem->title . '" was declined.',
            'borrow_request_id' => $this->borrowRequest->id,
        ];
    }
}
