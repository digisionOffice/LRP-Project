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

    public function processApproval(TransaksiPenjualan $transaksi, User $approver, string $status, ?string $note): TransaksiPenjualanApproval
    {
        // DB Transaction tetap tidak berubah, karena ini untuk integritas data.
        $approval = DB::transaction(function () use ($transaksi, $approver, $status, $note) {
            // ... (logika create approval dan update transaksi tetap sama) ...
            $approvalRecord = TransaksiPenjualanApproval::create([
                'id_transaksi_penjualan' => $transaksi->id,
                'user_id' => $approver->id,
                'status' => $status,
                'note' => $note,
            ]);

            $transaksi->status = match ($status) {
                'approved' => 'approved',
                'rejected' => 'rejected',
                'reject_with_perbaikan' => 'needs_revision',
                default => $transaksi->status,
            };
            $transaksi->save();

            return $approvalRecord;
        });

        // --- LOGIKA NOTIFIKASI YANG DISEMPURNAKAN ---
        try {
            if ($transaksi->createdBy->hp) {
                $salesPerson = $transaksi->createdBy;
                // Panggil service dan tangkap hasilnya ke variabel $waResponse
                $waResponse = match ($status) {
                    'approved' => $this->messageService->sendPenjualanApprovedNotification($transaksi, $salesPerson, $approver),
                    'rejected' => $this->messageService->sendPenjualanRejectedNotification($transaksi, $salesPerson, $approver, $note),
                    'reject_with_perbaikan' => $this->messageService->sendPenjualanNeedsRevisionNotification($transaksi, $salesPerson, $approver, $note),
                    default => null,
                };

                // Jika $waResponse ada DAN status di dalamnya false, maka catat sebagai Peringatan.
                if ($waResponse && ($waResponse['status'] ?? false) === false) {
                    Log::warning('Notifikasi WA Gagal Terkirim (dijawab GAGAL oleh provider)', [
                        'transaksi_id' => $transaksi->id,
                        'status_approval' => $status,
                        'provider_response' => $waResponse, // Penting untuk debugging!
                    ]);
                }
            }
        } catch (Throwable $e) {
            // Blok ini menangani jika service notifikasi tidak bisa dihubungi sama sekali.
            Log::error('Gagal Menghubungi Service Notifikasi', [
                'transaksi_id' => $transaksi->id,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $approval;
    }
}
