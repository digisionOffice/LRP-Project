<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengirimanDriver extends Model
{
    use SoftDeletes;

    protected $table = 'pengiriman_driver';

    protected $fillable = [
        'id_do',
        'totalisator_awal',
        'totalisator_tiba',
        'waktu_mulai',
        'waktu_tiba',
        'foto_pengiriman',
        'totalisator_pool_return',
        'waktu_pool_arrival',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'waktu_mulai' => 'datetime',
        'waktu_tiba' => 'datetime',
        'waktu_pool_arrival' => 'datetime',
        'totalisator_awal' => 'float',
        'totalisator_tiba' => 'float',
        'totalisator_pool_return' => 'float',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'id_do');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
