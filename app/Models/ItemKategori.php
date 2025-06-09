<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemKategori extends Model
{
    use SoftDeletes;

    protected $table = 'item_kategori';

    protected $fillable = [
        'nama',
        'deskripsi',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'id_item_jenis');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
