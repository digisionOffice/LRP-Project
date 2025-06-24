<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales_transactions';

    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'customer_name',
        'payment_method',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'created_by',
    ];

    protected $dates = ['deleted_at', 'transaction_date'];

    protected $casts = [
        'transaction_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function journals()
    {
        return $this->morphMany(Journal::class, 'source');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Auto-generate transaction code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = static::generateTransactionCode();
            }
        });
    }

    public static function generateTransactionCode()
    {
        $year = date('Y');
        $month = date('m');
        $lastTransaction = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? (int)substr($lastTransaction->transaction_code, -4) + 1 : 1;

        return sprintf('TRX-%s%s-%04d', $year, $month, $sequence);
    }
}
