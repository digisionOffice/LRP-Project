<?php

namespace App\Filament\Resources\TbbmResource\Pages;

use App\Filament\Resources\TbbmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTbbm extends EditRecord
{
    protected static string $resource = TbbmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
