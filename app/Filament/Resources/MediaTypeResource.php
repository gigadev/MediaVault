<?php

namespace App\Filament\Resources;

use App\Enums\MediaApiSource;
use App\Filament\Resources\MediaTypeResource\Pages;
use App\Models\MediaType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class MediaTypeResource extends Resource
{
    protected static ?string $model = MediaType::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Settings';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('family_id', filament()->getTenant()?->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('icon')
                    ->label('Icon')
                    ->maxLength(255)
                    ->placeholder('e.g. heroicon-o-film'),

                Forms\Components\Select::make('api_source')
                    ->label('API Source')
                    ->options(MediaApiSource::options()),

                Forms\Components\Repeater::make('metadata_schema')
                    ->label('Metadata Schema')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->options([
                                'text' => 'Text',
                                'select' => 'Select',
                                'textarea' => 'Textarea',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('required')
                            ->default(false),

                        Forms\Components\TagsInput::make('options')
                            ->label('Options')
                            ->placeholder('Add option')
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'select'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->defaultItems(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('api_source')
                    ->label('API Source')
                    ->badge()
                    ->color(fn (?MediaApiSource $state): string => match ($state) {
                        MediaApiSource::Discogs => 'info',
                        MediaApiSource::Omdb => 'warning',
                        MediaApiSource::None => 'gray',
                        null => 'gray',
                    }),
                Tables\Columns\TextColumn::make('media_items_count')
                    ->label('Items')
                    ->counts('mediaItems')
                    ->sortable(),
                Tables\Columns\IconColumn::make('family_id')
                    ->label('Custom')
                    ->boolean()
                    ->getStateUsing(fn (MediaType $record): bool => $record->family_id !== null),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaTypes::route('/'),
            'create' => Pages\CreateMediaType::route('/create'),
            'edit' => Pages\EditMediaType::route('/{record}/edit'),
        ];
    }
}
