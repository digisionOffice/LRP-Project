<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use SoftDeletes;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class, 'id_jabatan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
