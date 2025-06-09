<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiPenjualan extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_penjualan';

    protected $fillable = [
        'kode',
        'tipe',
        'tanggal',
        'id_pelanggan',
        'id_subdistrict',
        'alamat',
        'nomor_po',
        'top_pembayaran',
        'id_tbbm',
        'id_akun_pendapatan',
        'id_akun_piutang',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'id_subdistrict');
    }

    public function tbbm()
    {
        return $this->belongsTo(Tbbm::class, 'id_tbbm');
    }

    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_transaksi_penjualan');
    }

    public function deliveryOrder()
    {
        return $this->hasOne(DeliveryOrder::class, 'id_transaksi');
    }

    public function fakturPajak()
    {
        return $this->hasOne(FakturPajak::class, 'id_transaksi_penjualan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function akunPendapatan()
    {
        return $this->belongsTo(Akun::class, 'id_akun_pendapatan');
    }

    public function akunPiutang()
    {
        return $this->belongsTo(Akun::class, 'id_akun_piutang');
    }
}
