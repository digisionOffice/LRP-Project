<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'shift';

    protected $fillable = [
        'nama_shift',
        'waktu_mulai',
        'waktu_selesai',
        'toleransi_keterlambatan',
        'is_split_shift',
        'waktu_mulai_periode2',
        'waktu_selesai_periode2',
        'toleransi_keterlambatan_periode2',
        'keterangan',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_split_shift' => 'boolean',
        'waktu_mulai' => 'datetime:H:i:s',
        'waktu_selesai' => 'datetime:H:i:s',
        'waktu_mulai_periode2' => 'datetime:H:i:s',
        'waktu_selesai_periode2' => 'datetime:H:i:s',
    ];

    /**
     * Get the schedules associated with this shift
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'shift_id');
    }

    /**
     * Get the user who created this shift
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the bulk schedules associated with this shift
     */
    public function jadwalMasal()
    {
        return $this->hasMany(JadwalMasal::class, 'shift_id');
    }

    /**
     * Check if this is a split shift
     */
    public function isSplitShift(): bool
    {
        return $this->is_split_shift &&
            !is_null($this->waktu_mulai_periode2) &&
            !is_null($this->waktu_selesai_periode2);
    }
}
