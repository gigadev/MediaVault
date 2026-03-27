<?php

namespace App\Filament\Pages;

use App\Enums\FamilyVisibility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class FamilySettings extends EditTenantProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'Family Settings';

    public static function getLabel(): string
    {
        return 'Family Settings';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->helperText('The slug is auto-generated and cannot be changed.'),

                Forms\Components\Select::make('visibility')
                    ->options(FamilyVisibility::options())
                    ->required(),

                Forms\Components\Toggle::make('allow_open_borrow_requests')
                    ->label('Allow Open Borrow Requests')
                    ->helperText('When enabled, any connected family can send borrow requests without an invitation.'),
            ]);
    }
}
