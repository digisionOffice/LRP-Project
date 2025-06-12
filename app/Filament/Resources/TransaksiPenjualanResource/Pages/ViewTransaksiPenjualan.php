<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaksiPenjualan extends ViewRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the penjualan details for viewing
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
}
