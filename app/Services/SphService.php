<?php

namespace App\Services;

// --- Models ---
use App\Models\Sph;
use App\Models\SphApproval;
use App\Models\NotificationSetting;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Models\NumberingSetting; // <-- Import the new model

// --- Framework & Helpers ---
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Carbon\Carbon;

/**
 * Handles all business logic related to Quotations (SPH).
 */
class SphService
{
    /**
     * Inject the MessageService for sending notifications.
     */
    public function __construct(protected MessageService $messageService)
    {
    }

    /**
     * Creates a new SPH and its detail items within a database transaction.
     *
     * @param User $creator The user creating the SPH.
     * @param array $data The data from the creation form, including detail items.
     * @return Sph The newly created SPH record.
     */
    public function createSph(User $creator, array $data): Sph
    {
        Log::info('Data received for SPH creation:', $data);

        return DB::transaction(function () use ($creator, $data) {
            
            // --- REFACTORED: Calculate total amount before creating the parent record ---
            // 1. Calculate the total amount from the item details first.
            $totalAmount = collect($data['details'] ?? [])->sum(function ($item) {
                $price = (float)($item['harga_dasar'] ?? 0) + (float)($item['ppn'] ?? 0) + (float)($item['oat'] ?? 0);
                return (float)($item['quantity'] ?? 0) * $price;
            });

            Log::info('Calculated Total Amount:', ['total' => $totalAmount]);
            
            // 2. Prepare data for the main SPH record, now including the correct total.
            $sphData = array_merge($data, [
                'sph_number'   => $this->generateNumber('sph'),
                'created_by'   => $creator->id,
                'total_amount' => $totalAmount, // The correct total is passed on creation
                'status'       => 'draft',
            ]);
            unset($sphData['details']);

            // 3. Create the parent SPH record with the final total.
            $sph = Sph::create($sphData);

            // 4. Create the detail records.
            if (!empty($data['details'])) {
                foreach ($data['details'] as $item) {
                    $price = (float)($item['harga_dasar'] ?? 0) + (float)($item['ppn'] ?? 0) + (float)($item['oat'] ?? 0);
                    $subtotal = (float)($item['quantity'] ?? 0) * $price;

                    $sph->details()->create([
                        'item_id'     => $item['item_id'],
                        'description' => $item['description'] ?? "",
                        'quantity'    => $item['quantity'],
                        'harga_dasar' => $item['harga_dasar'],
                        'ppn'         => $item['ppn'],
                        'oat'         => $item['oat'],
                        'price'       => $price,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            return $sph;
        });
    }

    /**
     * Updates an existing SPH and syncs its detail items.
     */
    public function updateSph(Sph $sph, array $data): Sph
    {
        return DB::transaction(function () use ($sph, $data) {
            $totalAmount = collect($data['details'] ?? [])->sum(function ($item) {
                $price = (float)($item['harga_dasar'] ?? 0) + (float)($item['ppn'] ?? 0) + (float)($item['oat'] ?? 0);
                return (float)($item['quantity'] ?? 0) * $price;
            });

            $sph->update(array_merge($data, ['total_amount' => $totalAmount]));

            $sph->details()->delete();

            if (!empty($data['details'])) {
                foreach ($data['details'] as $item) {
                    $price = (float)($item['harga_dasar'] ?? 0) + (float)($item['ppn'] ?? 0) + (float)($item['oat'] ?? 0);
                    $subtotal = (float)($item['quantity'] ?? 0) * $price;
                    $sph->details()->create([
                        'item_id'     => $item['item_id'],
                        'description' => $item['description'] ?? "",
                        'quantity'    => $item['quantity'],
                        'harga_dasar' => $item['harga_dasar'],
                        'ppn'         => $item['ppn'],
                        'oat'         => $item['oat'],
                        'price'       => $price,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            return $sph->fresh();
        });
    }

    /**
     * Processes an internal approval decision for an SPH.
     */
    public function processApproval(Sph $sph, User $approver, string $status, ?string $note): SphApproval
    {
        $approval = DB::transaction(function () use ($sph, $approver, $status, $note) {
            // 1. Create a new record in the approvals history table.
            $approvalRecord = SphApproval::create([
                'sph_id'    => $sph->id,
                'user_id'   => $approver->id,
                'status'    => $status,
                'note'      => $note,
            ]);

            // 2. Update the main status on the parent SPH record.
            // The status from the form will be 'accepted' or 'rejected'.
            $sph->status = $status;
            $sph->save();

            return $approvalRecord;
        });

        // TODO: Add notifications later.
        return $approval;
    }

    /**
     * Submits an SPH for approval, changing its status and notifying managers.
     *
     * @param Sph $sph The SPH to be submitted.
     * @return void
     */
    public function submitForApproval(Sph $sph): void
    {
        // 1. Use a transaction to update the status.
        DB::transaction(function () use ($sph) {
            if ($sph->status !== 'draft') {
                throw new \Exception('Hanya SPH dengan status draft yang bisa diajukan.');
            }
            $sph->status = 'pending_approval';
            $sph->save();
        });

        // 2. Handle notifications outside the transaction.
        try {
            // Eager load creator and customer to prevent N+1 queries.
            $sph->loadMissing(['createdBy', 'customer']);
            $creator = $sph->createdBy;
            $customer = $sph->customer;

            if (!$creator) {
                Log::warning("Skipping SPH submission notification: Creator not found for SPH ID {$sph->id}.");
                return;
            }

            // The event name is fixed as per the request.
            $eventName = 'sph_manager_update_sales';

            // Find all active notification rules for this event.
            $settings = NotificationSetting::findActiveRecipientsForEvent($eventName);
            if ($settings->isEmpty()) {
                Log::info("No active notification settings found for event '{$eventName}'.");
                return;
            }

            // Prepare Data Transfer Objects (DTOs)
            $creatorData = (object) ['name' => $creator->name];
            $sphData = (object) [
                'id' => $sph->id,
                'sph_number' => $sph->sph_number,
                'customer_name' => $customer?->nama ?? 'N/A',
                'total_amount' => $sph->total_amount,
            ];

            // Loop through each recipient and send the notification.
            foreach ($settings as $setting) {
                $manager = $setting->user;
                if (!$manager || empty($manager->hp) || $manager->id === $creator->id) continue;

                $managerData = (object) ['name' => $manager->name, 'hp' => $manager->hp];
                $this->messageService->sendNewSphNotification($managerData, $creatorData, $sphData);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send new SPH notification.', ['sph_id' => $sph->id, 'error_message' => $e->getMessage()]);
        }
    }

    /**
     * Marks an SPH as 'sent' and automatically creates the corresponding sales transaction.
     *
     * @param Sph $sph The approved SPH to be published.
     * @return TransaksiPenjualan The newly created sales transaction.
     */
    public function publishAndCreateTransaction(Sph $sph): TransaksiPenjualan
    {
        return DB::transaction(function () use ($sph) {
            // 1. Update the SPH status to 'sent'.
            $sph->update(['status' => 'sent']);

            // 2. Eager load details to prevent N+1 queries.
            $sph->load('details.item');

            // 3. Create the new parent TransaksiPenjualan record.
            $transaksi = TransaksiPenjualan::create([
                'sph_id'              => $sph->id,
                'kode'                => $this->generateNumber('transaksi_penjualan'), // Generate a new SO number
                'customer_id'         => $sph->customer_id,
                'total_amount'        => $sph->total_amount,
                'status'              => 'pending_approval', // The new SO will need its own approval
                'created_by'          => auth()->id(), // Or $sph->created_by depending on business rule
                'top_pembayaran'      => $sph->top_pembayaran, // Copy relevant fields
                'opsional_pic'        => $sph->opsional_pic,
            ]);

            // 4. Loop through SPH details and create corresponding sales transaction details.
            foreach ($sph->details as $detail) {
                $transaksi->penjualanDetails()->create([
                    'item_id'     => $detail->item_id,
                    'description' => $detail->description,
                    'quantity'    => $detail->quantity,
                    'harga_dasar' => $detail->harga_dasar,
                    'ppn'         => $detail->ppn,
                    'oat'         => $detail->oat,
                    'price'       => $detail->price,
                    'subtotal'    => $detail->subtotal,
                ]);
            }

            // TODO: Notify relevant parties about the new Sales Order.

            return $transaksi;
        });
    }

    /**
     * Generates a new unique number based on settings in the database.
     *
     * @param string $type The entity type (e.g., 'sph', 'expense_request')
     * @return string The generated number string.
     */
    public function generateNumber(string $type): string
    {
        // Use a transaction with locking to prevent two users from getting the same number.
        return DB::transaction(function () use ($type) {
            // 1. Find the setting for the given type and lock it for update.
            $setting = NumberingSetting::where('type', $type)->lockForUpdate()->first();

            // Fallback if no setting is found in the database.
            if (!$setting) {
                Log::error("Numbering setting for type '{$type}' not found.");
                return strtoupper($type) . '-' . time();
            }

            $now = Carbon::now();
            $reset = false;

            // 2. Check if the sequence needs to be reset based on the frequency.
            if ($setting->reset_frequency === 'daily' && !$now->isSameDay($setting->last_reset_date)) {
                $reset = true;
            } elseif ($setting->reset_frequency === 'monthly' && !$now->isSameMonth($setting->last_reset_date)) {
                $reset = true;
            } elseif ($setting->reset_frequency === 'yearly' && !$now->isSameYear($setting->last_reset_date)) {
                $reset = true;
            }

            $newSequence = $reset ? 1 : $setting->last_sequence + 1;

            // 3. Update the setting with the new sequence and reset date.
            $setting->update([
                'last_sequence' => $newSequence,
                'last_reset_date' => $now,
            ]);

            // 4. Replace placeholders in the format string with actual values.
            $replacements = [
                '{PREFIX}'      => $setting->prefix,
                '{SUFFIX}'      => $setting->suffix,
                '{YEAR}'        => $now->format('Y'),
                '{YEAR_SHORT}'  => $now->format('y'),
                '{MONTH}'       => $now->format('m'),
                '{MONTH_ROMAN}' => $this->toRoman($now->month),
                '{DAY}'         => $now->format('d'),
                '{SEQUENCE}'    => str_pad($newSequence, $setting->sequence_digits, '0', STR_PAD_LEFT),
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $setting->format);
        });
    }

    /**
     * Helper function to convert a number to Roman numerals.
     */
    private function toRoman($number): string 
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }

    /**
     * Updates the status of an SPH to 'used_in_transaction'.
     * This should be called after a TransaksiPenjualan has been created using this SPH.
     *
     * @param Sph $sph The SPH record to be updated.
     * @return Sph The updated SPH record.
     */
    public function markAsUsedInTransaction(Sph $sph): Sph
    {
        // This is a simple status update, but keeping it in the service
        // allows for future logic (like logging or notifications) to be added easily.
        $sph->update(['status' => 'used_in_transaction']);

        return $sph;
    }
}
