<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Placeholder;
use Afsakar\LeafletMapPicker\LeafletMapPickerEntry;

class ViewDeliveryOrder extends ViewRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // semua section harus ditampilkan mas bro
                Section::make('Informasi Delivery Order')
                    ->schema([
                        TextEntry::make('kode')
                            ->icon('heroicon-o-document-text')
                            ->color('primary')
                            ->weight('bold')
                            ->label('Nomor DO'),
                        TextEntry::make('transaksi.kode')
                            ->label('Nomor SO'),

                        TextEntry::make('transaksi.created_at')
                            ->label('Tanggal SO')
                            ->date(),


                        TextEntry::make('kendaraan.no_pol_kendaraan')
                            ->icon('heroicon-o-truck')
                            ->label('Plat Nomor Truk'),

                        TextEntry::make('user.name')
                            ->icon('heroicon-o-user')
                            ->label('Sopir'),

                        TextEntry::make('tanggal_delivery')
                            ->icon('heroicon-o-calendar')
                            ->label('Tanggal Pengiriman')
                            ->datetime(),

                        TextEntry::make('transaksi.pelanggan.nama')
                            ->icon('heroicon-o-building-office')
                            ->label('Nama Pelanggan'),

                        TextEntry::make('transaksi.alamatPelanggan.alamat')
                            ->icon('heroicon-o-map-pin')
                            ->label('Alamat Pelanggan'),

                        // use leafleat to show the map
                        LeafletMapPickerEntry::make('transaksi.alamatPelanggan.location')
                            ->label('Lokasi di Peta')
                            ->height('400px')
                            ->tileProvider('google')
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->collapsible(),
                // informasi so

                // informasi uang jalan
                Section::make('Informasi Uang Jalan')
                    ->schema([
                        TextEntry::make('uangJalan.nominal')
                            ->label('Jumlah Uang Jalan')
                            ->money('IDR')
                            ->placeholder('No allowance record'),

                        IconEntry::make('uangJalan.status_kirim')
                            ->label('Status Pengiriman')
                            ->boolean()
                            ->placeholder('Not recorded'),

                        TextEntry::make('uangJalan.tanggal_kirim')
                            ->label('Tanggal Pengiriman')
                            ->date()
                            ->placeholder('Not recorded'),

                        IconEntry::make('uangJalan.status_terima')
                            ->label('Status Penerimaan')
                            ->boolean()
                            ->placeholder('Not recorded'),

                        TextEntry::make('uangJalan.tanggal_terima')
                            ->label('Tanggal Penerimaan')
                            ->date()
                            ->placeholder('Not recorded'),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->uangJalan)
                    ->collapsible(),

                // informasi muat
                Section::make('Informasi Muat')
                    ->schema([
                        TextEntry::make('waktu_muat')
                            ->label('Waktu Mulai Muat')
                            ->dateTime()
                            ->placeholder('Not recorded'),

                        TextEntry::make('waktu_selesai_muat')
                            ->label('Waktu Selesai Muat')
                            ->dateTime()
                            ->placeholder('Not recorded'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // informasi pengiriman
                Section::make('Informasi Pengiriman Driver')
                    ->schema([
                        TextEntry::make('pengirimanDriver.waktu_mulai')
                            ->label('Waktu Mulai')
                            ->dateTime()
                            ->placeholder('Not recorded'),
                        TextEntry::make('pengirimanDriver.waktu_tiba')
                            ->label('Waktu Tiba')
                            ->dateTime()
                            ->placeholder('Not recorded'),

                        TextEntry::make('pengirimanDriver.waktu_pool_arrival')
                            ->label('Waktu Kembali Pool')
                            ->dateTime()
                            ->placeholder('Not recorded'),

                        // informasi totalisator
                        TextEntry::make('pengirimanDriver.totalisator_awal')
                            ->label('Totalisator Awal')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),

                        TextEntry::make('pengirimanDriver.totalisator_tiba')
                            ->label('Totalisator Tiba')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),

                            TextEntry::make('pengirimanDriver.totalisator_pool_return')
                            ->label('Totalisator Kembali Pool')
                            ->suffix(' L')
                            ->placeholder('Belum Diisi'),


                        // TextEntry::make('pengirimanDriver.catatan_pengiriman')
                        //     ->label('Catatan Pengiriman')
                        //     ->placeholder('No notes')
                        //     ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            //lihat sales order
            Actions\Action::make('view_so')
                ->label('Lihat SO')
                ->icon('heroicon-o-document-text')
                ->url(fn($record) => $record->transaksi ? route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record->transaksi]) : null)
                ->visible(fn($record) => $record->transaksi !== null)
                ->openUrlInNewTab(false),
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->action(function ($record) {
                    try {
                        // Generate dynamic filename
                        $filename = 'delivery_order_' . $record->id . '_' . now()->format('Ymd_His') . '.pdf';

                        // Load the PDF view with the record data
                        $pdf = Pdf::loadView('pdf.delivery_order', ['record' => $record])
                            ->setPaper('a4', 'portrait');

                        // Stream the PDF as a download
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    } catch (\Exception $e) {
                        // Log the error for debugging
                        Log::error('Failed to generate PDF: ' . $e->getMessage());

                        // Notify the user of the error
                        // $this->notify('error', 'Failed to generate PDF. Please try again.');
                        return;
                    }
                }),
            Actions\Action::make('createAllowance')
                ->label('Create Driver Allowance')
                ->icon('heroicon-o-banknotes')
                ->url(fn($record) => route('filament.admin.resources.uang-jalans.create', ['id_do' => $record->id]))
                ->visible(fn($record) => !$record->uangJalan),
            Actions\Action::make('editAllowance')
                ->label('Edit Driver Allowance')
                ->icon('heroicon-o-banknotes')
                ->url(fn($record) => $record->uangJalan ? route('filament.admin.resources.uang-jalans.edit', ['record' => $record->uangJalan->id]) : null)
                ->visible(fn($record) => $record->uangJalan),
            Actions\Action::make('createDelivery')
                ->label('Create Driver Delivery')
                ->icon('heroicon-o-truck')
                ->url(fn($record) => route('filament.admin.resources.pengiriman-drivers.create', ['id_do' => $record->id]))
                ->visible(fn($record) => !$record->pengirimanDriver),
            // view
            Actions\Action::make('viewDelivery')
                ->label('View Driver Delivery')
                ->icon('heroicon-o-truck')
                ->url(fn($record) => $record->pengirimanDriver ? route('filament.admin.resources.pengiriman-drivers.view', ['record' => $record->pengirimanDriver->id]) : null)
                ->visible(fn($record) => $record->pengirimanDriver)
            // Actions\Action::make('editDelivery')
            //     ->label('Edit Driver Delivery')
            //     ->icon('heroicon-o-truck')
            //     ->url(fn($record) => $record->pengirimanDriver ? route('filament.admin.resources.pengiriman-drivers.edit', ['record' => $record->pengirimanDriver->id]) : null)
            //     ->visible(fn($record) => $record->pengirimanDriver),
        ];
    }
}
