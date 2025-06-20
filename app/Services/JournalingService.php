<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\PostingRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalingService
{
    /**
     * Post transaction to journal based on posting rules
     */
    public function postTransaction(string $sourceType, Model $sourceModel): void
    {
        try {
            DB::beginTransaction();

            // Find applicable posting rules
            $postingRules = PostingRule::active()
                ->bySourceType($sourceType)
                ->orderedByPriority()
                ->with('postingRuleEntries.account')
                ->get();

            foreach ($postingRules as $rule) {
                // Evaluate trigger condition
                if ($rule->evaluateCondition($sourceModel)) {
                    $this->createJournalFromRule($rule, $sourceModel);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error posting transaction to journal', [
                'source_type' => $sourceType,
                'source_id' => $sourceModel->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create journal from posting rule
     */
    private function createJournalFromRule(PostingRule $rule, Model $sourceModel): Journal
    {
        $journal = Journal::create([
            'transaction_date' => data_get($sourceModel, 'transaction_date', now()),
            'reference_number' => data_get($sourceModel, 'transaction_code') ?? data_get($sourceModel, 'id'),
            'source_type' => $rule->source_type,
            'source_id' => $sourceModel->id,
            'description' => $rule->description . ' - ' . data_get($sourceModel, 'transaction_code', $sourceModel->id),
            'status' => 'Draft',
            'posting_rule_id' => $rule->id,
            'created_by' => auth()->id(),
        ]);

        $totalDebit = 0;
        $totalCredit = 0;

        // Create journal entries
        foreach ($rule->postingRuleEntries as $ruleEntry) {
            $amount = $ruleEntry->calculateAmount($sourceModel);
            
            if ($amount > 0) {
                $journalEntry = JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $ruleEntry->account_id,
                    'description' => $ruleEntry->generateDescription($sourceModel),
                    'debit' => $ruleEntry->dc_type === 'Debit' ? $amount : 0,
                    'credit' => $ruleEntry->dc_type === 'Credit' ? $amount : 0,
                    'sort_order' => $ruleEntry->sort_order,
                ]);

                if ($ruleEntry->dc_type === 'Debit') {
                    $totalDebit += $amount;
                } else {
                    $totalCredit += $amount;
                }
            }
        }

        // Validate balance
        if (abs($totalDebit - $totalCredit) < 0.01) { // Allow small rounding differences
            $journal->update(['status' => 'Posted']);
        } else {
            $journal->update(['status' => 'Error']);
            Log::warning('Journal not balanced', [
                'journal_id' => $journal->id,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'difference' => $totalDebit - $totalCredit
            ]);
        }

        return $journal;
    }

    /**
     * Reverse journal entries (for cancellations)
     */
    public function reverseTransaction(string $sourceType, Model $sourceModel): void
    {
        try {
            DB::beginTransaction();

            // Find existing journals for this transaction
            $journals = Journal::where('source_type', $sourceType)
                ->where('source_id', $sourceModel->id)
                ->where('status', 'Posted')
                ->get();

            foreach ($journals as $journal) {
                // Create reversal journal
                $reversalJournal = Journal::create([
                    'transaction_date' => now(),
                    'reference_number' => $journal->reference_number . '-REV',
                    'source_type' => $sourceType,
                    'source_id' => $sourceModel->id,
                    'description' => 'Reversal: ' . $journal->description,
                    'status' => 'Posted',
                    'created_by' => auth()->id(),
                ]);

                // Create reversal entries (swap debit/credit)
                foreach ($journal->journalEntries as $entry) {
                    JournalEntry::create([
                        'journal_id' => $reversalJournal->id,
                        'account_id' => $entry->account_id,
                        'description' => 'Reversal: ' . $entry->description,
                        'debit' => $entry->credit, // Swap
                        'credit' => $entry->debit, // Swap
                        'sort_order' => $entry->sort_order,
                    ]);
                }

                // Mark original journal as cancelled
                $journal->update(['status' => 'Cancelled']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reversing transaction', [
                'source_type' => $sourceType,
                'source_id' => $sourceModel->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
