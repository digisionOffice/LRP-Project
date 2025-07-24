<?php

namespace App\Filament\Resources\IsoCertificationResource\Pages;

use App\Filament\Resources\IsoCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIsoCertifications extends ListRecords
{
    protected static string $resource = IsoCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
