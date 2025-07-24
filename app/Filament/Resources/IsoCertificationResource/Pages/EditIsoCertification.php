<?php

namespace App\Filament\Resources\IsoCertificationResource\Pages;

use App\Filament\Resources\IsoCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIsoCertification extends EditRecord
{
    protected static string $resource = IsoCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
