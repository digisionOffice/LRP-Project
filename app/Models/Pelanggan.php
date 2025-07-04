<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use SoftDeletes;

    protected $table = 'pelanggan';

    protected $fillable = [
        'kode',
        'type',
        'nama',
        'pic_nama',
        'pic_phone',
        'npwp',
        'id_subdistrict',
        'alamat',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'id_subdistrict');
    }

    public function alamatUtama()
    {
        return $this->hasOne(AlamatPelanggan::class, 'id_pelanggan')->where('is_primary', true);
    }

    public function alamatPelanggan()
    {
        return $this->hasMany(AlamatPelanggan::class, 'id_pelanggan');
    }

    public function details()
    {
        return $this->hasMany(PelangganDetail::class, 'id_pelanggan');
    }

    public function transaksiPenjualan()
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_pelanggan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // distric
}
