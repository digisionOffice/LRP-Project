<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakturPajak extends Model
{
    protected $table = 'faktur_pajak';

    protected $fillable = [
        'nomor_faktur',
        'id_transaksi_penjualan',
        'tanggal_faktur',
        'npwp_pelanggan',
        'nama_pelanggan',
        'total_dpp',
        'total_ppn',
        'status',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_faktur' => 'datetime',
    ];

    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
