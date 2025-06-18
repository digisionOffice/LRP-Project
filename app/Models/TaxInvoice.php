<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxInvoice extends Model
{
    use SoftDeletes;

    protected $table = 'tax_invoice';

    protected $fillable = [
        'nomor_tax_invoice',
        'id_invoice',
        'id_do',
        'id_transaksi',
        'tanggal_tax_invoice',
        'nama_pelanggan',
        'alamat_pelanggan',
        'npwp_pelanggan',
        'nama_perusahaan',
        'alamat_perusahaan',
        'npwp_perusahaan',
        'dasar_pengenaan_pajak',
        'tarif_pajak',
        'pajak_pertambahan_nilai',
        'total_tax_invoice',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_tax_invoice' => 'datetime',
        'dasar_pengenaan_pajak' => 'decimal:2',
        'tarif_pajak' => 'decimal:2',
        'pajak_pertambahan_nilai' => 'decimal:2',
        'total_tax_invoice' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the tax invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice');
    }

    /**
     * Get the delivery order that owns the tax invoice.
     */
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'id_do');
    }

    /**
     * Get the sales transaction that owns the tax invoice.
     */
    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi');
    }

    /**
     * Get the user who created the tax invoice.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if tax invoice is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Calculate PPN amount based on DPP and tax rate.
     */
    public function calculatePpn()
    {
        return $this->dasar_pengenaan_pajak * ($this->tarif_pajak / 100);
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
            'id_transaksi', // Local key on TaxInvoice table
            'id_pelanggan' // Local key on TransaksiPenjualan table
        );
    }
}
