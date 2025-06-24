<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostingRule;
use App\Models\PostingRuleEntry;
use App\Models\Akun;

class InvoicePostingRulesSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Delete existing invoice posting rules
        PostingRule::where('source_type', 'Invoice')->delete();

        $this->createInvoicePostingRules();
    }

    private function createInvoicePostingRules()
    {
        // Get required accounts
        $piutangAccount = Akun::where('kode_akun', '1200')->first();
        $pendapatanAccount = Akun::where('kode_akun', '4100')->first();
        $ppnKeluaranAccount = Akun::where('kode_akun', '2110')->first();
        $biayaOngkosAccount = Akun::where('kode_akun', '4200')->first();
        $biayaPbbkbAccount = Akun::where('kode_akun', '4300')->first();
        $biayaOperasionalAccount = Akun::where('kode_akun', '4400')->first();

        // Create accounts if they don't exist
        if (!$piutangAccount) {
            $piutangAccount = Akun::create([
                'kode_akun' => '1200',
                'nama_akun' => 'Piutang Dagang',
                'kategori_akun' => 'Aset',
                'tipe_akun' => 'Debit',
                'created_by' => 1,
            ]);
        }

        if (!$pendapatanAccount) {
            $pendapatanAccount = Akun::create([
                'kode_akun' => '4100',
                'nama_akun' => 'Pendapatan Penjualan',
                'kategori_akun' => 'Pendapatan',
                'tipe_akun' => 'Kredit',
                'created_by' => 1,
            ]);
        }

        if (!$ppnKeluaranAccount) {
            $ppnKeluaranAccount = Akun::create([
                'kode_akun' => '2110',
                'nama_akun' => 'PPN Keluaran',
                'kategori_akun' => 'Kewajiban',
                'tipe_akun' => 'Kredit',
                'created_by' => 1,
            ]);
        }

        if (!$biayaOngkosAccount) {
            $biayaOngkosAccount = Akun::create([
                'kode_akun' => '4200',
                'nama_akun' => 'Pendapatan Ongkos Angkut',
                'kategori_akun' => 'Pendapatan',
                'tipe_akun' => 'Kredit',
                'created_by' => 1,
            ]);
        }

        if (!$biayaPbbkbAccount) {
            $biayaPbbkbAccount = Akun::create([
                'kode_akun' => '4300',
                'nama_akun' => 'Pendapatan PBBKB',
                'kategori_akun' => 'Pendapatan',
                'tipe_akun' => 'Kredit',
                'created_by' => 1,
            ]);
        }

        if (!$biayaOperasionalAccount) {
            $biayaOperasionalAccount = Akun::create([
                'kode_akun' => '4400',
                'nama_akun' => 'Pendapatan Operasional Kerja',
                'kategori_akun' => 'Pendapatan',
                'tipe_akun' => 'Kredit',
                'created_by' => 1,
            ]);
        }

        // 1. Posting Rule untuk Invoice Penjualan (Tanpa PPN)
        $invoiceRule = PostingRule::create([
            'rule_name' => 'Invoice Penjualan - Tanpa PPN',
            'source_type' => 'Invoice',
            'trigger_condition' => ['include_ppn' => false],
            'description' => 'Auto posting untuk invoice penjualan tanpa PPN',
            'is_active' => true,
            'priority' => 1,
            'created_by' => 1,
        ]);

        // Debit: Piutang Dagang
        PostingRuleEntry::create([
            'posting_rule_id' => $invoiceRule->id,
            'account_id' => $piutangAccount->id,
            'dc_type' => 'Debit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_invoice',
            'description_template' => 'Piutang Penjualan - {source.nomor_invoice}',
            'sort_order' => 1,
        ]);

        // Credit: Pendapatan Penjualan
        PostingRuleEntry::create([
            'posting_rule_id' => $invoiceRule->id,
            'account_id' => $pendapatanAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'subtotal',
            'description_template' => 'Penjualan - {source.nomor_invoice}',
            'sort_order' => 2,
        ]);

        // 2. Posting Rule untuk Invoice Penjualan (Dengan PPN)
        $invoicePpnRule = PostingRule::create([
            'rule_name' => 'Invoice Penjualan - Dengan PPN',
            'source_type' => 'Invoice',
            'trigger_condition' => ['include_ppn' => true],
            'description' => 'Auto posting untuk invoice penjualan dengan PPN',
            'is_active' => true,
            'priority' => 2,
            'created_by' => 1,
        ]);

        // Debit: Piutang Dagang
        PostingRuleEntry::create([
            'posting_rule_id' => $invoicePpnRule->id,
            'account_id' => $piutangAccount->id,
            'dc_type' => 'Debit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_invoice',
            'description_template' => 'Piutang Penjualan - {source.nomor_invoice}',
            'sort_order' => 1,
        ]);

        // Credit: Pendapatan Penjualan
        PostingRuleEntry::create([
            'posting_rule_id' => $invoicePpnRule->id,
            'account_id' => $pendapatanAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'subtotal',
            'description_template' => 'Penjualan - {source.nomor_invoice}',
            'sort_order' => 2,
        ]);

        // Credit: PPN Keluaran
        PostingRuleEntry::create([
            'posting_rule_id' => $invoicePpnRule->id,
            'account_id' => $ppnKeluaranAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_pajak',
            'description_template' => 'PPN Keluaran - {source.nomor_invoice}',
            'sort_order' => 3,
        ]);

        // 3. Posting Rule untuk Biaya Ongkos Angkut (jika include_operasional_kerja = true)
        $ongkosRule = PostingRule::create([
            'rule_name' => 'Invoice - Biaya Ongkos Angkut',
            'source_type' => 'Invoice',
            'trigger_condition' => ['include_operasional_kerja' => true],
            'description' => 'Auto posting untuk biaya ongkos angkut',
            'is_active' => true,
            'priority' => 3,
            'created_by' => 1,
        ]);

        // Credit: Pendapatan Operasional Kerja (sudah di-debit di piutang di rule utama)
        PostingRuleEntry::create([
            'posting_rule_id' => $ongkosRule->id,
            'account_id' => $biayaOperasionalAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'biaya_operasional_kerja',
            'description_template' => 'Pendapatan Operasional - {source.nomor_invoice}',
            'sort_order' => 1,
        ]);

        // 4. Posting Rule untuk PBBKB (jika include_pbbkb = true)
        $pbbkbRule = PostingRule::create([
            'rule_name' => 'Invoice - PBBKB',
            'source_type' => 'Invoice',
            'trigger_condition' => ['include_pbbkb' => true],
            'description' => 'Auto posting untuk PBBKB',
            'is_active' => true,
            'priority' => 4,
            'created_by' => 1,
        ]);

        // Credit: Pendapatan PBBKB
        PostingRuleEntry::create([
            'posting_rule_id' => $pbbkbRule->id,
            'account_id' => $biayaPbbkbAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'biaya_pbbkb',
            'description_template' => 'Pendapatan PBBKB - {source.nomor_invoice}',
            'sort_order' => 1,
        ]);

        $this->command->info('Invoice posting rules created successfully.');
    }
}
