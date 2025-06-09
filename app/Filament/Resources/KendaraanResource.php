<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KendaraanResource\Pages;
use App\Filament\Resources\KendaraanResource\RelationManagers;
use App\Models\Kendaraan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KendaraanResource extends Resource
{
    protected static ?string $model = Kendaraan::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Kendaraan';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kendaraan')
                    ->schema([
                        Forms\Components\TextInput::make('no_pol_kendaraan')
                            ->label('Nomor Polisi')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Contoh: B 1234 ABC'),

                        Forms\Components\TextInput::make('merk')
                            ->label('Merk Kendaraan')
                            ->maxLength(255)
                            ->placeholder('Contoh: Hino'),

                        Forms\Components\TextInput::make('tipe')
                            ->label('Tipe Kendaraan')
                            ->maxLength(100)
                            ->placeholder('Contoh: Ranger FM 260 JD'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Spesifikasi')
                    ->schema([
                        Forms\Components\TextInput::make('kapasitas')
                            ->label('Kapasitas Tangki')
                            ->numeric()
                            ->suffix('Liter')
                            ->placeholder('Contoh: 16000'),

                        Forms\Components\Select::make('kapasitas_satuan')
                            ->label('Satuan Kapasitas')
                            ->options([
                                1 => 'Liter',
                                2 => 'Kiloliter',
                            ])
                            ->default(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Periode Validitas')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_awal_valid')
                            ->label('Tanggal Mulai Valid')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_akhir_valid')
                            ->label('Tanggal Berakhir Valid')
                            ->required()
                            ->after('tanggal_awal_valid'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Kendaraan')
                            ->rows(3)
                            ->placeholder('Masukkan deskripsi kondisi dan penggunaan kendaraan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_pol_kendaraan')
                    ->label('Nomor Polisi')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('merk')
                    ->label('Merk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 0) . ' L'),

                Tables\Columns\TextColumn::make('tanggal_awal_valid')
                    ->label('Mulai Valid')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tanggal_akhir_valid')
                    ->label('Berakhir Valid')
                    ->date('d M Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $now = now();
                        $expiry = \Carbon\Carbon::parse($state);

                        if ($expiry->isPast()) {
                            return 'danger';
                        } elseif ($expiry->diffInDays($now) <= 30) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('merk')
                    ->label('Merk')
                    ->options(function () {
                        return \App\Models\Kendaraan::distinct('merk')
                            ->whereNotNull('merk')
                            ->pluck('merk', 'merk');
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
            'index' => Pages\ListKendaraans::route('/'),
            'create' => Pages\CreateKendaraan::route('/create'),
            'edit' => Pages\EditKendaraan::route('/{record}/edit'),
        ];
    }
}
