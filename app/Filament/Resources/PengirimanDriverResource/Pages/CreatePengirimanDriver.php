<?php

namespace App\Filament\Resources\PengirimanDriverResource\Pages;

use App\Filament\Resources\PengirimanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengirimanDriver extends CreateRecord
{
    protected static string $resource = PengirimanDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->color('info'),
            Actions\DeleteAction::make()
                ->color('danger'),
        ];
    }
}
