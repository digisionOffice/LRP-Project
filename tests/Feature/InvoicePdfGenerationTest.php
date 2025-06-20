<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\User;
use App\Helpers\NumberToWords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin'
        ]);
        
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_generate_invoice_pdf()
    {
        // Create test data
        $pelanggan = Pelanggan::factory()->create([
            'nama' => 'PT. Test Customer',
            'npwp' => '12.345.678.9-012.345'
        ]);

        $transaksi = TransaksiPenjualan::factory()->create([
            'kode' => 'TRX-001',
            'id_pelanggan' => $pelanggan->id,
            'tanggal' => now(),
            'created_by' => $this->user->id
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'kode' => 'DO-001',
            'id_transaksi' => $transaksi->id,
            'tanggal_delivery' => now(),
            'created_by' => $this->user->id
        ]);

        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-001',
            'id_do' => $deliveryOrder->id,
            'id_transaksi' => $transaksi->id,
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => $pelanggan->nama,
            'npwp_pelanggan' => $pelanggan->npwp,
            'subtotal' => 100000000,
            'total_pajak' => 11000000,
            'total_invoice' => 111000000,
            'total_terbayar' => 0,
            'sisa_tagihan' => 111000000,
            'status' => 'sent',
            'created_by' => $this->user->id
        ]);

        // Test PDF generation
        $pdf = Pdf::loadView('pdf.invoice', ['record' => $invoice])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

        $this->assertNotNull($pdf);
        
        // Test that PDF content is generated
        $output = $pdf->output();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    /** @test */
    public function it_can_access_invoice_pdf_from_filament_table_action()
    {
        // Create test data
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-TEST-001',
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => 'Test Customer',
            'subtotal' => 50000000,
            'total_pajak' => 5500000,
            'total_invoice' => 55500000,
            'status' => 'sent',
            'created_by' => $this->user->id
        ]);

        // Test accessing the invoice list page
        $response = $this->get('/admin/invoices');
        $response->assertStatus(200);
        
        // Test that the invoice appears in the list
        $response->assertSee($invoice->nomor_invoice);
        $response->assertSee($invoice->nama_pelanggan);
    }

    /** @test */
    public function it_can_access_invoice_pdf_from_view_page()
    {
        // Create test data
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-VIEW-001',
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => 'View Test Customer',
            'subtotal' => 75000000,
            'total_pajak' => 8250000,
            'total_invoice' => 83250000,
            'status' => 'draft',
            'created_by' => $this->user->id
        ]);

        // Test accessing the invoice view page
        $response = $this->get("/admin/invoices/{$invoice->id}");
        $response->assertStatus(200);
        
        // Test that invoice details are displayed
        $response->assertSee($invoice->nomor_invoice);
        $response->assertSee($invoice->nama_pelanggan);
        $response->assertSee('Cetak PDF');
    }

    /** @test */
    public function number_to_words_helper_works_correctly()
    {
        // Test basic numbers
        $this->assertEquals('nol', NumberToWords::convert(0));
        $this->assertEquals('satu', NumberToWords::convert(1));
        $this->assertEquals('sepuluh', NumberToWords::convert(10));
        $this->assertEquals('seratus', NumberToWords::convert(100));
        $this->assertEquals('seribu', NumberToWords::convert(1000));
        
        // Test larger numbers
        $this->assertEquals('satu juta', NumberToWords::convert(1000000));
        $this->assertEquals('seratus juta', NumberToWords::convert(100000000));
        
        // Test currency conversion
        $this->assertEquals('satu juta rupiah', NumberToWords::convertCurrency(1000000));
    }

    /** @test */
    public function invoice_pdf_template_renders_without_errors()
    {
        // Create minimal test data
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-TEMPLATE-001',
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => 'Template Test Customer',
            'alamat_pelanggan' => 'Jl. Test No. 123',
            'npwp_pelanggan' => '12.345.678.9-012.345',
            'subtotal' => 90000000,
            'total_pajak' => 9900000,
            'total_invoice' => 99900000,
            'total_terbayar' => 50000000,
            'sisa_tagihan' => 49900000,
            'status' => 'sent',
            'catatan' => 'Test invoice notes',
            'created_by' => $this->user->id
        ]);

        // Test that the view renders without errors
        $view = view('pdf.invoice', ['record' => $invoice]);
        $html = $view->render();
        
        $this->assertNotEmpty($html);
        $this->assertStringContainsString($invoice->nomor_invoice, $html);
        $this->assertStringContainsString($invoice->nama_pelanggan, $html);
        $this->assertStringContainsString('PT. LINTAS RIAU PRIMA', $html);
        $this->assertStringContainsString('INVOICE', $html);
    }

    /** @test */
    public function invoice_pdf_handles_missing_relationships_gracefully()
    {
        // Create invoice without related data
        $invoice = Invoice::factory()->create([
            'nomor_invoice' => 'INV-MINIMAL-001',
            'id_do' => null,
            'id_transaksi' => null,
            'tanggal_invoice' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(30),
            'nama_pelanggan' => 'Minimal Test Customer',
            'subtotal' => 25000000,
            'total_pajak' => 2750000,
            'total_invoice' => 27750000,
            'status' => 'draft',
            'created_by' => $this->user->id
        ]);

        // Test that PDF generation doesn't fail with missing relationships
        $pdf = Pdf::loadView('pdf.invoice', ['record' => $invoice])
            ->setPaper('a4', 'portrait');

        $this->assertNotNull($pdf);
        
        $output = $pdf->output();
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('%PDF', $output);
    }

    /** @test */
    public function invoice_status_displays_correctly_in_pdf()
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        
        foreach ($statuses as $status) {
            $invoice = Invoice::factory()->create([
                'nomor_invoice' => "INV-STATUS-{$status}",
                'status' => $status,
                'created_by' => $this->user->id
            ]);

            $view = view('pdf.invoice', ['record' => $invoice]);
            $html = $view->render();
            
            $this->assertStringContainsString("status-{$status}", $html);
        }
    }
}
