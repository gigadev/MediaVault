<?php

namespace App\Filament\Resources\FamilyMemberResource\Pages;

use App\Filament\Resources\FamilyMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFamilyMember extends EditRecord
{
    protected static string $resource = FamilyMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
