<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use App\Models\Karyawan;
use App\Models\Shift;
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

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Kerja';

    protected static ?string $modelLabel = 'Jadwal';

    protected static ?string $pluralModelLabel = 'Jadwal Kerja';

    protected static ?string $navigationGroup = 'Manajemen Karyawan';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jadwal')
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan')
                            ->relationship('karyawan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('shift_id')
                            ->label('Shift')
                            ->relationship('shift', 'nama_shift')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('supervisor_id')
                            ->label('Supervisor')
                            ->relationship('supervisor', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('tanggal_jadwal')
                            ->label('Tanggal Jadwal')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Waktu Kerja (Opsional)')
                    ->description('Kosongkan jika menggunakan waktu dari shift')
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
                                'Hadir' => 'Hadir',
                                'Libur' => 'Libur',
                                'Cuti' => 'Cuti',
                                'Izin' => 'Izin',
                            ])
                            ->default('Hadir'),

                        Forms\Components\Toggle::make('is_approved')
                            ->label('Disetujui')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keterangan')
                    ->schema([
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
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

                Tables\Columns\TextColumn::make('tanggal_jadwal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shift.nama_shift')
                    ->label('Shift')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Masuk')
                    ->time('H:i')
                    ->placeholder('Sesuai shift')
                    ->getStateUsing(function ($record) {
                        return $record->waktu_masuk ?? $record->shift?->waktu_mulai;
                    }),

                Tables\Columns\TextColumn::make('waktu_keluar')
                    ->label('Keluar')
                    ->time('H:i')
                    ->placeholder('Sesuai shift')
                    ->getStateUsing(function ($record) {
                        return $record->waktu_keluar ?? $record->shift?->waktu_selesai;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => $state ?? 'Hadir')
                    ->color(fn(?string $state): string => match ($state ?? 'Hadir') {
                        'Hadir' => 'success',
                        'Libur' => 'info',
                        'Cuti' => 'warning',
                        'Izin' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean(),

                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('Supervisor')
                    ->placeholder('-')
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

                SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'nama_shift')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Libur' => 'Libur',
                        'Cuti' => 'Cuti',
                        'Izin' => 'Izin',
                    ]),

                Filter::make('tanggal_jadwal')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_jadwal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_jadwal', '<=', $date),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Status Persetujuan')
                    ->placeholder('Semua')
                    ->trueLabel('Disetujui')
                    ->falseLabel('Belum Disetujui'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Schedule $record) {
                        $record->update([
                            'is_approved' => true,
                        ]);
                    })
                    ->visible(fn(Schedule $record): bool => !$record->is_approved),
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
                                if (!$record->is_approved) {
                                    $record->update(['is_approved' => true]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('tanggal_jadwal', 'desc');
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'view' => Pages\ViewSchedule::route('/{record}'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['karyawan', 'shift', 'supervisor']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('tanggal_jadwal', today())->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
