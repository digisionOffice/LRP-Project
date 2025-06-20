<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $table = 'sale_items';

    protected $fillable = [
        'sales_transaction_id',
        'item_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'total_price',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // Relationships
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // Boot method untuk auto-calculate totals
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($saleItem) {
            $saleItem->total_price = $saleItem->quantity * ($saleItem->unit_price ?? 0);
            $saleItem->total_cost = $saleItem->quantity * ($saleItem->unit_cost ?? 0);
        });
    }
}
