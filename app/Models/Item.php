<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'item';

    protected $fillable = [
        'kode',
        'name',
        'description',
        'id_item_jenis',
        'id_satuan',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function kategori()
    {
        return $this->belongsTo(ItemKategori::class, 'id_item_jenis');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuanDasar::class, 'id_satuan');
    }

    public function penjualanDetails()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_item');
    }

    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'id_item');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
