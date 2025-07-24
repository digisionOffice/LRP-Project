<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SphDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'sph_details';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'sph_id',
        'item_id',
        'description',
        'quantity',
        'harga_dasar',
        'subtotal',
        'ppn',
        'oat',
        'price',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'harga_dasar' => 'decimal:2',
        'ppn' => 'decimal:2',
    ];

    /**
     * Get the parent SPH that this detail line belongs to.
     */
    public function sph(): BelongsTo
    {
        return $this->belongsTo(Sph::class, 'sph_id');
    }

    /**
     * Get the item associated with this detail line.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
