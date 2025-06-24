<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryOrderResource\Pages;
use App\Filament\Resources\DeliveryOrderResource\RelationManagers;
use App\Models\DeliveryOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;


class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Delivery Order';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Delivery Order')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Nomor DO')
                            ->placeholder('DO-MMDDYYYY-0001')
                            ->required()
                            ->helperText('Contoh: DO-01012024-0001')
                            ->unique(ignoreRecord: true)

                            ->maxLength(50),

                        Forms\Components\Select::make('id_transaksi')
                            ->label('Nomor SO')
                            ->placeholder('Pilih Nomor SO')
                            ->relationship('transaksi', 'kode')
                            ->searchable()
                            ->helperText('Pilih nomor SO yang akan dijadwalkan pengirimannya')
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Auto-calculate remaining volume when SO changes
                                if ($state) {
                                    $transaksi = \App\Models\TransaksiPenjualan::find($state);
                                    if ($transaksi) {
                                        $totalSoVolume = $transaksi->penjualanDetails->sum('volume_item');
                                        $deliveredVolume = \App\Models\DeliveryOrder::where('id_transaksi', $state)
                                            ->where('id', '!=', $get('id') ?? 0)
                                            ->sum('volume_do');
                                        $remainingVolume = $totalSoVolume - $deliveredVolume;
                                        $set('sisa_volume_do', $remainingVolume);

                                        // Set default volume_do to remaining volume if not set
                                        if (!$get('volume_do')) {
                                            $set('volume_do', $remainingVolume);
                                        }
                                    }
                                }
                            })
                            ->default(function () {
                                // Autofill from URL parameter
                                return request()->query('id_transaksi', null);
                            })
                            ->required(),

                        Forms\Components\Select::make('id_user')
                            ->label('Supir')
                            ->required()
                            ->placeholder('Pilih Supir')
                            ->helperText('Pilih supir yang akan mengantar barang')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query) {
                                    $query->whereHas('jabatan', function ($query) {
                                        $query->where('nama', 'like', '%driver%');
                                    });
                                }
                            )
                            ->searchable()
                            ->preload(),

                        // !todo : tambah keterangan nama kendaraan
                        Forms\Components\Select::make('id_kendaraan')
                            ->label('Kendaraan')
                            ->required()
                            ->placeholder('Pilih Kendaraan')
                            ->helperText('Pilih kendaraan yang akan mengantar barang')
                            ->relationship('kendaraan', 'no_pol_kendaraan')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('volume_do')
                            ->label('Volume DO')
                            ->placeholder('Masukkan volume yang akan dikirim')
                            ->helperText('Volume barang yang akan dikirim dalam DO ini (dalam liter)')
                            ->numeric()
                            ->suffix('L')
                            ->reactive()
                            ->required()
                            ->minValue(0.01)
                            ->rules([
                                function (callable $get) {
                                    return function ($attribute, $value, \Closure $fail) use ($get) {
                                        $idTransaksi = $get('id_transaksi');
                                        if ($idTransaksi && $value) {
                                            $transaksi = \App\Models\TransaksiPenjualan::find($idTransaksi);
                                            if ($transaksi) {
                                                $totalSoVolume = $transaksi->penjualanDetails->sum('volume_item');
                                                $deliveredVolume = \App\Models\DeliveryOrder::where('id_transaksi', $idTransaksi)
                                                    ->where('id', '!=', $get('id') ?? 0)
                                                    ->sum('volume_do');
                                                $availableVolume = $totalSoVolume - $deliveredVolume;

                                                if ($value > $availableVolume) {
                                                    $fail("Volume DO tidak boleh melebihi sisa volume yang tersedia ({$availableVolume} L)");
                                                }
                                            }
                                        }
                                    };
                                },
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Auto-calculate remaining volume when volume_do changes
                                $idTransaksi = $get('id_transaksi');
                                if ($idTransaksi && $state) {
                                    $transaksi = \App\Models\TransaksiPenjualan::find($idTransaksi);
                                    if ($transaksi) {
                                        $totalSoVolume = $transaksi->penjualanDetails->sum('volume_item');
                                        $deliveredVolume = \App\Models\DeliveryOrder::where('id_transaksi', $idTransaksi)
                                            ->where('id', '!=', $get('id') ?? 0)
                                            ->sum('volume_do');
                                        $remainingVolume = $totalSoVolume - $deliveredVolume - $state;
                                        $set('sisa_volume_do', $remainingVolume);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('sisa_volume_do')
                            ->label('Sisa Volume DO')
                            ->placeholder('Sisa volume akan otomatis terhitung')
                            ->helperText('Sisa volume dari SO yang belum dikirim (otomatis terhitung)')
                            ->numeric()
                            ->suffix('L')
                            ->disabled()
                            ->dehydrated(),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Muat')
                    ->description('Informasi ini akan diisi oleh supir setelah barang dikirim')
                    ->schema([
                        Forms\Components\Select::make('status_muat')
                            ->label('Status Muat')
                            ->disabled()
                            ->helperText('Status ini akan diisi oleh supir setiap perubahan status muat')
                            ->options([
                                'pending' => 'Perintah Muat Diterbitkan',
                                'muat' => 'Muat Dikonfirmasi',
                                'selesai' => 'Muat Selesai',
                            ])
                            ->default('pending'),

                        Forms\Components\DateTimePicker::make('waktu_muat')
                            ->disabled()
                            ->helperText('Waktu mulai muat akan automatis terisi oleh supir setelah barang dikirim')
                            ->label('Waktu Mulai Muat'),

                        Forms\Components\DateTimePicker::make('waktu_selesai_muat')
                            ->disabled()
                            ->helperText('Waktu selesai muat akan automatis terisi oleh supir setelah barang dikirim')
                            ->label('Waktu Selesai Muat'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Administrasi')
                    ->schema([
                        Forms\Components\DateTimePicker::make('tanggal_delivery')
                            ->label('Tanggal Pengiriman')
                            ->helperText('Pilih tanggal pengiriman barang'),

                        Forms\Components\TextInput::make('no_segel')
                            ->label('Nomor Segel')
                            ->placeholder('Contoh: SGL-000001')
                            ->helperText('Nomor segel untuk penandaan barang, dapat diisi setelah barang dikirim')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('do_signatory_name')
                            ->placeholder('Nama Penandatangan DO')
                            ->helperText('Nama penandatangan DO akan otomatis terisi oleh supir setelah barang dikirim')
                            ->label('Nama Penandatangan DO'),

                        Forms\Components\Toggle::make('do_print_status')
                            ->helperText('Status cetak DO akan otomatis terisi setelah DO dicetak')
                            ->label('Status Cetak DO'),



                        Forms\Components\Toggle::make('allowance_receipt_status')
                            ->label('Status Penerimaan Uang Jalan'),
                    ])
                    ->columns(2),
                // Forms\Components\Section::make('Informasi Uang Jalan')
                //     ->schema([
                //         Forms\Components\Fieldset::make('Existing Allowance')
                //             ->schema([
                //                 Forms\Components\TextInput::make('uangJalan.nominal')
                //                     ->label('Allowance Amount')
                //                     ->numeric()
                //                     ->prefix('IDR')
                //                     ->minValue(0),

                //                 Forms\Components\Toggle::make('uangJalan.status_kirim')
                //                     ->label('Sending Status')
                //                     ->helperText('Toggle on if allowance has been sent to driver'),

                //                 Forms\Components\DatePicker::make('uangJalan.tanggal_kirim')
                //                     ->label('Sending Date'),

                //                 Forms\Components\Toggle::make('uangJalan.status_terima')
                //                     ->label('Receipt Status')
                //                     ->helperText('Toggle on if allowance receipt has been confirmed'),

                //                 Forms\Components\DatePicker::make('uangJalan.tanggal_terima')
                //                     ->label('Receipt Date'),

                //                 Forms\Components\FileUpload::make('uangJalan.bukti_kirim')
                //                     ->label('Sending Proof')
                //                     ->directory('allowance-proofs')
                //                     ->visibility('private')
                //                     ->downloadable(),

                //                 Forms\Components\FileUpload::make('uangJalan.bukti_terima')
                //                     ->label('Receipt Proof')
                //                     ->directory('allowance-proofs')
                //                     ->visibility('private')
                //                     ->downloadable(),
                //             ])
                //             ->columns(2)
                //             ->visible(fn($record) => $record && $record->uangJalan),

                //         Forms\Components\Placeholder::make('create_allowance_placeholder')
                //             ->label('No Driver Allowance Record')
                //             ->content('Save this delivery order first, then you can create a driver allowance record.')
                //             ->visible(fn($record) => $record && !$record->uangJalan),
                //     ])
                //     ->collapsible(),

                // Forms\Components\Section::make('Driver Delivery Information')
                //     ->schema([
                //         Forms\Components\Fieldset::make('Delivery Progress')
                //             ->schema([
                //                 Forms\Components\DateTimePicker::make('pengirimanDriver.waktu_mulai')
                //                     ->label('Departure Time'),

                //                 Forms\Components\DateTimePicker::make('pengirimanDriver.waktu_tiba')
                //                     ->label('Arrival Time'),

                //                 Forms\Components\DateTimePicker::make('pengirimanDriver.waktu_pool_arrival')
                //                     ->label('Pool Arrival Time'),


                //             ])
                //             ->columns(2)
                //             ->visible(fn($record) => $record && $record->pengirimanDriver),

                //         Forms\Components\Placeholder::make('create_delivery_placeholder')
                //             ->label('No Driver Delivery Record')
                //             ->content('Save this delivery order first, then you can create a driver delivery record.')
                //             ->visible(fn($record) => $record && !$record->pengirimanDriver),
                //     ])
                //     ->collapsible(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Nomor DO')

                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('transaksi.kode')
                    ->label('Nomor SO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaksi.pelanggan.nama')
                    ->label('Pelanggan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sopir')
                    ->searchable()
                    ->placeholder('Belum Ditugaskan'),

                Tables\Columns\TextColumn::make('kendaraan.no_pol_kendaraan')
                    ->label('Kendaraan')
                    ->searchable()
                    ->placeholder('Belum Ditugaskan'),

                Tables\Columns\TextColumn::make('volume_do')
                    ->label('Volume DO')
                    ->numeric()
                    ->suffix(' L')
                    ->placeholder('Belum Diisi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_volume_do')
                    ->label('Sisa Volume')
                    ->numeric()
                    ->suffix(' L')
                    ->placeholder('Belum Dihitung')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_muat')
                    ->label('Status Muat')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'muat' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Perintah Muat Diterbitkan',
                        'muat' => 'Muat Dikonfirmasi',
                        'selesai' => 'Muat Selesai',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_delivery')
                    ->label('Tanggal Pengiriman')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('do_print_status')
                    ->label('DO Dicetak')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Tertunda',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'overdue' => 'Terlambat',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_muat')
                    ->label('Loading Status')
                    ->options([
                        'pending' => 'Load Order Issued',
                        'muat' => 'Load Confirmed',
                        'selesai' => 'Loading Complete',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                    ]),

                Tables\Filters\TernaryFilter::make('do_print_status')
                    ->label('DO Print Status')
                    ->placeholder('All')
                    ->trueLabel('Printed')
                    ->falseLabel('Not Printed'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('Download PDF')
                    ->color('success')
                    ->action(function (DeliveryOrder $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadHtml(
                                Blade::render('pdf', ['record' => $record])
                            )->stream();
                        }, $record->number . '.pdf');
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_delivery', 'desc');
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
            'index' => Pages\ListDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'view' => Pages\ViewDeliveryOrder::route('/{record}'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
