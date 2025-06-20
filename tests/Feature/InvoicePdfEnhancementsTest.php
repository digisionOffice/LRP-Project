<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\DeliveryOrder;
use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\User;
use App\Models\PenjualanDetail;
use App\Models\Item;
use App\Helpers\NumberToWords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class InvoicePdfEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->actingAs(User::factory()->create([
            'role' => 'admin'
        ]));
    }

    /** @test */
    public function invoice_pdf_template_renders_without_errors()
    {
        $invoice = $this->createCompleteInvoice();

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $this->assertNotEmpty($html);
        $this->assertStringContainsString($invoice->nomor_invoice, $html);
        $this->assertStringContainsString($invoice->nama_pelanggan, $html);
        $this->assertStringContainsString('PT. LINTAS RIAU PRIMA', $html);
        $this->assertStringContainsString('INVOICE', $html);
    }

    /** @test */
    public function invoice_pdf_displays_customer_information_correctly()
    {
        $invoice = $this->createCompleteInvoice([
            'nama_pelanggan' => 'Test PDF Customer',
            'alamat_pelanggan' => 'Test PDF Address 123',
            'npwp_pelanggan' => 'PDF123456789'
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $this->assertStringContainsString('Test PDF Customer', $html);
        $this->assertStringContainsString('Test PDF Address 123', $html);
        $this->assertStringContainsString('PDF123456789', $html);
    }

    /** @test */
    public function invoice_pdf_displays_financial_calculations_correctly()
    {
        $invoice = $this->createCompleteInvoice([
            'subtotal' => 100000000,
            'include_ppn' => true,
            'total_pajak' => 11000000,
            'total_invoice' => 111000000,
            'biaya_operasional_kerja' => 5000000,
            'include_operasional_kerja' => true,
            'biaya_pbbkb' => 2000000,
            'include_pbbkb' => true
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        // Check if amounts are formatted correctly
        $this->assertStringContainsString('100.000.000', $html); // Subtotal
        $this->assertStringContainsString('11.000.000', $html);  // Tax
        $this->assertStringContainsString('5.000.000', $html);   // Operational
        $this->assertStringContainsString('2.000.000', $html);   // PBBKB
    }

    /** @test */
    public function invoice_pdf_handles_line_items_correctly()
    {
        $invoice = $this->createCompleteInvoiceWithLineItems();

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        // Check if line items are displayed
        $this->assertStringContainsString('Test Item 1', $html);
        $this->assertStringContainsString('Test Item 2', $html);
        $this->assertStringContainsString('10.000', $html); // Price
        $this->assertStringContainsString('1.000', $html);  // Volume
    }

    /** @test */
    public function invoice_pdf_handles_fallback_data_correctly()
    {
        $invoice = $this->createCompleteInvoice([
            'subtotal' => 0,
            'total_invoice' => 0
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        // Should show fallback data
        $this->assertStringContainsString('Layanan Pengiriman BBM', $html);
        $this->assertNotEmpty($html);
    }

    /** @test */
    public function invoice_pdf_terbilang_conversion_works()
    {
        $invoice = $this->createCompleteInvoice([
            'total_invoice' => 111000000
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $expectedTerbilang = ucwords(NumberToWords::convert(111000000));
        $this->assertStringContainsString($expectedTerbilang, $html);
        $this->assertStringContainsString('rupiah', $html);
    }

    /** @test */
    public function invoice_pdf_logo_integration_works()
    {
        $invoice = $this->createCompleteInvoice();
        
        // Test with logo
        $logoBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => $logoBase64]);
        $html = $view->render();
        
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString($logoBase64, $html);
        
        // Test without logo (fallback)
        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $this->assertStringContainsString('LINTAS<br>RIAU<br>PRIMA', $html);
    }

    /** @test */
    public function invoice_pdf_dates_are_formatted_correctly()
    {
        $invoiceDate = now()->setDate(2024, 6, 15);
        $dueDate = $invoiceDate->copy()->addDays(30);
        
        $invoice = $this->createCompleteInvoice([
            'tanggal_invoice' => $invoiceDate,
            'tanggal_jatuh_tempo' => $dueDate
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $this->assertStringContainsString('15/06/2024', $html);
        $this->assertStringContainsString('15/07/2024', $html);
    }

    /** @test */
    public function invoice_pdf_delivery_order_information_displays()
    {
        $deliveryOrder = DeliveryOrder::factory()->create([
            'kode' => 'DO-PDF-TEST',
            'no_segel' => 'SEAL-123',
            'volume_do' => 10000
        ]);
        
        $invoice = $this->createCompleteInvoice([
            'id_do' => $deliveryOrder->id
        ]);

        $view = view('pdf.invoice', ['record' => $invoice, 'logoBase64' => '']);
        $html = $view->render();
        
        $this->assertStringContainsString('DO-PDF-TEST', $html);
        $this->assertStringContainsString('SEAL-123', $html);
        $this->assertStringContainsString('10.000', $html);
    }

    /** @test */
    public function number_to_words_helper_works_with_large_amounts()
    {
        // Test various amounts
        $testCases = [
            1000000 => 'satu juta',
            100000000 => 'seratus juta',
            111000000 => 'seratus sebelas juta',
            1500000000 => 'satu miliar lima ratus juta'
        ];

        foreach ($testCases as $amount => $expected) {
            $result = NumberToWords::convert($amount);
            $this->assertEquals($expected, $result, "Failed for amount: {$amount}");
        }
    }

    /** @test */
    public function invoice_pdf_handles_conditional_sections()
    {
        // Test with PPN included
        $invoiceWithPpn = $this->createCompleteInvoice([
            'include_ppn' => true,
            'total_pajak' => 11000000
        ]);

        $view = view('pdf.invoice', ['record' => $invoiceWithPpn, 'logoBase64' => '']);
        $html = $view->render();
        $this->assertStringContainsString('PPN (11%)', $html);

        // Test without PPN
        $invoiceWithoutPpn = $this->createCompleteInvoice([
            'include_ppn' => false,
            'total_pajak' => 0
        ]);

        $view = view('pdf.invoice', ['record' => $invoiceWithoutPpn, 'logoBase64' => '']);
        $html = $view->render();
        $this->assertStringNotContainsString('PPN (11%)', $html);
    }

    private function createCompleteInvoice(array $attributes = []): Invoice
    {
        $pelanggan = Pelanggan::factory()->create([
            'nama' => 'PDF Test Customer',
            'alamat' => 'PDF Test Address',
            'npwp' => '123456789'
        ]);

        $transaksi = TransaksiPenjualan::factory()->create([
            'id_pelanggan' => $pelanggan->id,
            'kode' => 'SO-PDF-001',
            'nomor_po' => 'PO-PDF-001'
        ]);

        $deliveryOrder = DeliveryOrder::factory()->create([
            'id_transaksi' => $transaksi->id,
            'kode' => 'DO-PDF-001'
        ]);

        return Invoice::factory()->create(array_merge([
            'nomor_invoice' => 'INV-PDF-001',
            'id_do' => $deliveryOrder->id,
            'id_transaksi' => $transaksi->id,
            'nama_pelanggan' => $pelanggan->nama,
            'alamat_pelanggan' => $pelanggan->alamat,
            'npwp_pelanggan' => $pelanggan->npwp,
            'subtotal' => 100000000,
            'total_pajak' => 11000000,
            'total_invoice' => 111000000,
            'include_ppn' => true
        ], $attributes));
    }

    private function createCompleteInvoiceWithLineItems(): Invoice
    {
        $invoice = $this->createCompleteInvoice();
        
        $item1 = Item::factory()->create(['nama' => 'Test Item 1']);
        $item2 = Item::factory()->create(['nama' => 'Test Item 2']);

        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $invoice->id_transaksi,
            'id_item' => $item1->id,
            'volume_item' => 1000,
            'harga_jual' => 10000
        ]);

        PenjualanDetail::factory()->create([
            'id_transaksi_penjualan' => $invoice->id_transaksi,
            'id_item' => $item2->id,
            'volume_item' => 500,
            'harga_jual' => 15000
        ]);

        return $invoice->load('transaksiPenjualan.penjualanDetails.item');
    }
}
