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

    protected static ?string $navigationLabel = 'Delivery Order';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Delivery Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('DO Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Forms\Components\Select::make('id_transaksi')
                            ->label('Sales Order')
                            ->relationship('transaksi', 'kode')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('id_user')
                            ->label('Driver')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('id_kendaraan')
                            ->label('Vehicle')
                            ->relationship('kendaraan', 'no_pol_kendaraan')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('tanggal_delivery')
                            ->label('Delivery Date'),

                        Forms\Components\TextInput::make('no_segel')
                            ->label('Seal Number')
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Loading Information')
                    ->schema([
                        Forms\Components\Select::make('status_muat')
                            ->label('Loading Status')
                            ->options([
                                'pending' => 'Load Order Issued',
                                'muat' => 'Load Confirmed',
                                'selesai' => 'Loading Complete',
                            ])
                            ->default('pending'),

                        Forms\Components\DateTimePicker::make('waktu_muat')
                            ->label('Loading Start Time'),

                        Forms\Components\DateTimePicker::make('waktu_selesai_muat')
                            ->label('Loading Complete Time'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Administration')
                    ->schema([
                        Forms\Components\TextInput::make('do_signatory_name')
                            ->label('DO Signatory Name'),

                        Forms\Components\Toggle::make('do_print_status')
                            ->label('DO Print Status'),

                        Forms\Components\TextInput::make('driver_allowance_amount')
                            ->label('Driver Allowance Amount')
                            ->numeric()
                            ->prefix('IDR'),

                        Forms\Components\Toggle::make('allowance_receipt_status')
                            ->label('Allowance Receipt Status'),

                        Forms\Components\Textarea::make('fuel_usage_notes')
                            ->label('Fuel Usage Notes')
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
                    ->label('DO Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaksi.pelanggan.nama')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver')
                    ->searchable()
                    ->placeholder('Not Assigned'),

                Tables\Columns\TextColumn::make('kendaraan.no_pol_kendaraan')
                    ->label('Vehicle')
                    ->searchable()
                    ->placeholder('Not Assigned'),

                Tables\Columns\TextColumn::make('status_muat')
                    ->label('Loading Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'muat' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Load Order Issued',
                        'muat' => 'Load Confirmed',
                        'selesai' => 'Loading Complete',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_delivery')
                    ->label('Delivery Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('do_print_status')
                    ->label('DO Printed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
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
