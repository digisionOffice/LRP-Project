<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTransaksiPenjualan extends EditRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the penjualan details for editing
        $record = $this->getRecord();
        $data['penjualanDetails'] = $record->penjualanDetails->map(function ($detail) {
            return [
                'id_item' => $detail->id_item,
                'volume_item' => $detail->volume_item,
                'harga_jual' => $detail->harga_jual,
                'item_info' => $detail->item ? $detail->item->kode . ' - ' . $detail->item->name : '',
                'satuan_info' => $detail->item?->satuan?->nama ?? '',
                'subtotal' => $detail->volume_item * $detail->harga_jual,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        // Show success notification with item count
        $record = $this->getRecord();
        $itemCount = $record->penjualanDetails()->count();

        if ($itemCount === 0) {
            Notification::make()
                ->title('Peringatan')
                ->body('Transaksi penjualan tidak memiliki item. Silakan tambahkan minimal satu item.')
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('Berhasil')
                ->body("Transaksi penjualan berhasil diperbarui dengan {$itemCount} item.")
                ->success()
                ->send();
        }
    }
}
