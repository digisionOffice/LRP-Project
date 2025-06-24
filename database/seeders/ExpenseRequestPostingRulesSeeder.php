<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostingRule;
use App\Models\PostingRuleEntry;
use App\Models\Akun;

class ExpenseRequestPostingRulesSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Delete existing expense request posting rules
        PostingRule::where('source_type', 'ExpenseRequest')->delete();

        $this->createExpenseRequestPostingRules();
    }

    private function createExpenseRequestPostingRules()
    {
        // Get required accounts
        $kasAccount = Akun::where('kode_akun', '1110')->first();
        $maintenanceAccount = Akun::where('kode_akun', '5110')->first();
        $licenseAccount = Akun::where('kode_akun', '5120')->first();
        $travelAccount = Akun::where('kode_akun', '5130')->first();
        $utilitiesAccount = Akun::where('kode_akun', '5140')->first();
        $otherExpenseAccount = Akun::where('kode_akun', '5150')->first();

        if (!$kasAccount) {
            $this->command->warn('Kas account (1110) not found. Please run AccountingSeeder first.');
            return;
        }

        // 1. Posting Rule untuk Tank Truck Maintenance
        if ($maintenanceAccount) {
            $maintenanceRule = PostingRule::create([
                'rule_name' => 'Expense Request - Tank Truck Maintenance',
                'source_type' => 'ExpenseRequest',
                'trigger_condition' => ['category' => 'tank_truck_maintenance'],
                'description' => 'Auto posting untuk expense request perawatan truk tangki',
                'is_active' => true,
                'priority' => 1,
                'created_by' => 1,
            ]);

            // Debit: Biaya Perawatan Truk Tangki
            PostingRuleEntry::create([
                'posting_rule_id' => $maintenanceRule->id,
                'account_id' => $maintenanceAccount->id,
                'dc_type' => 'Debit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Biaya Perawatan Truk Tangki - {source.request_number}',
                'sort_order' => 1,
            ]);

            // Credit: Kas
            PostingRuleEntry::create([
                'posting_rule_id' => $maintenanceRule->id,
                'account_id' => $kasAccount->id,
                'dc_type' => 'Credit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pembayaran Biaya Perawatan - {source.request_number}',
                'sort_order' => 2,
            ]);
        }

        // 2. Posting Rule untuk License Fee
        if ($licenseAccount) {
            $licenseRule = PostingRule::create([
                'rule_name' => 'Expense Request - License Fee',
                'source_type' => 'ExpenseRequest',
                'trigger_condition' => ['category' => 'license_fee'],
                'description' => 'Auto posting untuk expense request biaya lisensi',
                'is_active' => true,
                'priority' => 2,
                'created_by' => 1,
            ]);

            // Debit: Biaya Lisensi
            PostingRuleEntry::create([
                'posting_rule_id' => $licenseRule->id,
                'account_id' => $licenseAccount->id,
                'dc_type' => 'Debit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Biaya Lisensi - {source.request_number}',
                'sort_order' => 1,
            ]);

            // Credit: Kas
            PostingRuleEntry::create([
                'posting_rule_id' => $licenseRule->id,
                'account_id' => $kasAccount->id,
                'dc_type' => 'Credit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pembayaran Biaya Lisensi - {source.request_number}',
                'sort_order' => 2,
            ]);
        }

        // 3. Posting Rule untuk Business Travel
        if ($travelAccount) {
            $travelRule = PostingRule::create([
                'rule_name' => 'Expense Request - Business Travel',
                'source_type' => 'ExpenseRequest',
                'trigger_condition' => ['category' => 'business_travel'],
                'description' => 'Auto posting untuk expense request perjalanan dinas',
                'is_active' => true,
                'priority' => 3,
                'created_by' => 1,
            ]);

            // Debit: Biaya Perjalanan Dinas
            PostingRuleEntry::create([
                'posting_rule_id' => $travelRule->id,
                'account_id' => $travelAccount->id,
                'dc_type' => 'Debit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Biaya Perjalanan Dinas - {source.request_number}',
                'sort_order' => 1,
            ]);

            // Credit: Kas
            PostingRuleEntry::create([
                'posting_rule_id' => $travelRule->id,
                'account_id' => $kasAccount->id,
                'dc_type' => 'Credit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pembayaran Perjalanan Dinas - {source.request_number}',
                'sort_order' => 2,
            ]);
        }

        // 4. Posting Rule untuk Utilities
        if ($utilitiesAccount) {
            $utilitiesRule = PostingRule::create([
                'rule_name' => 'Expense Request - Utilities',
                'source_type' => 'ExpenseRequest',
                'trigger_condition' => ['category' => 'utilities'],
                'description' => 'Auto posting untuk expense request utilitas',
                'is_active' => true,
                'priority' => 4,
                'created_by' => 1,
            ]);

            // Debit: Biaya Utilitas
            PostingRuleEntry::create([
                'posting_rule_id' => $utilitiesRule->id,
                'account_id' => $utilitiesAccount->id,
                'dc_type' => 'Debit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Biaya Utilitas - {source.request_number}',
                'sort_order' => 1,
            ]);

            // Credit: Kas
            PostingRuleEntry::create([
                'posting_rule_id' => $utilitiesRule->id,
                'account_id' => $kasAccount->id,
                'dc_type' => 'Credit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pembayaran Utilitas - {source.request_number}',
                'sort_order' => 2,
            ]);
        }

        // 5. Posting Rule untuk Other Expenses
        if ($otherExpenseAccount) {
            $otherRule = PostingRule::create([
                'rule_name' => 'Expense Request - Other Expenses',
                'source_type' => 'ExpenseRequest',
                'trigger_condition' => ['category' => 'other'],
                'description' => 'Auto posting untuk expense request pengeluaran lainnya',
                'is_active' => true,
                'priority' => 5,
                'created_by' => 1,
            ]);

            // Debit: Pengeluaran Lainnya
            PostingRuleEntry::create([
                'posting_rule_id' => $otherRule->id,
                'account_id' => $otherExpenseAccount->id,
                'dc_type' => 'Debit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pengeluaran Lainnya - {source.request_number}',
                'sort_order' => 1,
            ]);

            // Credit: Kas
            PostingRuleEntry::create([
                'posting_rule_id' => $otherRule->id,
                'account_id' => $kasAccount->id,
                'dc_type' => 'Credit',
                'amount_type' => 'SourceValue',
                'source_property' => 'approved_amount',
                'description_template' => 'Pembayaran Pengeluaran Lainnya - {source.request_number}',
                'sort_order' => 2,
            ]);
        }

        $this->command->info('Expense Request posting rules created successfully.');
    }
}
