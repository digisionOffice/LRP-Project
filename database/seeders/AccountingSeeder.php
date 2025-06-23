<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Akun;
use App\Models\PostingRule;
use App\Models\PostingRuleEntry;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Clear existing data safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PostingRuleEntry::truncate();
        PostingRule::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Update existing akun records to new structure
        $this->updateExistingAccounts();

        // Create basic chart of accounts
        $this->createChartOfAccounts();

        // Create posting rules
        $this->createPostingRules();
    }

    private function updateExistingAccounts()
    {
        // Update existing accounts to match new structure
        $existingAccounts = Akun::all();

        foreach ($existingAccounts as $account) {
            // Map old tipe_akun to new kategori_akun and tipe_akun
            $kategori = 'Aset';
            $tipe = 'Debit';

            switch ($account->tipe_akun) {
                case 'Aktiva':
                    $kategori = 'Aset';
                    $tipe = 'Debit';
                    break;
                case 'Kewajiban':
                    $kategori = 'Kewajiban';
                    $tipe = 'Kredit';
                    break;
                case 'Modal':
                    $kategori = 'Ekuitas';
                    $tipe = 'Kredit';
                    break;
                case 'Pendapatan':
                    $kategori = 'Pendapatan';
                    $tipe = 'Kredit';
                    break;
                case 'Beban':
                    $kategori = 'Beban';
                    $tipe = 'Debit';
                    break;
            }

            $account->update([
                'kategori_akun' => $kategori,
                'tipe_akun' => $tipe,
                'saldo_awal' => 0,
            ]);
        }
    }

    private function createChartOfAccounts()
    {
        $accounts = [
            // ASET
            ['kode_akun' => '1101', 'nama_akun' => 'Kas', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '1102', 'nama_akun' => 'Bank', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '1201', 'nama_akun' => 'Piutang Usaha', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '1301', 'nama_akun' => 'Persediaan Barang Dagang', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '1401', 'nama_akun' => 'Peralatan', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '1402', 'nama_akun' => 'Akumulasi Penyusutan Peralatan', 'kategori_akun' => 'Aset', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],

            // KEWAJIBAN
            ['kode_akun' => '2101', 'nama_akun' => 'Hutang Usaha', 'kategori_akun' => 'Kewajiban', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],
            ['kode_akun' => '2102', 'nama_akun' => 'Hutang Pajak', 'kategori_akun' => 'Kewajiban', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],
            ['kode_akun' => '2103', 'nama_akun' => 'Hutang Gaji', 'kategori_akun' => 'Kewajiban', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],

            // EKUITAS
            ['kode_akun' => '3101', 'nama_akun' => 'Modal Pemilik', 'kategori_akun' => 'Ekuitas', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],
            ['kode_akun' => '3201', 'nama_akun' => 'Prive', 'kategori_akun' => 'Ekuitas', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],

            // PENDAPATAN
            ['kode_akun' => '4101', 'nama_akun' => 'Penjualan', 'kategori_akun' => 'Pendapatan', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],
            ['kode_akun' => '4102', 'nama_akun' => 'Pendapatan Lain-lain', 'kategori_akun' => 'Pendapatan', 'tipe_akun' => 'Kredit', 'saldo_awal' => 0],

            // BEBAN
            ['kode_akun' => '5101', 'nama_akun' => 'Harga Pokok Penjualan', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '5201', 'nama_akun' => 'Beban Gaji', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '5202', 'nama_akun' => 'Beban Listrik', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '5203', 'nama_akun' => 'Beban Telepon', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '5204', 'nama_akun' => 'Beban Penyusutan', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
            ['kode_akun' => '5205', 'nama_akun' => 'Beban Lain-lain', 'kategori_akun' => 'Beban', 'tipe_akun' => 'Debit', 'saldo_awal' => 0],
        ];

        foreach ($accounts as $accountData) {
            Akun::firstOrCreate(
                ['kode_akun' => $accountData['kode_akun']],
                array_merge($accountData, ['created_by' => 1])
            );
        }
    }

    private function createPostingRules()
    {
        // Posting Rule untuk Penjualan
        $salesRule = PostingRule::create([
            'rule_name' => 'Penjualan Tunai',
            'source_type' => 'Sale',
            'trigger_condition' => ['payment_method' => 'Cash'],
            'description' => 'Aturan posting untuk penjualan tunai',
            'is_active' => true,
            'priority' => 1,
            'created_by' => 1,
        ]);

        // Entri untuk penjualan tunai
        PostingRuleEntry::create([
            'posting_rule_id' => $salesRule->id,
            'account_id' => Akun::where('kode_akun', '1101')->first()->id, // Kas
            'dc_type' => 'Debit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_amount',
            'description_template' => 'Penjualan tunai - {source.transaction_code}',
            'sort_order' => 1,
        ]);

        PostingRuleEntry::create([
            'posting_rule_id' => $salesRule->id,
            'account_id' => Akun::where('kode_akun', '4101')->first()->id, // Penjualan
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_amount',
            'description_template' => 'Penjualan tunai - {source.transaction_code}',
            'sort_order' => 2,
        ]);

        // Posting Rule untuk HPP
        $cogsRule = PostingRule::create([
            'rule_name' => 'Harga Pokok Penjualan',
            'source_type' => 'Sale',
            'trigger_condition' => null,
            'description' => 'Aturan posting untuk mencatat HPP',
            'is_active' => true,
            'priority' => 2,
            'created_by' => 1,
        ]);

        PostingRuleEntry::create([
            'posting_rule_id' => $cogsRule->id,
            'account_id' => Akun::where('kode_akun', '5101')->first()->id, // HPP
            'dc_type' => 'Debit',
            'amount_type' => 'Calculated',
            'calculation_expression' => 'saleItems.sum(quantity * unit_cost)',
            'description_template' => 'HPP - {source.transaction_code}',
            'sort_order' => 1,
        ]);

        PostingRuleEntry::create([
            'posting_rule_id' => $cogsRule->id,
            'account_id' => Akun::where('kode_akun', '1301')->first()->id, // Persediaan
            'dc_type' => 'Credit',
            'amount_type' => 'Calculated',
            'calculation_expression' => 'saleItems.sum(quantity * unit_cost)',
            'description_template' => 'Pengurangan persediaan - {source.transaction_code}',
            'sort_order' => 2,
        ]);
    }
}
