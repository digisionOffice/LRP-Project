<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseRequest;
use App\Models\User;

class ExpenseRequestTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding test expense requests...');

        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please seed users first.');
            return;
        }

        $categories = ['tank_truck_maintenance', 'license_fee', 'business_travel', 'utilities', 'other'];
        $statuses = ['draft', 'submitted', 'approved', 'paid'];

        for ($i = 1; $i <= 10; $i++) {
            $category = $categories[array_rand($categories)];
            $status = $statuses[array_rand($statuses)];
            $requestedBy = $users->random();
            $approvedBy = $status !== 'draft' ? $users->random() : null;

            // Get default account for category
            $defaultAccount = ExpenseRequest::getDefaultAccountForCategory($category);

            $requestedAmount = rand(500000, 5000000);
            $approvedAmount = in_array($status, ['approved', 'paid']) ? $requestedAmount * (0.8 + (rand(0, 40) / 100)) : null;

            $expenseRequest = ExpenseRequest::create([
                'request_number' => ExpenseRequest::generateRequestNumber($category),
                'category' => $category,
                'user_id' => $requestedBy->id,
                'title' => $this->generateTitle($category),
                'description' => $this->generateDescription($category),
                'requested_amount' => $requestedAmount,
                'approved_amount' => $approvedAmount,
                'status' => $status,
                'priority' => ['low', 'medium', 'high', 'urgent'][array_rand(['low', 'medium', 'high', 'urgent'])],
                'requested_date' => now()->subDays(rand(1, 30)),
                'needed_by_date' => now()->addDays(rand(7, 30)),
                'justification' => $this->generateJustification($category),
                'requested_by' => $requestedBy->id,
                'approved_by' => $approvedBy?->id,
                'account_id' => $defaultAccount?->id,
                'submitted_at' => $status !== 'draft' ? now()->subDays(rand(1, 20)) : null,
                'reviewed_at' => in_array($status, ['approved', 'rejected', 'paid']) ? now()->subDays(rand(1, 15)) : null,
                'approved_at' => in_array($status, ['approved', 'paid']) ? now()->subDays(rand(1, 10)) : null,
                'paid_at' => $status === 'paid' ? now()->subDays(rand(1, 5)) : null,
                'approval_notes' => in_array($status, ['approved', 'paid']) ? 'Approved as per company policy.' : null,
            ]);

            // Create journal entry for approved/paid requests
            if (in_array($status, ['approved', 'paid'])) {
                $journal = $expenseRequest->createJournalEntry();

                // Post journal if status is paid
                if ($status === 'paid' && $journal) {
                    $expenseRequest->postJournalEntry();
                }
            }
        }

        $this->command->info('Test expense requests seeded successfully!');
    }

    private function generateTitle(string $category): string
    {
        $titles = [
            'tank_truck_maintenance' => [
                'Perawatan Rutin Truk Tangki',
                'Perbaikan Sistem Hidrolik',
                'Ganti Oli dan Filter',
                'Service Berkala Kendaraan',
            ],
            'license_fee' => [
                'Perpanjangan Lisensi Software',
                'Biaya Perizinan Usaha',
                'Sertifikasi ISO',
                'Lisensi Antivirus Perusahaan',
            ],
            'business_travel' => [
                'Perjalanan Dinas ke Jakarta',
                'Kunjungan Klien di Surabaya',
                'Training di Bandung',
                'Meeting dengan Supplier',
            ],
            'utilities' => [
                'Tagihan Listrik Kantor',
                'Biaya Internet Bulanan',
                'Tagihan Air PDAM',
                'Biaya Telepon Kantor',
            ],
            'other' => [
                'Biaya Konsultasi Hukum',
                'Pembelian Alat Tulis Kantor',
                'Biaya Cleaning Service',
                'Pemeliharaan Taman Kantor',
            ],
        ];

        return $titles[$category][array_rand($titles[$category])];
    }

    private function generateDescription(string $category): string
    {
        $descriptions = [
            'tank_truck_maintenance' => 'Perawatan dan perbaikan kendaraan operasional untuk menjaga performa optimal.',
            'license_fee' => 'Pembayaran lisensi dan perizinan yang diperlukan untuk operasional perusahaan.',
            'business_travel' => 'Perjalanan dinas untuk keperluan bisnis dan pengembangan usaha.',
            'utilities' => 'Pembayaran utilitas kantor untuk mendukung operasional harian.',
            'other' => 'Pengeluaran operasional lainnya yang mendukung kegiatan perusahaan.',
        ];

        return $descriptions[$category];
    }

    private function generateJustification(string $category): string
    {
        $justifications = [
            'tank_truck_maintenance' => 'Diperlukan untuk menjaga kondisi kendaraan agar tetap optimal dan aman untuk operasional.',
            'license_fee' => 'Wajib dibayar untuk memenuhi regulasi dan menjaga legalitas operasional perusahaan.',
            'business_travel' => 'Diperlukan untuk pengembangan bisnis dan menjaga hubungan dengan klien/partner.',
            'utilities' => 'Kebutuhan dasar untuk mendukung operasional kantor sehari-hari.',
            'other' => 'Mendukung kelancaran operasional dan produktivitas perusahaan.',
        ];

        return $justifications[$category];
    }
}
