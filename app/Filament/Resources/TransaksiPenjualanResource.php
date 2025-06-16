<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiPenjualanResource\Pages;
use App\Filament\Resources\TransaksiPenjualanResource\RelationManagers;
use App\Models\TransaksiPenjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class TransaksiPenjualanResource extends Resource
{
    protected static ?string $model = TransaksiPenjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Pesanan Penjualan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan Penjualan')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Nomor SO')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        // media upload sales order
                        SpatieMediaLibraryFileUpload::make('dokumen_so')
                            ->label('Dokumen SO')
                            ->collection('dokumen_so')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png'
                            ])
                            ->maxSize(10240),

                        // Forms\Components\Select::make('tipe')
                        //     ->label('Tipe')
                        //     ->options([
                        //         'dagang' => 'Dagang',
                        //         'jasa' => 'Jasa',
                        //     ])
                        //     ->required(),

                        Forms\Components\DateTimePicker::make('tanggal')
                            ->label('Tanggal Pesanan')
                            ->required()
                            ->default(now()),



                        Forms\Components\Select::make('id_tbbm')
                            ->label('Lokasi TBBM')
                            ->relationship('tbbm', 'nama')
                            ->searchable()
                            ->required()
                            ->preload(),

                        Forms\Components\TextInput::make('nomor_po')
                            ->label('Nomor PO')
                            ->required()
                            ->maxLength(50),

                        // media upload sales order
                        SpatieMediaLibraryFileUpload::make('dokumen_po')
                            ->label('Dokumen PO')
                            ->collection('dokumen_po')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png'
                            ])
                            ->maxSize(10240),
                        Forms\Components\TextInput::make('nomor_sph')
                            ->label('Nomor SPH')
                            ->required()
                            ->maxLength(50),

                        // media upload sales order
                        SpatieMediaLibraryFileUpload::make('dokumen_sph')
                            ->label('Dokumen SPH')
                            ->collection('dokumen_sph')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg',
                                'image/png'
                            ])
                            ->maxSize(10240),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pengiriman')
                    ->schema([
                        // Forms\Components\Select::make('id_subdistrict')
                        //     ->label('Kelurahan')
                        //     ->relationship('subdistrict', 'name')
                        //     ->searchable()
                        //     ->preload(),

                        Forms\Components\Select::make('id_pelanggan')
                            ->label('Pelanggan')
                            ->relationship('pelanggan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('id_alamat_pelanggan')
                            ->label('Alamat Pengiriman')
                            ->required()
                            ->relationship(
                                'alamatPelanggan',
                                'alamat',
                                fn(Builder $query, $get) =>
                                $query->where('id_pelanggan', $get('id_pelanggan'))
                            )
                            ->searchable()
                            ->preload()
                            ->disabled(fn($get) => !$get('id_pelanggan')),





                        Forms\Components\TextInput::make('data_dp')
                            ->label('Data DP')
                            ->numeric()
                            ->prefix('IDR')
                            ->step(0.01),

                        Forms\Components\TextInput::make('top_pembayaran')
                            ->label('Termin Pembayaran (Hari)')
                            ->numeric()
                            ->suffix('hari'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Item Penjualan')
                    ->description('Tambahkan item-item yang akan dijual dalam transaksi ini')
                    ->schema([
                        Forms\Components\Repeater::make('penjualanDetails')
                            ->label('Item Penjualan')
                            ->relationship()
                            ->required()
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Select::make('id_item')
                                    ->label('Item/Produk')
                                    ->relationship('item', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $item = \App\Models\Item::find($state);
                                            if ($item) {
                                                // Set item info for display
                                                $set('item_info', $item->kode . ' - ' . $item->name);
                                                $set('satuan_info', $item->satuan?->nama ?? '');
                                            }
                                        }
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('volume_item')
                                    ->label('Volume/Kuantitas')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $volume = floatval($state ?: 0);
                                        $harga = floatval($get('harga_jual') ?: 0);
                                        $subtotal = $volume * $harga;
                                        $set('subtotal', $subtotal);
                                    })
                                    ->suffix(function (callable $get) {
                                        return $get('satuan_info') ?: 'unit';
                                    }),

                                Forms\Components\TextInput::make('harga_jual')
                                    ->label('Harga Jual')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('IDR')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $harga = floatval($state ?: 0);
                                        $volume = floatval($get('volume_item') ?: 0);
                                        $subtotal = $volume * $harga;
                                        $set('subtotal', $subtotal);
                                    }),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('IDR')
                                    ->numeric()
                                    ->formatStateUsing(function ($state) {
                                        return number_format($state ?: 0, 0, ',', '.');
                                    }),

                                Forms\Components\Hidden::make('item_info'),
                                Forms\Components\Hidden::make('satuan_info'),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->deleteAction(
                                fn($action) => $action->requiresConfirmation()
                            )
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (!empty($state['item_info'])) {
                                    $volume = floatval($state['volume_item'] ?? 0);
                                    $harga = floatval($state['harga_jual'] ?? 0);
                                    $subtotal = number_format($volume * $harga, 0, ',', '.');
                                    return "{$state['item_info']} - {$volume} x IDR " . number_format($harga, 0, ',', '.') . " = IDR {$subtotal}";
                                }
                                return 'Item Baru';
                            }),
                    ])
                    ->collapsible(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Nomor SO')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'dagang' => 'success',
                        'jasa' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal Pesanan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_po')
                    ->label('Nomor PO')
                    ->searchable()
                    ->placeholder('T/A'),

                Tables\Columns\TextColumn::make('nomor_sph')
                    ->label('Nomor SPH')
                    ->searchable()
                    ->placeholder('T/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('data_dp')
                    ->label('Data DP')
                    ->money('IDR')
                    ->placeholder('T/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('top_pembayaran')
                    ->label('Termin Pembayaran')
                    ->formatStateUsing(fn($state) => $state ? "{$state} hari" : 'Tunai')
                    ->badge()
                    ->color(fn($state) => $state > 30 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('tbbm.nama')
                    ->label('Lokasi TBBM')
                    ->placeholder('T/A'),

                Tables\Columns\TextColumn::make('penjualan_details_count')
                    ->label('Jumlah Item')
                    ->counts('penjualanDetails')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('has_dokumen_sph')
                    ->label('Dokumen SPH')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->getMedia('dokumen_sph')->count() > 0)
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->getMedia('dokumen_sph')->count() > 0
                        ? $record->getMedia('dokumen_sph')->count() . ' dokumen SPH'
                        : 'Tidak ada dokumen SPH'),

                Tables\Columns\IconColumn::make('has_dokumen_dp')
                    ->label('Dokumen DP')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->getMedia('dokumen_dp')->count() > 0)
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->getMedia('dokumen_dp')->count() > 0
                        ? $record->getMedia('dokumen_dp')->count() . ' dokumen DP'
                        : 'Tidak ada dokumen DP'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->label('Tipe')
                    ->options([
                        'dagang' => 'Dagang',
                        'jasa' => 'Jasa',
                    ]),

                Tables\Filters\SelectFilter::make('id_pelanggan')
                    ->label('Pelanggan')
                    ->relationship('pelanggan', 'nama')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('id_tbbm')
                    ->label('Lokasi TBBM')
                    ->relationship('tbbm', 'nama')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('has_dokumen_sph')
                    ->label('Memiliki Dokumen SPH')
                    ->placeholder('Semua Data')
                    ->trueLabel('Dengan Dokumen SPH')
                    ->falseLabel('Tanpa Dokumen SPH')
                    ->queries(
                        true: fn($query) => $query->whereHas('media', function ($q) {
                            $q->where('collection_name', 'dokumen_sph');
                        }),
                        false: fn($query) => $query->whereDoesntHave('media', function ($q) {
                            $q->where('collection_name', 'dokumen_sph');
                        }),
                    ),

                Tables\Filters\TernaryFilter::make('has_dokumen_dp')
                    ->label('Memiliki Dokumen DP')
                    ->placeholder('Semua Data')
                    ->trueLabel('Dengan Dokumen DP')
                    ->falseLabel('Tanpa Dokumen DP')
                    ->queries(
                        true: fn($query) => $query->whereHas('media', function ($q) {
                            $q->where('collection_name', 'dokumen_dp');
                        }),
                        false: fn($query) => $query->whereDoesntHave('media', function ($q) {
                            $q->where('collection_name', 'dokumen_dp');
                        }),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // lihat timeline
                Tables\Actions\Action::make('view_timeline')
                    ->label('Lihat Timeline')
                    ->icon('heroicon-o-clock')
                    ->url(fn(TransaksiPenjualan $record): string => "/admin/sales-order-timeline-detail?record={$record->id}")
                    ->openUrlInNewTab(false),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
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
            'index' => Pages\ListTransaksiPenjualans::route('/'),
            'create' => Pages\CreateTransaksiPenjualan::route('/create'),
            'view' => Pages\ViewTransaksiPenjualan::route('/{record}'),
            'edit' => Pages\EditTransaksiPenjualan::route('/{record}/edit'),
        ];
    }
}
