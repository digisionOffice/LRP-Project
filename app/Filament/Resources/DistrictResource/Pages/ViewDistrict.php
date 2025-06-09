<?php

namespace App\Filament\Resources\DistrictResource\Pages;

use App\Filament\Resources\DistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDistrict extends ViewRecord
{
    protected static string $resource = DistrictResource::class;

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
        return 'District: ' . $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Manage subdistricts for ' . $this->record->name;
    }
}
