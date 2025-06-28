<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Throwable;

// call models
use App\Models\Traits\LogsModelUpdates;
use App\Models\NotificationSetting;

// call services
use App\Services\MessageService;
use App\Services\JournalingService;
use App\Services\ExpenseRequestService;

// call filament resource
use App\Filament\Resources\ExpenseRequestResource;

class ExpenseRequest extends Model implements HasMedia
{
    use SoftDeletes, LogsModelUpdates, InteractsWithMedia;

    protected $fillable = [
        'request_number',
        'category',
        'user_id',
        'title',
        'description',
        'requested_amount',
        'approved_amount', // This can be kept to store the final approved amount
        'status',
        'priority',
        'requested_date',
        'needed_by_date',
        'justification',
        'supporting_documents',
        'requested_by',
        'submitted_at',
        'reviewed_at', // This might be moved to approvals table later
        'paid_at',
        'paid_by',
        'cost_center',
        'budget_code',
        'approval_workflow',
        'account_id',
        'journal_id',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'needed_by_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'account_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
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
        return in_array($this->status, ['submitted', 'on_review']);
    }

    public function isDeletable(): bool
    {
        return in_array($this->status, ['submitted']);
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

    /**
     * Get default account for expense category
     */
    public static function getDefaultAccountForCategory(string $category): ?Akun
    {
        $accountMapping = [
            'tank_truck_maintenance' => '5110',
            'license_fee' => '5120',
            'business_travel' => '5130',
            'utilities' => '5140',
            'other' => '5150',
        ];

        $accountCode = $accountMapping[$category] ?? null;

        if ($accountCode) {
            return Akun::where('kode_akun', $accountCode)->first();
        }

        return null;
    }

    /**
     * Create journal entry using posting rules when expense is approved
     */
    public function createJournalEntry(): ?Journal
    {
        if ($this->journal_id) {
            return $this->journal; // Journal already exists
        }

        try {
            $journalingService = new JournalingService();
            $journalingService->postTransaction('ExpenseRequest', $this);

            // Find the created journal
            $journal = Journal::where('source_type', 'ExpenseRequest')
                ->where('source_id', $this->id)
                ->latest()
                ->first();

            if ($journal) {
                $this->update(['journal_id' => $journal->id]);
                return $journal;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to create journal entry for expense request', [
                'expense_request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Post journal entry when expense is paid
     */
    public function postJournalEntry(): bool
    {
        if (!$this->journal_id) {
            return false;
        }

        $journal = $this->journal;
        if ($journal && $journal->status === 'Draft') {
            $journal->update(['status' => 'Posted']);
            return true;
        }

        return false;
    }

    /**
     * Get transaction amount for posting rules
     */
    public function getTransactionAmount(): float
    {
        return (float) ($this->approved_amount ?? $this->requested_amount);
    }

    /**
     * Get transaction date for posting rules
     */
    public function getTransactionDate(): \Carbon\Carbon
    {
        return $this->approved_at ?? $this->created_at;
    }

    /**
     * Get transaction code for posting rules
     */
    public function getTransactionCode(): string
    {
        return $this->request_number;
    }

    /**
     * Generate journal number
     */
    private function generateJournalNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = Journal::whereDate('created_at', now()->toDateString())->count() + 1;
        return sprintf('JRN-EXP-%s-%04d', $date, $sequence);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseRequestApproval::class, 'expense_request_id')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('supporting_documents');
        
        // Collection 'bukti_pembayaran' juga didaftarkan di sini, 
        // meskipun form-nya sudah kita hapus, untuk jaga-jaga.
        // Anda bisa menghapusnya jika yakin tidak akan dipakai lagi.
        $this->addMediaCollection('bukti_pembayaran')
            ->singleFile(); 
    }

    // booted ================================================================================================================
    protected static function booted(): void
    {
        static::created(function (ExpenseRequest $expenseRequest) {
            // handle notif WA
            resolve(ExpenseRequestService::class)->handleCreationNotification($expenseRequest);
        });

        static::updated(function (ExpenseRequest $expenseRequest) {
            // This notification should only trigger when a staff member submits a revision
            // for an expense that a manager has marked as 'needs_revision' (status: under_review).

            // 1. The expense must have been in 'under_review' status *before* this update.
            // 2. The user performing the update must be the original requester.
            // 3. The model must have actual changes.
            if (
                $expenseRequest->getOriginal('status') !== 'under_review' ||
                auth()->id() !== $expenseRequest->requested_by ||
                !$expenseRequest->isDirty()
            ) {
                return;
            }

            // handle notif WA
            resolve(ExpenseRequestService::class)->handleRevisionSubmissionNotification($expenseRequest);
        });
    }

}
