<?php

namespace App\Filament\Karyawan\Resources\AbsensiResource\Pages;

use App\Filament\Karyawan\Resources\AbsensiResource;
use App\Models\Karyawan;
use App\Services\PhotoMetadataService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAbsensi extends CreateRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id_user', $user->id)->first();

        if (!$karyawan) {
            throw new \Exception('Data karyawan tidak ditemukan');
        }

        // Set karyawan_id
        $data['karyawan_id'] = $karyawan->id;

        // Get today's schedule
        $schedule = $karyawan->schedules()
            ->with('shift')
            ->whereDate('tanggal_jadwal', $data['tanggal_absensi'])
            ->first();

        if ($schedule) {
            $data['jadwal_id'] = $schedule->id;
        }

        // Set current time if not provided
        if (empty($data['waktu_masuk'])) {
            $data['waktu_masuk'] = now()->format('H:i:s');
        }

        // Determine attendance status based on schedule and time
        if ($karyawan && isset($data['waktu_masuk'])) {
            $status = PhotoMetadataService::determineAttendanceStatus(
                $karyawan->id,
                now()->setTimeFromTimeString($data['waktu_masuk'])
            );

            if ($status !== 'Unknown') {
                $data['status'] = strtolower($status === 'Tepat Waktu' ? 'hadir' : 'terlambat');
            }
        }

        // Process photo metadata if photo is uploaded
        if (!empty($data['foto_masuk'])) {
            $metadata = [
                'filename' => 'attendance_photo.jpg',
                'captured_at' => now()->toISOString(),
                'latitude' => $data['latitude_masuk'] ?? null,
                'longitude' => $data['longitude_masuk'] ?? null,
                'status_kehadiran' => $data['status'] ?? 'hadir',
            ];

            $data['metadata_foto_masuk'] = $metadata;
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Absensi berhasil disimpan!';
    }
}
