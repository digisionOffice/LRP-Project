<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateTransaksiPenjualan extends CreateRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set created_by for the main transaction
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Show success notification with item count
        $record = $this->getRecord();
        $itemCount = $record->penjualanDetails()->count();

        Notification::make()
            ->title('Berhasil')
            ->body("Transaksi penjualan berhasil dibuat dengan {$itemCount} item.")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
