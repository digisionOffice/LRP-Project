<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenjualan extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'transaksi_penjualan';

    protected $fillable = [
        'kode',
        'tipe',
        'tanggal',
        'id_pelanggan',
        'id_alamat_pelanggan',
        'nomor_po',
        'nomor_sph',
        'data_dp',
        'top_pembayaran',
        'id_tbbm',
        'id_akun_pendapatan',
        'id_akun_piutang',
        'created_by',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal' => 'datetime',
        'data_dp' => 'decimal:2',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function alamatPelanggan()
    {
        return $this->belongsTo(AlamatPelanggan::class, 'id_alamat_pelanggan');
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

    /**
     * Register media collections for the TransaksiPenjualan model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dokumen_sph')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

        $this->addMediaCollection('dokumen_dp')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

        $this->addMediaCollection('dokumen_po')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Register media conversions for the TransaksiPenjualan model
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('dokumen_sph', 'dokumen_dp', 'dokumen_po');

        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300)
            ->performOnCollections('dokumen_sph', 'dokumen_dp', 'dokumen_po');
    }

    public function getDokumenSphUrlAttribute()
    {
        return $this->getFirstMediaUrl('dokumen_sph');
    }

    public function getDokumenDpUrlAttribute()
    {
        return $this->getFirstMediaUrl('dokumen_dp');
    }

    public function getDokumenPoUrlAttribute()
    {
        return $this->getFirstMediaUrl('dokumen_po');
    }

    /**
     * Get the invoices associated with the sales transaction.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'id_transaksi');
    }

    /**
     * Get the receipts associated with the sales transaction.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'id_transaksi');
    }

    /**
     * Get the tax invoices associated with the sales transaction.
     */
    public function taxInvoices()
    {
        return $this->hasMany(TaxInvoice::class, 'id_transaksi');
    }

    // delivery order
    public function getDeliveryOrderUrlAttribute()
    {
        return $this->deliveryOrder ? route('filament.admin.resources.delivery-orders.view', ['record' => $this->deliveryOrder->id]) : null;
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TransaksiPenjualanApproval::class, 'id_transaksi_penjualan')->latest();
    }
}
