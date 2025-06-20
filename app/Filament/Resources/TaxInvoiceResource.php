<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxInvoiceResource\Pages;
use App\Models\TaxInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxInvoiceResource extends Resource
{
    protected static ?string $model = TaxInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Tax Invoice';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tax Invoice')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_tax_invoice')
                            ->label('Nomor Tax Invoice')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\Select::make('id_invoice')
                            ->label('Invoice')
                            ->relationship('invoice', 'nomor_invoice')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('id_do')
                            ->label('Delivery Order')
                            ->relationship('deliveryOrder', 'kode')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('tanggal_tax_invoice')
                            ->label('Tanggal Tax Invoice')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Diajukan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pelanggan')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('alamat_pelanggan')
                            ->label('Alamat Pelanggan')
                            ->rows(3),

                        Forms\Components\TextInput::make('npwp_pelanggan')
                            ->label('NPWP Pelanggan')
                            ->maxLength(50),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Perusahaan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_perusahaan')
                            ->label('Nama Perusahaan')
                            ->required()
                            ->maxLength(255)
                            ->default('PT. Logistik Riau Prima'),

                        Forms\Components\Textarea::make('alamat_perusahaan')
                            ->label('Alamat Perusahaan')
                            ->required()
                            ->rows(3)
                            ->default('Jl. Riau Prima No. 123, Pekanbaru'),

                        Forms\Components\TextInput::make('npwp_perusahaan')
                            ->label('NPWP Perusahaan')
                            ->required()
                            ->maxLength(50)
                            ->default('01.234.567.8-901.000'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Informasi Pajak')
                    ->schema([
                        Forms\Components\TextInput::make('dasar_pengenaan_pajak')
                            ->label('Dasar Pengenaan Pajak (DPP)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('tarif_pajak')
                            ->label('Tarif Pajak (%)')
                            ->required()
                            ->numeric()
                            ->default(11.00)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('pajak_pertambahan_nilai')
                            ->label('Pajak Pertambahan Nilai (PPN)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('total_tax_invoice')
                            ->label('Total Tax Invoice')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_tax_invoice')
                    ->label('Nomor Tax Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice.nomor_invoice')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deliveryOrder.kode')
                    ->label('Delivery Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_tax_invoice')
                    ->label('Tanggal Tax Invoice')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dasar_pengenaan_pajak')
                    ->label('DPP')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pajak_pertambahan_nilai')
                    ->label('PPN')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_tax_invoice')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Diajukan',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),

                Tables\Filters\Filter::make('tanggal_tax_invoice')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_tax_invoice', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_tax_invoice', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTaxInvoices::route('/'),
            'create' => Pages\CreateTaxInvoice::route('/create'),
            'view' => Pages\ViewTaxInvoice::route('/{record}'),
            'edit' => Pages\EditTaxInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
