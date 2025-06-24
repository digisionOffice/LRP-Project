<?php

namespace App\Services;


use App\Models\TransaksiPenjualan;
use App\Models\TransaksiPenjualanDetail;
use App\Models\TransaksiPenjualanApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Exceptions\TransactionException; // Recommended to create a custom exception
use Illuminate\Support\Facades\Log; // Add this at the top of your file
use Throwable; // Add this at the top of your file


// Import your future notification classes here
// use App\Notifications\PenjualanApproved;
// use App\Notifications\PenjualanRejected;
// use App\Notifications\NewPenjualanForApproval;

/**
 * Handles all business logic related to Sales Transactions (Transaksi Penjualan).
 * This includes creation, approval processing, and other related operations.
 */
class TransaksiPenjualanService
{
    public function __construct(protected MessageService $messageService)
    {
        // MessageService is now injected
    }

    /**
     * Create a new sales transaction and its detail items.
     *
     * @param User $salesperson The user creating the transaction.
     * @param array $data The data for the transaction, including items.
     * @return TransaksiPenjualan The newly created sales transaction.
     */
    public function createTransaksi(User $salesperson, array $data): TransaksiPenjualan
    {
        return DB::transaction(function () use ($salesperson, $data) {
            // 1. Calculate total amount from items
            $totalAmount = array_reduce($data['items'], function ($sum, $item) {
                return $sum + ($item['quantity'] * $item['price']);
            }, 0);

            // 2. Create the parent transaction record
            $transaksi = TransaksiPenjualan::create([
                'user_id' => $salesperson->id,
                'customer_id' => $data['customer_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending_approval', // Initial status
                // ... any other fields for the parent transaction
            ]);

            // 3. Create the detail records
            foreach ($data['items'] as $item) {
                $transaksi->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            
            // 4. (Optional) Notify the approver that a new transaction needs review
            // $approver = User::where('position', 'Manager')->first();
            // Notification::send($approver, new NewPenjualanForApproval($transaksi));

            return $transaksi;
        });
    }

    /**
     * Process an approval decision for a sales transaction.
     *
     * @param TransaksiPenjualan $transaksi The transaction being approved.
     * @param User $approver The user making the decision.
     * @param string $status The approval status ('approved', 'rejected', 'reject_with_perbaikan').
     * @param ?string $note An optional note for the decision.
     * @return TransaksiPenjualanApproval The created approval record.
     */
    public function processApproval(TransaksiPenjualan $transaksi, User $approver, string $status, ?string $note): TransaksiPenjualanApproval
    {
        // The main transaction for database operations
        $approval = DB::transaction(function () use ($transaksi, $approver, $status, $note) {
            // Corrected variable names to match conventions, but your original names are fine too.
            $approvalRecord = TransaksiPenjualanApproval::create([
                'id_transaksi_penjualan' => $transaksi->id,
                'user_id' => $approver->id,
                'status' => $status,
                'note' => $note,
            ]);

            // Update the parent transaction's status
            if ($status === 'approved') {
                $transaksi->status = 'approved';
            } elseif ($status === 'rejected') {
                $transaksi->status = 'rejected';
            } elseif ($status === 'reject_with_perbaikan') {
                $transaksi->status = 'needs_revision';
            }
            $transaksi->save();

            return $approvalRecord;
        });

        // --- Error handling for notifications happens OUTSIDE the database transaction ---
        
        try {
            if ($transaksi->user?->hp) { // Check if salesperson and phone number exist
                // I've corrected the use of $decision to $status
                match ($status) {
                    'approved' => $this->messageService->sendPenjualanApprovedNotification($transaksi, $approver),
                    'rejected' => $this->messageService->sendPenjualanRejectedNotification($transaksi, $approver, $note),
                    'reject_with_perbaikan' => $this->messageService->sendPenjualanNeedsRevisionNotification($transaksi, $approver, $note),
                    default => null,
                };
            }
        } catch (Throwable $e) {
            // If the MessageService fails, catch the exception.
            // The approval is already saved, so we don't need to panic.
            
            // 1. Log the error for developers to investigate later.
            Log::error('Failed to send approval notification for Transaksi ID: ' . $transaksi->id, [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Optional: for detailed debugging
            ]);

            // 2. You could also add logic here to notify your team via Slack or email.
        }

        return $approval;
    }

    // You can add other methods here in the future, for example:
    // public function processPayment(TransaksiPenjualan $transaksi, float $amount) { ... }
    // public function cancelTransaction(TransaksiPenjualan $transaksi, User $canceller) { ... }
}
