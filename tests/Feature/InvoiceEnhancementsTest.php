<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\User;
use App\Filament\Resources\InvoiceResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Filament\Actions\Testing\TestsActions;
use Filament\Forms\Testing\TestsForms;
use Filament\Infolists\Testing\TestsInfolists;
use Filament\Tables\Testing\TestsTables;
use Livewire\Livewire;

class InvoiceEnhancementsTest extends TestCase
{
    use RefreshDatabase;
    use TestsActions;
    use TestsForms;
    use TestsInfolists;
    use TestsTables;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->actingAs(User::factory()->create([
            'role' => 'admin'
        ]));
    }

    /** @test */
    public function invoice_view_page_displays_all_relevant_data()
    {
        // Create test data
        $pelanggan = Pelanggan::factory()->create([
            'nama' => 'Test Customer',
            'alamat' => 'Test Address',
            'npwp' => '123456789'
        ]);

        $transaksi = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => $pelanggan->id,
            'kode' => 'SO-001',
            'nomor_po' => 'PO-001'
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $transaksi->id,
            'kode' => 'DO-001'
        ]);

        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-001',
            'id_do' => $deliveryOrder->id,
            'id_transaksi' => $transaksi->id,
            'nama_pelanggan' => $pelanggan->nama,
            'alamat_pelanggan' => $pelanggan->alamat,
            'npwp_pelanggan' => $pelanggan->npwp,
            'subtotal' => 100000000,
            'total_pajak' => 11000000,
            'total_invoice' => 111000000,
            'total_terbayar' => 0,
            'sisa_tagihan' => 111000000,
            'status' => 'sent'
        ]);

        // Test view page
        Livewire::test(InvoiceResource\Pages\ViewInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertSeeText('INV-001')
            ->assertSeeText('Test Customer')
            ->assertSeeText('DO-001')
            ->assertSeeText('SO-001')
            ->assertSeeText('Rp 100,000,000.00')
            ->assertSeeText('Rp 11,000,000.00')
            ->assertSeeText('Rp 111,000,000.00');
    }

    /** @test */
    public function invoice_model_field_mapping_works_correctly()
    {
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-TEST',
            'subtotal' => 50000000,
            'total_pajak' => 5500000,
            'total_invoice' => 55500000,
            'include_ppn' => true,
            'include_pbbkb' => false,
            'include_operasional_kerja' => false
        ]);

        $this->assertEquals('INV-TEST', $invoice->nomor_invoice);
        $this->assertEquals(50000000, $invoice->subtotal);
        $this->assertEquals(5500000, $invoice->total_pajak);
        $this->assertEquals(55500000, $invoice->total_invoice);
        $this->assertTrue($invoice->include_ppn);
        $this->assertFalse($invoice->include_pbbkb);
        $this->assertFalse($invoice->include_operasional_kerja);
    }

    /** @test */
    public function invoice_relationships_work_correctly()
    {
        $pelanggan = Pelanggan::factory()->create();
        $transaksi = TransaksiPenjualan::factory()->create(['id_pelanggan' => $pelanggan->id]);
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $transaksi->id]);
        
        $invoice = Invoice::factory()->create([
            'id_do' => $deliveryOrder->id,
            'id_transaksi' => $transaksi->id
        ]);

        $this->assertNotNull($invoice->transaksiPenjualan);
        $this->assertNotNull($invoice->deliveryOrder);
        $this->assertEquals($transaksi->id, $invoice->transaksiPenjualan->id);
        $this->assertEquals($deliveryOrder->id, $invoice->deliveryOrder->id);
    }

    /** @test */
    public function invoice_calculated_totals_work_correctly()
    {
        $invoice = Invoice::factory()->create([
            'subtotal' => 100000000,
            'include_ppn' => true,
            'biaya_operasional_kerja' => 5000000,
            'include_operasional_kerja' => true,
            'biaya_pbbkb' => 2000000,
            'include_pbbkb' => true
        ]);

        $expectedTotal = 100000000 + (100000000 * 0.11) + 5000000 + 2000000;
        $this->assertEquals($expectedTotal, $invoice->calculated_total);
    }

    /** @test */
    public function invoice_status_accessor_works_correctly()
    {
        $invoice1 = Invoice::factory()->create(['status' => 'paid']);
        $invoice2 = Invoice::factory()->create(['status_bayar' => 'lunas']);
        
        $this->assertEquals('paid', $invoice1->status);
        $this->assertEquals('paid', $invoice2->status);
    }

    /** @test */
    public function invoice_delivery_order_url_accessor_works()
    {
        $deliveryOrder = DeliveryOrder::factory()->create();
        $invoice = Invoice::factory()->create(['id_do' => $deliveryOrder->id]);

        $expectedUrl = route('filament.admin.resources.delivery-orders.view', ['record' => $deliveryOrder]);
        $this->assertEquals($expectedUrl, $invoice->deliveryOrderUrl);
    }

    /** @test */
    public function invoice_table_displays_correct_columns()
    {
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-TABLE-TEST',
            'nama_pelanggan' => 'Table Test Customer',
            'total_invoice' => 75000000,
            'sisa_tagihan' => 25000000,
            'status' => 'sent'
        ]);

        Livewire::test(InvoiceResource\Pages\ListInvoices::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$invoice])
            ->assertTableColumnExists('nomor_invoice')
            ->assertTableColumnExists('nama_pelanggan')
            ->assertTableColumnExists('total_invoice')
            ->assertTableColumnExists('sisa_tagihan')
            ->assertTableColumnExists('status');
    }

    /** @test */
    public function invoice_form_has_all_required_fields()
    {
        Livewire::test(InvoiceResource\Pages\CreateInvoice::class)
            ->assertSuccessful()
            ->assertFormFieldExists('nomor_invoice')
            ->assertFormFieldExists('id_do')
            ->assertFormFieldExists('id_transaksi')
            ->assertFormFieldExists('tanggal_invoice')
            ->assertFormFieldExists('tanggal_jatuh_tempo')
            ->assertFormFieldExists('nama_pelanggan')
            ->assertFormFieldExists('alamat_pelanggan')
            ->assertFormFieldExists('npwp_pelanggan')
            ->assertFormFieldExists('subtotal')
            ->assertFormFieldExists('include_ppn')
            ->assertFormFieldExists('total_invoice')
            ->assertFormFieldExists('status');
    }

    /** @test */
    public function invoice_can_be_created_with_all_fields()
    {
        $pelanggan = Pelanggan::factory()->create();
        $transaksi = TransaksiPenjualan::factory()->create(['id_pelanggan' => $pelanggan->id]);
        $deliveryOrder = DeliveryOrder::factory()->create(['id_transaksi' => $transaksi->id]);

        $invoiceData = [
            'nomor_invoice' => 'INV-CREATE-TEST',
            'id_do' => $deliveryOrder->id,
            'id_transaksi' => $transaksi->id,
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => 'Create Test Customer',
            'alamat_pelanggan' => 'Create Test Address',
            'npwp_pelanggan' => '987654321',
            'subtotal' => 80000000,
            'include_ppn' => true,
            'total_pajak' => 8800000,
            'total_invoice' => 88800000,
            'total_terbayar' => 0,
            'sisa_tagihan' => 88800000,
            'status' => 'draft'
        ];

        Livewire::test(InvoiceResource\Pages\CreateInvoice::class)
            ->fillForm($invoiceData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoice', [
            'nomor_invoice' => 'INV-CREATE-TEST',
            'nama_pelanggan' => 'Create Test Customer'
        ]);
    }

    /** @test */
    public function invoice_can_be_edited_with_updated_fields()
    {
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-EDIT-TEST',
            'nama_pelanggan' => 'Original Customer',
            'status' => 'draft'
        ]);

        Livewire::test(InvoiceResource\Pages\EditInvoice::class, [
            'record' => $invoice->getRouteKey(),
        ])
            ->fillForm([
                'nama_pelanggan' => 'Updated Customer',
                'status' => 'sent'
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $invoice->refresh();
        $this->assertEquals('Updated Customer', $invoice->nama_pelanggan);
        $this->assertEquals('sent', $invoice->status);
    }
}
