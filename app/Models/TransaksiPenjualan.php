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
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
        'attachment_size',
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

    /**
     * Check if the sales order has an attachment
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    /**
     * Get the attachment URL for download
     */
    public function getAttachmentUrl(): ?string
    {
        if (!$this->hasAttachment()) {
            return null;
        }

        return asset('storage/' . $this->attachment_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSize(): ?string
    {
        if (!$this->attachment_size) {
            return null;
        }

        $bytes = $this->attachment_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension from mime type
     */
    public function getFileExtension(): ?string
    {
        if (!$this->attachment_mime_type) {
            return null;
        }

        $mimeToExt = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'text/plain' => 'txt',
        ];

        return $mimeToExt[$this->attachment_mime_type] ?? 'file';
    }
}
