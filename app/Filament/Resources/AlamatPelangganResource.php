<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlamatPelangganResource\Pages;
use App\Filament\Resources\AlamatPelangganResource\RelationManagers;
use App\Models\AlamatPelanggan;
use App\Models\Pelanggan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Afsakar\LeafletMapPicker\LeafletMapPicker;
use Filament\Notifications\Notification;

class AlamatPelangganResource extends Resource
{
    protected static ?string $model = AlamatPelanggan::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Alamat Pelanggan';

    protected static ?string $modelLabel = 'Alamat Pelanggan';

    protected static ?string $pluralModelLabel = 'Alamat Pelanggan';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelanggan')
                    ->description('Pilih pelanggan dan atur alamat')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('id_pelanggan')
                            ->label('Pelanggan')
                            ->relationship('pelanggan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih pelanggan')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('kode')
                                    ->label('Kode Pelanggan')
                                    ->required()
                                    ->unique('pelanggan', 'kode')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('nama')
                                    ->label('Nama Pelanggan')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('type')
                                    ->label('Tipe')
                                    ->options([
                                        'Corporate' => 'Corporate',
                                        'Individual' => 'Individual',
                                    ])
                                    ->required(),
                            ])
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('is_primary')
                            ->label('Alamat Utama')
                            ->helperText('Jadikan alamat ini sebagai alamat utama pelanggan')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    Notification::make()
                                        ->title('Alamat Utama')
                                        ->body('Alamat ini akan menjadi alamat utama. Alamat utama lainnya akan dinonaktifkan.')
                                        ->info()
                                        ->send();
                                }
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Alamat')
                    ->description('Masukkan alamat lengkap dan pilih lokasi di peta')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('alamat')
                            ->label('Alamat Lengkap')
                            ->placeholder('Masukkan alamat lengkap...')
                            ->required()
                            ->columnSpanFull(),

                        LeafletMapPicker::make('location')
                            ->label('Lokasi di peta')
                            ->defaultLocation([0.5394419,101.451907]) // lrp
                            ->defaultZoom(15)
                            ->draggable(true)
                            ->clickable(true)
                            ->myLocationButtonLabel('Lokasi Saya')
                            ->tileProvider('openstreetmap')
                            ->columnSpanFull(),

                        // LeafletMapPicker::make('location')
                        //     ->label('Lokasi di Peta')
                        //     ->height('400px')
                        //     ->defaultLocation([-6.2088, 106.8456]) // Jakarta default
                        //     ->defaultZoom(15)
                        //     ->draggable(true)
                        //     ->clickable(true)
                        //     ->myLocationButtonLabel('Lokasi Saya')
                        //     ->tileProvider('openstreetmap')
                        //     ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('Akan terisi otomatis dari peta')
                                    ->readOnly()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('Akan terisi otomatis dari peta')
                                    ->readOnly()
                                    ->dehydrated(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pelanggan.nama')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Nama pelanggan disalin!')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('pelanggan.kode')
                    ->label('Kode Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Alamat Utama')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn($record) => $record->is_primary ? 'Alamat utama' : 'Alamat tambahan'),

                Tables\Columns\TextColumn::make('formatted_coordinates')
                    ->label('Koordinat')
                    ->getStateUsing(fn($record) => $record->formatted_coordinates)
                    ->placeholder('Belum ada koordinat')
                    ->copyable()
                    ->copyMessage('Koordinat disalin!')
                    ->badge()
                    ->color(fn($record) => $record->hasCoordinates() ? 'success' : 'gray')
                    ->icon(fn($record) => $record->hasCoordinates() ? 'heroicon-m-map-pin' : 'heroicon-m-x-mark')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude')
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    ->placeholder('T/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude')
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    ->placeholder('T/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_pelanggan')
                    ->label('Pelanggan')
                    ->relationship('pelanggan', 'nama')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pelanggan'),

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Status Alamat')
                    ->placeholder('Semua alamat')
                    ->trueLabel('Alamat utama')
                    ->falseLabel('Alamat tambahan'),

                Tables\Filters\TernaryFilter::make('has_coordinates')
                    ->label('Koordinat')
                    ->placeholder('Semua alamat')
                    ->trueLabel('Dengan koordinat')
                    ->falseLabel('Tanpa koordinat')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('location'),
                        false: fn($query) => $query->whereNull('location'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('view_map')
                    ->label('Lihat Peta')
                    ->icon('heroicon-o-map')
                    ->color('info')
                    ->url(fn($record) => $record->hasCoordinates()
                        ? "https://www.google.com/maps?q={$record->formatted_coordinates}"
                        : null)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->hasCoordinates()),

                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Alamat Pelanggan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus alamat ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Alamat Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus alamat yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListAlamatPelanggans::route('/'),
            'create' => Pages\CreateAlamatPelanggan::route('/create'),
            'edit' => Pages\EditAlamatPelanggan::route('/{record}/edit'),
        ];
    }

    /**
     * Get the navigation badge for the resource
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    /**
     * Get the navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'warning' : 'primary';
    }
}
