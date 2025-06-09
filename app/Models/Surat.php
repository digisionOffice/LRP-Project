<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table = 'surat';

    protected $fillable = [
        'nomor_surat',
        'jenis_surat',
        'tanggal_surat',
        'id_pelanggan',
        'id_supplier',
        'isi_surat',
        'file_dokumen',
        'status',
        'status_pembayaran',
        'tanggal_pembayaran',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tanggal_surat' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
