<?php

namespace App\Services;

// --- Models ---
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestApproval;
use App\Models\User;
use App\Models\NotificationSetting;

// --- Framework & Helpers ---
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

// --- Services ---
use App\Services\MessageService;

// support
use App\Support\Formatter;

/**
 * Handles all business logic related to Expense Requests.
 * This includes creation, approval processing, and other related operations.
 */
class ExpenseRequestService
{
    /**
     * Inject the MessageService for sending notifications.
     */
    public function __construct(protected MessageService $messageService)
    {
    }

    /**
     * Processes an approval decision (approve, reject, etc.) for an Expense Request.
     *
     * @param ExpenseRequest $expenseRequest The expense request record to be processed.
     * @param User $approver The user performing the approval action.
     * @param string $status The new status ('approved', 'rejected', 'needs_revision').
     * @param string|null $note Optional notes for the approval or rejection.
     * @return ExpenseRequestApproval The newly created approval record.
     */
    public function processApproval(ExpenseRequest $expenseRequest, User $approver, string $status, ?string $note, ?float $approvedAmount = null): ExpenseRequestApproval
    {
        // Use a database transaction to ensure data integrity.
        // All operations will succeed, or none will.
        $approval = DB::transaction(function () use ($expenseRequest, $approver, $status, $note, $approvedAmount) {

            $setnote = $note;

            if ($status === 'approved') {
                $expenseRequest->approved_amount = $approvedAmount ?? $expenseRequest->requested_amount;

                $setnote = "Nominal disetujui sebesar Rp " . Formatter::number($expenseRequest->approved_amount);
            }
            
            // 1. Create a new record in the approvals history table.
            $approvalRecord = ExpenseRequestApproval::create([
                'expense_request_id' => $expenseRequest->id,
                'user_id'          => $approver->id,
                'status'           => $status,
                'note'             => $setnote,
            ]);

            // 2. Update the main status on the parent ExpenseRequest record.
            $expenseRequest->status = match ($status) {
                'approved'       => 'approved',
                'rejected'       => 'rejected',
                'needs_revision' => 'under_review',
                default          => $expenseRequest->status,
            };

            $expenseRequest->save();

            return $approvalRecord;
        });

        // --- Handle Notifications (Side-Effect, outside the DB transaction) ---
        // This logic is kept separate so that a notification failure does not
        // roll back the successful approval in the database.
        try {
            // Eager load the requester to avoid extra queries
            $expenseRequest->loadMissing('requestedBy');
            $requester = $expenseRequest->requestedBy;

            // --- A. Notify the Requester ---
            if ($requester && !empty($requester->hp)) {
                // Prepare data objects once to be passed to the MessageService.
                $requesterData = (object) ['name' => $requester->name, 'hp' => $requester->hp];
                $approverData = (object) ['name' => $approver->name];
                $expenseData = (object) [
                    'id'              => $expenseRequest->id,
                    'request_number'  => $expenseRequest->request_number,
                    'approved_amount' => $expenseRequest->approved_amount,
                    'note'            => $note, // The note/reason from the approver
                ];

                // Call the appropriate notification method based on the status.
                match ($status) {
                    'approved'      => $this->messageService->sendExpenseApprovedNotification($requesterData, $approverData, $expenseData),
                    'rejected'      => $this->messageService->sendExpenseRejectedNotification($requesterData, $approverData, $expenseData),
                    'needs_revision'=> $this->messageService->sendExpenseNeedsRevisionNotification($requesterData, $approverData, $expenseData),
                    default         => null,
                };
            }

            // --- B. Notify the Finance Team if the status is 'approved' ---
            if ($status === 'approved') {
                $this->notifyFinanceTeam($expenseRequest, $requester, $approver);
            }

        } catch (Throwable $e) {
            // Log any critical failure in contacting the notification service.
            Log::error('Failed to contact notification service for expense approval.', [
                'expense_request_id' => $expenseRequest->id,
                'error_message'      => $e->getMessage(),
            ]);
        }

        return $approval;
    }

