<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;

class ExpenseAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding expense accounts...');

        $expenseAccounts = [
            [
                'kode_akun' => '1110',
                'nama_akun' => 'Kas & Bank',
                'kategori_akun' => 'Aset',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
            [
                'kode_akun' => '5110',
                'nama_akun' => 'Beban Perawatan Kendaraan',
                'kategori_akun' => 'Beban',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
            [
                'kode_akun' => '5120',
                'nama_akun' => 'Beban Lisensi & Perizinan',
                'kategori_akun' => 'Beban',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
            [
                'kode_akun' => '5130',
                'nama_akun' => 'Beban Perjalanan Dinas',
                'kategori_akun' => 'Beban',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
            [
                'kode_akun' => '5140',
                'nama_akun' => 'Beban Utilitas',
                'kategori_akun' => 'Beban',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
            [
                'kode_akun' => '5150',
                'nama_akun' => 'Beban Lain-lain',
                'kategori_akun' => 'Beban',
                'tipe_akun' => 'Debit',
                'saldo_awal' => 0,
                'created_by' => 1,
            ],
        ];

        foreach ($expenseAccounts as $account) {
            Akun::firstOrCreate(
                ['kode_akun' => $account['kode_akun']],
                $account
            );
        }

        $this->command->info('Expense accounts seeded successfully!');
    }
}
