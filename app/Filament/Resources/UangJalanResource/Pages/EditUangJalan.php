<?php

namespace App\Filament\Resources\UangJalanResource\Pages;

use App\Filament\Resources\UangJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUangJalan extends EditRecord
{
    protected static string $resource = UangJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
