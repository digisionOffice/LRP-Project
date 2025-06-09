<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UangJalan extends Model
{
    use SoftDeletes;

    protected $table = 'uang_jalan';

    protected $fillable = [
        'id_do',
        'nominal',
        'status_kirim',
        'bukti_kirim',
        'status_terima',
        'bukti_terima',
        'id_user',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'nominal' => 'float',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'id_do');
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
