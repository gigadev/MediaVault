<?php

namespace App\Filament\Pages\Auth;

use App\Enums\FamilyMemberRole;
use App\Models\Family;
use App\Models\FamilyMember;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;

class RegisterFamily extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Create Family';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Family Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. The Smiths'),
            ]);
    }

    protected function handleRegistration(array $data): Family
    {
        $family = Family::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::random(4),
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => auth()->id(),
            'role' => FamilyMemberRole::Owner,
            'joined_at' => now(),
        ]);

        return $family;
    }
}
