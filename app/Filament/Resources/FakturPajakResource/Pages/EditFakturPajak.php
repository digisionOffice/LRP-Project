<?php

namespace App\Filament\Resources\FakturPajakResource\Pages;

use App\Filament\Resources\FakturPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFakturPajak extends EditRecord
{
    protected static string $resource = FakturPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
