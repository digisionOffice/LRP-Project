<?php

namespace App\Filament\Resources\AbsensiResource\Pages;

use App\Filament\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ViewAbsensi extends ViewRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Karyawan')
                    ->schema([
                        TextEntry::make('karyawan.nama')
                            ->label('Nama Karyawan'),
                        TextEntry::make('karyawan.no_induk')
                            ->label('No. Induk'),
                        TextEntry::make('jadwal.shift.nama_shift')
                            ->label('Shift'),
                        TextEntry::make('tanggal_absensi')
                            ->label('Tanggal Absensi')
                            ->date('d F Y'),
                        TextEntry::make('periode')
                            ->label('Periode')
                            ->formatStateUsing(fn (string $state): string => "Periode {$state}"),
                    ])
                    ->columns(2),

                Section::make('Waktu Absensi')
                    ->schema([
                        TextEntry::make('waktu_masuk')
                            ->label('Waktu Masuk')
                            ->time('H:i:s'),
                        TextEntry::make('waktu_keluar')
                            ->label('Waktu Keluar')
                            ->time('H:i:s'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->color(fn (string $state): string => match ($state) {
                                'hadir' => 'success',
                                'terlambat' => 'warning',
                                'izin' => 'info',
                                'sakit' => 'info',
                                'cuti' => 'primary',
                                'alpha' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Lokasi')
                    ->schema([
                        TextEntry::make('latitude_masuk')
                            ->label('Latitude Masuk'),
                        TextEntry::make('longitude_masuk')
                            ->label('Longitude Masuk'),
                        TextEntry::make('latitude_keluar')
                            ->label('Latitude Keluar'),
                        TextEntry::make('longitude_keluar')
                            ->label('Longitude Keluar'),
                    ])
                    ->columns(2),

                Section::make('Foto Absensi')
                    ->schema([
                        ImageEntry::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->size(200),
                        ImageEntry::make('foto_keluar')
                            ->label('Foto Keluar')
                            ->size(200),
                    ])
                    ->columns(2),

                Section::make('Persetujuan')
                    ->schema([
                        TextEntry::make('approvedBy.name')
                            ->label('Disetujui Oleh')
                            ->placeholder('Belum disetujui'),
                        TextEntry::make('approved_at')
                            ->label('Waktu Persetujuan')
                            ->dateTime('d F Y H:i:s')
                            ->placeholder('Belum disetujui'),
                    ])
                    ->columns(2),
            ]);
    }
}
