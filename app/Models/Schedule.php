<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kerja';

    protected $fillable = [
        'karyawan_id',
        'shift_id',
        'supervisor_id',
        'tanggal_jadwal',
        'waktu_masuk',
        'waktu_keluar',
        'status',
        'keterangan',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'tanggal_jadwal' => 'date',
    ];

    /**
     * Get the employee associated with this schedule
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Get the shift associated with this schedule
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the supervisor who created this schedule
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the attendance records for this schedule
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'jadwal_id');
    }
}
