<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalResource\Pages;
use App\Models\Journal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Jurnal';

    protected static ?string $modelLabel = 'Jurnal';

    protected static ?string $pluralModelLabel = 'Jurnal';

    protected static ?string $navigationGroup = 'Akuntansi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('journal_number')
                    ->label('Nomor Jurnal')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Tanggal Transaksi')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('reference_number')
                    ->label('Nomor Referensi')
                    ->maxLength(255),
                Forms\Components\Select::make('source_type')
                    ->label('Tipe Sumber')
                    ->options([
                        'Sale' => 'Penjualan',
                        'Purchase' => 'Pembelian',
                        'Payment' => 'Pembayaran',
                        'Receipt' => 'Penerimaan',
                        'ManualAdjust' => 'Penyesuaian Manual',
                    ])
                    ->native(false),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Draft' => 'Draft',
                        'Posted' => 'Posted',
                        'Cancelled' => 'Cancelled',
                        'Error' => 'Error',
                    ])
                    ->default('Draft')
                    ->native(false),
                Forms\Components\Repeater::make('journalEntries')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->label('Akun')
                            ->relationship('account', 'nama_akun')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('debit')
                            ->label('Debit')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('credit')
                            ->label('Credit')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3)
                    ->defaultItems(2)
                    ->addActionLabel('Tambah Entri Jurnal')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('journal_number')
                    ->label('Nomor Jurnal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referensi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('Tipe')
                    ->badge(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'Draft',
                        'success' => 'Posted',
                        'danger' => 'Cancelled',
                        'warning' => 'Error',
                    ]),
                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->journalEntries->sum('debit')),
                Tables\Columns\TextColumn::make('total_credit')
                    ->label('Total Credit')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->journalEntries->sum('credit')),
                Tables\Columns\IconColumn::make('is_balanced')
                    ->label('Seimbang')
                    ->boolean()
                    ->getStateUsing(fn($record) => $record->isBalanced()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Posted' => 'Posted',
                        'Cancelled' => 'Cancelled',
                        'Error' => 'Error',
                    ]),
                Tables\Filters\SelectFilter::make('source_type')
                    ->options([
                        'Sale' => 'Penjualan',
                        'Purchase' => 'Pembelian',
                        'Payment' => 'Pembayaran',
                        'Receipt' => 'Penerimaan',
                        'ManualAdjust' => 'Penyesuaian Manual',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->status === 'Draft'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Only delete records that are in Draft status
                            $records->each(function ($record) {
                                if ($record->status === 'Draft') {
                                    $record->delete();
                                }
                            });
                        }),
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
            'index' => Pages\ListJournals::route('/'),
            'create' => Pages\CreateJournal::route('/create'),
            'view' => Pages\ViewJournal::route('/{record}'),
            'edit' => Pages\EditJournal::route('/{record}/edit'),
        ];
    }
}
