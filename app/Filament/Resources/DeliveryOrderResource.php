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

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Surat Perintah Muat';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Surat Perintah Muat')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Nomor SPM')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Forms\Components\Select::make('id_transaksi')
                            ->label('Pesanan Penjualan')
                            ->relationship('transaksi', 'kode')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('id_user')
                            ->label('Sopir')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('id_kendaraan')
                            ->label('Kendaraan')
                            ->relationship('kendaraan', 'no_pol_kendaraan')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('tanggal_delivery')
                            ->label('Tanggal Pengiriman'),

                        Forms\Components\TextInput::make('no_segel')
                            ->label('Nomor Segel')
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Muat')
                    ->schema([
                        Forms\Components\Select::make('status_muat')
                            ->label('Status Muat')
                            ->options([
                                'pending' => 'Perintah Muat Diterbitkan',
                                'muat' => 'Muat Dikonfirmasi',
                                'selesai' => 'Muat Selesai',
                            ])
                            ->default('pending'),

                        Forms\Components\DateTimePicker::make('waktu_muat')
                            ->label('Waktu Mulai Muat'),

                        Forms\Components\DateTimePicker::make('waktu_selesai_muat')
                            ->label('Waktu Selesai Muat'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Administrasi')
                    ->schema([
                        Forms\Components\TextInput::make('do_signatory_name')
                            ->label('Nama Penandatangan SPM'),

                        Forms\Components\Toggle::make('do_print_status')
                            ->label('Status Cetak SPM'),

                        Forms\Components\TextInput::make('driver_allowance_amount')
                            ->label('Jumlah Uang Jalan Sopir')
                            ->numeric()
                            ->prefix('IDR'),

                        Forms\Components\Toggle::make('allowance_receipt_status')
                            ->label('Status Penerimaan Uang Jalan'),

                        Forms\Components\Textarea::make('fuel_usage_notes')
                            ->label('Catatan Penggunaan BBM')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Nomor SPM')
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
                    ->label('SPM Dicetak')
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
