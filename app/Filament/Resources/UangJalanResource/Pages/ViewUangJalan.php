<?php

namespace App\Filament\Resources\UangJalanResource\Pages;

use App\Filament\Resources\UangJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUangJalan extends ViewRecord
{
    protected static string $resource = UangJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
