<?php

namespace App\Filament\Resources\ProvinceResource\Pages;

use App\Filament\Resources\ProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProvince extends ViewRecord
{
    protected static string $resource = ProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning'),
            Actions\DeleteAction::make()
                ->color('danger'),
        ];
    }

    public function getTitle(): string
    {
        return 'Province: ' . $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Manage administrative hierarchy for ' . $this->record->name . ' Province';
    }
}
