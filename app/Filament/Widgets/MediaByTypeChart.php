<?php

namespace App\Filament\Widgets;

use App\Models\MediaItem;
use App\Models\MediaType;
use Filament\Widgets\ChartWidget;

class MediaByTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Media by Type';

    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $tenantId = filament()->getTenant()?->id;

        $data = MediaItem::where('family_id', $tenantId)
            ->selectRaw('media_type_id, count(*) as count')
            ->groupBy('media_type_id')
            ->get();

        $typeNames = MediaType::whereIn('id', $data->pluck('media_type_id'))
            ->pluck('name', 'id');

        $labels = $data->map(fn ($item) => $typeNames[$item->media_type_id] ?? 'Unknown')->toArray();
        $counts = $data->pluck('count')->toArray();

        $colors = [
            '#6366f1', '#f59e0b', '#10b981', '#f43f5e',
            '#8b5cf6', '#06b6d4', '#84cc16', '#ec4899',
        ];

        return [
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => array_slice($colors, 0, count($counts)),
                ],
            ],
            'labels' => $labels,
        ];
    }
}
