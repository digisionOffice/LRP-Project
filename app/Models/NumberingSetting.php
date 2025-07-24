<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberingSetting extends Model
{
    use HasFactory;

    /**
     * Define the valid entity types for numbering.
     * This acts as the single source of truth for the application.
     */
    public const TYPE_LABELS = [
        'sph' => 'SPH (Penawaran)',
        'expense_request' => 'Expense Request',
        'transaksi_penjualan' => 'Transaksi Penjualan (SO)',
        'delivery_order' => 'Delivery Order (DO)',
        // Add any other types you need here in the future
    ];

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'numbering_settings';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'prefix',
        'suffix',
        'sequence_digits',
        'format',
        'reset_frequency',
        'last_sequence',
        'last_reset_date',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'last_reset_date' => 'date',
        'last_sequence' => 'integer',
        'sequence_digits' => 'integer',
    ];
}
