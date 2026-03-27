<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class BrowseFamilies extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Family';

    protected static ?string $title = 'Browse Families';

    protected static ?string $navigationLabel = 'Browse Families';

    protected static string $view = 'filament.pages.browse-families';

    public string $search = '';

    public function getViewData(): array
    {
        $tenantId = filament()->getTenant()?->id;

        $query = \App\Models\Family::query()
            ->where('id', '!=', $tenantId)
            ->where(function ($q) use ($tenantId) {
                $q->where('visibility', \App\Enums\FamilyVisibility::PublicBrowsable)
                    ->orWhereHas('sentConnections', function ($cq) use ($tenantId) {
                        $cq->where('receiver_family_id', $tenantId)
                            ->where('status', \App\Enums\ConnectionStatus::Accepted);
                    })
                    ->orWhereHas('receivedConnections', function ($cq) use ($tenantId) {
                        $cq->where('requester_family_id', $tenantId)
                            ->where('status', \App\Enums\ConnectionStatus::Accepted);
                    });
            });

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        return [
            'families' => $query->limit(20)->get(),
        ];
    }
}
