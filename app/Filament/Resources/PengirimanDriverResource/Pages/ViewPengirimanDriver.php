<?php

namespace App\Filament\Resources\PengirimanDriverResource\Pages;

use App\Filament\Resources\PengirimanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Afsakar\LeafletMapPicker\LeafletMapPickerEntry;

class ViewPengirimanDriver extends ViewRecord
{
    protected static string $resource = PengirimanDriverResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Delivery Order')
                    ->schema([
                        TextEntry::make('deliveryOrder.kode')
                            ->label('Nomor DO')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->weight('bold'),

                        TextEntry::make('deliveryOrder.transaksi.kode')
                            ->label('Nomor SO')
                            ->icon('heroicon-o-shopping-cart'),

                        TextEntry::make('deliveryOrder.transaksi.pelanggan.nama')
                            ->label('Pelanggan')
                            ->icon('heroicon-o-building-office'),

                        TextEntry::make('deliveryOrder.user.name')
                            ->label('Sopir')
                            ->icon('heroicon-o-user')
                            ->placeholder('Belum Ditugaskan'),

                        TextEntry::make('deliveryOrder.kendaraan.no_pol_kendaraan')
                            ->label('Kendaraan')
                            ->icon('heroicon-o-truck')
                            ->placeholder('Belum Ditugaskan'),

                        TextEntry::make('deliveryOrder.tanggal_delivery')
                            ->label('Tanggal Pengiriman')
                            ->date('d M Y')
                            ->icon('heroicon-o-calendar'),

                        // map
                        LeafletMapPickerEntry::make('deliveryOrder.transaksi.alamatPelanggan.location')
                            ->label('Lokasi di Peta')
                            ->height('400px')
                            ->tileProvider('google')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Data Totalisator')
                    ->schema([
                        TextEntry::make('totalisator_awal')
                            ->label('Totalisator Awal')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),

                        TextEntry::make('totalisator_tiba')
                            ->label('Totalisator Tiba')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),

                        TextEntry::make('totalisator_pool_return')
                            ->label('Totalisator Kembali Pool')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),

                        // TextEntry::make('volume_terkirim')
                        //     ->label('Volume Terkirim')
                        //     ->suffix(' L')
                        //     ->placeholder('Belum Diisi')
                        //     ->color(fn($state) => $state ? 'success' : 'gray'),
                    ])
                    ->columns(3),

                Section::make('Timeline Pengiriman')
                    ->schema([
                        TextEntry::make('waktu_mulai')
                            ->label('Waktu Mulai')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum Mulai')
                            ->icon('heroicon-o-play'),

                        TextEntry::make('waktu_tiba')
                            ->label('Waktu Tiba')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum Tiba')
                            ->icon('heroicon-o-map-pin'),


                        TextEntry::make('waktu_pool_arrival')
                            ->label('Waktu Kembali Pool')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum Kembali')
                            ->icon('heroicon-o-home'),
                    ])
                    ->columns(3),

                Section::make('Dokumentasi')
                    ->schema([
                        ImageEntry::make('foto_pengiriman')
                            ->label('Foto Pengiriman')
                            ->placeholder('Belum Ada Foto'),

                        ImageEntry::make('foto_totalizer_awal')
                            ->label('Foto Totalizer Awal')
                            ->placeholder('Belum Ada Foto'),

                        ImageEntry::make('foto_totalizer_akhir')
                            ->label('Foto Totalizer Akhir')
                            ->placeholder('Belum Ada Foto'),
                    ])
                    ->columns(3),

                // action
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_do')
                ->label('Lihat Delivery Order')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->url(fn() => $this->record->deliveryOrder ?
                    route('filament.admin.resources.delivery-orders.view', ['record' => $this->record->deliveryOrder->id]) : null)
                ->visible(fn() => $this->record->deliveryOrder !== null)
                ->openUrlInNewTab(false),

            Actions\Action::make('view_timeline')
                ->label('Lihat Timeline')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->url(fn() => $this->record->deliveryOrder?->transaksi ?
                    "/admin/sales-order-timeline-detail?record={$this->record->deliveryOrder->transaksi->id}" : null)
                ->visible(fn() => $this->record->deliveryOrder?->transaksi !== null)
                ->openUrlInNewTab(false),

            Actions\Action::make('view_sales_order')
                ->label('Lihat Sales Order')
                ->icon('heroicon-o-shopping-cart')
                ->color('warning')
                ->url(fn() => $this->record->deliveryOrder?->transaksi ?
                    route('filament.admin.resources.transaksi-penjualans.view', ['record' => $this->record->deliveryOrder->transaksi->id]) : null)
                ->visible(fn() => $this->record->deliveryOrder?->transaksi !== null)
                ->openUrlInNewTab(false),

            Actions\EditAction::make()
                ->color('gray'),

            Actions\DeleteAction::make(),
        ];
    }
}
