<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratResource\Pages;
use App\Filament\Resources\SuratResource\RelationManagers;
use App\Models\Surat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuratResource extends Resource
{
    protected static ?string $model = Surat::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Surat Menyurat';

    protected static ?string $navigationLabel = 'Dokumen Surat';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Information')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_surat')
                            ->label('Document Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        Forms\Components\Select::make('jenis_surat')
                            ->label('Document Type')
                            ->options([
                                'penawaran' => 'Quotation',
                                'kontrak' => 'Contract',
                                'invoice' => 'Invoice',
                                'lainnya' => 'Others',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('tanggal_surat')
                            ->label('Document Date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Related Parties')
                    ->schema([
                        Forms\Components\Select::make('id_pelanggan')
                            ->label('Customer')
                            ->relationship('pelanggan', 'nama')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('id_supplier')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Document Content')
                    ->schema([
                        Forms\Components\Textarea::make('isi_surat')
                            ->label('Document Content')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('file_dokumen')
                            ->label('Document File')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->directory('documents')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable(false)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('status_pembayaran')
                            ->label('Payment Status')
                            ->options([
                                'belum bayar' => 'Not Paid',
                                'sudah bayar' => 'Paid',
                                'terlambat' => 'Overdue',
                            ])
                            ->default('belum bayar')
                            ->required(),

                        Forms\Components\DateTimePicker::make('tanggal_pembayaran')
                            ->label('Payment Date'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')
                    ->label('Document Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('jenis_surat')
                    ->label('Document Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'penawaran' => 'info',
                        'kontrak' => 'success',
                        'invoice' => 'warning',
                        'lainnya' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'penawaran' => 'Quotation',
                        'kontrak' => 'Contract',
                        'invoice' => 'Invoice',
                        'lainnya' => 'Others',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_surat')
                    ->label('Document Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('supplier.nama')
                    ->label('Supplier')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\IconColumn::make('file_dokumen')
                    ->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-document-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => !empty($record->file_dokumen)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'belum bayar' => 'warning',
                        'sudah bayar' => 'success',
                        'terlambat' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'belum bayar' => 'Not Paid',
                        'sudah bayar' => 'Paid',
                        'terlambat' => 'Overdue',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_surat')
                    ->label('Document Type')
                    ->options([
                        'penawaran' => 'Quotation',
                        'kontrak' => 'Contract',
                        'invoice' => 'Invoice',
                        'lainnya' => 'Others',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Payment Status')
                    ->options([
                        'belum bayar' => 'Not Paid',
                        'sudah bayar' => 'Paid',
                        'terlambat' => 'Overdue',
                    ]),

                Tables\Filters\TernaryFilter::make('has_file')
                    ->label('Has Document File')
                    ->placeholder('All')
                    ->trueLabel('With File')
                    ->falseLabel('Without File')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === true,
                            fn(Builder $query): Builder => $query->whereNotNull('file_dokumen'),
                        )->when(
                            $data['value'] === false,
                            fn(Builder $query): Builder => $query->whereNull('file_dokumen'),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => $record->file_dokumen ? asset('storage/' . $record->file_dokumen) : null)
                    ->visible(fn($record) => !empty($record->file_dokumen))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_surat', 'desc');
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
            'index' => Pages\ListSurats::route('/'),
            'create' => Pages\CreateSurat::route('/create'),
            'view' => Pages\ViewSurat::route('/{record}'),
            'edit' => Pages\EditSurat::route('/{record}/edit'),
        ];
    }
}
