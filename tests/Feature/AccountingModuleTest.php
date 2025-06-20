<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Akun;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\PostingRule;
use App\Models\PostingRuleEntry;
use App\Models\Inventory;
use App\Models\Item;

class AccountingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'superadmin'
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_chart_of_accounts()
    {
        $account = Akun::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 1000000,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('akun', [
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
        ]);

        $this->assertEquals(1000000, $account->saldo_awal);
    }

    /** @test */
    public function it_can_create_journal_entries()
    {
        // Create accounts
        $kasAccount = Akun::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
            'created_by' => $this->user->id,
        ]);

        $salesAccount = Akun::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Penjualan',
            'kategori_akun' => 'Pendapatan',
            'tipe_akun' => 'Kredit',
            'saldo_awal' => 0,
            'created_by' => $this->user->id,
        ]);

        // Create journal
        $journal = Journal::create([
            'transaction_date' => now(),
            'reference_number' => 'TRX-001',
            'source_type' => 'Sale',
            'description' => 'Test sales transaction',
            'status' => 'Posted',
            'created_by' => $this->user->id,
        ]);

        // Create journal entries
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $kasAccount->id,
            'description' => 'Cash received from sales',
            'debit' => 100000,
            'credit' => 0,
            'sort_order' => 1,
        ]);

        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $salesAccount->id,
            'description' => 'Sales revenue',
            'debit' => 0,
            'credit' => 100000,
            'sort_order' => 2,
        ]);

        $this->assertTrue($journal->isBalanced());
        $this->assertEquals(100000, $journal->getTotalDebitAttribute());
        $this->assertEquals(100000, $journal->getTotalCreditAttribute());
    }

    /** @test */
    public function it_can_calculate_account_balance()
    {
        // Create account
        $kasAccount = Akun::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 500000,
            'created_by' => $this->user->id,
        ]);

        // Create journal
        $journal = Journal::create([
            'transaction_date' => now(),
            'reference_number' => 'TRX-001',
            'source_type' => 'Sale',
            'description' => 'Test transaction',
            'status' => 'Posted',
            'created_by' => $this->user->id,
        ]);

        // Add journal entry
        JournalEntry::create([
            'journal_id' => $journal->id,
            'account_id' => $kasAccount->id,
            'description' => 'Cash received',
            'debit' => 100000,
            'credit' => 0,
            'sort_order' => 1,
        ]);

        // Calculate balance
        $balance = $kasAccount->getCurrentBalance();
        $this->assertEquals(600000, $balance); // 500000 + 100000
    }

    /** @test */
    public function it_can_create_posting_rules()
    {
        // Create accounts
        $kasAccount = Akun::create([
            'kode_akun' => '1101',
            'nama_akun' => 'Kas',
            'kategori_akun' => 'Aset',
            'tipe_akun' => 'Debit',
            'saldo_awal' => 0,
            'created_by' => $this->user->id,
        ]);

        $salesAccount = Akun::create([
            'kode_akun' => '4101',
            'nama_akun' => 'Penjualan',
            'kategori_akun' => 'Pendapatan',
            'tipe_akun' => 'Kredit',
            'saldo_awal' => 0,
            'created_by' => $this->user->id,
        ]);

        // Create posting rule
        $postingRule = PostingRule::create([
            'rule_name' => 'Cash Sales',
            'source_type' => 'Sale',
            'trigger_condition' => ['payment_method' => 'Cash'],
            'description' => 'Posting rule for cash sales',
            'is_active' => true,
            'priority' => 1,
            'created_by' => $this->user->id,
        ]);

        // Create posting rule entries
        PostingRuleEntry::create([
            'posting_rule_id' => $postingRule->id,
            'account_id' => $kasAccount->id,
            'dc_type' => 'Debit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_amount',
            'description_template' => 'Cash received from sales',
            'sort_order' => 1,
        ]);

        PostingRuleEntry::create([
            'posting_rule_id' => $postingRule->id,
            'account_id' => $salesAccount->id,
            'dc_type' => 'Credit',
            'amount_type' => 'SourceValue',
            'source_property' => 'total_amount',
            'description_template' => 'Sales revenue',
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('posting_rules', [
            'rule_name' => 'Cash Sales',
            'source_type' => 'Sale',
            'is_active' => true,
        ]);

        $this->assertEquals(2, $postingRule->postingRuleEntries()->count());
    }

    /** @test */
    public function it_can_manage_inventory()
    {
        // Create item first
        $item = Item::create([
            'kode' => 'ITM001',
            'name' => 'Test Item',
            'kategori_id' => 1,
            'satuan_dasar_id' => 1,
            'harga_jual' => 10000,
            'created_by' => $this->user->id,
        ]);

        // Create inventory
        $inventory = Inventory::create([
            'item_id' => $item->id,
            'quantity' => 100,
            'unit_cost' => 8000,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('inventories', [
            'item_id' => $item->id,
            'quantity' => 100,
            'unit_cost' => 8000,
            'total_value' => 800000, // 100 * 8000
        ]);

        $this->assertEquals(800000, $inventory->total_value);
    }
}
