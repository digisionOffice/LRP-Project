<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SatuanDasar extends Model
{
    use SoftDeletes;

    protected $table = 'satuan_dasar';

    protected $fillable = [
        'kode',
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
        return $this->hasMany(Item::class, 'id_satuan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
