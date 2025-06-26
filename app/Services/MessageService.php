<?php

namespace App\Services;

use App\Support\Formatter;
use Illuminate\Support\Facades\Log;

// models call
use App\Models\TransaksiPenjualan;
use App\Models\ExpenseRequest;
class MessageService
{
    public function __construct(protected StarSenderService $starSender)
    {
    }

    /**
     * Sends a test message. Can be called with an optional sender.
     */
    public function sendTestMessage(?string $senderAccount = null): ?array
    {
        $testReceiverNumber = '6285274897212';
        $message = "🧪 PING - UJI COBA PESAN\n\n" .
                   "Pesan ini dikirim dari akun sender: " . ($senderAccount ?? config('services.starsender.default_sender')) . ".\n" . // Menggunakan default sender dari config jika tidak disediakan
                   "Waktu: " . Formatter::dateTime(now());
                   
        Log::info("Attempting to send test message to {$testReceiverNumber}.");
        
        return $this->starSender->send(
            receiverNumber: $testReceiverNumber,
            message: $message,
            senderAccount: $senderAccount // Pass the sender, will use default if null
        );
    }

    /**
     * Formats and sends a "Driver Assignment" notification.
     *
     * @param object $driver The driver object.
     * @param object $transaction The transaction object.
     * @param object $vehicle The vehicle object.
     * @param string $redirectUrl The full URL for the driver to be redirected to.
     * @param string|null $senderAccount The account to send from ('ilham', 'indra', etc). Defaults to 'indra'.
     * @return array|null The API response.
     */
    public function sendDriverAssignmentNotification(object $driver, object $transaction, object $vehicle, string $redirectUrl, ?string $senderAccount = null): ?array
    {
        if (empty($driver->hp)) {
            Log::warning("Cannot send assignment notification: Driver {$driver->nama} has no phone number.");
            return null;
        }

        $message = "⭐ *Penugasan Pengiriman Baru* ⭐\n\n" .
                   "Halo Bpk. *{$driver->nama}*,\n" .
                   "Anda mendapatkan tugas pengiriman baru dengan detail sebagai berikut:\n\n" .
                   "📋 *No. DO: {$transaction->kode}*\n" .
                   "👤 Pelanggan: {$transaction->pelanggan_nama}\n" .
                   "📍 Lokasi Muat (TBBM): {$transaction->lokasi_muat}\n" .
                   "🚚 Kendaraan: {$vehicle->no_pol_kendaraan} ({$vehicle->merk} {$vehicle->tipe})\n" .
                   "📅 Tgl. Penjemputan: *" . Formatter::date($transaction->tanggal_jemput) . "*\n\n" .
                   "Untuk melihat detail tugas, silakan klik link di bawah ini:\n" .
                   $redirectUrl . "\n\n" . // Using the provided URL directly
                   "Terima kasih dan selamat bekerja! 💪";

        // Pass the senderAccount to the StarSenderService
        return $this->starSender->send(
            receiverNumber: $driver->hp,
            message: $message,
            senderAccount: $senderAccount // It will use the default 'indra' if this is null
        );
    }

