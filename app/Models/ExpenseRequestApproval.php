<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequestApproval extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'expense_request_approvals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'expense_request_id',
        'user_id',
        'status',
        'note',
        'step_sequence',
    ];

    /**
     * Get the expense request that this approval belongs to.
     */
    public function expenseRequest(): BelongsTo
    {
        return $this->belongsTo(ExpenseRequest::class, 'expense_request_id');
    }

    /**
     * Get the user who performed this approval action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
