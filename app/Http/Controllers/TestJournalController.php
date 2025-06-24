<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Test Controller for Journal API endpoints
 * Used exclusively for Cypress testing
 * 
 * WARNING: This controller should only be available in testing environment
 */
class TestJournalController extends Controller
{
    public function __construct()
    {
        // Only allow in testing environment
        if (!app()->environment('testing', 'local')) {
            abort(404);
        }
    }

    /**
     * Create a single test journal
     */
    public function create(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validate([
                'transaction_date' => 'required|date',
                'reference_number' => 'nullable|string|max:255',
                'source_type' => 'nullable|string|in:Sale,Purchase,Payment,Receipt,ManualAdjust',
                'description' => 'required|string',
                'status' => 'nullable|string|in:Draft,Posted,Cancelled,Error',
                'entries' => 'required|array|min:2',
                'entries.*.account' => 'required|string',
                'entries.*.description' => 'required|string',
                'entries.*.debit' => 'required|numeric|min:0',
                'entries.*.credit' => 'required|numeric|min:0',
                'entries.*.sort_order' => 'nullable|integer'
            ]);

            // Create journal
            $journal = Journal::create([
                'transaction_date' => $data['transaction_date'],
                'reference_number' => $data['reference_number'] ?? 'TEST-' . time(),
                'source_type' => $data['source_type'] ?? 'Sale',
                'description' => $data['description'],
                'status' => $data['status'] ?? 'Draft',
                'created_by' => auth()->id() ?? 1
            ]);

            // Create journal entries
            foreach ($data['entries'] as $index => $entryData) {
                // Find account by name
                $account = Akun::where('nama_akun', $entryData['account'])->first();
                
                if (!$account) {
                    // Create account if not exists
                    $account = Akun::create([
                        'kode_akun' => strtoupper(substr($entryData['account'], 0, 4)) . rand(1000, 9999),
                        'nama_akun' => $entryData['account'],
                        'kategori_akun' => $this->getAccountCategory($entryData['account']),
                        'tipe_akun' => $entryData['debit'] > 0 ? 'Debit' : 'Kredit',
                        'saldo_awal' => 0,
                        'created_by' => auth()->id() ?? 1
                    ]);
                }

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'account_id' => $account->id,
                    'description' => $entryData['description'],
                    'debit' => $entryData['debit'],
                    'credit' => $entryData['credit'],
                    'sort_order' => $entryData['sort_order'] ?? ($index + 1)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test journal created successfully',
                'data' => $journal->load('journalEntries.account')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Test journal creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create test journal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create multiple test journals in batch
     */
    public function createBatch(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'journals' => 'required|array',
                'journals.*.transaction_date' => 'nullable|date',
                'journals.*.reference_number' => 'required|string',
                'journals.*.source_type' => 'nullable|string',
                'journals.*.description' => 'required|string',
                'journals.*.status' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $createdJournals = [];

            foreach ($data['journals'] as $journalData) {
                $journal = Journal::create([
                    'transaction_date' => $journalData['transaction_date'] ?? now(),
                    'reference_number' => $journalData['reference_number'],
                    'source_type' => $journalData['source_type'] ?? 'Sale',
                    'description' => $journalData['description'],
                    'status' => $journalData['status'] ?? 'Draft',
                    'created_by' => auth()->id() ?? 1
                ]);

                // Create default balanced entries
                $this->createDefaultEntries($journal);

                $createdJournals[] = $journal;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test journals created successfully',
                'data' => $createdJournals,
                'count' => count($createdJournals)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch journal creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create test journals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all test journals
     */
    public function clear(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete journal entries first (foreign key constraint)
            $deletedEntries = JournalEntry::whereHas('journal', function ($query) {
                $query->where('reference_number', 'like', 'TEST-%')
                      ->orWhere('reference_number', 'like', 'REF-%')
                      ->orWhere('reference_number', 'like', 'CYPRESS-%');
            })->delete();

            // Delete journals
            $deletedJournals = Journal::where('reference_number', 'like', 'TEST-%')
                                   ->orWhere('reference_number', 'like', 'REF-%')
                                   ->orWhere('reference_number', 'like', 'CYPRESS-%')
                                   ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test journals cleared successfully',
                'deleted_journals' => $deletedJournals,
                'deleted_entries' => $deletedEntries
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear test journals failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear test journals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get journal balance information
     */
    public function balance($journalId): JsonResponse
    {
        try {
            $journal = Journal::with('journalEntries')->findOrFail($journalId);

            $totalDebit = $journal->journalEntries->sum('debit');
            $totalCredit = $journal->journalEntries->sum('credit');
            $isBalanced = $totalDebit == $totalCredit;

            return response()->json([
                'success' => true,
                'data' => [
                    'journal_id' => $journal->id,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference' => $totalDebit - $totalCredit,
                    'is_balanced' => $isBalanced
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get journal balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create default balanced entries for a journal
     */
    private function createDefaultEntries(Journal $journal): void
    {
        // Get or create default accounts
        $kasAccount = $this->getOrCreateAccount('Kas', 'Aset', 'Debit');
        $salesAccount = $this->getOrCreateAccount('Penjualan', 'Pendapatan', 'Kredit');

        // Create debit entry
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $kasAccount->id,
            'description' => 'Test debit entry',
            'debit' => 1000000,
            'credit' => 0,
            'sort_order' => 1
        ]);

        // Create credit entry
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $salesAccount->id,
            'description' => 'Test credit entry',
            'debit' => 0,
            'credit' => 1000000,
            'sort_order' => 2
        ]);
    }

    /**
     * Get or create account by name
     */
    private function getOrCreateAccount(string $name, string $category, string $type): Akun
    {
        $account = Akun::where('nama_akun', $name)->first();

        if (!$account) {
            $account = Akun::create([
                'kode_akun' => strtoupper(substr($name, 0, 4)) . rand(1000, 9999),
                'nama_akun' => $name,
                'kategori_akun' => $category,
                'tipe_akun' => $type,
                'saldo_awal' => 0,
                'created_by' => auth()->id() ?? 1
            ]);
        }

        return $account;
    }

    /**
     * Determine account category based on account name
     */
    private function getAccountCategory(string $accountName): string
    {
        $categories = [
            'kas' => 'Aset',
            'piutang' => 'Aset',
            'persediaan' => 'Aset',
            'penjualan' => 'Pendapatan',
            'pendapatan' => 'Pendapatan',
            'beban' => 'Beban',
            'biaya' => 'Beban',
            'hutang' => 'Kewajiban',
            'modal' => 'Ekuitas'
        ];

        $accountLower = strtolower($accountName);

        foreach ($categories as $keyword => $category) {
            if (strpos($accountLower, $keyword) !== false) {
                return $category;
            }
        }

        return 'Aset'; // Default category
    }
}
