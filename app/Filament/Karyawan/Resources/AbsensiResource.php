<?php

namespace App\Filament\Karyawan\Resources;

use App\Filament\Karyawan\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Services\GeofencingService;
use App\Services\PhotoMetadataService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Absensi Saya';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi Saya';

    protected static ?string $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Absensi')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_absensi')
                            ->label('Tanggal Absensi')
                            ->required()
                            ->default(now())
                            ->disabled(fn($operation) => $operation === 'edit'),

                        Forms\Components\Select::make('periode')
                            ->label('Periode')
                            ->options([
                                1 => 'Periode 1',
                                2 => 'Periode 2',
                            ])
                            ->default(1)
                            ->required()
                            ->disabled(fn($operation) => $operation === 'edit'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'hadir' => 'Hadir',
                                'terlambat' => 'Terlambat',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'cuti' => 'Cuti',
                            ])
                            ->required()
                            ->default('hadir'),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Lokasi Absensi')
                    ->description('Pastikan GPS aktif dan izinkan akses lokasi')
                    ->schema([
                        Forms\Components\Placeholder::make('location_info')
                            ->label('')
                            ->content(function () {
                                $user = Auth::user();
                                $karyawan = Karyawan::where('id_user', $user->id)->first();

                                if (!$karyawan) {
                                    return 'Data karyawan tidak ditemukan.';
                                }

                                $locationInfo = GeofencingService::getAttendanceLocationInfo($karyawan);

                                if (!$locationInfo['has_location']) {
                                    return $locationInfo['message'];
                                }

                                return "📍 Lokasi Kerja: {$locationInfo['entitas_name']}<br>" .
                                    "📍 Koordinat: {$locationInfo['coordinates']}<br>" .
                                    "📍 Radius: {$locationInfo['radius']} meter<br>" .
                                    "📍 Alamat: {$locationInfo['address']}<br><br>" .
                                    $locationInfo['message'];
                            })
                            ->columnSpanFull(),

                        Forms\Components\View::make('filament.karyawan.components.geolocation-picker')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('latitude_masuk')
                            ->default(0),

                        Forms\Components\Hidden::make('longitude_masuk')
                            ->default(0),

                        Forms\Components\Hidden::make('latitude_keluar')
                            ->default(0),

                        Forms\Components\Hidden::make('longitude_keluar')
                            ->default(0),
                    ]),

                Forms\Components\Section::make('Foto Absensi')
                    ->description('Ambil foto selfie untuk verifikasi kehadiran')
                    ->schema([
                        Forms\Components\View::make('filament.karyawan.components.camera-interface')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('attendance/photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->required(fn($operation) => $operation === 'create')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('foto_keluar')
                            ->label('Foto Keluar')
                            ->image()
                            ->directory('attendance/photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu Absensi')
                    ->schema([
                        Forms\Components\TimePicker::make('waktu_masuk')
                            ->label('Waktu Masuk')
                            ->seconds(false)
                            ->default(now()->format('H:i')),

                        Forms\Components\TimePicker::make('waktu_keluar')
                            ->label('Waktu Keluar')
                            ->seconds(false),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->label('Foto')
                    ->circular()
                    ->size(40),

                Tables\Columns\IconColumn::make('approved_at')
                    ->label('Disetujui')
                    ->boolean()
                    ->getStateUsing(fn($record) => !is_null($record->approved_at)),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'alpha' => 'Alpha',
                    ]),

                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        1 => 'Periode 1',
                        2 => 'Periode 2',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(Absensi $record): bool => is_null($record->approved_at)),
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
        $user = Auth::user();
        $karyawan = Karyawan::where('id_user', $user->id)->first();

        if (!$karyawan) {
            return parent::getEloquentQuery()->where('id', 0); // Return empty query
        }

        return parent::getEloquentQuery()
            ->where('karyawan_id', $karyawan->id)
            ->with(['jadwal.shift', 'approvedBy']);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id_user', $user->id)->first();

        if (!$karyawan) {
            return null;
        }

        return static::getModel()::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal_absensi', today())
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id_user', $user->id)->first();

        if (!$karyawan) {
            return false;
        }

        // Check if user already has attendance for today
        $todayAttendance = static::getModel()::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal_absensi', today())
            ->exists();

        return !$todayAttendance;
    }
}
