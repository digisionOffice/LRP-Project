<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'karyawan_id',
        'jadwal_id',
        'periode',
        'tanggal_absensi',
        'waktu_masuk',
        'waktu_keluar',
        'status',
        'keterangan',
        'lokasi_masuk',
        'lokasi_keluar',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'foto_masuk',
        'foto_keluar',
        'metadata_foto_masuk',
        'metadata_foto_keluar',
        'approved_by',
        'approved_at',
        // Compatibility fields for mass assignment
        'latitude',
        'longitude',
        'foto',
        'foto_absensi',
    ];

    protected $casts = [
        'tanggal_absensi' => 'date',
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'approved_at' => 'datetime',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_keluar' => 'decimal:8',
        'longitude_keluar' => 'decimal:8',
        'metadata_foto_masuk' => 'array',
        'metadata_foto_keluar' => 'array',
    ];

    /**
     * Get the employee associated with this attendance record
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Get the schedule associated with this attendance record
     */
    public function jadwal()
    {
        return $this->belongsTo(Schedule::class, 'jadwal_id');
    }

    /**
     * Get the user who approved this attendance record
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get current attendance status for split shift support
     */
    public static function getCurrentAttendanceStatus($karyawanId, $date, $shift = null): array
    {
        $existingAttendance = self::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal_absensi', $date)
            ->orderBy('periode')
            ->get();

        if (!$shift) {
            // Regular shift logic
            if ($existingAttendance->isEmpty()) {
                return [
                    'action' => 'check_in',
                    'periode' => 1,
                    'message' => 'Silakan lakukan absensi masuk'
                ];
            }

            $latest = $existingAttendance->first();
            if ($latest->waktu_masuk && !$latest->waktu_keluar) {
                return [
                    'action' => 'check_out',
                    'periode' => 1,
                    'message' => 'Silakan lakukan absensi keluar'
                ];
            }

            return [
                'action' => 'completed',
                'periode' => 1,
                'message' => 'Absensi hari ini sudah lengkap'
            ];
        }

        // Split shift logic
        if ($shift->isSplitShift()) {
            $periode1 = $existingAttendance->where('periode', 1)->first();
            $periode2 = $existingAttendance->where('periode', 2)->first();

            if (!$periode1) {
                return [
                    'action' => 'check_in',
                    'periode' => 1,
                    'message' => 'Silakan lakukan absensi masuk periode 1'
                ];
            }

            if ($periode1->waktu_masuk && !$periode1->waktu_keluar) {
                return [
                    'action' => 'check_out',
                    'periode' => 1,
                    'message' => 'Silakan lakukan absensi keluar periode 1'
                ];
            }

            if (!$periode2) {
                $now = Carbon::now();
                $periode2Start = Carbon::parse($shift->waktu_mulai_periode2);

                if ($now->lt($periode2Start->subMinutes(60))) {
                    return [
                        'action' => 'waiting',
                        'periode' => 2,
                        'message' => 'Menunggu waktu periode 2 (mulai ' . $shift->waktu_mulai_periode2 . ')'
                    ];
                }

                return [
                    'action' => 'check_in',
                    'periode' => 2,
                    'message' => 'Silakan lakukan absensi masuk periode 2'
                ];
            }

            if ($periode2->waktu_masuk && !$periode2->waktu_keluar) {
                return [
                    'action' => 'check_out',
                    'periode' => 2,
                    'message' => 'Silakan lakukan absensi keluar periode 2'
                ];
            }

            return [
                'action' => 'completed',
                'periode' => 2,
                'message' => 'Absensi untuk kedua periode sudah lengkap'
            ];
        }

        // Regular shift with single period
        if ($existingAttendance->isEmpty()) {
            return [
                'action' => 'check_in',
                'periode' => 1,
                'message' => 'Silakan lakukan absensi masuk'
            ];
        }

        $latest = $existingAttendance->first();
        if ($latest->waktu_masuk && !$latest->waktu_keluar) {
            return [
                'action' => 'check_out',
                'periode' => 1,
                'message' => 'Silakan lakukan absensi keluar'
            ];
        }

        return [
            'action' => 'completed',
            'periode' => 1,
            'message' => 'Absensi hari ini sudah lengkap'
        ];
    }
}
