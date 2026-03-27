<?php

namespace App\Notifications;

use App\Models\Checkout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemOverdue extends Notification
{
    use Queueable;

    public function __construct(
        public Checkout $checkout,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Overdue Item - ' . $this->checkout->mediaItem->title)
            ->line('"' . $this->checkout->mediaItem->title . '" was due on ' . $this->checkout->due_at->format('M j, Y') . '.')
            ->line('Please return this item as soon as possible.')
            ->action('View Checkouts', url('/app'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Overdue Item',
            'body' => '"' . $this->checkout->mediaItem->title . '" is overdue (was due ' . $this->checkout->due_at->format('M j, Y') . ')',
            'checkout_id' => $this->checkout->id,
        ];
    }
}
