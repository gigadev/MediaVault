<?php

namespace App\Filament\Resources;

use App\Enums\ConnectionStatus;
use App\Filament\Resources\FamilyConnectionResource\Pages;
use App\Models\Family;
use App\Models\FamilyConnection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyConnectionResource extends Resource
{
    protected static ?string $model = FamilyConnection::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Family';

    protected static ?string $tenantOwnershipRelationshipName = null;

    public static function getEloquentQuery(): Builder
    {
        $tenantId = filament()->getTenant()?->id;

        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->where(function (Builder $query) use ($tenantId) {
                $query->where('requester_family_id', $tenantId)
                    ->orWhere('receiver_family_id', $tenantId);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('receiver_family_id')
                    ->label('Family to Connect With')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        $tenantId = filament()->getTenant()?->id;

                        return Family::where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->where('id', '!=', $tenantId)
                            ->limit(20)
                            ->pluck('name', 'id');
                    })
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $tenantId = filament()->getTenant()?->id;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('connected_family')
                    ->label('Connected Family')
                    ->state(function (FamilyConnection $record) use ($tenantId): string {
                        if ($record->requester_family_id === $tenantId) {
                            return $record->receiverFamily?->name ?? '';
                        }

                        return $record->requesterFamily?->name ?? '';
                    })
                    ->searchable(query: function (Builder $query, string $search) use ($tenantId): Builder {
                        return $query->where(function (Builder $q) use ($search, $tenantId) {
                            $q->where('requester_family_id', $tenantId)
                                ->whereHas('receiverFamily', fn (Builder $fq) => $fq->where('name', 'like', "%{$search}%"));
                        })->orWhere(function (Builder $q) use ($search, $tenantId) {
                            $q->where('receiver_family_id', $tenantId)
                                ->whereHas('requesterFamily', fn (Builder $fq) => $fq->where('name', 'like', "%{$search}%"));
                        });
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ConnectionStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('requested_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responded_at')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (FamilyConnection $record): bool => $record->status === ConnectionStatus::Pending
                        && $record->receiver_family_id === $tenantId)
                    ->action(function (FamilyConnection $record) {
                        $record->update([
                            'status' => ConnectionStatus::Accepted,
                            'responded_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('decline')
                    ->label('Decline')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FamilyConnection $record): bool => $record->status === ConnectionStatus::Pending
                        && $record->receiver_family_id === $tenantId)
                    ->action(function (FamilyConnection $record) {
                        $record->update([
                            'status' => ConnectionStatus::Declined,
                            'responded_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFamilyConnections::route('/'),
            'create' => Pages\CreateFamilyConnection::route('/create'),
        ];
    }
}
