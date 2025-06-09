<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tbbm extends Model
{
    use SoftDeletes;

    protected $table = 'tbbms';

    protected $fillable = [
        'kode',
        'nama',
        'pic_nama',
        'pic_phone',
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

    public function transaksiPenjualan()
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_tbbm');
    }

    public function transaksiPembelian()
    {
        return $this->hasMany(TransaksiPembelian::class, 'id_TBBM');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
