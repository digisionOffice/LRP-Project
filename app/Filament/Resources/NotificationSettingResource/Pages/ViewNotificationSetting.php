<?php

namespace App\Filament\Resources\NotificationSettingResource\Pages;

use App\Filament\Resources\NotificationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNotificationSetting extends ViewRecord
{
    protected static string $resource = NotificationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Menambahkan tombol "Edit" di pojok kanan atas halaman View
            Actions\EditAction::make(),
        ];
    }
}
