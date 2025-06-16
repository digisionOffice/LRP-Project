<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PengirimanDriver extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = 'pengiriman_driver';

    protected $fillable = [
        'id_do',
        'totalisator_awal',
        'totalisator_tiba',
        'totalisator_pool_return',
        'waktu_mulai',
        'waktu_tiba',
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_pengiriman')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('foto_totalizer_awal')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('foto_totalizer_akhir')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('foto_pengiriman', 'foto_totalizer_awal', 'foto_totalizer_akhir');

        $this->addMediaConversion('preview')
            ->width(300)
            ->height(300)
            ->performOnCollections('foto_pengiriman', 'foto_totalizer_awal', 'foto_totalizer_akhir');
    }
    public function getFotoPengirimanUrlAttribute()
    {
        return $this->getFirstMediaUrl('foto_pengiriman');
    }

    public function getFotoTotalizerAwalUrlAttribute()
    {
        return $this->getFirstMediaUrl('foto_totalizer_awal');
    }

    public function getFotoTotalizerAkhirUrlAttribute()
    {
        return $this->getFirstMediaUrl('foto_totalizer_akhir');
    }
}
