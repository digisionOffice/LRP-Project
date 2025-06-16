<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MessageService;

class SendWaTestCommand extends Command
{
    /**
     * CHANGED: Added an optional --sender flag.
     */
    protected $signature = 'wa:test {type : The type of test message to send (penugasan-driver, driver-kembali, uang-jalan, konfirmasi-uang-jalan, pengiriman-selesai, driver-kembali-kirim, do-siap-invoice, kirim-invoice, konfirmasi-terima-invoice, pembayaran-diterima, invoice-lunas-internal, ping, all-sequence)}
                                    {--sender= : Specify a sender account (e.g., ilham, indra)}';
    protected $description = 'Sends various types of test WhatsApp messages or a sequence of all messages.';

    public function handle(MessageService $messageService): int
    {
        $type = $this->argument('type');
        // Get the sender from the option, it will be null if not provided
        $sender = $this->option('sender');

        $this->info("Dispatching '{$type}' test message...");
        if ($sender) {
            $this->info("--> Sending from account: '{$sender}'");
        } else {
            $this->info("--> Sending from default account: '" . config('services.starsender.default_sender') . "'");
        }


        switch ($type) {
            case 'penugasan-driver':
                $this->testPenugasanDriver($messageService, $sender);
                break;

            case 'driver-kembali':
                $this->testDriverReturnToPool($messageService, $sender);
                break;

            case 'uang-jalan':
                $this->testUangJalan($messageService, $sender);
                break;

            case 'konfirmasi-uang-jalan':
                $this->testKonfirmasiUangJalanAdmin($messageService, $sender);
                break;

            case 'pengiriman-selesai':
                $this->testPengirimanSelesai($messageService, $sender);
                break;

            case 'driver-kembali-kirim':
                $this->testDriverReturnPostDelivery($messageService, $sender);
                break;

            case 'do-siap-invoice':
                $this->testDoReadyForInvoice($messageService, $sender);
                break;

            case 'kirim-invoice':
                $this->testInvoiceToCustomer($messageService, $sender);
                break;

            case 'konfirmasi-terima-invoice':
                $this->testInvoiceReceiptConfirmationRequest($messageService, $sender);
                break;

            case 'pembayaran-diterima':
                $this->testPembayaranDiterima($messageService, $sender);
                break;

            case 'invoice-lunas-internal':
                $this->testInvoiceLunasInternal($messageService, $sender);
                break;

            case 'ping':
                $messageService->sendTestMessage($sender);
                $this->info('Ping test sent!');
                break;
            case 'all-sequence':
                $this->testAllSequence($messageService, $sender);
                break;
            default:
                $this->error("Invalid test type '{$type}'. See command help for available types.");
                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * CHANGED: Now accepts a $sender parameter.
     */
    private function testPenugasanDriver(MessageService $messageService, ?string $sender): void
    {
        // 1. Mock Data
        $driver = (object) ['nama' => 'Budi Santoso', 'hp' => '6285274897212'];
        $transaction = (object) ['kode' => 'DO-PKU-2025-06-17-001', 'pelanggan_nama' => 'PT. Sinar Jaya Abadi', 'lokasi_muat' => 'TBBM Sei Siak', 'tanggal_jemput' => '2025-06-17'];
        $vehicle = (object) ['nopol' => 'BM 1234 XY', 'jenis' => 'Truk Tangki 8000L'];
        
        // This is the simple redirect link we want to include
        $redirectUrl = 'https://your-app.com/driver/task/123';

        // 2. Call the service
        $response = $messageService->sendDriverAssignmentNotification($driver, $transaction, $vehicle, $redirectUrl, $sender);

        // 3. Report result
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message for "Penugasan Driver" sent successfully!');
            $this->line('API Response: ' . json_encode($response, JSON_PRETTY_PRINT));
        } else {
            $this->error('Failed to send test message. Please check the logs.');
        }
    }

    /**
     * Tests the "Driver Return to Pool" notification.
     */
    private function testDriverReturnToPool(MessageService $messageService, ?string $sender): void
    {
        // 1. Mock Data
        $driver = (object) ['nama' => 'Agus Setiawan', 'hp' => '6285274897212']; // Use a test number
        $transaction = (object) ['kode' => 'DO-PKU-2025-06-18-005'];
        
        // This is a placeholder link
        $redirectUrl = 'https://your-app.com/driver/activity/456';

        // 2. Call the service
        $response = $messageService->sendDriverReturnToPoolNotification($driver, $transaction, $redirectUrl, $sender);

        // 3. Report result
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message for "Driver Kembali ke Pool" sent successfully!');
            $this->line('API Response: ' . json_encode($response, JSON_PRETTY_PRINT));
        } else {
            $this->error('Failed to send test message for "Driver Kembali ke Pool". Please check the logs.');
        }
    }

