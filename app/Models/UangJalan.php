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
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'id_user',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'approved_at' => 'datetime',
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
}
