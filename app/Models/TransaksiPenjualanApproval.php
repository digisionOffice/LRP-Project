<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenjualanApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_transaksi_penjualan',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
