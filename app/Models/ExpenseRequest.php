<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_number',
        'category',
        'title',
        'description',
        'requested_amount',
        'approved_amount',
        'status',
        'priority',
        'requested_date',
        'needed_by_date',
        'justification',
        'supporting_documents',
        'requested_by',
        'approved_by',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'paid_at',
        'approval_notes',
        'rejection_reason',
        'cost_center',
        'budget_code',
        'approval_workflow',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'needed_by_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'supporting_documents' => 'array',
        'approval_workflow' => 'array',
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'tank_truck_maintenance' => 'Tank Truck Maintenance',
            'license_fee' => 'License Fee',
            'business_travel' => 'Business Travel',
            'utilities' => 'Utilities',
            'other' => 'Other Expenses',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            default => ucfirst($this->status),
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => ucfirst($this->priority),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'info',
            'under_review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'paid' => 'primary',
            default => 'gray',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'gray',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, ['submitted', 'under_review']);
    }

    public function canBePaid(): bool
    {
        return $this->status === 'approved';
    }

    public function getFormattedRequestedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->requested_amount, 0, ',', '.');
    }

    public function getFormattedApprovedAmountAttribute(): string
    {
        return $this->approved_amount ? 'Rp ' . number_format($this->approved_amount, 0, ',', '.') : '-';
    }

    public static function generateRequestNumber(string $category): string
    {
        $prefix = match ($category) {
            'tank_truck_maintenance' => 'MTN',
            'license_fee' => 'LIC',
            'business_travel' => 'TRV',
            'utilities' => 'UTL',
            'other' => 'OTH',
            default => 'EXP',
        };

        $date = now()->format('Ymd');
        $sequence = static::whereDate('created_at', now()->toDateString())
            ->where('category', $category)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
