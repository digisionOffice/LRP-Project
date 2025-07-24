<?php

namespace App\Filament\Resources\NumberingSettingResource\Pages;

use App\Filament\Resources\NumberingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNumberingSetting extends EditRecord
{
    protected static string $resource = NumberingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
