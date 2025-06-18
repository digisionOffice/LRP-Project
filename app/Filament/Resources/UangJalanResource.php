<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UangJalanResource\Pages;
use App\Filament\Resources\UangJalanResource\RelationManagers;
use App\Models\UangJalan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UangJalanResource extends Resource
{
    protected static ?string $model = UangJalan::class;

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Driver Allowance';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Driver Allowance Information')
                    ->schema([
                        Forms\Components\Select::make('id_do')
                            ->label('Delivery Order')
                            ->relationship('deliveryOrder', 'kode')
                            ->searchable()
                            ->preload()
                            ->default(function () {
                                // Autofill from URL parameter
                                return request()->query('id_do', null);
                            })
                            // ->disabled()
                            ->helperText('DO akan otomatis terisi dari URL parameter')
                            ->required(),

                        Forms\Components\Select::make('id_user')
                            ->label('Driver')
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
                            ->helperText('Pilih sopir yang akan menerima uang jalan')
                            ->preload(),

                        Forms\Components\TextInput::make('nominal')
                            ->label('Allowance Amount')
                            ->required()
                            ->dehydrated(false)
                            ->numeric()
                            ->prefix('IDR')
                            ->minValue(0),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status Uang Jalan')
                    ->schema([
                        Forms\Components\Select::make('status_kirim')
                            ->label('Status kirim')
                            ->options([
                                'pending' => 'Pending',
                                'kirim' => 'Sent',
                                'ditolak' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\FileUpload::make('bukti_kirim')
                            ->label('Bukti Kirim')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->directory('allowance-proofs/sending')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable()
                            ->image()
                            ->imageEditor(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Penerimaan Uang jalan')
                    ->schema([
                        Forms\Components\Select::make('status_terima')
                            ->label('Status Terima')
                            ->options([
                                'pending' => 'Pending',
                                'terima' => 'Received',
                                'ditolak' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\FileUpload::make('bukti_terima')
                            ->label('Bukti Terima')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->directory('allowance-proofs/receiving')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable()
                            ->image()
                            ->imageEditor(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('deliveryOrder.kode')
                    ->label('Delivery Order')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('deliveryOrder.transaksi.kode')
                    ->label('SO Number')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver')
                    ->searchable()
                    ->placeholder('Not Assigned'),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_kirim')
                    ->label('Sending Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'kirim' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'kirim' => 'Sent',
                        'ditolak' => 'Rejected',
                        default => $state,
                    }),

                Tables\Columns\ImageColumn::make('bukti_kirim')
                    ->label('Sending Proof')
                    ->circular()
                    ->size(40)
                    ->placeholder('No Proof'),

                Tables\Columns\TextColumn::make('status_terima')
                    ->label('Receiving Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'terima' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'terima' => 'Received',
                        'ditolak' => 'Rejected',
                        default => $state,
                    }),

                Tables\Columns\ImageColumn::make('bukti_terima')
                    ->label('Receiving Proof')
                    ->circular()
                    ->size(40)
                    ->placeholder('No Proof'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_kirim')
                    ->label('Sending Status')
                    ->options([
                        'pending' => 'Pending',
                        'kirim' => 'Sent',
                        'ditolak' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('status_terima')
                    ->label('Receiving Status')
                    ->options([
                        'pending' => 'Pending',
                        'terima' => 'Received',
                        'ditolak' => 'Rejected',
                    ]),

                Tables\Filters\TernaryFilter::make('has_sending_proof')
                    ->label('Has Sending Proof')
                    ->placeholder('All')
                    ->trueLabel('With Proof')
                    ->falseLabel('Without Proof')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === true,
                            fn(Builder $query): Builder => $query->whereNotNull('bukti_kirim'),
                        )->when(
                            $data['value'] === false,
                            fn(Builder $query): Builder => $query->whereNull('bukti_kirim'),
                        );
                    }),

                Tables\Filters\TernaryFilter::make('has_receiving_proof')
                    ->label('Has Receiving Proof')
                    ->placeholder('All')
                    ->trueLabel('With Proof')
                    ->falseLabel('Without Proof')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] === true,
                            fn(Builder $query): Builder => $query->whereNotNull('bukti_terima'),
                        )->when(
                            $data['value'] === false,
                            fn(Builder $query): Builder => $query->whereNull('bukti_terima'),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadSendingProof')
                    ->label('Download Sending Proof')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => $record->bukti_kirim ? asset('storage/' . $record->bukti_kirim) : null)
                    ->visible(fn($record) => !empty($record->bukti_kirim))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('downloadReceivingProof')
                    ->label('Download Receiving Proof')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => $record->bukti_terima ? asset('storage/' . $record->bukti_terima) : null)
                    ->visible(fn($record) => !empty($record->bukti_terima))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUangJalans::route('/'),
            'create' => Pages\CreateUangJalan::route('/create'),
            'view' => Pages\ViewUangJalan::route('/{record}'),
            'edit' => Pages\EditUangJalan::route('/{record}/edit'),
        ];
    }
}
