<?php

namespace App\Filament\Karyawan\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Traits\HasWidgetFilters;

class AbsensiOverview extends BaseWidget
{
    use HasWidgetFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            return [];
        }

        // Hitung statistik absensi bulan ini
        $absensiStats = $this->hitungAbsensiBulanIni($karyawan);

        // Hitung statistik absensi hari ini
        $absensiHariIni = $this->getAbsensiHariIni($karyawan);

        // Hitung jadwal minggu ini
        $jadwalMingguIni = $this->getJadwalMingguIni($karyawan);

        return [
            Stat::make('Absensi Hari Ini', $absensiHariIni['status'])
                ->description($absensiHariIni['keterangan'])
                ->descriptionIcon($absensiHariIni['icon'])
                ->color($absensiHariIni['color']),

            Stat::make('Hadir Bulan Ini', $absensiStats['hadir'] . ' hari')
                ->description('Dari ' . $absensiStats['total_hari_kerja'] . ' hari kerja')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getChartAbsensiBulanIni($karyawan)),

            Stat::make('Terlambat Bulan Ini', $absensiStats['terlambat'] . ' hari')
                ->description($absensiStats['terlambat'] > 0 ? 'Perlu diperbaiki' : 'Sangat baik!')
                ->descriptionIcon('heroicon-m-clock')
                ->color($absensiStats['terlambat'] > 0 ? 'warning' : 'success'),

            Stat::make('Tidak Hadir Bulan Ini', $absensiStats['alpha'] . ' hari')
                ->description($absensiStats['alpha'] > 0 ? 'Perhatian khusus' : 'Excellent!')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absensiStats['alpha'] > 0 ? 'danger' : 'success'),

            Stat::make('Jadwal Minggu Ini', $jadwalMingguIni['total'] . ' hari')
                ->description($jadwalMingguIni['keterangan'])
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }

    private function hitungAbsensiBulanIni($karyawan)
    {
        $dateRange = $this->getFilteredDateRange();
        $startOfMonth = $dateRange['start'];
        $endOfMonth = $dateRange['end'];

        $absensi = $karyawan->absensi()
            ->with(['jadwal.shift'])
            ->whereBetween('tanggal_absensi', [$startOfMonth, $endOfMonth])
            ->get();

        $hadir = $absensi->where('status', 'hadir')->count();
        $terlambat = $absensi->where('status', 'terlambat')->count();
        $alpha = $absensi->where('status', 'alpha')->count();

        // Hitung total hari kerja (exclude weekend)
        $totalHariKerja = 0;
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth) && $current->lte(now())) {
            if (!$current->isWeekend()) {
                $totalHariKerja++;
            }
            $current->addDay();
        }

        return [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'alpha' => $alpha,
            'total_hari_kerja' => $totalHariKerja,
        ];
    }

    private function getAbsensiHariIni($karyawan)
    {
        $today = now()->format('Y-m-d');
        $absensiHariIni = $karyawan->absensi()
            ->with(['jadwal.shift'])
            ->whereDate('tanggal_absensi', $today)
            ->first();

        if (!$absensiHariIni) {
            // Cek apakah hari ini weekend
            if (now()->isWeekend()) {
                return [
                    'status' => 'Weekend',
                    'keterangan' => 'Hari libur',
                    'icon' => 'heroicon-m-sun',
                    'color' => 'info'
                ];
            }

            return [
                'status' => 'Belum Absen',
                'keterangan' => 'Silakan lakukan absensi',
                'icon' => 'heroicon-m-exclamation-triangle',
                'color' => 'warning'
            ];
        }

        switch ($absensiHariIni->status) {
            case 'hadir':
                return [
                    'status' => 'Hadir',
                    'keterangan' => 'Tepat waktu - ' . $absensiHariIni->waktu_masuk?->format('H:i'),
                    'icon' => 'heroicon-m-check-circle',
                    'color' => 'success'
                ];
            case 'terlambat':
                return [
                    'status' => 'Terlambat',
                    'keterangan' => 'Masuk: ' . $absensiHariIni->waktu_masuk?->format('H:i'),
                    'icon' => 'heroicon-m-clock',
                    'color' => 'warning'
                ];
            case 'alpha':
                return [
                    'status' => 'Tidak Hadir',
                    'keterangan' => 'Alpha hari ini',
                    'icon' => 'heroicon-m-x-circle',
                    'color' => 'danger'
                ];
            default:
                return [
                    'status' => 'Unknown',
                    'keterangan' => 'Status tidak diketahui',
                    'icon' => 'heroicon-m-question-mark-circle',
                    'color' => 'gray'
                ];
        }
    }

    private function getJadwalMingguIni($karyawan)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $jadwal = $karyawan->schedules()
            ->with(['shift'])
            ->whereBetween('tanggal_jadwal', [$startOfWeek, $endOfWeek])
            ->count();

        if ($jadwal == 0) {
            return [
                'total' => 0,
                'keterangan' => 'Tidak ada jadwal'
            ];
        }

        return [
            'total' => $jadwal,
            'keterangan' => 'Jadwal kerja minggu ini'
        ];
    }

    private function getChartAbsensiBulanIni($karyawan)
    {
        // Chart untuk 30 hari terakhir
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);

            // Skip weekend
            if ($tanggal->isWeekend()) {
                continue;
            }

            $absensi = $karyawan->absensi()
                ->with(['jadwal.shift'])
                ->whereDate('tanggal_absensi', $tanggal)
                ->first();

            if ($absensi && $absensi->status === 'hadir') {
                $data[] = 1;
            } elseif ($absensi && $absensi->status === 'terlambat') {
                $data[] = 0.5;
            } else {
                $data[] = 0;
            }
        }

        return $data;
    }
}
