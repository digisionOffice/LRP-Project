<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Alias for driver (same as user)
    public function driver()
    {
        return $this->user();
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan');
    }

    public function uangJalan()
    {
        return $this->hasOne(UangJalan::class, 'id_do');
    }

    public function pengirimanDriver()
    {
        return $this->hasOne(PengirimanDriver::class, 'id_do');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
