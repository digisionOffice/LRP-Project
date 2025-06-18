<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use SoftDeletes;

    protected $table = 'receipt';

    protected $fillable = [
        'nomor_receipt',
        'id_invoice',
        'id_do',
        'id_transaksi',
        'tanggal_receipt',
        'tanggal_pembayaran',
        'metode_pembayaran',
        'referensi_pembayaran',
        'jumlah_pembayaran',
        'biaya_admin',
        'total_diterima',
        'status',
        'catatan',
        'bank_pengirim',
        'bank_penerima',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_receipt' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
        'jumlah_pembayaran' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'total_diterima' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the receipt.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice');
    }

    /**
     * Get the delivery order that owns the receipt.
     */
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'id_do');
    }

    /**
     * Get the sales transaction that owns the receipt.
     */
    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi');
    }

    /**
     * Get the user who created the receipt.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if receipt is confirmed.
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Get the customer from the related sales transaction.
     */
    public function pelanggan()
    {
        return $this->hasOneThrough(
            \App\Models\Pelanggan::class,
            \App\Models\TransaksiPenjualan::class,
            'id', // Foreign key on TransaksiPenjualan table
            'id', // Foreign key on Pelanggan table
            'id_transaksi', // Local key on Receipt table
            'id_pelanggan' // Local key on TransaksiPenjualan table
        );
    }
}
