<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventories';

    protected $fillable = [
        'item_id',
        'quantity',
        'unit_cost',
        'total_value',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helper method untuk menghitung total value
    public function calculateTotalValue()
    {
        $this->total_value = $this->quantity * $this->unit_cost;
        return $this->total_value;
    }

    // Boot method untuk auto-calculate total_value
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            $inventory->total_value = $inventory->quantity * ($inventory->unit_cost ?? 0);
        });
    }
}
