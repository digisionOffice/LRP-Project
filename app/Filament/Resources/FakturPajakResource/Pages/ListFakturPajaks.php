<?php

namespace App\Filament\Resources\FakturPajakResource\Pages;

use App\Filament\Resources\FakturPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFakturPajaks extends ListRecords
{
    protected static string $resource = FakturPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
