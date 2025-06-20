<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Manajemen Keuangan';

    protected static ?string $navigationLabel = 'Invoice';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Invoice')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_invoice')
                            ->label('Nomor Invoice')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\Select::make('id_do')
                            ->label('Delivery Order')
                            ->relationship('deliveryOrder', 'kode')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('id_transaksi')
                            ->label('Transaksi Penjualan')
                            ->relationship('transaksiPenjualan', 'kode')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('tanggal_invoice')
                            ->label('Tanggal Invoice')
                            ->required()
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->required()
                            ->default(now()->addDays(30)),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Terkirim',
                                'paid' => 'Lunas',
                                'overdue' => 'Jatuh Tempo',
                                'cancelled' => 'Dibatalkan',
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

                Forms\Components\Section::make('Informasi Keuangan')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('total_pajak')
                            ->label('Total Pajak')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('total_invoice')
                            ->label('Total Invoice')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\TextInput::make('total_terbayar')
                            ->label('Total Terbayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('sisa_tagihan')
                            ->label('Sisa Tagihan')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Biaya Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('biaya_ongkos_angkut')
                            ->label('Biaya Ongkos Angkut')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),

                        Forms\Components\TextInput::make('biaya_pbbkb')
                            ->label('Biaya PBBKB')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),

                        Forms\Components\TextInput::make('biaya_operasional_kerja')
                            ->label('Biaya Operasional Kerja')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Pengaturan Komponen')
                    ->schema([
                        Forms\Components\Toggle::make('include_ppn')
                            ->label('Sertakan PPN')
                            ->default(true)
                            ->helperText('Centang untuk menyertakan PPN dalam perhitungan'),

                        Forms\Components\Toggle::make('include_pbbkb')
                            ->label('Sertakan PBBKB')
                            ->default(false)
                            ->helperText('Centang untuk menyertakan biaya PBBKB dalam invoice'),

                        Forms\Components\Toggle::make('include_operasional_kerja')
                            ->label('Sertakan Operasional Kerja')
                            ->default(false)
                            ->helperText('Centang untuk menyertakan biaya operasional kerja dalam invoice'),
                    ])
                    ->columns(3),

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
                Tables\Columns\TextColumn::make('nomor_invoice')
                    ->label('Nomor Invoice')
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

                Tables\Columns\TextColumn::make('tanggal_invoice')
                    ->label('Tanggal Invoice')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_invoice')
                    ->label('Total Invoice')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'paid' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        'cancelled' => 'Dibatalkan',
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
                        'sent' => 'Terkirim',
                        'paid' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\Filter::make('tanggal_invoice')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_invoice', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_invoice', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('print_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn(Invoice $record): bool => Auth::user()?->can('view', $record) ?? false)
                    ->action(function (Invoice $record) {
                        try {
                            // Load the invoice with all necessary relationships
                            $invoice = Invoice::with([
                                'transaksiPenjualan.pelanggan.alamatUtama',
                                'transaksiPenjualan.pelanggan.subdistrict.district.regency',
                                'transaksiPenjualan.penjualanDetails.item.satuanDasar',
                                'deliveryOrder',
                                'createdBy'
                            ])->find($record->id);

                            // Generate dynamic filename
                            $filename = 'Invoice_' . $invoice->nomor_invoice . '_' . now()->format('Ymd_His') . '.pdf';

                            // Load the PDF view with the record data
                            $pdf = Pdf::loadView('pdf.invoice', ['record' => $invoice])
                                ->setPaper('a4', 'portrait')
                                ->setOptions([
                                    'isHtml5ParserEnabled' => true,
                                    'isPhpEnabled' => true,
                                    'defaultFont' => 'Arial'
                                ]);

                            // Stream the PDF as a download
                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $filename);
                        } catch (\Exception $e) {
                            // Log the error for debugging
                            Log::error('Failed to generate Invoice PDF: ' . $e->getMessage());
                            Log::error('Invoice PDF Error Stack Trace: ' . $e->getTraceAsString());

                            // Show notification to user
                            \Filament\Notifications\Notification::make()
                                ->title('Error generating PDF')
                                ->body('Failed to generate PDF. Please try again or contact administrator.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
