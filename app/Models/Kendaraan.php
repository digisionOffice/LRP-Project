<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kendaraan extends Model
{
    use SoftDeletes;

    protected $table = 'kendaraans';

    protected $fillable = [
        'no_pol_kendaraan',
        'merk',
        'tipe',
        'kapasitas',
        'kapasitas_satuan',
        'tanggal_awal_valid',
        'tanggal_akhir_valid',
        'deskripsi',
        'created_by',
    ];

    protected $casts = [
        'tanggal_awal_valid' => 'datetime',
        'tanggal_akhir_valid' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'kapasitas' => 'float',
    ];

    public function details()
    {
        return $this->hasMany(KendaraanDetail::class, 'id_kendaraan');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'id_kendaraan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
