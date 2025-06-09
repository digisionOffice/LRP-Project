<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $table = 'supplier';

    protected $fillable = [
        'kode',
        'nama',
        'pic_nama',
        'pic_phone',
        'id_subdistrict',
        'alamat',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'id_subdistrict');
    }

    public function details()
    {
        return $this->hasMany(SupplierDetail::class, 'id_supplier');
    }

    public function transaksiPembelian()
    {
        return $this->hasMany(TransaksiPembelian::class, 'id_supplier');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
