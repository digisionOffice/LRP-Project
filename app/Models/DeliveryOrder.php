<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DeliveryOrder extends Model
{
    protected $table = 'delivery_order';

    protected $fillable = [
        'kode',
        'id_transaksi',
        'id_user',
        'id_kendaraan',
        'tanggal_delivery',
        'no_segel',
        'status_muat',
        'waktu_muat',
        'waktu_selesai_muat',
        'volume_do',
        'sisa_volume_do',
        'do_signatory_name',
        'do_print_status',
        'fuel_usage_notes',
        'driver_allowance_amount',
        'allowance_receipt_status',
        'allowance_receipt_time',
        'do_handover_status',
        'do_handover_time',
        'invoice_number',
        'tax_invoice_number',
        'invoice_delivery_status',
        'invoice_archive_status',
        'invoice_confirmation_status',
        'invoice_confirmation_time',
        'payment_status',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_delivery' => 'datetime',
        'waktu_muat' => 'datetime',
        'waktu_selesai_muat' => 'datetime',
        'allowance_receipt_time' => 'datetime',
        'do_handover_time' => 'datetime',
        'invoice_confirmation_time' => 'datetime',
        'do_print_status' => 'boolean',
        'allowance_receipt_status' => 'boolean',
        'do_handover_status' => 'boolean',
        'invoice_delivery_status' => 'boolean',
        'invoice_archive_status' => 'boolean',
        'invoice_confirmation_status' => 'boolean',
        'driver_allowance_amount' => 'decimal:2',
        'volume_do' => 'float',
        'sisa_volume_do' => 'float',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // on update

    // if status => selesai, update status_muat to selesai

    // Alias for driver (same as user)
    public function driver()
    {
        return $this->user();
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan');
    }

    /**
     * Get the uang jalan (driver allowance) associated with the delivery order.
     */
    public function uangJalan()
    {
        return $this->hasOne(UangJalan::class, 'id_do');
    }

    /**
     * Get the pengiriman driver (driver delivery) associated with the delivery order.
     */
    public function pengirimanDriver()
    {
        return $this->hasOne(PengirimanDriver::class, 'id_do');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoice associated with the delivery order.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_do');
    }

    /**
     * Get the receipts associated with the delivery order.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'id_do');
    }

    /**
     * Get the tax invoice associated with the delivery order.
     */
    public function taxInvoice()
    {
        return $this->hasOne(TaxInvoice::class, 'id_do');
    }

    /**
     * Get the total volume from the related sales order (SO).
     */
    public function getTotalSoVolumeAttribute()
    {
        if (!$this->transaksi || !$this->transaksi->penjualanDetails) {
            return 0;
        }

        return $this->transaksi->penjualanDetails->sum('volume_item');
    }

    /**
     * Calculate remaining volume from SO after all deliveries.
     */
    public function calculateRemainingVolume()
    {
        $totalSoVolume = $this->total_so_volume;

        // Get all delivery orders for this SO except current one
        $deliveredVolume = DeliveryOrder::where('id_transaksi', $this->id_transaksi)
            ->where('id', '!=', $this->id)
            ->sum('volume_do');

        return $totalSoVolume - $deliveredVolume;
    }
}