    /**
     * Finds and notifies the finance team based on notification settings.
     * This is a private helper method called by processApproval.
     *
     * @param ExpenseRequest $expenseRequest
     * @param User $requester
     * @param User $approver
     * @return void
     */
    private function notifyFinanceTeam(ExpenseRequest $expenseRequest, User $requester, User $approver): void
    {
        // 1. Define the specific event name for this notification.
        $eventName = 'expense_approved_for_finance';

        // 2. Find all active notification rules for this event.
        $settings = NotificationSetting::with('user')
            ->where('event_name', $eventName)
            ->where('is_active', true)
            ->get();

        if ($settings->isEmpty()) {
            Log::info("No active notification settings found for event '{$eventName}'.");
            return;
        }

        // 3. Prepare the data transfer objects (DTOs).
        $requesterData = (object) ['name' => $requester->name];
        $approverData = (object) ['name' => $approver->name];
        $expenseData = (object) [
            'id'              => $expenseRequest->id,
            'request_number'  => $expenseRequest->request_number,
            'approved_amount' => $expenseRequest->approved_amount,
        ];

        // 4. Send a notification to every user found in the settings.
        foreach ($settings as $setting) {
            $financeUser = $setting->user;
            if ($financeUser && !empty($financeUser->hp)) {
                $financeUserData = (object) ['name' => $financeUser->name, 'hp' => $financeUser->hp];
                
                // Call the specific method for finance in the MessageService
                $this->messageService->sendFinanceNotificationForApprovedExpense(
                    $financeUserData,
                    $requesterData,
                    $approverData,
                    $expenseData
                );
            }
        }
    }

