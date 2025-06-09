<?php

namespace App\Filament\Resources\SubdistrictResource\Pages;

use App\Filament\Resources\SubdistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubdistrict extends EditRecord
{
    protected static string $resource = SubdistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
