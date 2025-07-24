<?php

namespace App\Filament\Resources\PengirimanDriverResource\Pages;

use App\Filament\Resources\PengirimanDriverResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengirimanDriver extends CreateRecord
{
    protected static string $resource = PengirimanDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed on create page
        ];
    }
}
