<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\TransaksiPenjualan;

class ViewTransaksiPenjualan extends ViewRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //  lihat timeline, buat do kalau belum ada
            Actions\Action::make('view_timeline')
                ->label('Lihat Timeline')
                ->icon('heroicon-o-clock')
                ->url(fn(TransaksiPenjualan $record): string => "/admin/sales-order-timeline-detail?record={$record->id}")
                ->openUrlInNewTab(false),
            Actions\Action::make('create_do')
                ->label('Buat DO')
                ->icon('heroicon-o-truck')
                ->url(fn(TransaksiPenjualan $record): string => route('filament.admin.resources.delivery-orders.create', ['id_transaksi' => $record->id]))
                ->visible(fn(TransaksiPenjualan $record): bool => !$record->deliveryOrder)
                ->openUrlInNewTab(false),
            // lihat do kalau ada
            Actions\Action::make('view_do')
                ->label('Lihat DO')
                ->icon('heroicon-o-eye')
                ->url(fn(TransaksiPenjualan $record): string => $record->deliveryOrderUrl)
                ->visible(fn(TransaksiPenjualan $record): bool => $record->deliveryOrderUrl !== null)
                ->openUrlInNewTab(false),
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
            ];
        })->toArray();

        return $data;
    }
}
