<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelangganResource\Pages;
use App\Filament\Resources\PelangganResource\RelationManagers;
use App\Models\Pelanggan;
use App\Models\Subdistrict;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Afsakar\LeafletMapPicker\LeafletMapPicker;
use Filament\Notifications\Notification;

class PelangganResource extends Resource
{
    protected static ?string $model = Pelanggan::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Pelanggan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Pelanggan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('Contoh: CUST001'),

                        Forms\Components\Select::make('type')
                            ->label('Tipe Pelanggan')
                            ->options([
                                'Corporate' => 'Corporate',
                                'Individual' => 'Individual',
                            ])
                            ->required()
                            ->placeholder('Pilih tipe pelanggan'),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pelanggan/Perusahaan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama pelanggan atau perusahaan'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Informasi Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('pic_nama')
                            ->label('Nama PIC')
                            ->maxLength(255)
                            ->placeholder('Nama person in charge'),

                        Forms\Components\TextInput::make('pic_phone')
                            ->label('Telepon PIC')
                            ->tel()
                            ->maxLength(15)
                            ->placeholder('Contoh: 021-5551234'),

                        Forms\Components\TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(20)
                            ->placeholder('Contoh: 01.234.567.8-901.000'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Alamat Pelanggan')
                    ->description('Kelola alamat-alamat pelanggan dengan lokasi peta')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Repeater::make('alamatPelanggan')
                            ->relationship('alamatPelanggan')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('alamat')
                                            ->label('Alamat Lengkap')
                                            ->placeholder('Masukkan alamat lengkap...')
                                            ->required()
                                            ->columnSpan(1),

                                        Forms\Components\Toggle::make('is_primary')
                                            ->label('Alamat Utama')
                                            ->helperText('Jadikan alamat ini sebagai alamat utama')
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
                                    ]),

                                LeafletMapPicker::make('location')
                                    ->label('Lokasi di Peta')
                                    ->height('300px')
                                    ->defaultLocation([0.5394419,101.451907]) // Jakarta default
                                    ->defaultZoom(15)
                                    ->draggable(true)
                                    ->clickable(true)
                                    ->tileProvider('openstreetmap')
                                    ->columnSpanFull(),

                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Alamat')
                            ->deleteAction(
                                fn($action) => $action->requiresConfirmation()
                                    ->modalHeading('Hapus Alamat')
                                    ->modalDescription('Apakah Anda yakin ingin menghapus alamat ini?')
                                    ->modalSubmitActionLabel('Ya, Hapus')
                            )
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                if (!empty($state['alamat'])) {
                                    $primary = $state['is_primary'] ?? false;
                                    $label = $state['alamat'];
                                    if (strlen($label) > 50) {
                                        $label = substr($label, 0, 50) . '...';
                                    }
                                    return ($primary ? '⭐ ' : '') . $label;
                                }
                                return 'Alamat Baru';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // Forms\Components\Section::make('Alamat')
                //     ->schema([
                //         Forms\Components\Select::make('id_subdistrict')
                //             ->label('Kelurahan')
                //             ->options(Subdistrict::with('district.regency.province')
                //                 ->get()
                //                 ->mapWithKeys(function ($subdistrict) {
                //                     return [
                //                         $subdistrict->id => $subdistrict->name .
                //                             ' - ' . $subdistrict->district->name .
                //                             ', ' . $subdistrict->district->regency->name .
                //                             ', ' . $subdistrict->district->regency->province->name
                //                     ];
                //                 }))
                //             ->searchable()
                //             ->placeholder('Pilih kelurahan'),

                //         Forms\Components\Textarea::make('alamat')
                //             ->label('Alamat Lengkap')
                //             ->rows(3)
                //             ->placeholder('Masukkan alamat lengkap')
                //             ->columnSpanFull(),
                //     ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Corporate' => 'success',
                        'Individual' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pic_nama')
                    ->label('PIC')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pic_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('npwp')
                    ->label('NPWP')
                    ->searchable()
                    ->copyable()
                    ->placeholder('T/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('alamat_pelanggan_count')
                    ->label('Jumlah Alamat')
                    ->counts('alamatPelanggan')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\TextColumn::make('primary_address')
                    ->label('Alamat Utama')
                    ->getStateUsing(function ($record) {
                        $primaryAddress = $record->alamatPelanggan()->where('is_primary', true)->first();
                        if ($primaryAddress) {
                            return strlen($primaryAddress->alamat) > 40
                                ? substr($primaryAddress->alamat, 0, 40) . '...'
                                : $primaryAddress->alamat;
                        }
                        return 'Belum ada alamat utama';
                    })
                    ->tooltip(function ($record) {
                        $primaryAddress = $record->alamatPelanggan()->where('is_primary', true)->first();
                        return $primaryAddress ? $primaryAddress->alamat : null;
                    })
                    ->icon('heroicon-m-star')
                    ->color(fn($record) => $record->alamatPelanggan()->where('is_primary', true)->exists() ? 'warning' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('subdistrict.name')
                    ->label('Kelurahan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Pelanggan')
                    ->options([
                        'Corporate' => 'Corporate',
                        'Individual' => 'Individual',
                    ]),

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
            'index' => Pages\ListPelanggans::route('/'),
            'create' => Pages\CreatePelanggan::route('/create'),
            'edit' => Pages\EditPelanggan::route('/{record}/edit'),
        ];
    }
}
