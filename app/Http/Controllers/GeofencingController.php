<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Services\GeofencingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeofencingController extends Controller
{
    /**
     * Validate attendance location for current employee
     */
    public function validateAttendanceLocation(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $user = Auth::user();
            $karyawan = Karyawan::where('id_user', $user->id)->first();

            if (!$karyawan) {
                return response()->json([
                    'allowed' => false,
                    'message' => 'Data karyawan tidak ditemukan',
                    'error' => true
                ], 404);
            }

            // Validate geofencing
            $validation = GeofencingService::validateAttendanceLocation(
                $karyawan,
                $request->latitude,
                $request->longitude
            );

            return response()->json($validation);

        } catch (\Exception $e) {
            return response()->json([
                'allowed' => false,
                'message' => 'Terjadi kesalahan saat validasi lokasi: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }
}
