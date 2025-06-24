<?php

namespace App\Filament\Resources\AlamatPelangganResource\Pages;

use App\Filament\Resources\AlamatPelangganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlamatPelanggans extends ListRecords
{
    protected static string $resource = AlamatPelangganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
