<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surat;
use App\Models\Pelanggan;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SuratSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get or create test user
        $user = User::firstOrCreate([
            'email' => 'admin.staff@lrp.com'
        ], [
            'name' => 'Admin Test',
            'password' => bcrypt('password'),
        ]);

        // Get existing customers and suppliers
        $customers = Pelanggan::all();
        $suppliers = Supplier::all();

        // Create test documents
        $documentTypes = ['penawaran', 'kontrak', 'invoice', 'lainnya'];
        $statuses = ['draft', 'approved', 'rejected'];
        $paymentStatuses = ['belum bayar', 'sudah bayar', 'terlambat'];

        for ($i = 1; $i <= 15; $i++) {
            $documentType = $documentTypes[array_rand($documentTypes)];
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Randomly assign to customer or supplier
            $isCustomerDocument = rand(0, 1);
            $customer = $isCustomerDocument && $customers->count() > 0 ? $customers->random() : null;
            $supplier = !$isCustomerDocument && $suppliers->count() > 0 ? $suppliers->random() : null;

            $document = Surat::create([
                'nomor_surat' => $this->generateDocumentNumber($documentType, $i),
                'jenis_surat' => $documentType,
                'tanggal_surat' => now()->subDays(rand(0, 90)),
                'id_pelanggan' => $customer?->id,
                'id_supplier' => $supplier?->id,
                'isi_surat' => $this->generateDocumentContent($documentType, $customer, $supplier),
                'status' => $status,
                'status_pembayaran' => $paymentStatus,
                'tanggal_pembayaran' => $paymentStatus === 'sudah bayar' ? now()->subDays(rand(0, 30)) : null,
                'created_by' => $user->id,
            ]);

            // Simulate file upload for some documents (60% chance)
            if (rand(1, 100) <= 60) {
                $this->createDummyFile($document, $documentType);
            }
        }

        $this->command->info('Surat seeder completed! Created 15 test documents.');
    }

    /**
     * Generate document number based on type
     */
    private function generateDocumentNumber(string $type, int $sequence): string
    {
        $prefix = match($type) {
            'penawaran' => 'QUO',
            'kontrak' => 'CTR',
            'invoice' => 'INV',
            'lainnya' => 'DOC',
            default => 'DOC',
        };

        return $prefix . '-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate document content based on type
     */
    private function generateDocumentContent(string $type, $customer, $supplier): string
    {
        $partyName = $customer ? $customer->nama : ($supplier ? $supplier->nama : 'Unknown Party');

        return match($type) {
            'penawaran' => "Dengan hormat,\n\nBersama ini kami sampaikan penawaran harga untuk kebutuhan BBM kepada {$partyName}.\n\nDemikian penawaran ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.\n\nHormat kami,\nManagement",

            'kontrak' => "KONTRAK KERJASAMA\n\nPada hari ini telah dibuat dan ditandatangani kontrak kerjasama antara perusahaan dengan {$partyName} untuk pengadaan dan distribusi BBM.\n\nKontrak ini berlaku selama 12 bulan terhitung dari tanggal penandatanganan.\n\nDemikian kontrak ini dibuat untuk dilaksanakan dengan sebaik-baiknya.",

            'invoice' => "INVOICE\n\nKepada: {$partyName}\n\nBerdasarkan pengiriman BBM yang telah dilakukan, dengan ini kami sampaikan tagihan sebagai berikut:\n\n- Pengiriman BBM sesuai DO\n- Jumlah yang harus dibayar tercantum dalam lampiran\n\nMohon pembayaran dapat dilakukan sesuai dengan termin yang telah disepakati.\n\nTerima kasih atas kerjasamanya.",

            'lainnya' => "Kepada: {$partyName}\n\nDengan hormat,\n\nBersama ini kami sampaikan dokumen terkait dengan kegiatan operasional perusahaan.\n\nMohon untuk dapat diproses sesuai dengan prosedur yang berlaku.\n\nDemikian disampaikan, terima kasih atas perhatiannya.\n\nHormat kami,\nManagement",

            default => "Dokumen resmi perusahaan untuk {$partyName}.",
        };
    }

    /**
     * Create dummy file for document
     */
    private function createDummyFile(Surat $document, string $type): void
    {
        // Create documents directory if it doesn't exist
        if (!Storage::disk('public')->exists('documents')) {
            Storage::disk('public')->makeDirectory('documents');
        }

        // Generate dummy file content
        $content = "DUMMY DOCUMENT FILE\n";
        $content .= "Document Number: {$document->nomor_surat}\n";
        $content .= "Type: {$type}\n";
        $content .= "Date: {$document->tanggal_surat}\n";
        $content .= "Status: {$document->status}\n";
        $content .= "\nThis is a dummy file created for testing purposes.\n";
        $content .= "In a real application, this would be an actual document file.\n";

        // Create file with appropriate extension
        $extensions = ['pdf', 'doc', 'docx'];
        $extension = $extensions[array_rand($extensions)];
        $filename = "documents/document-{$document->id}-{$document->nomor_surat}.{$extension}";

        // Store the dummy file
        Storage::disk('public')->put($filename, $content);

        // Update the document record
        $document->update(['file_dokumen' => $filename]);
    }
}
