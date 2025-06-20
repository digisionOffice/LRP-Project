<?php

namespace App\Filament\Karyawan\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\Karyawan;

class RecentAttendance extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Absensi Terakhir';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Absensi Terakhir')
            ->query(function (): Builder {
                $user = Auth::user();
                $karyawan = Karyawan::with(['departemen', 'divisi'])->where('id_user', $user->id)->first();

                if (!$karyawan) {
                    return Absensi::query()->where('id', 0); // Empty query
                }

                return Absensi::query()
                    ->where('karyawan_id', $karyawan->id)
                    ->orderBy('tanggal_absensi', 'desc')
                    ->limit(10);
            })
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_absensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

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
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'hadir' => 'success',
                        'terlambat' => 'warning',
                        'izin' => 'info',
                        'sakit' => 'info',
                        'cuti' => 'primary',
                        'alpha' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Absensi $record): string => route('filament.karyawan.resources.absensis.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false);
    }
}
