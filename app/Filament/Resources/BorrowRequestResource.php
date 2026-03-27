<?php

namespace App\Filament\Resources;

use App\Enums\BorrowRequestStatus;
use App\Filament\Resources\BorrowRequestResource\Pages;
use App\Models\BorrowRequest;
use App\Services\BorrowRequestService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BorrowRequestResource extends Resource
{
    protected static ?string $model = BorrowRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Activity';

    protected static ?string $tenantOwnershipRelationshipName = null;

    public static function getEloquentQuery(): Builder
    {
        $tenantId = filament()->getTenant()?->id;

        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->where(function (Builder $query) use ($tenantId) {
                $query->where('owning_family_id', $tenantId)
                    ->orWhere('requesting_family_id', $tenantId);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('mediaItem.title')
                    ->label('Media Item')
                    ->disabled(),
                Forms\Components\TextInput::make('requestingUser.name')
                    ->label('Requested By')
                    ->disabled(),
                Forms\Components\TextInput::make('requestingFamily.name')
                    ->label('Requesting Family')
                    ->disabled(),
                Forms\Components\TextInput::make('owningFamily.name')
                    ->label('Owning Family')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
                Forms\Components\TextInput::make('message')
                    ->label('Message')
                    ->disabled(),
                Forms\Components\TextInput::make('response_message')
                    ->label('Response')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('requested_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('due_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('returned_at')
                    ->disabled(),
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
                Tables\Columns\TextColumn::make('requestingUser.name')
                    ->label('Requested By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('requestingFamily.name')
                    ->label('Requesting Family')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owningFamily.name')
                    ->label('Owning Family')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (BorrowRequestStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('requested_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('returned_at')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BorrowRequestStatus::options()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BorrowRequest $record): bool => $record->status === BorrowRequestStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('response_message')
                            ->label('Response Message')
                            ->maxLength(500),
                        Forms\Components\DateTimePicker::make('due_at')
                            ->label('Due Date'),
                    ])
                    ->action(function (BorrowRequest $record, array $data) {
                        app(BorrowRequestService::class)->approve(
                            $record,
                            auth()->user(),
                            $data['response_message'] ?? null,
                            isset($data['due_at']) ? \Carbon\Carbon::parse($data['due_at']) : null,
                        );
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('deny')
                    ->label('Deny')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (BorrowRequest $record): bool => $record->status === BorrowRequestStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('response_message')
                            ->label('Reason')
                            ->maxLength(500),
                    ])
                    ->action(function (BorrowRequest $record, array $data) {
                        app(BorrowRequestService::class)->deny(
                            $record,
                            auth()->user(),
                            $data['response_message'] ?? null,
                        );
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('mark_checked_out')
                    ->label('Mark Checked Out')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (BorrowRequest $record): bool => $record->status === BorrowRequestStatus::Approved)
                    ->action(fn (BorrowRequest $record) => app(BorrowRequestService::class)->markCheckedOut($record))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('mark_returned')
                    ->label('Mark Returned')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (BorrowRequest $record): bool => $record->status === BorrowRequestStatus::CheckedOut)
                    ->action(fn (BorrowRequest $record) => app(BorrowRequestService::class)->markReturned($record))
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBorrowRequests::route('/'),
        ];
    }
}
