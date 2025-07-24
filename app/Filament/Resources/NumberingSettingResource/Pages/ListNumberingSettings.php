<?php

namespace App\Filament\Resources\NumberingSettingResource\Pages;

use App\Filament\Resources\NumberingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNumberingSettings extends ListRecords
{
    protected static string $resource = NumberingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
