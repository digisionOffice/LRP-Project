<?php

namespace App\Services;

use App\Models\Entitas;
use App\Models\Karyawan;

class GeofencingService
{
    /**
     * Validate if user can perform attendance based on location
     *
     * @param Karyawan $karyawan
     * @param float $userLat
     * @param float $userLon
     * @return array
     */
    public static function validateAttendanceLocation(Karyawan $karyawan, float $userLat, float $userLon): array
    {
        // Get karyawan's entitas
        $entitas = $karyawan->entitas;

        if (!$entitas) {
            return [
                'allowed' => false,
                'message' => 'Entitas karyawan tidak ditemukan',
                'distance' => 0,
                'radius' => 0,
                'error' => true
            ];
        }

        // Check if geofencing is enabled for this entitas
        if (!$entitas->enable_geofencing) {
            return [
                'allowed' => true,
                'message' => 'Geofencing tidak aktif untuk entitas ini',
                'distance' => 0,
                'radius' => $entitas->radius ?? 0,
                'entitas_name' => $entitas->nama_entitas ?? 'Unknown'
            ];
        }

        // Check if entitas has coordinates
        if (!$entitas->latitude || !$entitas->longitude) {
            return [
                'allowed' => false,
                'message' => 'Koordinat entitas belum diatur',
                'distance' => 0,
                'radius' => $entitas->radius ?? 0,
                'error' => true
            ];
        }

        // Calculate distance
        $distance = self::calculateDistance(
            $userLat,
            $userLon,
            $entitas->latitude,
            $entitas->longitude
        );

        $radius = $entitas->radius ?? 100; // Default 100 meters

        return [
            'allowed' => $distance <= $radius,
            'message' => $distance <= $radius
                ? 'Lokasi valid untuk absensi'
                : 'Anda berada di luar radius yang diperbolehkan',
            'distance' => $distance,
            'radius' => $radius,
            'entitas_name' => $entitas->nama_entitas ?? 'Unknown',
            'entitas_coordinates' => [
                'latitude' => $entitas->latitude,
                'longitude' => $entitas->longitude
            ]
        ];
    }

    /**
     * Get attendance location info for karyawan
     */
    public static function getAttendanceLocationInfo(Karyawan $karyawan): array
    {
        $entitas = $karyawan->entitas;

        if (!$entitas) {
            return [
                'has_location' => false,
                'message' => 'Entitas karyawan tidak ditemukan. Hubungi administrator untuk mengatur entitas Anda.'
            ];
        }

        if (!$entitas->latitude || !$entitas->longitude) {
            return [
                'has_location' => false,
                'message' => 'Koordinat lokasi kerja belum diatur. Hubungi administrator untuk mengatur koordinat entitas.',
                'entitas_name' => $entitas->nama_entitas ?? 'Unknown'
            ];
        }

        return [
            'has_location' => true,
            'entitas_name' => $entitas->nama_entitas ?? 'Unknown',
            'coordinates' => $entitas->latitude . ', ' . $entitas->longitude,
            'radius' => $entitas->radius ?? 100,
            'address' => $entitas->alamat ?? 'Alamat tidak tersedia',
            'message' => $entitas->enable_geofencing
                ? 'Anda harus berada dalam radius ' . ($entitas->radius ?? 100) . ' meter dari lokasi kerja untuk melakukan absensi.'
                : 'Geofencing tidak aktif untuk lokasi kerja ini.'
        ];
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    private static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }
}
