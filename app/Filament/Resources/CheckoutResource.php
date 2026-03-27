<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckoutResource\Pages;
use App\Models\Checkout;
use App\Services\CheckoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckoutResource extends Resource
{
    protected static ?string $model = Checkout::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Activity';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('media_item_id')
                    ->relationship('mediaItem', 'title')
                    ->disabled(),
                Forms\Components\Select::make('checked_out_to_user_id')
                    ->relationship('checkedOutTo', 'name')
                    ->label('Checked Out To')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('checked_out_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('due_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('returned_at')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mediaItem.title')
                    ->label('Media Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkedOutTo.name')
                    ->label('Checked Out To')
                    ->searchable(),
                Tables\Columns\TextColumn::make('checked_out_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->date()
                    ->sortable()
                    ->color(fn (Checkout $record): ?string => $record->is_overdue ? 'danger' : null),
                Tables\Columns\TextColumn::make('returned_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Checkout $record): string {
                        if ($record->returned_at) {
                            return 'Returned';
                        }
                        if ($record->is_overdue) {
                            return 'Overdue';
                        }

                        return 'Active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Returned' => 'success',
                        'Overdue' => 'danger',
                        'Active' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('returned_at'),
                        false: fn (Builder $query) => $query->whereNotNull('returned_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNull('returned_at')
                        ->where('due_at', '<', now())
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('return')
                    ->label('Return')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Checkout $record): bool => $record->returned_at === null)
                    ->requiresConfirmation()
                    ->action(function (Checkout $record): void {
                        app(CheckoutService::class)->returnItem($record);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckouts::route('/'),
        ];
    }
}
