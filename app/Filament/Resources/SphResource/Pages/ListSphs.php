<?php

namespace App\Filament\Resources\SphResource\Pages;

use App\Filament\Resources\SphResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSphs extends ListRecords
{
    protected static string $resource = SphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
