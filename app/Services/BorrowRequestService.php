<?php

namespace App\Services;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\MediaItem;
use App\Models\User;
use App\Notifications\BorrowRequestApproved;
use App\Notifications\BorrowRequestDenied;
use App\Notifications\BorrowRequestReceived;
use App\Notifications\ItemReturnedNotification;
use Illuminate\Support\Facades\DB;

class BorrowRequestService
{
    public function createRequest(MediaItem $item, User $requestingUser, ?string $message = null): BorrowRequest
    {
        $request = BorrowRequest::create([
            'media_item_id' => $item->id,
            'requesting_user_id' => $requestingUser->id,
            'requesting_family_id' => filament()->getTenant()->id,
            'owning_family_id' => $item->family_id,
            'status' => BorrowRequestStatus::Pending,
            'requested_at' => now(),
            'message' => $message,
        ]);

        // Notify owning family admins
        $item->family->users()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->get()
            ->each(fn (User $admin) => $admin->notify(new BorrowRequestReceived($request)));

        return $request;
    }

    public function approve(BorrowRequest $request, User $respondedBy, ?string $responseMessage = null, ?\Carbon\Carbon $dueAt = null): BorrowRequest
    {
        return DB::transaction(function () use ($request, $respondedBy, $responseMessage, $dueAt) {
            $request->update([
                'status' => BorrowRequestStatus::Approved,
                'responded_at' => now(),
                'responded_by_user_id' => $respondedBy->id,
                'response_message' => $responseMessage,
                'due_at' => $dueAt,
            ]);

            $request->requestingUser->notify(new BorrowRequestApproved($request));

            return $request->fresh();
        });
    }

    public function deny(BorrowRequest $request, User $respondedBy, ?string $responseMessage = null): BorrowRequest
    {
        $request->update([
            'status' => BorrowRequestStatus::Denied,
            'responded_at' => now(),
            'responded_by_user_id' => $respondedBy->id,
            'response_message' => $responseMessage,
        ]);

        $request->requestingUser->notify(new BorrowRequestDenied($request));

        return $request->fresh();
    }

    public function markCheckedOut(BorrowRequest $request): BorrowRequest
    {
        return DB::transaction(function () use ($request) {
            $request->update([
                'status' => BorrowRequestStatus::CheckedOut,
                'checked_out_at' => now(),
            ]);

            $request->mediaItem->update(['is_available' => false]);

            return $request->fresh();
        });
    }

    public function markReturned(BorrowRequest $request): BorrowRequest
    {
        return DB::transaction(function () use ($request) {
            $request->update([
                'status' => BorrowRequestStatus::Returned,
                'returned_at' => now(),
            ]);

            $request->mediaItem->update(['is_available' => true]);

            // Notify both parties
            $request->requestingUser->notify(new ItemReturnedNotification($request));

            return $request->fresh();
        });
    }

    public function cancel(BorrowRequest $request): BorrowRequest
    {
        $request->update([
            'status' => BorrowRequestStatus::Cancelled,
        ]);

        return $request->fresh();
    }
}
