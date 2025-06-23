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
use App\Services\TransaksiPenjualanService; 
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\RepeatableEntry;


class ViewTransaksiPenjualan extends ViewRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Transaksi Penjualan')
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
                            ->label('Alamat Invoice')
                            ->icon('heroicon-o-map-pin'),
                        // TextEntry::make('data_dp')
                        //     ->label('Data DP')
                        //     ->money('IDR')
                        //     ->icon('heroicon-o-currency-dollar'),
                        TextEntry::make('top_pembayaran')
                            ->label('Termin Pembayaran')
                            ->formatStateUsing(fn($state) => $state ? "{$state} hari" : 'Tunai')
                            ->badge()
                            ->color(fn($state) => $state > 30 ? 'warning' : 'success'),

                            // masukan detail penjualan
                            // TextEntry::make('penjualanDetails.item.name')
                            // ->badge()
                            // ->color('info')
                            // ->formatStateUsing(function ($record) {
                            //     return $record->penjualanDetails->pluck('item.name')->unique()->join(', ');
                            // })
                            // ->label('Jenis BBM'),

                            // TextEntry::make('penjualanDetails.volume_item')
                            // ->getStateUsing(function ($record) {
                            //     return $record->penjualanDetails->sum('volume_item');
                            // })
                            // ->label('Jumlah BBM')
                            // ->numeric(decimalPlaces: 2)
                            // ->suffix(' Liter'),

                        // use leafleat to show the map
                        // LeafletMapPickerEntry::make('alamatPelanggan.location')
                        //     ->label('Lokasi di Peta')
                        //     ->height('400px')
                        //     ->tileProvider('google')
                        //     ->columnSpanFull(),
                    ])
                    ->columns(2),

                    // informasi detail repeater transaksi per item, use repeatableentry
                    Section::make('Detail Transaksi')
                    ->schema([
                        RepeatableEntry::make('penjualanDetails')
                            ->label('Item Penjualan')
                            ->schema([
                                TextEntry::make('item.name')
                                    ->label('Item/Produk'),
                                TextEntry::make('volume_item')
                                    ->label('Volume')
                                    ->numeric(decimalPlaces: 2)
                                    ->suffix(' Liter'),
                                TextEntry::make('harga_jual')
                                    ->label('Harga')
                                    ->money('IDR'),
                            ])
                            ->columns(3),

                    ])
                    ->columns(1),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // approval action
            Actions\Action::make('approval')
                ->label('Proses Approval')
                ->color('primary')
                ->icon('heroicon-o-check-badge')
                ->form([
                    Radio::make('status')
                        ->label('Status Approval')
                        ->options([
                            'approved' => 'Approved (Disetujui)',
                            'reject_with_perbaikan' => 'Reject with Revision (Tolak dengan Perbaikan)',
                            'rejected' => 'Rejected (Ditolak Final)',
                        ])
                        ->required()
                        ->live(),
                    Textarea::make('note')
                        ->label('Catatan / Alasan')
                        // Required only if status is a type of rejection
                        ->required(fn (Get $get) => in_array($get('status'), ['rejected', 'reject_with_perbaikan']))
                        // Visible only if status is a type of rejection
                        ->visible(fn (Get $get) => in_array($get('status'), ['rejected', 'reject_with_perbaikan'])),
                ])
                // 2. Service class and variable name updated here
                ->action(function (TransaksiPenjualan $record, array $data, TransaksiPenjualanService $penjualanService) {
                    // The action is simple: just call the service.
                    $penjualanService->processApproval(
                        $record,
                        auth()->user(),
                        $data['status'],
                        // 3. IMPORTANT: Use null coalescing operator to prevent errors when 'note' is not present.
                        $data['note'] ?? null 
                    );

                    Notification::make()
                        ->title('Proses approval berhasil disimpan')
                        ->success()
                        ->send();
                }),
                // Optional: Only show this button if the record needs approval
                // ->visible(fn (TransaksiPenjualan $record) => $record->status === 'pending_approval'),

            //  lihat timeline, buat do kalau belum ada
            // modal acc untuk transaksi, batal atau terima
            // Actions\Action::make('accept')
            //     ->label('Terima SO')
            //     ->icon('heroicon-o-check')
            //     ->color('success')
            //     ->action(function (TransaksiPenjualan $record) {
            //         $record->update(['status' => 'Accepted']);
            //     })
            //     // visible if status is pending, visible ketika jabatan manager dan divisi sales
            //     ->visible(fn(TransaksiPenjualan $record) => $record->status === 'Pending' && auth()->user()->hasRole('Manager')),
            
            Actions\Action::make('reject')
                ->label('Tolak SO'),
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
        return 'Detail Transaksi';
    }

    public function getSubheading(): ?string
    {
        return 'Lihat detail transaksi penjualan';
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin' => 'Home',
            '/admin/transaksi-penjualans' => 'Transaksi Penjualan',
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