    private function testUangJalan(MessageService $messageService, ?string $sender): void
    {
        $driver = (object) ['nama' => 'Driver UJ', 'hp' => '6285274897212'];
        $transaction = (object) ['kode' => 'DO-UJ-001'];
        $response = $messageService->sendUangJalanNotification($driver, $transaction, '1.500.000', now()->format('d F Y'), 'https://your-app.com/konfirmasi-uj/1', $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Uang Jalan Siap" sent successfully!');
        } else {
            $this->error('Failed to send "Uang Jalan Siap". Check logs.');
        }
    }

    private function testKonfirmasiUangJalanAdmin(MessageService $messageService, ?string $sender): void
    {
        $driver = (object) ['nama' => 'Driver UJ Konfirm'];
        $transaction = (object) ['kode' => 'DO-UJ-001'];
        $adminPhoneNumber = '6285274897212'; // Placeholder admin number
        $response = $messageService->sendKonfirmasiUangJalanToAdminNotification($driver, $transaction, now()->format('d F Y H:i:s'), 'Klik Link', $adminPhoneNumber, $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Konfirmasi Uang Jalan ke Admin" sent successfully!');
        } else {
            $this->error('Failed to send "Konfirmasi Uang Jalan ke Admin". Check logs.');
        }
    }

    private function testPengirimanSelesai(MessageService $messageService, ?string $sender): void
    {
        $driver = (object) ['nama' => 'Driver Kirim Selesai'];
        $transaction = (object) ['kode' => 'DO-KIRIM-002', 'pelanggan_nama' => 'PT. Maju Mundur', 'lokasi_kirim' => 'Gudang PT. Maju Mundur'];
        $opsPhoneNumber = '6285274897212'; // Placeholder ops number
        $response = $messageService->sendPengirimanSelesaiNotification($driver, $transaction, now()->format('d F Y H:i:s'), 'Barang diterima baik', $opsPhoneNumber, $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Pengiriman Selesai" sent successfully!');
        } else {
            $this->error('Failed to send "Pengiriman Selesai". Check logs.');
        }
    }

    private function testDriverReturnPostDelivery(MessageService $messageService, ?string $sender): void
    {
        $driver = (object) ['nama' => 'Driver Kembali Pasca Kirim'];
        $transaction = (object) ['kode' => 'DO-KIRIM-002'];
        $opsPhoneNumber = '6285274897212'; // Placeholder ops number
        $response = $messageService->sendDriverReturnPostDeliveryNotification($driver, $transaction, $opsPhoneNumber, $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Driver Kembali Pasca Pengiriman" sent successfully!');
        } else {
            $this->error('Failed to send "Driver Kembali Pasca Pengiriman". Check logs.');
        }
    }

    private function testDoReadyForInvoice(MessageService $messageService, ?string $sender): void
    {
        $transaction = (object) ['kode' => 'DO-KIRIM-002', 'pelanggan_nama' => 'PT. Maju Mundur'];
        $financePhoneNumber = '6285274897212'; // Placeholder finance number
        $response = $messageService->sendDoReadyForInvoiceNotification($transaction, 'Staff Operasional', 'https://your-app.com/do/DO-KIRIM-002', $financePhoneNumber, $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "DO Siap Invoice" sent successfully!');
        } else {
            $this->error('Failed to send "DO Siap Invoice". Check logs.');
        }
    }

    private function testInvoiceToCustomer(MessageService $messageService, ?string $sender): void
    {
        $customer = (object) ['hp' => '6285274897212', 'nama_kontak' => 'Bpk. Customer', 'nama_perusahaan' => 'PT. Pelanggan Setia'];
        $invoice = (object) ['nomor_invoice' => 'INV/2025/001', 'nomor_do' => 'DO-KIRIM-002', 'tanggal_invoice' => now()->toDateString(), 'jumlah_tagihan' => 5000000, 'jatuh_tempo' => now()->addDays(14)->toDateString()];
        $invoicePdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'; // Placeholder PDF
        $response = $messageService->sendInvoiceToCustomerNotification($customer, $invoice, $invoicePdfUrl, 'https://your-app.com/invoice/INV2025001', $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Kirim Invoice ke Pelanggan" sent successfully!');
        } else {
            $this->error('Failed to send "Kirim Invoice ke Pelanggan". Check logs.');
        }
    }

