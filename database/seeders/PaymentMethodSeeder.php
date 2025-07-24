<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This will populate the payment_methods table with default values.
     */
    public function run(): void
    {
        // Use DB::statement to disable foreign key checks temporarily, which can be safer for seeding.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaymentMethod::truncate(); // Optional: Clears the table before seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $paymentMethods = [
            [
                'method_name' => 'bank_bni_utama',
                'bank_name' => 'Bank BNI',
                'account_number' => str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT),
                'account_name' => 'PT. Lintas Riau Prima',
                'is_active' => true,
            ],
            [
                'method_name' => 'bank_mandiri',
                'bank_name' => 'Bank Mandiri',
                'account_number' => str_pad(mt_rand(1, 9999999999999), 13, '0', STR_PAD_LEFT),
                'account_name' => 'PT. Lintas Riau Prima',
                'is_active' => true,
            ],
            [
                'method_name' => 'bank_bca',
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_number' => str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT),
                'account_name' => 'PT. Lintas Riau Prima',
                'is_active' => true,
            ],
            [
                'method_name' => 'bank_bri',
                'bank_name' => 'Bank Rakyat Indonesia (BRI)',
                'account_number' => str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT),
                'account_name' => 'PT. Lintas Riau Prima',
                'is_active' => true,
            ],
            [
                'method_name' => 'kas_kecil_kantor',
                'bank_name' => null,
                'account_number' => null,
                'account_name' => 'Kasir Kantor',
                'is_active' => true,
            ],
        ];

        // Loop through the data and create records
        foreach ($paymentMethods as $method) {
            // Use updateOrCreate to prevent duplicates if the seeder is run multiple times
            PaymentMethod::updateOrCreate(
                ['method_name' => $method['method_name']], // The unique key to check
                $method  // The data to insert or update
            );
        }
    }
}
