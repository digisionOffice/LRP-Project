<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class PhotoMetadataService
{
    /**
     * Extract metadata from uploaded photo
     */
    public static function extractMetadata(UploadedFile $file, ?float $latitude = null, ?float $longitude = null): array
    {
        $metadata = [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'captured_at' => Carbon::now()->toISOString(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status_kehadiran' => null, // Will be set later
        ];

        // Try to extract EXIF data if available
        try {
            if (function_exists('exif_read_data') && in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'])) {
                $exifData = @exif_read_data($file->getPathname());
                if ($exifData) {
                    $metadata['exif'] = [
                        'camera_make' => $exifData['Make'] ?? null,
                        'camera_model' => $exifData['Model'] ?? null,
                        'datetime_original' => $exifData['DateTimeOriginal'] ?? null,
                        'gps_latitude' => self::getGpsCoordinate($exifData, 'GPSLatitude', 'GPSLatitudeRef'),
                        'gps_longitude' => self::getGpsCoordinate($exifData, 'GPSLongitude', 'GPSLongitudeRef'),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract EXIF data: ' . $e->getMessage());
        }

        return $metadata;
    }

    /**
     * Format metadata for display
     */
    public static function formatMetadataForDisplay(array $metadata): array
    {
        $formatted = [
            'coordinates' => null,
            'datetime' => null,
            'status_kehadiran' => null,
            'camera' => null,
        ];

        // Format coordinates
        if (isset($metadata['latitude']) && isset($metadata['longitude'])) {
            $formatted['coordinates'] = number_format($metadata['latitude'], 6) . '°, ' . number_format($metadata['longitude'], 6) . '°';
        }

        // Format datetime
        if (isset($metadata['captured_at'])) {
            try {
                $date = Carbon::parse($metadata['captured_at']);
                $formatted['datetime'] = $date->format('d/m/Y H:i:s') . ' WIB';
            } catch (\Exception $e) {
                $formatted['datetime'] = $metadata['captured_at'];
            }
        }

        // Format status
        if (isset($metadata['status_kehadiran'])) {
            $formatted['status_kehadiran'] = $metadata['status_kehadiran'];
        }

        // Format camera info
        if (isset($metadata['exif']['camera_make']) && isset($metadata['exif']['camera_model'])) {
            $formatted['camera'] = $metadata['exif']['camera_make'] . ' ' . $metadata['exif']['camera_model'];
        } elseif (isset($metadata['camera_info'])) {
            $formatted['camera'] = $metadata['camera_info'];
        }

        return $formatted;
    }

    /**
     * Determine attendance status based on schedule and time
     */
    public static function determineAttendanceStatus($karyawanId, $datetime = null): string
    {
        if (!$datetime) {
            $datetime = Carbon::now();
        }

        try {
            // Get today's schedule for the employee
            $schedule = \App\Models\Schedule::with(['shift', 'karyawan'])
                ->whereHas('karyawan', function ($query) use ($karyawanId) {
                    $query->where('id', $karyawanId);
                })
                ->whereDate('tanggal_jadwal', $datetime->toDateString())
                ->first();

            if (!$schedule || !$schedule->shift) {
                return 'Tidak Ada Jadwal';
            }

            $shift = $schedule->shift;
            $actualTime = Carbon::parse($datetime);

            // Handle split shift
            if ($shift->isSplitShift()) {
                // Determine which period we're in
                $periode1Start = Carbon::parse($shift->waktu_mulai);
                $periode1End = Carbon::parse($shift->waktu_selesai);
                $periode2Start = Carbon::parse($shift->waktu_mulai_periode2);
                $periode2End = Carbon::parse($shift->waktu_selesai_periode2);

                if ($actualTime->between($periode1Start->subHour(), $periode1End->addHour())) {
                    // Period 1 logic
                    $toleranceMinutes = $shift->toleransi_keterlambatan ?? 0;
                    if ($actualTime->lessThanOrEqualTo($periode1Start->addMinutes($toleranceMinutes))) {
                        return 'Tepat Waktu';
                    } else {
                        return 'Telat';
                    }
                } elseif ($actualTime->between($periode2Start->subHour(), $periode2End->addHour())) {
                    // Period 2 logic
                    $toleranceMinutes = $shift->toleransi_keterlambatan_periode2 ?? $shift->toleransi_keterlambatan ?? 0;
                    if ($actualTime->lessThanOrEqualTo($periode2Start->addMinutes($toleranceMinutes))) {
                        return 'Tepat Waktu';
                    } else {
                        return 'Telat';
                    }
                }
            } else {
                // Regular shift logic
                $shiftStart = Carbon::parse($shift->waktu_mulai);
                $toleranceMinutes = $shift->toleransi_keterlambatan ?? 0;

                // Compare with shift start time + tolerance
                if ($actualTime->lessThanOrEqualTo($shiftStart->addMinutes($toleranceMinutes))) {
                    return 'Tepat Waktu';
                } else {
                    return 'Telat';
                }
            }

            return 'Unknown'; // Fallback if no period matches
        } catch (\Exception $e) {
            Log::warning('Failed to determine attendance status: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    /**
     * Extract GPS coordinates from EXIF data
     */
    private static function getGpsCoordinate(array $exifData, string $coordKey, string $refKey): ?float
    {
        if (!isset($exifData[$coordKey]) || !isset($exifData[$refKey])) {
            return null;
        }

        $coordinates = $exifData[$coordKey];
        $reference = $exifData[$refKey];

        if (!is_array($coordinates) || count($coordinates) < 3) {
            return null;
        }

        // Convert DMS to decimal
        $degrees = self::gpsToDecimal($coordinates[0]);
        $minutes = self::gpsToDecimal($coordinates[1]);
        $seconds = self::gpsToDecimal($coordinates[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        // Apply reference (N/S for latitude, E/W for longitude)
        if (in_array($reference, ['S', 'W'])) {
            $decimal = -$decimal;
        }

        return $decimal;
    }

    /**
     * Convert GPS fraction to decimal
     */
    private static function gpsToDecimal(string $fraction): float
    {
        $parts = explode('/', $fraction);
        if (count($parts) == 2 && $parts[1] != 0) {
            return $parts[0] / $parts[1];
        }
        return (float) $fraction;
    }
}
