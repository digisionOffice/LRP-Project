<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoice';

    protected $fillable = [
        'id_transaksi_penjualan',
        'nomor_invoice',
        'tanggal_invoice',
        'total_amount',
        'biaya_ongkos_angkut',
        'biaya_pbbkb',
        'biaya_operasional_kerja',
        'include_ppn',
        'include_pbbkb',
        'include_operasional_kerja',
        'status_invoice',
        'status_kirim',
        'status_arsip',
        'status_konfirmasi',
        'status_bayar',
        'tanggal_kirim',
        'metode_kirim',
        'penerima',
        'lokasi_arsip',
        'catatan_arsip',
        'tanggal_arsip',
        'tanggal_konfirmasi_diterima',
        'nominal_bayar',
        'tanggal_bayar',
        'metode_bayar',
        'referensi_bayar',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_invoice' => 'date',
        'tanggal_kirim' => 'datetime',
        'tanggal_arsip' => 'datetime',
        'tanggal_konfirmasi_diterima' => 'datetime',
        'tanggal_bayar' => 'date',
        'total_amount' => 'decimal:2',
        'biaya_ongkos_angkut' => 'decimal:2',
        'biaya_pbbkb' => 'decimal:2',
        'biaya_operasional_kerja' => 'decimal:2',
        'nominal_bayar' => 'decimal:2',
        'include_ppn' => 'boolean',
        'include_pbbkb' => 'boolean',
        'include_operasional_kerja' => 'boolean',
    ];

    /**
     * Get the sales transaction that owns the invoice.
     */
    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi');
    }

    /**
     * Get the delivery order through the sales transaction.
     */
    public function deliveryOrder()
    {
        return $this->belongsTo(
            DeliveryOrder::class,
            'id_do'
        );
    }

    /**
     * Get the receipts for the invoice.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'id_invoice');
    }

    /**
     * Get the tax invoice for the invoice.
     */
    public function taxInvoice()
    {
        return $this->hasOne(TaxInvoice::class, 'id_invoice');
    }

    /**
     * Get the user who created the invoice.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue()
    {
        return $this->status_bayar !== 'lunas';
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->status_bayar === 'lunas';
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
            'id_transaksi_penjualan', // Local key on Invoice table
            'id_pelanggan' // Local key on TransaksiPenjualan table
        );
    }

    // Add accessor methods for compatibility with PDF template
    public function getNomorInvoiceAttribute()
    {
        return $this->attributes['nomor_invoice'] ?? null;
    }

    public function getNamaPelangganAttribute()
    {
        return $this->transaksiPenjualan?->pelanggan?->nama ?? 'Unknown Customer';
    }

    public function getAlamatPelangganAttribute()
    {
        return $this->transaksiPenjualan?->pelanggan?->alamat ?? null;
    }

    public function getNpwpPelangganAttribute()
    {
        return $this->transaksiPenjualan?->pelanggan?->npwp ?? null;
    }

    public function getTanggalJatuhTempoAttribute()
    {
        // Calculate due date as 30 days from invoice date
        return $this->tanggal_invoice ? $this->tanggal_invoice->addDays(30) : null;
    }

    public function getSubtotalAttribute()
    {
        return $this->total_amount ?? 0;
    }

    public function getTotalPajakAttribute()
    {
        // Calculate 11% tax only if PPN is included
        if ($this->include_ppn) {
            return ($this->total_amount ?? 0) * 0.11;
        }
        return 0;
    }

    public function getTotalInvoiceAttribute()
    {
        $subtotal = $this->total_amount ?? 0;
        $pajak = $this->include_ppn ? ($this->total_pajak ?? ($subtotal * 0.11)) : 0;
        $operasional = $this->include_operasional_kerja ? ($this->biaya_operasional_kerja ?? 0) : 0;
        $pbbkb = $this->include_pbbkb ? ($this->biaya_pbbkb ?? 0) : 0;

        return $subtotal + $pajak + $operasional + $pbbkb;
    }

    public function getTotalTerbayarAttribute()
    {
        return $this->nominal_bayar ?? 0;
    }

    public function getSisaTagihanAttribute()
    {
        return $this->getTotalInvoiceAttribute() - $this->getTotalTerbayarAttribute();
    }

    public function getStatusAttribute()
    {
        return match ($this->status_bayar) {
            'lunas' => 'paid',
            'sebagian' => 'sent',
            'belum_dibayar' => 'sent',
            default => 'draft'
        };
    }
}
