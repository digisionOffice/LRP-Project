<?php

namespace App\Filament\Karyawan\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\Karyawan;

class UpcomingSchedule extends BaseWidget
{
    protected static ?string $heading = 'Jadwal Mendatang';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Jadwal Mendatang')
            ->query(function (): Builder {
                $user = Auth::user();
                $karyawan = Karyawan::where('id_user', $user->id)->first();

                if (!$karyawan) {
                    return Schedule::query()->where('id', 0); // Empty query
                }

                return Schedule::query()
                    ->where('karyawan_id', $karyawan->id)
                    ->where('tanggal_jadwal', '>=', now()->toDateString())
                    ->orderBy('tanggal_jadwal')
                    ->limit(7);
            })
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_jadwal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shift.nama_shift')
                    ->label('Shift')
                    ->placeholder('Tidak ada shift'),

                Tables\Columns\TextColumn::make('waktu_masuk')
                    ->label('Jam Masuk')
                    ->time('H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('waktu_keluar')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => $state ? ucfirst($state) : 'Hadir')
                    ->color(fn(?string $state): string => match (strtolower($state ?? 'hadir')) {
                        'hadir' => 'success',
                        'libur' => 'info',
                        'cuti' => 'warning',
                        'izin' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->paginated(false);
    }
}
