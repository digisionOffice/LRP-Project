<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Item extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

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

    /**
     * Register media collections for the Item model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);
    }

    /**
     * Register media conversions for the Item model
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('images');

        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400)
            ->performOnCollections('images');

        $this->addMediaConversion('large')
            ->width(800)
            ->height(800)
            ->performOnCollections('images');
    }
}