    private function testInvoiceReceiptConfirmationRequest(MessageService $messageService, ?string $sender): void
    {
        $customer = (object) ['hp' => '6285274897212', 'nama_kontak' => 'Bpk. Customer', 'nama_perusahaan' => 'PT. Pelanggan Setia'];
        $invoice = (object) ['nomor_invoice' => 'INV/2025/001', 'nomor_do' => 'DO-KIRIM-002', 'tanggal_kirim_invoice' => now()->toDateString()];
        $response = $messageService->sendInvoiceReceiptConfirmationRequest($customer, $invoice, 'https://your-app.com/konfirmasi-invoice/INV2025001', $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Konfirmasi Terima Invoice" sent successfully!');
        } else {
            $this->error('Failed to send "Konfirmasi Terima Invoice". Check logs.');
        }
    }

    private function testPembayaranDiterima(MessageService $messageService, ?string $sender): void
    {
        $customer = (object) ['hp' => '6285274897212', 'nama_kontak' => 'Bpk. Customer', 'nama_perusahaan' => 'PT. Pelanggan Setia'];
        $invoice = (object) ['nomor_invoice' => 'INV/2025/001'];
        $response = $messageService->sendPembayaranDiterimaNotification($customer, $invoice, '5.000.000', now()->format('d F Y'), $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Pembayaran Diterima" sent successfully!');
        } else {
            $this->error('Failed to send "Pembayaran Diterima". Check logs.');
        }
    }

    private function testInvoiceLunasInternal(MessageService $messageService, ?string $sender): void
    {
        $invoice = (object) ['nomor_invoice' => 'INV/2025/001', 'pelanggan_nama' => 'PT. Pelanggan Setia', 'jumlah_tagihan' => 5000000];
        $internalRecipientPhoneNumber = '6285274897212'; // Placeholder internal number
        $response = $messageService->sendInvoiceLunasInternalNotification($invoice, now()->format('d F Y'), $internalRecipientPhoneNumber, $sender);
        if ($response && ($response['success'] ?? false)) {
            $this->info('Test message "Invoice Lunas Internal" sent successfully!');
        } else {
            $this->error('Failed to send "Invoice Lunas Internal". Check logs.');
        }
    }

    private function testAllSequence(MessageService $messageService, ?string $sender): void
    {
        $this->info("Starting 'all-sequence' test. Each message will have a 5-second delay.");
        $delay = 5;

        $this->comment("\n[1/12] Testing Penugasan Driver...");
        $this->testPenugasanDriver($messageService, $sender);
        sleep($delay);

        $this->comment("\n[2/12] Testing Driver Kembali ke Pool (Penjemputan)...");
        $this->testDriverReturnToPool($messageService, $sender);
        sleep($delay);

        $this->comment("\n[3/12] Testing Uang Jalan Siap...");
        $this->testUangJalan($messageService, $sender);
        sleep($delay);

        $this->comment("\n[4/12] Testing Konfirmasi Penerimaan Uang Jalan (Admin)...");
        $this->testKonfirmasiUangJalanAdmin($messageService, $sender);
        sleep($delay);

        $this->comment("\n[5/12] Testing Selesai Pengiriman & Serah Terima...");
        $this->testPengirimanSelesai($messageService, $sender);
        sleep($delay);

        $this->comment("\n[6/12] Testing Driver Kembali ke Pool (Pasca Pengiriman)...");
        $this->testDriverReturnPostDelivery($messageService, $sender);
        sleep($delay);

        $this->comment("\n[7/12] Testing DO Siap untuk Invoicing...");
        $this->testDoReadyForInvoice($messageService, $sender);
        sleep($delay);

        $this->comment("\n[8/12] Testing Pengiriman Invoice ke Pelanggan...");
        $this->testInvoiceToCustomer($messageService, $sender);
        sleep($delay);

        $this->comment("\n[9/12] Testing Permintaan Konfirmasi Penerimaan Invoice...");
        $this->testInvoiceReceiptConfirmationRequest($messageService, $sender);
        sleep($delay);

        $this->comment("\n[10/12] Testing Pembayaran Invoice Diterima (Pelanggan)...");
        $this->testPembayaranDiterima($messageService, $sender);
        sleep($delay);

        $this->comment("\n[11/12] Testing Notifikasi Internal: Invoice Telah Dibayar...");
        $this->testInvoiceLunasInternal($messageService, $sender);
        sleep($delay);

        $this->comment("\n[12/12] Testing Ping...");
        $messageService->sendTestMessage($sender);
        $this->info('Ping test sent!');

        $this->info("\n'all-sequence' test completed.");
    }
}
