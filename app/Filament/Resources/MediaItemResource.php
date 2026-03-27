<?php

namespace App\Filament\Resources;

use App\Enums\MediaCondition;
use App\Filament\Resources\MediaItemResource\Pages;
use App\Models\MediaItem;
use App\Models\MediaType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaItemResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Info')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('media_type_id')
                            ->label('Media Type')
                            ->relationship('mediaType', 'name')
                            ->required()
                            ->reactive()
                            ->preload(),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->minValue(1800)
                            ->maxValue(date('Y') + 1),
                        Forms\Components\Select::make('condition')
                            ->options(MediaCondition::options()),
                    ])->columns(2),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_shareable')
                            ->label('Shareable with connected families')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Cover Image')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image_path')
                            ->label('Cover Image')
                            ->image()
                            ->disk('public')
                            ->directory('media-covers')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Type-Specific Details')
                    ->schema(fn (Get $get): array => static::buildDynamicMetadataFields($get))
                    ->visible(fn (Get $get): bool => filled($get('media_type_id'))),
            ]);
    }

    protected static function buildDynamicMetadataFields(Get $get): array
    {
        $mediaTypeId = $get('media_type_id');

        if (! $mediaTypeId) {
            return [];
        }

        $mediaType = MediaType::find($mediaTypeId);

        if (! $mediaType || empty($mediaType->metadata_schema)) {
            return [];
        }

        $fields = [];

        foreach ($mediaType->metadata_schema as $field) {
            $name = $field['name'] ?? null;
            $type = $field['type'] ?? 'text';
            $label = $field['label'] ?? ucfirst($name ?? '');
            $required = $field['required'] ?? false;
            $options = $field['options'] ?? [];

            if (! $name) {
                continue;
            }

            $component = match ($type) {
                'select' => Forms\Components\Select::make("metadata.{$name}")
                    ->label($label)
                    ->options(
                        collect($options)->mapWithKeys(fn ($opt) => is_array($opt)
                            ? [$opt['value'] => $opt['label']]
                            : [$opt => $opt]
                        )->toArray()
                    ),
                'textarea' => Forms\Components\Textarea::make("metadata.{$name}")
                    ->label($label)
                    ->rows(3),
                default => Forms\Components\TextInput::make("metadata.{$name}")
                    ->label($label),
            };

            if ($required) {
                $component = $component->required();
            }

            $fields[] = $component;
        }

        return $fields;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mediaType.name')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->color(fn (MediaCondition $state): string => match ($state) {
                        MediaCondition::Mint => 'success',
                        MediaCondition::Excellent => 'info',
                        MediaCondition::Good => 'primary',
                        MediaCondition::Fair => 'warning',
                        MediaCondition::Poor => 'danger',
                    }),
                Tables\Columns\BooleanColumn::make('is_available')
                    ->label('Available')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('media_type_id')
                    ->label('Media Type')
                    ->relationship('mediaType', 'name'),
                Tables\Filters\SelectFilter::make('condition')
                    ->options(MediaCondition::options()),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Available'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaItems::route('/'),
            'create' => Pages\CreateMediaItem::route('/create'),
            'edit' => Pages\EditMediaItem::route('/{record}/edit'),
        ];
    }
}
