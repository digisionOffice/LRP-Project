<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Services\JournalingService;
use App\Models\Journal;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoice';

    protected $fillable = [
        // Core invoice fields (matching database schema)
        'nomor_invoice',
        'id_do',
        'id_transaksi',
        'tanggal_invoice',
        'tanggal_jatuh_tempo',
        'nama_pelanggan',
        'alamat_pelanggan',
        'npwp_pelanggan',
        'subtotal',
        'total_pajak',
        'total_invoice',
        'total_terbayar',
        'sisa_tagihan',
        'status',
        'catatan',

        // Additional cost fields
        'biaya_ongkos_angkut',
        'biaya_pbbkb',
        'biaya_operasional_kerja',
        'include_ppn',
        'include_pbbkb',
        'include_operasional_kerja',

        // Legacy fields for backward compatibility
        'total_amount',
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
        'journal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_invoice' => 'datetime',
        'tanggal_jatuh_tempo' => 'datetime',
        'tanggal_kirim' => 'datetime',
        'tanggal_arsip' => 'datetime',
        'tanggal_konfirmasi_diterima' => 'datetime',
        'tanggal_bayar' => 'date',

        // Financial fields
        'subtotal' => 'decimal:2',
        'total_pajak' => 'decimal:2',
        'total_invoice' => 'decimal:2',
        'total_terbayar' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'biaya_ongkos_angkut' => 'decimal:2',
        'biaya_pbbkb' => 'decimal:2',
        'biaya_operasional_kerja' => 'decimal:2',
        'nominal_bayar' => 'decimal:2',

        // Boolean flags
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
     * Get the journal for the invoice.
     */
    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
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
        // Return the actual subtotal field value, not calculated
        return $this->attributes['subtotal'] ?? 0;
    }

    public function getTotalPajakAttribute()
    {
        // Return the actual total_pajak field value, not calculated
        return $this->attributes['total_pajak'] ?? 0;
    }

    public function getTotalInvoiceAttribute()
    {
        // Return the actual total_invoice field value, not calculated
        return $this->attributes['total_invoice'] ?? 0;
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
        // Check if status field exists in attributes, otherwise derive from status_bayar
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'];
        }

        return match ($this->status_bayar) {
            'lunas' => 'paid',
            'sebagian' => 'sent',
            'belum_dibayar' => 'sent',
            default => 'draft'
        };
    }

    /**
     * Get delivery order URL for navigation
     */
    public function getDeliveryOrderUrlAttribute()
    {
        if ($this->deliveryOrder) {
            return route('filament.admin.resources.delivery-orders.view', ['record' => $this->deliveryOrder]);
        }
        return null;
    }

    /**
     * Get calculated total invoice amount
     */
    public function getCalculatedTotalAttribute()
    {
        $subtotal = $this->subtotal ?? $this->total_amount ?? 0;
        $pajak = $this->include_ppn ? ($this->total_pajak ?? ($subtotal * 0.11)) : 0;
        $operasional = $this->include_operasional_kerja ? ($this->biaya_operasional_kerja ?? 0) : 0;
        $pbbkb = $this->include_pbbkb ? ($this->biaya_pbbkb ?? 0) : 0;

        return $subtotal + $pajak + $operasional + $pbbkb;
    }

    /**
     * Create journal entry using posting rules when invoice is created
     */
    public function createJournalEntry(): ?Journal
    {
        if ($this->journal_id) {
            return $this->journal; // Journal already exists
        }

        try {
            $journalingService = new JournalingService();
            $journalingService->postTransaction('Invoice', $this);

            // Find the created journal
            $journal = Journal::where('source_type', 'Invoice')
                ->where('source_id', $this->id)
                ->latest()
                ->first();

            if ($journal) {
                $this->update(['journal_id' => $journal->id]);
                return $journal;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to create journal entry for invoice', [
                'invoice_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get transaction amount for posting rules
     */
    public function getTransactionAmount(): float
    {
        return (float) $this->getTotalInvoiceAttribute();
    }

    /**
     * Get transaction date for posting rules
     */
    public function getTransactionDate(): \Carbon\Carbon
    {
        return $this->tanggal_invoice ?? $this->created_at;
    }

    /**
     * Get transaction code for posting rules
     */
    public function getTransactionCode(): string
    {
        return $this->nomor_invoice;
    }
}
