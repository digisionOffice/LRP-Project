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

                        Forms\Components\Select::make('tipe')
                            ->label('Tipe')
                            ->options([
                                'dagang' => 'Dagang',
                                'jasa' => 'Jasa',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('tanggal')
                            ->label('Tanggal Pesanan')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('id_pelanggan')
                            ->label('Pelanggan')
                            ->relationship('pelanggan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('id_tbbm')
                            ->label('Lokasi TBBM')
                            ->relationship('tbbm', 'nama')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pengiriman')
                    ->schema([
                        Forms\Components\Select::make('id_subdistrict')
                            ->label('Kelurahan')
                            ->relationship('subdistrict', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Pengiriman')
                            ->rows(3),

                        Forms\Components\TextInput::make('nomor_po')
                            ->label('Nomor PO')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('top_pembayaran')
                            ->label('Termin Pembayaran (Hari)')
                            ->numeric()
                            ->suffix('hari'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Lampiran Dokumen')
                    ->description('Unggah dokumen pendukung untuk pesanan penjualan ini')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Lampiran')
                            ->disk('public')
                            ->directory('sales-orders')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'text/plain'
                            ])
                            ->maxSize(10240) // 10MB
                            ->helperText('Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF, TXT. Ukuran maksimal: 10MB')
                            ->storeFileNamesIn('attachment_original_name')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $file = $state;
                                    if (is_object($file) && method_exists($file, 'getMimeType')) {
                                        $set('attachment_mime_type', $file->getMimeType());
                                        $set('attachment_size', $file->getSize());
                                    }
                                }
                            }),

                        Forms\Components\Hidden::make('attachment_original_name'),
                        Forms\Components\Hidden::make('attachment_mime_type'),
                        Forms\Components\Hidden::make('attachment_size'),
                    ])
                    ->collapsible()
                    ->collapsed(),
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

                Tables\Columns\TextColumn::make('top_pembayaran')
                    ->label('Termin Pembayaran')
                    ->formatStateUsing(fn($state) => $state ? "{$state} hari" : 'Tunai')
                    ->badge()
                    ->color(fn($state) => $state > 30 ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('tbbm.nama')
                    ->label('Lokasi TBBM')
                    ->placeholder('T/A'),

                Tables\Columns\IconColumn::make('has_attachment')
                    ->label('Lampiran')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->hasAttachment())
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->hasAttachment()
                        ? 'File: ' . $record->attachment_original_name . ' (' . $record->getFormattedFileSize() . ')'
                        : 'Tidak ada lampiran'),

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

                Tables\Filters\TernaryFilter::make('has_attachment')
                    ->label('Memiliki Lampiran')
                    ->placeholder('Semua Data')
                    ->trueLabel('Dengan Lampiran')
                    ->falseLabel('Tanpa Lampiran')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('attachment_path'),
                        false: fn($query) => $query->whereNull('attachment_path'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('download_attachment')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn($record) => $record->getAttachmentUrl())
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->hasAttachment()),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
