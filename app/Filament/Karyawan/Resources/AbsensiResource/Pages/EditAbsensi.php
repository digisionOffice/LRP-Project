<?php

namespace App\Filament\Karyawan\Resources\AbsensiResource\Pages;

use App\Filament\Karyawan\Resources\AbsensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbsensi extends EditRecord
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Process photo metadata if photo is updated
        if (!empty($data['foto_keluar']) && empty($this->record->foto_keluar)) {
            $metadata = [
                'filename' => 'attendance_checkout_photo.jpg',
                'captured_at' => now()->toISOString(),
                'latitude' => $data['latitude_keluar'] ?? null,
                'longitude' => $data['longitude_keluar'] ?? null,
                'status_kehadiran' => $data['status'] ?? 'hadir',
            ];

            $data['metadata_foto_keluar'] = $metadata;
        }

        // Set checkout time if not provided
        if (!empty($data['foto_keluar']) && empty($data['waktu_keluar'])) {
            $data['waktu_keluar'] = now()->format('H:i:s');
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Absensi berhasil diperbarui!';
    }
}
