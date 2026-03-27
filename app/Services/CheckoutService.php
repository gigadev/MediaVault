<?php

namespace App\Services;

use App\Models\Checkout;
use App\Models\MediaItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function checkOut(MediaItem $item, User $member, ?Carbon $dueAt = null, ?User $checkedOutBy = null): Checkout
    {
        if (! $item->is_available) {
            throw new \RuntimeException('This item is already checked out.');
        }

        return DB::transaction(function () use ($item, $member, $dueAt, $checkedOutBy) {
            $checkout = Checkout::create([
                'family_id' => $item->family_id,
                'media_item_id' => $item->id,
                'checked_out_to_user_id' => $member->id,
                'checked_out_by_user_id' => $checkedOutBy?->id ?? auth()->id(),
                'checked_out_at' => now(),
                'due_at' => $dueAt,
                'condition_on_checkout' => $item->condition?->value,
            ]);

            $item->update(['is_available' => false]);

            return $checkout;
        });
    }

    public function returnItem(Checkout $checkout, ?string $conditionOnReturn = null, ?string $notes = null): Checkout
    {
        if ($checkout->returned_at) {
            throw new \RuntimeException('This item has already been returned.');
        }

        return DB::transaction(function () use ($checkout, $conditionOnReturn, $notes) {
            $checkout->update([
                'returned_at' => now(),
                'condition_on_return' => $conditionOnReturn,
                'notes' => $notes,
            ]);

            $checkout->mediaItem->update(['is_available' => true]);

            return $checkout->fresh();
        });
    }
}
