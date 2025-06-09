<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanDetail extends Model
{
    use SoftDeletes;

    protected $table = 'penjualan_detail';

    protected $fillable = [
        'id_transaksi_penjualan',
        'id_item',
        'volume_item',
        'harga_jual',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'volume_item' => 'float',
        'harga_jual' => 'float',
    ];

    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
