<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- ADDED
use Illuminate\Support\Str; // <-- ADDED

class SphApproval extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'sph_approvals';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'sph_id',
        'user_id',
        'status',
        'note',
        'step_sequence',
    ];

    /**
     * Get the SPH that this approval belongs to.
     */
    public function sph(): BelongsTo
    {
        return $this->belongsTo(Sph::class, 'sph_id');
    }

    /**
     * Get the user who performed this approval action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // --- NEW ACCESSORS ---

    /**
     * Get the formatted, human-readable label for the status.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::headline($this->status)
        );
    }

    /**
     * Get the color associated with the status for display in Filament.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'approved' => 'success',
                'rejected' => 'danger',
                'needs_revision' => 'warning',
                default => 'gray',
            }
        );
    }
}
