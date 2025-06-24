<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->color('info'),
            Actions\DeleteAction::make()
                ->color('danger'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Delivery Order';
    }

    public function getSubheading(): ?string
    {
        return 'Perbarui informasi delivery order';
    }
}