    /**
     * Formats and sends a "Driver Return to Pool" notification.
     *
     * @param object $driver The driver object.
     * @param object $transaction The transaction object related to the completed task.
     * @param string $redirectUrl Optional URL for more details.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendDriverReturnToPoolNotification(object $driver, object $transaction, string $redirectUrl, ?string $senderAccount = null): ?array
    {
        if (empty($driver->hp)) {
            Log::warning("Cannot send return notification: Driver {$driver->nama} has no phone number.");
            return null;
        }

        $message = "✅ *Driver Telah Kembali ke Pool* ✅\n\n" .
                   "Informasi Kepulangan Driver:\n\n" .
                   "👤 Driver: Bpk. *{$driver->nama}*\n" .
                   "🚚 No. DO: *{$transaction->kode}*\n" .
                   "Telah kembali ke pool setelah menyelesaikan tugas penjemputan.\n\n" .
                   "🕒 Waktu Lapor Kembali: " . Formatter::dateTime(now()) . "\n\n" .
                   "Untuk detail lebih lanjut atau tindakan selanjutnya, silakan periksa sistem atau klik link di bawah ini (jika tersedia):\n" .
                   $redirectUrl . "\n\n" .
                   "Terima kasih.";

        return $this->starSender->send(
            receiverNumber: $driver->hp, // Or a specific operations number
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Uang Jalan Siap" notification to the driver.
     *
     * @param object $driver The driver object (nama, hp).
     * @param object $transaction The transaction object (kode).
     * @param string $jumlahUangJalan Formatted amount of money.
     * @param string $tanggalProses Date of processing.
     * @param string $linkKonfirmasi URL for confirmation.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendUangJalanNotification(object $driver, object $transaction, string $jumlahUangJalan, string $tanggalProses, string $linkKonfirmasi, ?string $senderAccount = null): ?array
    {
        if (empty($driver->hp)) {
            Log::warning("Cannot send Uang Jalan notification: Driver {$driver->nama} has no phone number.");
            return null;
        }

        $message = "💰 *Informasi Uang Jalan* 💰\n\n" .
                   "Halo Bpk. *{$driver->nama}*,\n" .
                   "Uang jalan untuk perjalanan dengan No. DO: *{$transaction->kode}* telah kami proses/transfer.\n" .
                   "Silakan periksa rekening Anda atau hubungi administrasi untuk pengambilan.\n\n" .
                   "Jumlah: Rp {$jumlahUangJalan}\n" .
                   "Tanggal: {$tanggalProses}\n\n" .
                   "Mohon segera konfirmasi penerimaan melalui link berikut atau balas pesan ini:\n" .
                   $linkKonfirmasi . "\n\n" .
                   "Terima kasih & selamat bertugas!";

        return $this->starSender->send(
            receiverNumber: $driver->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Konfirmasi Penerimaan Uang Jalan" notification to administration.
     *
     * @param object $driver The driver object (nama).
     * @param object $transaction The transaction object (kode).
     * @param string $waktuKonfirmasi Timestamp of confirmation.
     * @param string $metodeKonfirmasi Method of confirmation.
     * @param string $adminPhoneNumber The admin's phone number.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendKonfirmasiUangJalanToAdminNotification(object $driver, object $transaction, string $waktuKonfirmasi, string $metodeKonfirmasi, string $adminPhoneNumber, ?string $senderAccount = null): ?array
    {
        $message = "✅ *Konfirmasi Penerimaan Uang Jalan* ✅\n\n" .
                   "Driver Bpk. *{$driver->nama}* telah mengkonfirmasi penerimaan uang jalan untuk No. DO: *{$transaction->kode}*.\n\n" .
                   "Waktu Konfirmasi: {$waktuKonfirmasi}\n" .
                   "Metode Konfirmasi: {$metodeKonfirmasi}\n\n" .
                   "Informasi ini telah tercatat dalam sistem.";

        return $this->starSender->send(
            receiverNumber: $adminPhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Selesai Pengiriman & Serah Terima" notification to operations/admin.
     *
     * @param object $driver The driver object (nama).
     * @param object $transaction The transaction object (kode, pelanggan_nama, lokasi_kirim).
     * @param string $waktuSerahTerima Timestamp of handover.
     * @param string|null $catatanDriver Optional notes from driver.
     * @param string $opsPhoneNumber Operations phone number.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendPengirimanSelesaiNotification(object $driver, object $transaction, string $waktuSerahTerima, ?string $catatanDriver, string $opsPhoneNumber, ?string $senderAccount = null): ?array
    {
        $message = "🚚 *Laporan Selesai Pengiriman* 🚚\n\n" .
                   "Driver Bpk. *{$driver->nama}* telah menyelesaikan pengiriman dan serah terima barang kepada pelanggan untuk:\n\n" .
                   "No. DO: *{$transaction->kode}*\n" .
                   "Pelanggan: *{$transaction->pelanggan_nama}*\n" .
                   "Lokasi Kirim: *{$transaction->lokasi_kirim}*\n" .
                   "Waktu Serah Terima: {$waktuSerahTerima}\n\n" .
                   ($catatanDriver ? "Catatan dari Driver: {$catatanDriver}\n\n" : "") .
                   "Driver sedang dalam perjalanan kembali ke pool.";

        return $this->starSender->send(
            receiverNumber: $opsPhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Driver Kembali ke Pool (Pasca Pengiriman)" notification to operations.
     * This is distinct from the initial return after pickup.
     *
     * @param object $driver The driver object (nama).
     * @param object $transaction The transaction object (kode).
     * @param string $opsPhoneNumber Operations phone number.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendDriverReturnPostDeliveryNotification(object $driver, object $transaction, string $opsPhoneNumber, ?string $senderAccount = null): ?array
    {
        $message = "🏠 *Driver Telah Kembali ke Pool (Pasca Pengiriman)* 🏠\n\n" .
                   "Driver Bpk. *{$driver->nama}* telah kembali ke pool setelah menyelesaikan seluruh rangkaian tugas untuk No. DO: *{$transaction->kode}*.\n\n" .
                   "Waktu Lapor Kembali: " . now()->format('d F Y, H:i:s') . "\n\n" .
                   "Mohon tim operasional untuk melakukan pengecekan akhir dokumen dan kendaraan."; // now()->format diubah menjadi Formatter::dateTime(now())

        return $this->starSender->send(
            receiverNumber: $opsPhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "DO Siap untuk Invoicing" notification from Operations to Finance.
     *
     * @param object $transaction The transaction object (kode, pelanggan_nama).
     * @param string $petugasOps Name of the operations staff.
     * @param string $linkDetailDo Link to DO details in the system.
     * @param string $financePhoneNumber Finance team phone number.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendDoReadyForInvoiceNotification(object $transaction, string $petugasOps, string $linkDetailDo, string $financePhoneNumber, ?string $senderAccount = null): ?array
    {
        $message = "🧾 *DO Siap untuk Proses Invoice* 🧾\n\n" .
                   "Kepada Tim Keuangan,\n" .
                   "Dokumen Order (DO) dengan detail berikut telah selesai diproses oleh tim operasional dan siap untuk tahap penagihan (invoicing):\n\n" .
                   "No. DO: *{$transaction->kode}*\n" .
                   "Pelanggan: *{$transaction->pelanggan_nama}*\n" .
                   "Tanggal Selesai Operasional: " . Formatter::date(now()) . "\n" .
                   "Petugas Operasional: {$petugasOps}\n\n" .
                   "Mohon untuk segera diproses pembuatan Invoice dan Faktur Pajak (jika ada).\n" .
                   "Link Detail DO: {$linkDetailDo}\n\n" .
                   "Terima kasih.";

        return $this->starSender->send(
            receiverNumber: $financePhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends an invoice notification to the customer with an attachment.
     *
     * @param object $customer The customer contact object (hp, nama_kontak, nama_perusahaan).
     * @param object $invoice The invoice object (nomor_invoice, nomor_do, tanggal_invoice, jumlah_tagihan, jatuh_tempo).
     * @param string $invoicePdfUrl The URL to the PDF invoice file for attachment.
     * @param string $invoiceOnlineLink A direct link to view the invoice online.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendInvoiceToCustomerNotification(object $customer, object $invoice, string $invoicePdfUrl, string $invoiceOnlineLink, ?string $senderAccount = null): ?array
    {
        if (empty($customer->hp)) {
            Log::warning("Cannot send invoice: Customer {$customer->nama_perusahaan} has no phone number for contact {$customer->nama_kontak}.");
            return null;
        }

        $message = "📄 *Invoice Pengiriman {$invoice->nomor_invoice}* 📄\n\n" .
                   "Yth. Bpk/Ibu {$customer->nama_kontak} / Tim Keuangan {$customer->nama_perusahaan},\n\n" .
                   "Bersama ini kami kirimkan invoice untuk transaksi dengan detail sebagai berikut:\n" .
                   "Nomor Invoice: *{$invoice->nomor_invoice}*\n" .
                   "No. DO Terkait: *{$invoice->nomor_do}*\n" .
                   "Tanggal Invoice: " . Formatter::date($invoice->tanggal_invoice) . "\n" .
                   "Jumlah Tagihan: Rp " . Formatter::number($invoice->jumlah_tagihan) . "\n" .
                   "Jatuh Tempo: " . Formatter::date($invoice->jatuh_tempo) . "\n\n" .
                   "File invoice (PDF) terlampir bersama pesan ini. Mohon dapat segera diproses pembayarannya.\n" .
                   "Untuk kemudahan, Anda juga dapat mengakses invoice melalui link: {$invoiceOnlineLink}\n\n" .
                   "Jika ada pertanyaan, jangan ragu untuk menghubungi kami.\n" .
                   "Terima kasih atas kerjasamanya.\n\n" .
                   "Hormat kami,\n" .
                   "Tim Keuangan " . config('app.name');

        Log::info("Attempting to send invoice {$invoice->nomor_invoice} to {$customer->hp}. PDF: {$invoicePdfUrl}");

        return $this->starSender->send(
            receiverNumber: $customer->hp,
            message: $message,
            fileUrl: $invoicePdfUrl,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Permintaan Konfirmasi Penerimaan Invoice" to the customer.
     *
     * @param object $customer The customer contact object (hp, nama_kontak, nama_perusahaan).
     * @param object $invoice The invoice object (nomor_invoice, nomor_do, tanggal_kirim_invoice).
     * @param string $linkKonfirmasi URL for customer to confirm.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendInvoiceReceiptConfirmationRequest(object $customer, object $invoice, string $linkKonfirmasi, ?string $senderAccount = null): ?array
    {
        if (empty($customer->hp)) {
            Log::warning("Cannot send invoice confirmation request: Customer {$customer->nama_perusahaan} has no phone number for contact {$customer->nama_kontak}.");
            return null;
        }

        $message = "📨 *Konfirmasi Penerimaan Invoice {$invoice->nomor_invoice}* 📨\n\n" .
                   "Yth. Bpk/Ibu {$customer->nama_kontak} / Tim Keuangan {$customer->nama_perusahaan},\n\n" .
                   "Kami ingin memastikan bahwa Anda telah menerima dengan baik invoice kami:\n" .
                   "Nomor Invoice: *{$invoice->nomor_invoice}*\n" .
                   "No. DO Terkait: *{$invoice->nomor_do}*\n" .
                   "Dikirim pada: " . Formatter::date($invoice->tanggal_kirim_invoice) . "\n\n" .
                   "Mohon kesediaannya untuk memberikan konfirmasi penerimaan dengan membalas pesan ini atau melalui link:\n" .
                   $linkKonfirmasi . "\n\n" .
                   "Konfirmasi Anda sangat berarti bagi kami untuk kelancaran administrasi.\n" .
                   "Terima kasih.";

        return $this->starSender->send(
            receiverNumber: $customer->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Pembayaran Invoice Diterima" notification to the customer.
     *
     * @param object $customer The customer contact object (hp, nama_kontak, nama_perusahaan).
     * @param object $invoice The invoice object (nomor_invoice).
     * @param string $jumlahDibayar Formatted amount paid.
     * @param string $tanggalPembayaran Date of payment.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendPembayaranDiterimaNotification(object $customer, object $invoice, string $jumlahDibayar, string $tanggalPembayaran, ?string $senderAccount = null): ?array
    {
        if (empty($customer->hp)) {
            Log::warning("Cannot send payment received notification: Customer {$customer->nama_perusahaan} has no phone number for contact {$customer->nama_kontak}.");
            return null;
        }

        $message = "🎉 *Konfirmasi Pembayaran Diterima - Invoice {$invoice->nomor_invoice}* 🎉\n\n" .
                   "Yth. Bpk/Ibu {$customer->nama_kontak} / Tim Keuangan {$customer->nama_perusahaan},\n\n" .
                   "Terima kasih! Kami telah menerima pembayaran Anda untuk:\n" .
                   "Nomor Invoice: *{$invoice->nomor_invoice}*\n" .
                   "Jumlah Dibayar: Rp {$jumlahDibayar}\n" .
                   "Tanggal Pembayaran: {$tanggalPembayaran}\n\n" .
                   "Status invoice Anda kini telah LUNAS.\n" .
                   "Kami sangat menghargai kerjasama dan pembayaran tepat waktu Anda.\n\n" .
                   "Hormat kami,\n" .
                   "Tim Keuangan " . config('app.name');

        return $this->starSender->send(
            receiverNumber: $customer->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends an "Internal Invoice Paid" notification.
     *
     * @param object $invoice The invoice object (nomor_invoice, pelanggan_nama, jumlah_tagihan).
     * @param string $tanggalLunas Date the invoice was paid.
     * @param string $internalRecipientPhoneNumber Phone number for internal notification.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendInvoiceLunasInternalNotification(object $invoice, string $tanggalLunas, string $internalRecipientPhoneNumber, ?string $senderAccount = null): ?array
    {
        $message = "💸 *Update Pembayaran: Invoice LUNAS* 💸\n\n" .
                   "Informasi Pembayaran Invoice:\n" .
                   "Nomor Invoice: *{$invoice->nomor_invoice}*\n" .
                   "Pelanggan: *{$invoice->pelanggan_nama}*\n" .
                   "Jumlah: Rp " . Formatter::number($invoice->jumlah_tagihan) . "\n" .
                   "Tanggal Lunas: {$tanggalLunas}\n\n" .
                   "Status: *LUNAS*\n\n" .
                   "Mohon dicatat untuk keperluan laporan dan tindak lanjut (jika ada).";

        return $this->starSender->send(
            receiverNumber: $internalRecipientPhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Penjualan Approved" notification to the salesperson.
     *
     * @param TransaksiPenjualan $transaction The sales transaction object.
     * @param object $approver The user object who approved the transaction.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendPenjualanApprovedNotification(TransaksiPenjualan $transaction, object $salesperson, object $approver, ?string $senderAccount = null): ?array
    {   
        $totalAmount = $transaction->total_amount ?? 0;

        if (empty($salesperson->hp)) {
            // Sebaiknya tambahkan ID untuk logging yang lebih mudah
            $salespersonId = $salesperson->id ?? 'N/A';
            Log::warning("Cannot send 'Penjualan Approved' notification: Salesperson {$salesperson->name} (ID: {$salespersonId}) has no phone number.");
            return null;
        }

        $message = "🎉 *Penjualan Disetujui!* 🎉\n\n" .
                   "Halo Bpk/Ibu *{$salesperson->name}*,\n" .
                   "Transaksi penjualan Anda dengan No. DO: *{$transaction->kode}* telah *disetujui* oleh Bpk/Ibu *{$approver->name}*.\n\n" .
                   "Detail transaksi:\n" .
                   "Total: Rp " . Formatter::number($totalAmount) . "\n" .
                   "Status: *Disetujui*\n\n" .
                   "Terima kasih atas kerja keras Anda!";

        return $this->starSender->send(
            receiverNumber: $salesperson->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    public function sendPenjualanRejectedNotification(TransaksiPenjualan $transaction, object $salesperson, object $approver, ?string $note, ?string $senderAccount = null): ?array
    {
        if (empty($salesperson->hp)) {
            Log::warning("Cannot send 'Penjualan Rejected' notification: Salesperson {$salesperson->name} has no phone number.");
            return null;
        }

        $message = "❌ *Penjualan Ditolak!* ❌\n\n" .
                "Halo Bpk/Ibu *{$salesperson->name}*,\n" .
                "Transaksi penjualan Anda dengan No. DO: *{$transaction->kode}* telah *ditolak* oleh Bpk/Ibu *{$approver->name}*.\n\n" .
                ($note ? "Alasan: {$note}\n\n" : "") .
                "Mohon periksa kembali detail transaksi dan hubungi tim terkait jika ada pertanyaan.";

        return $this->starSender->send(
            receiverNumber: $salesperson->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    public function sendPenjualanNeedsRevisionNotification(TransaksiPenjualan $transaction, object $salesperson, object $approver, ?string $note, ?string $senderAccount = null): ?array
    {
        if (empty($salesperson->hp)) {
            Log::warning("Cannot send 'Penjualan Needs Revision' notification: Salesperson {$salesperson->name} has no phone number.");
            return null;
        }

        $message = "📝 *Penjualan Membutuhkan Revisi* 📝\n\n" .
                "Halo Bpk/Ibu *{$salesperson->name}*,\n" .
                "Transaksi penjualan Anda dengan No. DO: *{$transaction->kode}* membutuhkan *revisi*.\n" .
                "Keputusan ini diberikan oleh Bpk/Ibu *{$approver->name}*.\n\n" .
                ($note ? "Catatan Revisi: {$note}\n\n" : "") .
                "Mohon segera perbaiki transaksi Anda sesuai catatan dan ajukan kembali untuk persetujuan.";

        return $this->starSender->send(
            receiverNumber: $salesperson->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a notification about a new sales transaction requiring approval.
     *
     * @param TransaksiPenjualan $transaction The newly created transaction.
     * @param string $recipientPhoneNumber The phone number of the approver/manager.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendNewPenjualanNotification(TransaksiPenjualan $transaction, string $recipientPhoneNumber, ?string $senderAccount = null): ?array
    {
        // Eager load relationships to avoid N+1 issues if not already loaded
        $transaction->loadMissing(['pelanggan', 'createdBy']);

        $salespersonName = $transaction->createdBy->name ?? 'N/A';
        $customerName = $transaction->pelanggan->nama ?? 'N/A';
        $totalAmount = $transaction->total_amount ?? 0;

        // Generate a link to the view page in Filament
        $viewUrl = route('filament.admin.resources.transaksi-penjualans.view', ['record' => $transaction->id]);

        $message = "🔔 *Transaksi Penjualan Baru* 🔔\n\n" .
                   "Halo,\n" .
                   "Ada transaksi penjualan baru yang membutuhkan persetujuan Anda.\n\n" .
                   "📝 *Detail Transaksi:*\n" .
                   "No. Transaksi: *{$transaction->kode}*\n" .
                   "Salesperson: *{$salespersonName}*\n" .
                   "Pelanggan: *{$customerName}*\n" .
                   "Total: *Rp " . Formatter::number($totalAmount) . "*\n\n" .
                   "Mohon segera ditinjau dan diproses melalui link berikut:\n" .
                   $viewUrl . "\n\n" .
                   "Terima kasih.";

        Log::info("Attempting to send new transaction notification for {$transaction->kode} to {$recipientPhoneNumber}.");

        return $this->starSender->send(
            receiverNumber: $recipientPhoneNumber,
            message: $message,
            senderAccount: $senderAccount
        );
    }


    // ==========================================================================================================================================================================
    // expense request ==========================================================================================================================================================

    // new expense made
    public function sendNewExpenseNotification(object $managerData, object $requesterData, object $expenseData, ?string $senderAccount = null): ?array
    {
        if (empty($managerData->hp)) {
            Log::warning("Cannot send expense notification: Designated manager {$managerData->name} has no phone number.");
            return null;
        }

        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "💵 *Approval Permintaan Biaya Baru* 💵\n\n" .
                   "Halo Bpk/Ibu *{$managerData->name}*,\n" .
                   "Ada permintaan biaya baru dari *{$requesterData->name}* yang membutuhkan persetujuan Anda.\n\n" .
                   "🧾 *Detail Permintaan:*\n" .
                   "Judul: {$expenseData->title}\n" .
                   "Jumlah: *Rp " . Formatter::number($expenseData->amount) . "*\n\n" .
                   "Mohon segera ditinjau melalui link berikut:\n" .
                   $viewUrl;

        Log::info("Formatted new expense notification for manager {$managerData->name}. Calling StarSender.");

        // Call the actual sender service
        return $this->starSender->send(
            receiverNumber: $managerData->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    public function sendExpenseApprovedNotification(object $requesterData, object $approverData, object $expenseData, ?string $senderAccount = null): ?array
    {
        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "✅ *Permintaan Biaya Disetujui Manager* ✅\n\n" .
                   "Halo Bpk/Ibu *{$requesterData->name}*,\n" .
                   "Kabar baik! Permintaan biaya Anda dengan No. *{$expenseData->request_number}* telah *DISETUJUI* oleh Bpk/Ibu *{$approverData->name}*.\n\n" .
                   "Jumlah Disetujui: *Rp " . Formatter::number($expenseData->approved_amount) . "*\n" .
                   "Silakan lihat detailnya di sini:\n" .
                   $viewUrl;
        
        return $this->starSender->send($requesterData->hp, $message, $senderAccount);
    }

    public function sendExpenseRejectedNotification(object $requesterData, object $approverData, object $expenseData, ?string $senderAccount = null): ?array
    {
        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "❌ *Permintaan Biaya Ditolak* ❌\n\n" .
                   "Halo Bpk/Ibu *{$requesterData->name}*,\n" .
                   "Mohon maaf, permintaan biaya Anda dengan No. *{$expenseData->request_number}* telah *DITOLAK* oleh Bpk/Ibu *{$approverData->name}*.\n\n" .
                   "Alasan Penolakan: *{$expenseData->note}*\n\n" .
                   "Untuk detail lebih lanjut, silakan buka link berikut:\n" .
                   $viewUrl;
        
        return $this->starSender->send($requesterData->hp, $message, $senderAccount);
    }

    public function sendExpenseNeedsRevisionNotification(object $requesterData, object $approverData, object $expenseData, ?string $senderAccount = null): ?array
    {
        $editUrl = route('filament.admin.resources.expense-requests.edit', ['record' => $expenseData->id]);

        $message = "📝 *Permintaan Biaya Butuh Revisi* 📝\n\n" .
                   "Halo Bpk/Ibu *{$requesterData->name}*,\n" .
                   "Permintaan biaya Anda dengan No. *{$expenseData->request_number}* membutuhkan *revisi* dari Bpk/Ibu *{$approverData->name}*.\n\n" .
                   "Catatan untuk Revisi: *{$expenseData->note}*\n\n" .
                   "Mohon segera perbaiki permintaan Anda melalui link berikut:\n" .
                   $editUrl;
        
        return $this->starSender->send($requesterData->hp, $message, $senderAccount);
    }

    /**
     * Sends a notification to the finance team about an approved expense request.
     *
     * @param object $financeUserData Object with finance user's data (name, hp).
     * @param object $requesterData Object with the original requester's data (name).
     * @param object $approverData Object with the approver's data (name).
     * @param object $expenseData Object with the approved expense details.
     * @param string|null $senderAccount
     * @return array|null
     */
    public function sendFinanceNotificationForApprovedExpense(object $financeUserData, object $requesterData, object $approverData, object $expenseData, ?string $senderAccount = null): ?array
    {
        if (empty($financeUserData->hp)) {
            Log::warning("Cannot send notification to Finance: User {$financeUserData->name} has no phone number.");
            return null;
        }

        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "💰 *Permintaan Biaya Siap Diproses* 💰\n\n" .
                   "Halo Bpk/Ibu *{$financeUserData->name}*,\n" .
                   "Sebuah permintaan biaya telah disetujui dan siap untuk proses pembayaran.\n\n" .
                   "📄 *Detail Persetujuan:*\n" .
                   "No. Request: *{$expenseData->request_number}*\n" .
                   "Pemohon: *{$requesterData->name}*\n" .
                   "Disetujui Oleh: *{$approverData->name}*\n" .
                   "Jumlah Disetujui: *Rp " . Formatter::number($expenseData->approved_amount) . "*\n\n" .
                   "Mohon segera lakukan proses selanjutnya melalui link berikut:\n" .
                   $viewUrl . "\n\n" .
                   "Terima kasih.";
        
        return $this->starSender->send(
            receiverNumber: $financeUserData->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a notification to the requester that their expense request has been paid.
     *
     * @param object $requesterData Object with requester's data (name, hp).
     * @param object $expenseData Object with expense details (request_number, title, approved_amount, paid_at, id).
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendExpensePaidNotificationToRequester(object $requesterData, object $expenseData, ?string $senderAccount = null): ?array
    {
        if (empty($requesterData->hp)) {
            Log::warning("Cannot send expense paid notification to requester: Requester {$requesterData->name} has no phone number.");
            return null;
        }

        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "✅ *Pembayaran Permintaan Biaya Anda Telah Diproses!* ✅\n\n" .
                   "Halo Bpk/Ibu *{$requesterData->name}*,\n" .
                   "Kami informasikan bahwa permintaan biaya Anda dengan detail berikut telah *DIBAYAR*:\n\n" .
                   "No. Request: *{$expenseData->request_number}*\n" .
                   "Judul: *{$expenseData->title}*\n" .
                   "Jumlah Dibayar: *Rp " . Formatter::number($expenseData->approved_amount) . "*\n" .
                   "Tanggal Pembayaran: *" . Formatter::date($expenseData->paid_at) . "*\n\n" .
                   "Untuk melihat detail lengkap, silakan kunjungi:\n" .
                   $viewUrl . "\n\n" .
                   "Terima kasih.";

        Log::info("Attempting to send expense paid notification to requester {$requesterData->name} for request {$expenseData->request_number}.");
        return $this->starSender->send($requesterData->hp, $message, $senderAccount);
    }

    /**
     * Sends a notification to the requester's manager that an expense request from their team member has been paid.
     *
     * @param object $managerData Object with manager's data (name, hp).
     * @param object $requesterData Object with requester's data (name).
     * @param object $expenseData Object with expense details (request_number, title, approved_amount, paid_at, id).
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendExpensePaidNotificationToManager(object $managerData, object $requesterData, object $expenseData, ?string $senderAccount = null): ?array
    {
        if (empty($managerData->hp)) {
            Log::warning("Cannot send expense paid notification to manager: Manager {$managerData->name} has no phone number.");
            return null;
        }

        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);

        $message = "💸 *Update Pembayaran Permintaan Biaya* 💸\n\n" .
                   "Halo Bpk/Ibu *{$managerData->name}*,\n" .
                   "Permintaan biaya dari staf Anda, *{$requesterData->name}* (No. Request: *{$expenseData->request_number}*), telah *DIBAYAR*.\n\n" .
                   "Judul: *{$expenseData->title}*\n" .
                   "Jumlah Dibayar: *Rp " . Formatter::number($expenseData->approved_amount) . "*\n" .
                   "Tanggal Pembayaran: *" . Formatter::date($expenseData->paid_at) . "*\n\n" .
                   "Detail dapat dilihat di:\n" . $viewUrl;

        Log::info("Attempting to send expense paid notification to manager {$managerData->name} for requester {$requesterData->name}'s request {$expenseData->request_number}.");
        return $this->starSender->send($managerData->hp, $message, $senderAccount);
    }

    /**
     * Sends a unified notification to the requester's manager about an expense request update.
     * This method handles both new requests and paid requests for managers.
     *
     * @param object $managerData Object with manager's data (name, hp).
     * @param object $requesterData Object with requester's data (name).
     * @param object $expenseData Object with expense details (request_number, title, amount/approved_amount, paid_at, id).
     * @param string $updateType Type of update ('new_request', 'paid').
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendExpenseManagerUpdateNotification(object $managerData, object $requesterData, object $expenseData, string $updateType, ?string $senderAccount = null): ?array
    {
        if (empty($managerData->hp)) {
            Log::warning("Cannot send expense manager update notification: Manager {$managerData->name} has no phone number.");
            return null;
        }

        $viewUrl = route('filament.admin.resources.expense-requests.view', ['record' => $expenseData->id]);
        $message = "";

        switch ($updateType) {
            case 'new_request':
                $message = "💵 *Permintaan Biaya Baru* 💵\n\n" .
                        "Halo Bpk/Ibu *{$managerData->name}*,\n" .
                        "Ada permintaan biaya baru dari staf Anda, *{$requesterData->name}* yang membutuhkan persetujuan Anda.\n\n" .
                        "🧾 *Detail Permintaan:*\n" .
                        "No. Request: *{$expenseData->request_number}*\n" . 
                        "Judul: *{$expenseData->title}*\n" .
                        "Jumlah: *Rp " . Formatter::number($expenseData->amount) . "*\n\n" .
                        "Mohon segera ditinjau melalui link berikut:\n" .
                        $viewUrl;
                break;
            case 'paid':
                $message = "💸 *Update Pembayaran Permintaan Biaya* 💸\n\n" .
                        "Halo Bpk/Ibu *{$managerData->name}*,\n" .
                        "Permintaan biaya dari staf Anda, *{$requesterData->name}* (No. Request: *{$expenseData->request_number}*), telah *DIBAYAR*.\n\n" .
                        "Judul: *{$expenseData->title}*\n" .
                        "Jumlah Dibayar: *Rp " . Formatter::number($expenseData->approved_amount) . "*\n" .
                        "Tanggal Pembayaran: *" . Formatter::date($expenseData->paid_at) . "*\n\n" .
                        "Detail dapat dilihat di:\n" . $viewUrl;
                break;
            default:
                Log::warning("Unknown expense manager update type: {$updateType}");
                return null;
        }

        Log::info("Attempting to send expense manager update notification ({$updateType}) to manager {$managerData->name} for requester {$requesterData->name}'s request {$expenseData->request_number}.");
        return $this->starSender->send($managerData->hp, $message, $senderAccount);
    }

}