    /**
     * Marks an expense request as paid and posts its journal entry.
     *
     * @param ExpenseRequest $expenseRequest
     * @return ExpenseRequest The updated expense request record.
     */
    public function markAsPaid(ExpenseRequest $expenseRequest): ExpenseRequest
    {
        // Use a transaction to ensure both updates happen or neither does.
        return DB::transaction(function () use ($expenseRequest) {
            $expenseRequest->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_by' => auth()->id()
            ]);

            // Call the existing model method to post the journal
            $expenseRequest->postJournalEntry();

            // --- Start Notification Logic for Paid Expense ---
            try {
                // Eager load necessary relationships for notifications
                $expenseRequest->loadMissing(['requestedBy.divisi']);
                $requester = $expenseRequest->requestedBy;

                if (!$requester) {
                    Log::warning("Skipping expense paid notification: Requester not found for ExpenseRequest ID: {$expenseRequest->id}.");
                    return $expenseRequest; // Return early, but the transaction is committed.
                }

                // Prepare common expense data for notifications
                $expenseData = (object) [
                    'id'             => $expenseRequest->id,
                    'request_number' => $expenseRequest->request_number,
                    'title'          => $expenseRequest->title,
                    'approved_amount'=> $expenseRequest->approved_amount,
                    'paid_at'        => $expenseRequest->paid_at,
                ];

                // 1. Notify the Requester
                if (!empty($requester->hp)) {
                    $requesterData = (object) ['name' => $requester->name, 'hp' => $requester->hp];
                    $this->messageService->sendExpensePaidNotificationToRequester($requesterData, $expenseData);
                    Log::info("Sent expense paid notification to requester {$requester->name} for request {$expenseRequest->request_number}.");
                } else {
                    Log::warning("Skipping expense paid notification to requester: Requester {$requester->name} has no phone number.");
                }

                // 2. Notify the Requester's Manager (using NotificationSetting)
                if ($requester->divisi) {
                    $divisionSlug = \Illuminate\Support\Str::slug($requester->divisi->nama, '_');
                    $eventName = "expense_manager_update_{$divisionSlug}"; // Unified event name

                    $managerSettings = NotificationSetting::with('user')
                        ->where('event_name', $eventName)
                        ->where('is_active', true)
                        ->get();

                    if ($managerSettings->isEmpty()) {
                        Log::info("No active notification settings found for manager for event '{$eventName}'.");
                    } else {
                        $requesterForManagerData = (object) ['name' => $requester->name]; // Only name needed for manager notification

                        foreach ($managerSettings as $setting) {
                            $managerUser = $setting->user;
                            if ($managerUser && !empty($managerUser->hp)) {
                                $managerData = (object) ['name' => $managerUser->name, 'hp' => $managerUser->hp]; // Ensure HP is included
                                $this->messageService->sendExpenseManagerUpdateNotification($managerData, $requesterForManagerData, $expenseData, 'paid');
                                Log::info("Sent expense paid notification to manager {$managerUser->name} for requester {$requester->name}'s request {$expenseRequest->request_number}.");
                            } else {
                                Log::warning("Skipping expense paid notification to manager: Manager user from setting (ID: {$setting->user_id}) has no phone number or user not found.");
                            }
                        }
                    }
                } else {
                    Log::warning("Skipping expense paid notification to manager: Requester {$requester->name} has no division set.");
                }

            } catch (Throwable $e) {
                Log::error('Failed to send expense paid notifications.', [
                    'expense_request_id' => $expenseRequest->id,
                    'error_message'      => $e->getMessage(),
                    'trace'              => $e->getTraceAsString(),
                ]);
            }
            // --- End Notification Logic for Paid Expense ---

            return $expenseRequest;
        });
    }

    /**
     * Handles sending notifications when a new expense request is created.
     * This method contains all the business logic, keeping the model clean.
     *
     * @param ExpenseRequest $expenseRequest The newly created record.
     * @return void
     */
    public function handleCreationNotification(ExpenseRequest $expenseRequest): void
    {
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
            $settings = NotificationSetting::findActiveRecipientsForEvent($eventName);
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
                // ADDED: Skip notification if the manager is the same as the requester.
                if ($manager->id === $requester->id) {
                    Log::info("Skipping new expense notification: Manager {$manager->name} is the same as the requester for ExpenseRequest ID: {$expenseRequest->id}.");
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
    }

    /**
     * Handles sending notifications when a staff member submits a revision.
     *
     * @param ExpenseRequest $expenseRequest The updated record.
     * @return void
     */
    public function handleRevisionSubmissionNotification(ExpenseRequest $expenseRequest): void
    {
        try {
            // 2. The rest of the notification logic is very similar to the creation handler.
            $expenseRequest->loadMissing('requestedBy.divisi');
            $requester = $expenseRequest->requestedBy;
    
            if (!$requester || !$requester->divisi) {
                Log::info("Skipping revision update: Requester or division not found for ExpenseRequest ID {$expenseRequest->id}.");
                return;
            }
    
            $divisionSlug = Str::slug($requester->divisi->nama, '_');
            $eventName = "expense_manager_update_{$divisionSlug}";
    
            $settings = NotificationSetting::findActiveRecipientsForEvent($eventName);
    
            if ($settings->isEmpty()) {
                Log::info("No active notification settings found for revision update event '{$eventName}'.");
                return;
            }
    
            // Prepare the data for the notification message
            $requesterData = (object) ['name' => $requester->name];
            $expenseData = (object) [
                'id'             => $expenseRequest->id,
                'request_number' => $expenseRequest->request_number,
                'title'          => $expenseRequest->title,
                'description'    => $expenseRequest->description,
                'amount'         => $expenseRequest->requested_amount,
            ];
    
            // Send the notification to all relevant managers
            foreach ($settings as $setting) {
                $manager = $setting->user;
                if (!$manager || empty($manager->hp)) continue;
    
                $managerData = (object) ['name' => $manager->name, 'hp' => $manager->hp];
                $this->messageService->sendExpenseManagerUpdateNotification(
                    $managerData,
                    $requesterData,
                    $expenseData,
                    'expense_update' // A new type for your MessageService switch case
                );
            }
        } catch (Throwable $e) {
            Log::error('Failed to send expense revision update notification.', [
                'expense_request_id' => $expenseRequest->id,
                'error_message'      => $e->getMessage(),
            ]);
        }
    }
}
