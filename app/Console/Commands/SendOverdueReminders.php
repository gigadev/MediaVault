<?php

namespace App\Console\Commands;

use App\Models\Checkout;
use App\Notifications\ItemOverdue;
use Illuminate\Console\Command;

class SendOverdueReminders extends Command
{
    protected $signature = 'mediavault:send-overdue-reminders';

    protected $description = 'Send notification reminders for overdue checked-out items';

    public function handle(): int
    {
        $overdueCheckouts = Checkout::query()
            ->whereNull('returned_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->with(['checkedOutTo', 'mediaItem'])
            ->get();

        $count = 0;

        foreach ($overdueCheckouts as $checkout) {
            $checkout->checkedOutTo->notify(new ItemOverdue($checkout));
            $count++;
        }

        $this->info("Sent {$count} overdue reminder(s).");

        return self::SUCCESS;
    }
}
