<?php

namespace App\Services;

use App\Models\TransaksiPenjualan;

use App\Support\Formatter;
use Illuminate\Support\Facades\Log;

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
                   "📍 Lokasi Muat: {$transaction->lokasi_muat}\n" .
                   "🚚 Kendaraan: {$vehicle->nopol} ({$vehicle->jenis})\n" .
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
    public function sendPenjualanApprovedNotification(TransaksiPenjualan $transaction, object $approver, ?string $senderAccount = null): ?array
    {
        // Assuming the salesperson is linked via a 'user' relationship on TransaksiPenjualan
        $salesperson = $transaction->user;

        if (empty($salesperson->hp)) {
            Log::warning("Cannot send 'Penjualan Approved' notification: Salesperson {$salesperson->name} has no phone number.");
            return null;
        }

        $message = "🎉 *Penjualan Disetujui!* 🎉\n\n" .
                   "Halo Bpk/Ibu *{$salesperson->name}*,\n" .
                   "Transaksi penjualan Anda dengan No. DO: *{$transaction->kode}* telah *disetujui* oleh Bpk/Ibu *{$approver->name}*.\n\n" .
                   "Detail transaksi:\n" .
                   "Total: Rp " . Formatter::number(123455678) . "\n" .
                   "Status: *Disetujui*\n\n" .
                   "Terima kasih atas kerja keras Anda!";

        return $this->starSender->send(
            receiverNumber: $salesperson->hp,
            message: $message,
            senderAccount: $senderAccount
        );
    }

    /**
     * Sends a "Penjualan Rejected" notification to the salesperson.
     *
     * @param TransaksiPenjualan $transaction The sales transaction object.
     * @param object $approver The user object who rejected the transaction.
     * @param string|null $note The reason for rejection.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendPenjualanRejectedNotification(TransaksiPenjualan $transaction, object $approver, ?string $note, ?string $senderAccount = null): ?array
    {
        $salesperson = $transaction->user;

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

    /**
     * Sends a "Penjualan Needs Revision" notification to the salesperson.
     *
     * @param TransaksiPenjualan $transaction The sales transaction object.
     * @param object $approver The user object who requested revision.
     * @param string|null $note The revision notes.
     * @param string|null $senderAccount The account to send from.
     * @return array|null The API response.
     */
    public function sendPenjualanNeedsRevisionNotification(TransaksiPenjualan $transaction, object $approver, ?string $note, ?string $senderAccount = null): ?array
    {
        $salesperson = $transaction->user;

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
}
