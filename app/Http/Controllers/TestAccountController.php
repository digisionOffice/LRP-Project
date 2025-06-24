<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Test Controller for Account API endpoints
 * Used exclusively for Cypress testing
 */
class TestAccountController extends Controller
{
    public function __construct()
    {
        // Only allow in testing environment
        if (!app()->environment('testing', 'local')) {
            abort(404);
        }
    }

    /**
     * Create multiple test accounts in batch
     */
    public function createBatch(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'accounts' => 'required|array',
                'accounts.*.kode_akun' => 'required|string|unique:akun,kode_akun',
                'accounts.*.nama_akun' => 'required|string',
                'accounts.*.kategori_akun' => 'required|string|in:Aset,Kewajiban,Ekuitas,Pendapatan,Beban',
                'accounts.*.tipe_akun' => 'required|string|in:Debit,Kredit',
                'accounts.*.saldo_awal' => 'nullable|numeric'
            ]);

            DB::beginTransaction();

            $createdAccounts = [];

            foreach ($data['accounts'] as $accountData) {
                // Check if account already exists
                $existingAccount = Akun::where('kode_akun', $accountData['kode_akun'])
                                     ->orWhere('nama_akun', $accountData['nama_akun'])
                                     ->first();

                if ($existingAccount) {
                    $createdAccounts[] = $existingAccount;
                    continue;
                }

                $account = Akun::create([
                    'kode_akun' => $accountData['kode_akun'],
                    'nama_akun' => $accountData['nama_akun'],
                    'kategori_akun' => $accountData['kategori_akun'],
                    'tipe_akun' => $accountData['tipe_akun'],
                    'saldo_awal' => $accountData['saldo_awal'] ?? 0,
                    'created_by' => auth()->id() ?? 1
                ]);

                $createdAccounts[] = $account;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test accounts created successfully',
                'data' => $createdAccounts,
                'count' => count($createdAccounts)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch account creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create test accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create default test accounts for journal testing
     */
    public function createDefaults(): JsonResponse
    {
        try {
            $defaultAccounts = [
                [
                    'kode_akun' => '1101',
                    'nama_akun' => 'Kas',
                    'kategori_akun' => 'Aset',
                    'tipe_akun' => 'Debit',
                    'saldo_awal' => 0
                ],
                [
                    'kode_akun' => '1201',
                    'nama_akun' => 'Piutang',
                    'kategori_akun' => 'Aset',
                    'tipe_akun' => 'Debit',
                    'saldo_awal' => 0
                ],
                [
                    'kode_akun' => '4101',
                    'nama_akun' => 'Penjualan',
                    'kategori_akun' => 'Pendapatan',
                    'tipe_akun' => 'Kredit',
                    'saldo_awal' => 0
                ],
                [
                    'kode_akun' => '5101',
                    'nama_akun' => 'Beban Operasional',
                    'kategori_akun' => 'Beban',
                    'tipe_akun' => 'Debit',
                    'saldo_awal' => 0
                ],
                [
                    'kode_akun' => '2101',
                    'nama_akun' => 'Hutang Usaha',
                    'kategori_akun' => 'Kewajiban',
                    'tipe_akun' => 'Kredit',
                    'saldo_awal' => 0
                ]
            ];

            DB::beginTransaction();

            $createdAccounts = [];

            foreach ($defaultAccounts as $accountData) {
                // Check if account already exists
                $existingAccount = Akun::where('kode_akun', $accountData['kode_akun'])->first();

                if ($existingAccount) {
                    $createdAccounts[] = $existingAccount;
                    continue;
                }

                $account = Akun::create([
                    'kode_akun' => $accountData['kode_akun'],
                    'nama_akun' => $accountData['nama_akun'],
                    'kategori_akun' => $accountData['kategori_akun'],
                    'tipe_akun' => $accountData['tipe_akun'],
                    'saldo_awal' => $accountData['saldo_awal'],
                    'created_by' => auth()->id() ?? 1
                ]);

                $createdAccounts[] = $account;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default test accounts created successfully',
                'data' => $createdAccounts,
                'count' => count($createdAccounts)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Default account creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create default test accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear test accounts
     */
    public function clear(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Only delete test accounts (those with specific codes or created for testing)
            $deletedCount = Akun::whereIn('kode_akun', ['1101', '1201', '4101', '5101', '2101'])
                               ->orWhere('nama_akun', 'like', 'Test%')
                               ->orWhere('kode_akun', 'like', 'TEST%')
                               ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Test accounts cleared successfully',
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Clear test accounts failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear test accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all accounts for testing
     */
    public function index(): JsonResponse
    {
        try {
            $accounts = Akun::select('id', 'kode_akun', 'nama_akun', 'kategori_akun', 'tipe_akun')
                           ->orderBy('kode_akun')
                           ->get();

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'count' => $accounts->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Get test accounts failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get test accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a single test account
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'kode_akun' => 'required|string|unique:akun,kode_akun',
                'nama_akun' => 'required|string',
                'kategori_akun' => 'required|string|in:Aset,Kewajiban,Ekuitas,Pendapatan,Beban',
                'tipe_akun' => 'required|string|in:Debit,Kredit',
                'saldo_awal' => 'nullable|numeric'
            ]);

            $account = Akun::create([
                'kode_akun' => $data['kode_akun'],
                'nama_akun' => $data['nama_akun'],
                'kategori_akun' => $data['kategori_akun'],
                'tipe_akun' => $data['tipe_akun'],
                'saldo_awal' => $data['saldo_awal'] ?? 0,
                'created_by' => auth()->id() ?? 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test account created successfully',
                'data' => $account
            ], 201);

        } catch (\Exception $e) {
            Log::error('Test account creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create test account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
