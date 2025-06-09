<?php

namespace App\Filament\Resources\PengirimanDriverResource\Pages;

use App\Filament\Resources\PengirimanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPengirimanDriver extends ViewRecord
{
    protected static string $resource = PengirimanDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
