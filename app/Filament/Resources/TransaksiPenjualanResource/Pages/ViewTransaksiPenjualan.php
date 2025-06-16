<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Models\TransaksiPenjualan;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Afsakar\LeafletMapPicker\LeafletMapPickerEntry;

class ViewTransaksiPenjualan extends ViewRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Sales Order')
                    ->schema([
                        TextEntry::make('kode')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->weight('bold')
                            ->label('Nomor SO'),
                        SpatieMediaLibraryImageEntry::make('dokumen_so')
                            ->label('Dokumen SO')
                            ->collection('dokumen_so')
                            ->conversion('preview'),

                        TextEntry::make('nomor_po')
                            ->label('Nomor PO')
                            ->icon('heroicon-o-document-text'),
                        SpatieMediaLibraryImageEntry::make('dokumen_po')
                            ->label('Dokumen PO')
                            ->collection('dokumen_po')
                            ->conversion('preview'),
                        TextEntry::make('nomor_sph')
                            ->label('Nomor SPH')
                            ->icon('heroicon-o-document-text'),
                        SpatieMediaLibraryImageEntry::make('dokumen_sph')
                            ->label('Dokumen SPH')
                            ->collection('dokumen_sph')
                            ->conversion('preview'),
                        TextEntry::make('pelanggan.nama')
                            ->label('Pelanggan')
                            ->icon('heroicon-o-building-office'),
                        TextEntry::make('alamatPelanggan.alamat')
                            ->label('Alamat Pengiriman')
                            ->icon('heroicon-o-map-pin'),
                        TextEntry::make('data_dp')
                            ->label('Data DP')
                            ->money('IDR')
                            ->icon('heroicon-o-currency-dollar'),
                        TextEntry::make('top_pembayaran')
                            ->label('Termin Pembayaran')
                            ->formatStateUsing(fn($state) => $state ? "{$state} hari" : 'Tunai')
                            ->badge()
                            ->color(fn($state) => $state > 30 ? 'warning' : 'success'),

                            // masukan detail penjualan
                            TextEntry::make('penjualanDetails.item.name')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(function ($record) {
                                return $record->penjualanDetails->pluck('item.name')->unique()->join(', ');
                            })
                            ->label('Jenis BBM'),

                            TextEntry::make('penjualanDetails.volume_item')
                            ->getStateUsing(function ($record) {
                                return $record->penjualanDetails->sum('volume_item');
                            })
                            ->label('Jumlah BBM')
                            ->numeric(decimalPlaces: 2)
                            ->suffix(' Liter'),

                        // use leafleat to show the map
                        LeafletMapPickerEntry::make('alamatPelanggan.location')
                            ->label('Lokasi di Peta')
                            ->height('400px')
                            ->tileProvider('google')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            //  lihat timeline, buat do kalau belum ada
            Actions\Action::make('view_timeline')
                ->label('Lihat Timeline')
                ->icon('heroicon-o-clock')
                ->color('success')
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
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn(TransaksiPenjualan $record): string => $record->deliveryOrderUrl)
                ->visible(fn(TransaksiPenjualan $record): bool => $record->deliveryOrderUrl !== null)
                ->openUrlInNewTab(false),
            Actions\EditAction::make()
                ->color('gray'),
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

    public function getTitle(): string
    {
        return 'Detail Sales Order';
    }

    public function getSubheading(): ?string
    {
        return 'Lihat detail sales order';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Home',
            '/admin/transaksi-penjualans' => 'Sales Order',
            '' => 'Detail',
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}
