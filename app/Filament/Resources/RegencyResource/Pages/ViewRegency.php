<?php

namespace App\Filament\Resources\RegencyResource\Pages;

use App\Filament\Resources\RegencyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRegency extends ViewRecord
{
    protected static string $resource = RegencyResource::class;

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
        return 'Regency: ' . $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Manage districts for ' . $this->record->name;
    }
}
