<?php

namespace App\Filament\Resources\FamilyConnectionResource\Pages;

use App\Enums\ConnectionStatus;
use App\Filament\Resources\FamilyConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFamilyConnection extends CreateRecord
{
    protected static string $resource = FamilyConnectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_family_id'] = filament()->getTenant()->id;
        $data['status'] = ConnectionStatus::Pending;
        $data['requested_at'] = now();

        return $data;
    }
}
