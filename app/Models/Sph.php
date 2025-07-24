<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Sph extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'sph';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'sph_number',
        'customer_id',
        'sph_date',
        'valid_until_date',
        'status',
        'created_by',
        'terms_and_conditions',
        'notes_internal',
        'total_amount',
        'opsional_pic'
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'sph_date' => 'date',
        'valid_until_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the customer that this SPH belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'customer_id');
    }

    /**
     * Get the user who created this SPH.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the detail items for this SPH.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SphDetail::class, 'sph_id');
    }

    /**
     * Get all of the approval steps for this SPH.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(SphApproval::class, 'sph_id')->latest();
    }

    /**
     * Check if the SPH can be approved.
     * @return bool
     */
    public function isApprovable(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Determine if the SPH can be edited.
     * An SPH is editable if it's still a draft or pending approval.
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Get the formatted, human-readable label for the SPH status.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'draft' => 'Draft',
                'pending_approval' => 'Menunggu Approval',
                'needs_revision' => 'Butuh Revisi', // <-- ADDED
                'sent' => 'Terkirim',
                'approved' => 'Disetujui', // <-- UPDATED from 'accepted'
                'rejected' => 'Ditolak',
                'expired' => 'Kadaluarsa',
                default => Str::headline($this->status),
            }
        );
    }

    /**
     * Get the color associated with the SPH status for display in Filament.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'draft' => 'gray',
                'pending_approval' => 'warning',
                'needs_revision' => 'warning', // <-- ADDED
                'sent' => 'info',
                'approved' => 'success', // <-- UPDATED from 'accepted'
                'rejected' => 'danger',
                'expired' => 'danger',
                default => 'gray',
            }
        );
    }
}
