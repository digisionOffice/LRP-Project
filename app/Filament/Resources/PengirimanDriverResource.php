<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengirimanDriverResource\Pages;
use App\Models\PengirimanDriver;
use App\Models\DeliveryOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Afsakar\LeafletMapPicker\LeafletMapPicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class PengirimanDriverResource extends Resource
{
    protected static ?string $model = PengirimanDriver::class;

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Status Pengiriman';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Delivery Order')
                    ->schema([
                        Forms\Components\Select::make('id_do')
                            ->label('Delivery Order')
                            ->relationship('deliveryOrder', 'kode')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function () {
                                // Autofill from URL parameter
                                return request()->query('id_do', null);
                            })
                            ->helperText('Pilih Delivery Order yang akan diproses'),

                        Forms\Components\Placeholder::make('delivery_info')
                            ->label('Info DO')
                            ->content(function ($get) {
                                $doId = $get('id_do');
                                if (!$doId) return 'Pilih DO terlebih dahulu';

                                $do = DeliveryOrder::with(['transaksi.pelanggan', 'user', 'kendaraan'])
                                    ->find($doId);

                                if (!$do) return 'DO tidak ditemukan';

                                return "Pelanggan: " . ($do->transaksi->pelanggan->nama ?? 'N/A') .
                                    " | Sopir: " . ($do->user->name ?? 'N/A') .
                                    " | Kendaraan: " . ($do->kendaraan->no_pol_kendaraan ?? 'N/A');
                            }),

                        // map
                        LeafletMapPicker::make('deliveryOrder.transaksi.alamatPelanggan.location')
                            ->label('Lokasi di Peta')
                            ->height('400px')
                            ->defaultLocation([0.5394419,101.451907]) // lrp
                            ->defaultZoom(15)
                            ->draggable(true)
                            ->clickable(true)
                            ->myLocationButtonLabel('Lokasi Saya')
                            ->tileProvider('openstreetmap')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),



                Forms\Components\Section::make('Data Totalisator')
                    ->schema([
                        Forms\Components\TextInput::make('totalisator_awal')
                            ->label('Totalisator Awal')
                            ->numeric()
                            ->suffix('L')
                            ->helperText('Volume awal pada totalisator'),

                        Forms\Components\TextInput::make('totalisator_tiba')
                            ->label('Totalisator Tiba')
                            ->numeric()
                            ->suffix('L')
                            ->helperText('Volume saat tiba di tujuan'),

                        Forms\Components\TextInput::make('totalisator_pool_return')
                            ->label('Totalisator Kembali Pool')
                            ->numeric()
                            ->suffix('L')
                            ->helperText('Volume saat kembali ke pool'),

                        // Forms\Components\TextInput::make('volume_terkirim')
                        //     ->label('Volume Terkirim')
                        //     ->numeric()
                        //     ->suffix('L')
                        //     ->helperText('Volume yang berhasil dikirim'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Waktu Pengiriman')
                    ->schema([
                        Forms\Components\DateTimePicker::make('waktu_mulai')
                            ->label('Waktu Mulai')
                            ->helperText('Waktu mulai persiapan pengiriman'),

                        Forms\Components\DateTimePicker::make('waktu_tiba')
                            ->label('Waktu Tiba')
                            ->helperText('Waktu tiba di lokasi tujuan'),

                        Forms\Components\DateTimePicker::make('waktu_pool_arrival')
                            ->label('Waktu Kembali Pool')
                            ->helperText('Waktu kembali ke pool'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Dokumentasi')
                    ->schema([
                        // Forms\Components\FileUpload::make('foto_pengiriman')
                        //     ->label('Foto Pengiriman')
                        //     ->image()
                        //     ->multiple()
                        //     ->helperText('Upload foto dokumentasi pengiriman'),

                        // Forms\Components\FileUpload::make('foto_totalizer_awal')
                        //     ->label('Foto Totalizer Awal')
                        //     ->image()
                        //     ->helperText('Foto totalizer sebelum berangkat'),

                        // Forms\Components\FileUpload::make('foto_totalizer_akhir')
                        //     ->label('Foto Totalizer Akhir')
                        //     ->image()
                        //     ->helperText('Foto totalizer setelah pengiriman'),
                        // media
                        SpatieMediaLibraryFileUpload::make('foto_pengiriman')
                            ->label('Foto Pengiriman')
                            ->multiple()
                            ->image()
                            ->collection('foto_pengiriman')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->helperText('Upload foto dokumentasi pengiriman'),

                        SpatieMediaLibraryFileUpload::make('foto_totalizer_awal')
                            ->label('Foto Totalizer Awal')
                            ->collection('foto_totalizer_awal')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->helperText('Upload foto totalizer sebelum berangkat'),

                        SpatieMediaLibraryFileUpload::make('foto_totalizer_akhir')
                            ->label('Foto Totalizer Akhir')
                            ->collection('foto_totalizer_akhir')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->helperText('Upload foto totalizer setelah pengiriman'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                PengirimanDriver::query()
                    ->with([
                        'deliveryOrder.transaksi.pelanggan',
                        'deliveryOrder.user',
                        'deliveryOrder.kendaraan',
                        'createdBy'
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('deliveryOrder.kode')
                    ->label('Nomor DO')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('deliveryOrder.transaksi.pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->deliveryOrder?->transaksi?->pelanggan?->nama;
                    }),

                Tables\Columns\TextColumn::make('deliveryOrder.user.name')
                    ->label('Sopir')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->placeholder('Belum Ditugaskan'),

                Tables\Columns\TextColumn::make('deliveryOrder.kendaraan.no_pol_kendaraan')
                    ->label('Kendaraan')
                    ->searchable()
                    ->icon('heroicon-o-truck')
                    ->placeholder('Belum Ditugaskan'),

                Tables\Columns\TextColumn::make('delivery_status')
                    ->label('Status Pengiriman')
                    ->getStateUsing(function ($record) {
                        if ($record->waktu_selesai) return 'Selesai';
                        if ($record->waktu_tiba) return 'Tiba';
                        if ($record->waktu_berangkat) return 'Berangkat';
                        if ($record->waktu_mulai) return 'Mulai';
                        return 'Belum Mulai';
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'Belum Mulai' => 'gray',
                            'Mulai' => 'blue',
                            'Berangkat' => 'orange',
                            'Tiba' => 'teal',
                            'Selesai' => 'green',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($state) {
                        return match ($state) {
                            'Belum Mulai' => 'heroicon-o-clock',
                            'Mulai' => 'heroicon-o-play',
                            'Berangkat' => 'heroicon-o-arrow-right',
                            'Tiba' => 'heroicon-o-map-pin',
                            'Selesai' => 'heroicon-o-check-badge',
                            default => 'heroicon-o-clock',
                        };
                    }),

                // Tables\Columns\TextColumn::make('volume_terkirim')
                //     ->label('Volume Terkirim')
                //     ->numeric(decimalPlaces: 2)
                //     ->suffix(' L')
                //     ->sortable()
                //     ->placeholder('Belum Diisi')
                //     ->color(fn($state) => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('waktu_mulai')
                    ->label('Waktu Berangkat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum Berangkat')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('waktu_tiba')
                    ->label('Waktu Tiba')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum Tiba')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('waktu_pool_arrival')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum Selesai')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_status')
                    ->label('Status Pengiriman')
                    ->options([
                        'belum_mulai' => 'Belum Mulai',
                        'mulai' => 'Mulai',
                        'berangkat' => 'Berangkat',
                        'tiba' => 'Tiba',
                        'selesai' => 'Selesai',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $status): Builder {
                                return match ($status) {
                                    'belum_mulai' => $query->whereNull('waktu_mulai'),
                                    'mulai' => $query->whereNotNull('waktu_mulai')->whereNull('waktu_berangkat'),
                                    'berangkat' => $query->whereNotNull('waktu_berangkat')->whereNull('waktu_tiba'),
                                    'tiba' => $query->whereNotNull('waktu_tiba')->whereNull('waktu_selesai'),
                                    'selesai' => $query->whereNotNull('waktu_selesai'),
                                    default => $query,
                                };
                            }
                        );
                    }),

                // Tables\Filters\Filter::make('has_volume')
                //     ->label('Sudah Ada Volume Terkirim')
                //     ->query(fn(Builder $query): Builder => $query->whereNotNull('volume_terkirim')),

                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),

                Tables\Actions\Action::make('view_do')
                    ->label('Lihat DO')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn($record) => $record->deliveryOrder ?
                        route('filament.admin.resources.delivery-orders.view', ['record' => $record->deliveryOrder->id]) : null)
                    ->visible(fn($record) => $record->deliveryOrder !== null)
                    ->openUrlInNewTab(false),

                Tables\Actions\Action::make('view_timeline')
                    ->label('Lihat Timeline')
                    ->icon('heroicon-o-clock')
                    ->color('success')
                    ->url(fn($record) => $record->deliveryOrder?->transaksi ?
                        "/admin/sales-order-timeline-detail?record={$record->deliveryOrder->transaksi->id}" : null)
                    ->visible(fn($record) => $record->deliveryOrder?->transaksi !== null)
                    ->openUrlInNewTab(false),

                Tables\Actions\Action::make('view_sales_order')
                    ->label('Lihat SO')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->url(fn($record) => $record->deliveryOrder?->transaksi ?
                        route('filament.admin.resources.transaksi-penjualans.view', ['record' => $record->deliveryOrder->transaksi->id]) : null)
                    ->visible(fn($record) => $record->deliveryOrder?->transaksi !== null)
                    ->openUrlInNewTab(false),

                Tables\Actions\EditAction::make()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengirimanDrivers::route('/'),
            'create' => Pages\CreatePengirimanDriver::route('/create'),
            'view' => Pages\ViewPengirimanDriver::route('/{record}'),
            'edit' => Pages\EditPengirimanDriver::route('/{record}/edit'),
        ];
    }
}
