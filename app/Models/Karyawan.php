<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use SoftDeletes;
    protected $table = 'karyawan';

    protected $fillable = [
        'no_induk',
        'nama',
        'hp',
        'email',
        'id_jabatan',
        'id_divisi',
        'id_entitas',
        'id_user',
        'supervisor_id',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'hp' => 'string',
        'email' => 'string',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function entitas()
    {
        return $this->belongsTo(Entitas::class, 'id_entitas');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'id_karyawan');
    }

    public function uangJalan()
    {
        return $this->hasMany(UangJalan::class, 'id_karyawan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the supervisor for this employee
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get all attendance records for this employee
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Get all schedules for this employee
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'karyawan_id');
    }

    /**
     * Get attendance records for a specific date
     */
    public function getAttendanceForDate($date)
    {
        return $this->absensi()->whereDate('tanggal_absensi', $date)->get();
    }

    /**
     * Get today's schedule
     */
    public function getTodaySchedule()
    {
        return $this->schedules()
            ->with('shift')
            ->whereDate('tanggal_jadwal', now()->toDateString())
            ->first();
    }
}
