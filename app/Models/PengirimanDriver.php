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
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'waktu_mulai' => 'datetime',
        'waktu_tiba' => 'datetime',
        'waktu_pool_arrival' => 'datetime',
        'approved_at' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Approval workflow methods
    public function canBeApproved(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function canBeRejected(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function approve(User $approver, ?string $notes = null): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        return $this->update([
            'approval_status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $approver, ?string $notes = null): bool
    {
        if (!$this->canBeRejected()) {
            return false;
        }

        return $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
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
