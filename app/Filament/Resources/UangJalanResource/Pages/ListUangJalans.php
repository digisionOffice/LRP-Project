<?php

namespace App\Filament\Resources\UangJalanResource\Pages;

use App\Filament\Resources\UangJalanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUangJalans extends ListRecords
{
    protected static string $resource = UangJalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
