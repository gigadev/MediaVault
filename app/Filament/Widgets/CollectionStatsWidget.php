<?php

namespace App\Filament\Widgets;

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\Checkout;
use App\Models\MediaItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CollectionStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenantId = filament()->getTenant()?->id;

        $totalItems = MediaItem::where('family_id', $tenantId)->count();

        $checkedOutItems = Checkout::where('family_id', $tenantId)
            ->active()
            ->count();

        $overdueItems = Checkout::where('family_id', $tenantId)
            ->overdue()
            ->count();

        $pendingRequests = BorrowRequest::where('owning_family_id', $tenantId)
            ->where('status', BorrowRequestStatus::Pending)
            ->count();

        return [
            Stat::make('Total Media Items', $totalItems)
                ->icon('heroicon-o-archive-box'),

            Stat::make('Items Checked Out', $checkedOutItems)
                ->icon('heroicon-o-arrow-right-circle'),

            Stat::make('Overdue Items', $overdueItems)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdueItems > 0 ? 'danger' : 'success'),

            Stat::make('Pending Borrow Requests', $pendingRequests)
                ->icon('heroicon-o-inbox-arrow-down'),
        ];
    }
}
