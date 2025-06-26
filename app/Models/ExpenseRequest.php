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
use App\Models\NotificationSetting;

// call services
use App\Services\MessageService;
use App\Services\JournalingService;

// call filament resource
use App\Filament\Resources\ExpenseRequestResource;

class ExpenseRequest extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;

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
            try {
                // 1. Resolve the MessageService from Laravel's service container.
                $messageService = resolve(MessageService::class);

                // 2. Eager load the 'requestedBy' user and their 'division' to prevent N+1 query issues.
                $expenseRequest->loadMissing('requestedBy.divisi');
                $requester = $expenseRequest->requestedBy;

                // 3. Stop if the requester cannot be found.
                if (!$requester) {
                    Log::info("Skipping notification: Requester user not found for ExpenseRequest ID: {$expenseRequest->id} (requested_by: {$expenseRequest->requested_by}).");
                    return;
                }

                // 4. Stop if the requester's division is not set.
                if (!$requester->divisi) {
                    Log::info("Skipping notification: Division not set for requester '{$requester->name}' on ExpenseRequest ID: {$expenseRequest->id}.");
                    return;
                }

                // 4. Create a dynamic event name based on the requester's division.
                //    Example: 'Finance' division becomes 'expense_request_created_finance'
                $divisionSlug = Str::slug($requester->divisi->nama, '_');
                $eventName = "expense_manager_update_{$divisionSlug}"; // Unified event name

                // 5. Find all active notification rules for this specific event.
                $settings = NotificationSetting::with('user')
                    ->where('event_name', $eventName)
                    ->where('is_active', true)
                    ->get();

                if ($settings->isEmpty()) {
                    Log::info("No active notification settings found for event '{$eventName}'.");
                    return;
                }

                // 6. Prepare the data transfer objects (DTOs) for the requester and the expense.
                //    These are created once and reused for every notification sent.
                $requesterData = (object) [
                    'name' => $requester->name,
                ];

                $expenseData = (object) [
                    'title'       => $expenseRequest->title,
                    'amount'      => $expenseRequest->requested_amount,
                    'date'        => $expenseRequest->requested_date,
                    'category'    => $expenseRequest->category,
                    'description' => $expenseRequest->description,
                    'id'          => $expenseRequest->id,
                    'request_number' => $expenseRequest->request_number
                ];

                // 7. Loop through each notification rule and send the message.
                foreach ($settings as $setting) {
                    $manager = $setting->user;

                    // Skip if the user in the setting is invalid or has no phone number.
                    if (!$manager || empty($manager->hp)) {
                        continue;
                    }

                    // Prepare the DTO for the manager (recipient).
                    $managerData = (object) [
                        'name' => $manager->name,
                        'hp'   => $manager->hp,
                    ];

                    // Call the notification service with the prepared data objects.
                    $messageService->sendExpenseManagerUpdateNotification(
                        $managerData,
                        $requesterData,
                        $expenseData,
                        'new_request'
                    );
                }

            } catch (Throwable $e) {
                // 8. If any error occurs during the notification process, log it
                //    without crashing the main application flow.
                Log::error('Failed to send new expense request notification.', [
                    'expense_request_id' => $expenseRequest->id,
                    'error_message'      => $e->getMessage(),
                    'trace'              => $e->getTraceAsString(), // Optional: for detailed debugging
                ]);
            }
        });
    }

}
