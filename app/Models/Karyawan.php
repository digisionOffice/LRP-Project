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
}
