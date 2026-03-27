<?php

namespace App\Filament\Widgets;

use App\Models\Checkout;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCheckoutsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Checkouts';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Checkout::query()
                    ->where('family_id', filament()->getTenant()?->id)
                    ->latest('checked_out_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('mediaItem.title')
                    ->label('Item'),
                Tables\Columns\TextColumn::make('checkedOutTo.name')
                    ->label('Member'),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->label('Checked Out')
                    ->date()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
