<?php

namespace App\Filament\Resources\NumberingSettingResource\Pages;

use App\Filament\Resources\NumberingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNumberingSetting extends ViewRecord
{
    protected static string $resource = NumberingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
