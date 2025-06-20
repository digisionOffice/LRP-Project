<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Storage;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Absensi';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Data Absensi';

    protected static ?string $navigationGroup = 'Manajemen Karyawan';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Karyawan')
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan')
                            ->relationship('karyawan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('jadwal_id')
                            ->label('Jadwal Kerja')
                            ->relationship('jadwal', 'tanggal_jadwal')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->tanggal_jadwal->format('d M Y') . ' - ' .
                                    ($record->shift->nama_shift ?? 'No Shift')
                            ),

                        Forms\Components\DatePicker::make('tanggal_absensi')
                            ->label('Tanggal Absensi')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('periode')
                            ->label('Periode')
                            ->options([
                                1 => 'Periode 1',
                                2 => 'Periode 2',
                            ])
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu Absensi')
                    ->schema([
                        Forms\Components\TimePicker::make('waktu_masuk')
                            ->label('Waktu Masuk')
                            ->seconds(false),

                        Forms\Components\TimePicker::make('waktu_keluar')
                            ->label('Waktu Keluar')
                            ->seconds(false),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'hadir' => 'Hadir',
                                'terlambat' => 'Terlambat',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'cuti' => 'Cuti',
                                'alpha' => 'Alpha',
                            ])
                            ->required()
                            ->default('hadir'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Lokasi & Foto')
                    ->schema([
                        Forms\Components\TextInput::make('latitude_masuk')
                            ->label('Latitude Masuk')
                            ->numeric()
                            ->step(0.000001),

                        Forms\Components\TextInput::make('longitude_masuk')
                            ->label('Longitude Masuk')
                            ->numeric()
                            ->step(0.000001),

                        Forms\Components\TextInput::make('latitude_keluar')
                            ->label('Latitude Keluar')
                            ->numeric()
                            ->step(0.000001),

                        Forms\Components\TextInput::make('longitude_keluar')
                            ->label('Longitude Keluar')
                            ->numeric()
                            ->step(0.000001),

                        Forms\Components\FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('attendance/photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('foto_keluar')
                            ->label('Foto Keluar')
                            ->image()
                            ->directory('attendance/photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpan(1),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Persetujuan')
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Waktu Persetujuan'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_absensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => "Periode {$state}")
                    ->color('info'),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Masuk')
                    ->time('H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('waktu_keluar')
                    ->label('Keluar')
                    ->time('H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'hadir' => 'success',
                        'terlambat' => 'warning',
                        'izin' => 'info',
                        'sakit' => 'info',
                        'cuti' => 'primary',
                        'alpha' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\ImageColumn::make('foto_masuk')
                    ->label('Foto Masuk')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/images/placeholder-avatar.png')),

                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Disetujui')
                    ->placeholder('Belum disetujui')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'alpha' => 'Alpha',
                    ]),

                Filter::make('tanggal_absensi')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_absensi', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_absensi', '<=', $date),
                            );
                    }),

                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        1 => 'Periode 1',
                        2 => 'Periode 2',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Absensi $record) {
                        $record->update([
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    })
                    ->visible(fn(Absensi $record): bool => is_null($record->approved_at)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Setujui Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (is_null($record->approved_at)) {
                                    $record->update([
                                        'approved_by' => auth()->id(),
                                        'approved_at' => now(),
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('tanggal_absensi', 'desc');
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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'view' => Pages\ViewAbsensi::route('/{record}'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['karyawan', 'jadwal.shift', 'approvedBy']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('tanggal_absensi', today())->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
