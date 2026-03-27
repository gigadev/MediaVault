<?php

namespace App\Filament\Resources\FamilyConnectionResource\Pages;

use App\Filament\Resources\FamilyConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFamilyConnections extends ListRecords
{
    protected static string $resource = FamilyConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
