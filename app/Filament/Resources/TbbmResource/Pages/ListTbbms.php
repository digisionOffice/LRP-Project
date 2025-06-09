<?php

namespace App\Filament\Resources\TbbmResource\Pages;

use App\Filament\Resources\TbbmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTbbms extends ListRecords
{
    protected static string $resource = TbbmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
